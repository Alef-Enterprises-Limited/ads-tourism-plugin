<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use AlefDigitalSolutions\ADSTourism\Application\ImportExport\CsvImportService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvReader;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRowValidationResult;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\DuplicatePolicy;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRunRepository;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRunStatus;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\TaxonomyImportMode;
use RuntimeException;
use Throwable;

final readonly class CsvImportController
{
    public const ACTION_BATCH = 'ads_tourism_import_batch';

    public const ACTION_PREVIEW = 'ads_tourism_import_preview';

    public const ACTION_UPLOAD = 'ads_tourism_import_upload';

    public const NONCE_ACTION = 'ads_tourism_csv_import';

    public function __construct(
        private CsvSchema $schema,
        private CsvReader $reader,
        private CsvImportService $imports,
        private ImportRunRepository $runs,
        private TransferFileManager $files,
        private TransferSettings $settings,
    ) {}

    public function upload(): void
    {
        $this->authorize();

        try {
            $contentType = $this->contentType($_POST['record_type'] ?? '');
            $file = $_FILES['csv_file'] ?? null;

            if (!is_array($file)) {
                throw new RuntimeException('Select a CSV file to upload.');
            }

            $stored = $this->files->storeUpload($file);
            $delimiter = $this->reader->detectDelimiter($stored['path']);
            $headers = $this->reader->headers($stored['path'], $delimiter);
            $totalRows = $this->reader->countRows($stored['path'], $delimiter);

            if ($totalRows < 1) {
                $this->files->delete($stored['path']);
                throw new RuntimeException('The CSV contains no data rows.');
            }

            $runId = $this->runs->createUpload(
                get_current_user_id(),
                $contentType,
                $stored['filename'],
                $stored['path'],
                $delimiter,
                $totalRows,
            );
            $columns = $this->schema->columns($contentType);
            $mapping = [];

            foreach ($headers as $header) {
                $normalized = $this->schema->normalizeHeader($header);
                $mapping[$header] = array_key_exists($normalized, $columns) ? $normalized : '';
            }

            wp_send_json_success([
                'run_id' => $runId,
                'headers' => $headers,
                'columns' => $columns,
                'mapping' => $mapping,
                'total_rows' => $totalRows,
                'delimiter' => $delimiter === "\t" ? 'tab' : $delimiter,
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }

    public function preview(): void
    {
        $this->authorize();

        try {
            $run = $this->run();
            $mapping = $this->mapping($_POST['mapping'] ?? '', $run->contentType);
            $duplicatePolicy = DuplicatePolicy::tryFrom($this->scalar($_POST['duplicate_policy'] ?? ''));
            $taxonomyMode = TaxonomyImportMode::tryFrom($this->scalar($_POST['taxonomy_mode'] ?? ''));

            if ($duplicatePolicy === null || $taxonomyMode === null) {
                throw new RuntimeException('Choose valid duplicate and taxonomy policies.');
            }

            $allowTermCreation = $taxonomyMode === TaxonomyImportMode::ADVANCED
                && $this->settings->allowTermCreation()
                && current_user_can('manage_categories')
                && rest_sanitize_boolean($_POST['allow_term_creation'] ?? false);
            $preview = $this->imports->preview(
                $run->sourcePath,
                $run->delimiter,
                $run->contentType,
                $mapping,
            );
            $previewRows = array_map(
                fn(CsvRowValidationResult $row): CsvRowValidationResult => $this->inspectTaxonomies(
                    $row,
                    $run->contentType,
                    $taxonomyMode,
                    $allowTermCreation,
                ),
                $preview->rows,
            );
            $validRows = count(array_filter(
                $previewRows,
                static fn(CsvRowValidationResult $row): bool => $row->isValid(),
            ));
            $rejectedPath = $this->files->rejectedPath($run->id);
            $this->runs->configure(
                $run->id,
                $mapping,
                $duplicatePolicy,
                $taxonomyMode,
                $allowTermCreation,
                $rejectedPath,
            );

            wp_send_json_success([
                'total_rows' => $preview->totalRows,
                'sample_valid_rows' => $validRows,
                'sample_invalid_rows' => count($previewRows) - $validRows,
                'sample_size' => count($previewRows),
                'rows' => array_map([$this, 'previewRow'], array_slice($previewRows, 0, 20)),
            ]);
        } catch (Throwable $exception) {
            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }

    public function batch(): void
    {
        $this->authorize();

        try {
            $run = $this->run();

            if (!in_array($run->status, [ImportRunStatus::VALIDATED, ImportRunStatus::RUNNING], true)) {
                throw new RuntimeException('Preview and validate this import before starting it.');
            }

            $result = $this->imports->importBatch(
                $run->sourcePath,
                $run->rejectedPath,
                $run->delimiter,
                $run->contentType,
                $run->mapping,
                $run->duplicatePolicy,
                $run->taxonomyMode,
                $run->allowTermCreation,
                $run->processedRows,
                $this->settings->batchSize(),
            );
            $this->runs->recordBatch($run->id, $result);
            $processed = $run->processedRows + $result->processed;
            $done = $processed >= $run->totalRows || $result->processed === 0;

            if ($done) {
                $this->runs->complete($run->id);
                $this->files->delete($run->sourcePath);
            }

            wp_send_json_success([
                'processed_rows' => $processed,
                'total_rows' => $run->totalRows,
                'imported_rows' => $run->importedRows + $result->imported,
                'updated_rows' => $run->updatedRows + $result->updated,
                'skipped_rows' => $run->skippedRows + $result->skipped,
                'rejected_rows' => $run->rejectedRows + $result->rejectedCount,
                'done' => $done,
            ]);
        } catch (Throwable $exception) {
            $runId = absint($_POST['run_id'] ?? 0);

            if ($runId > 0) {
                $ownedRun = $this->runs->findForUser($runId, get_current_user_id());

                if ($ownedRun !== null) {
                    $this->runs->fail($runId);
                }
            }

            wp_send_json_error(['message' => $exception->getMessage()], 400);
        }
    }

    private function authorize(): void
    {
        check_ajax_referer(self::NONCE_ACTION, 'nonce');

        if (!current_user_can('edit_others_posts')) {
            wp_send_json_error(['message' => __('You do not have permission to import tourism records.', 'ads-tourism')], 403);
        }
    }

    private function contentType(mixed $value): ContentType
    {
        $contentType = ContentType::tryFrom($this->scalar($value));

        if ($contentType === null) {
            throw new RuntimeException('Choose a valid tourism record type.');
        }

        return $contentType;
    }

    private function run(): \AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRun
    {
        $runId = absint($_POST['run_id'] ?? 0);
        $run = $this->runs->findForUser($runId, get_current_user_id());

        if ($run === null || !$this->files->isManagedPath($run->sourcePath) || !is_file($run->sourcePath)) {
            throw new RuntimeException('The import session has expired or is unavailable.');
        }

        return $run;
    }

    /**
     * @return array<string, string>
     */
    private function mapping(mixed $value, ContentType $contentType): array
    {
        $decoded = json_decode($this->scalar($value), true);

        if (!is_array($decoded)) {
            throw new RuntimeException('The CSV column mapping is invalid.');
        }

        $allowed = $this->schema->columns($contentType);
        $mapping = [];
        $targets = [];

        foreach ($decoded as $source => $target) {
            if (!is_string($source) || !is_string($target)) {
                continue;
            }

            $target = sanitize_key($target);

            if ($target !== '' && !array_key_exists($target, $allowed)) {
                throw new RuntimeException('The CSV mapping contains an unsupported target column.');
            }

            if ($target !== '' && isset($targets[$target])) {
                throw new RuntimeException('Each target field may be mapped only once.');
            }

            $mapping[sanitize_text_field($source)] = $target;

            if ($target !== '') {
                $targets[$target] = true;
            }
        }

        if (!isset($targets['external_id'], $targets['title'])) {
            throw new RuntimeException('Map both external_id and title before previewing the import.');
        }

        return $mapping;
    }

    /** @return array<string, mixed> */
    private function previewRow(CsvRowValidationResult $row): array
    {
        return [
            'row_number' => $row->rowNumber,
            'external_id' => $row->values['external_id'] ?? '',
            'title' => $row->values['title'] ?? '',
            'errors' => $row->errors,
            'warnings' => $row->warnings,
        ];
    }

    private function inspectTaxonomies(
        CsvRowValidationResult $row,
        ContentType $contentType,
        TaxonomyImportMode $mode,
        bool $allowTermCreation,
    ): CsvRowValidationResult {
        if ($mode !== TaxonomyImportMode::ADVANCED || !$row->isValid()) {
            return $row;
        }

        $errors = $row->errors;
        $warnings = $row->warnings;

        foreach ($row->values as $column => $value) {
            $taxonomy = $this->schema->taxonomyFromColumn($contentType, $column);

            if ($taxonomy === null || $value === '' || $value === CsvSchema::CLEAR_VALUE) {
                continue;
            }

            foreach (explode(CsvSchema::GALLERY_DELIMITER, $value) as $slug) {
                $slug = trim($slug);

                if (get_term_by('slug', $slug, $taxonomy->value) !== false) {
                    continue;
                }

                if ($allowTermCreation) {
                    $warnings[] = sprintf(
                        'Unknown %s slug %s will be created as “%s”.',
                        $taxonomy->value,
                        $slug,
                        ucwords(str_replace('-', ' ', $slug)),
                    );
                } else {
                    $errors[] = sprintf('Unknown %s term slug: %s.', $taxonomy->value, $slug);
                }
            }
        }

        return new CsvRowValidationResult($row->rowNumber, $row->values, $errors, $warnings);
    }

    private function scalar(mixed $value): string
    {
        return is_scalar($value) ? sanitize_text_field(wp_unslash((string) $value)) : '';
    }
}
