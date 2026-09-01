<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\DuplicatePolicy;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportBatchResult;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRun;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRunRepository;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRunStatus;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\TaxonomyImportMode;
use RuntimeException;
use stdClass;
use wpdb;

final readonly class WpdbImportRunRepository implements ImportRunRepository
{
    public function __construct(private wpdb $database) {}

    public function tableName(): string
    {
        return $this->database->prefix . 'ads_tourism_import_runs';
    }

    public function createUpload(
        int $userId,
        ContentType $contentType,
        string $sourceFilename,
        string $sourcePath,
        string $delimiter,
        int $totalRows,
    ): int {
        $inserted = $this->database->insert($this->tableName(), [
            'user_id' => $userId,
            'record_type' => $contentType->value,
            'schema_version' => \AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema::SCHEMA_VERSION,
            'status' => ImportRunStatus::UPLOADED->value,
            'source_filename' => $sourceFilename,
            'source_path' => $sourcePath,
            'delimiter' => $delimiter,
            'total_rows' => $totalRows,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);

        if ($inserted === false) {
            throw new RuntimeException('The import run could not be created.');
        }

        return $this->database->insert_id;
    }

    public function configure(
        int $runId,
        array $mapping,
        DuplicatePolicy $duplicatePolicy,
        TaxonomyImportMode $taxonomyMode,
        bool $allowTermCreation,
        string $rejectedPath,
    ): void {
        $encoded = wp_json_encode($mapping);

        if (!is_string($encoded)) {
            throw new RuntimeException('The CSV column mapping could not be encoded.');
        }

        $updated = $this->database->update(
            $this->tableName(),
            [
                'status' => ImportRunStatus::VALIDATED->value,
                'duplicate_policy' => $duplicatePolicy->value,
                'taxonomy_mode' => $taxonomyMode->value,
                'allow_term_creation' => $allowTermCreation ? 1 : 0,
                'rejected_path' => $rejectedPath,
                'mapping_json' => $encoded,
            ],
            ['id' => $runId],
        );

        if ($updated === false) {
            throw new RuntimeException('The import run could not be configured.');
        }
    }

    public function findForUser(int $runId, int $userId): ?ImportRun
    {
        $query = $this->database->prepare(
            "SELECT * FROM {$this->tableName()} WHERE id = %d AND user_id = %d",
            $runId,
            $userId,
        );
        $row = $this->database->get_row($query);

        return $row instanceof stdClass ? $this->hydrate($row) : null;
    }

    public function recordBatch(int $runId, ImportBatchResult $result): void
    {
        $query = $this->database->prepare(
            "UPDATE {$this->tableName()} SET
                status = %s,
                started_at = COALESCE(started_at, %s),
                processed_rows = processed_rows + %d,
                imported_rows = imported_rows + %d,
                updated_rows = updated_rows + %d,
                skipped_rows = skipped_rows + %d,
                rejected_rows = rejected_rows + %d
            WHERE id = %d",
            ImportRunStatus::RUNNING->value,
            gmdate('Y-m-d H:i:s'),
            $result->processed,
            $result->imported,
            $result->updated,
            $result->skipped,
            $result->rejectedCount,
            $runId,
        );

        if ($this->database->query($query) === false) {
            throw new RuntimeException('The import progress could not be recorded.');
        }
    }

    public function complete(int $runId): void
    {
        $this->setStatus($runId, ImportRunStatus::COMPLETED, true);
    }

    public function fail(int $runId): void
    {
        $this->setStatus($runId, ImportRunStatus::FAILED, true);
    }

    public function recent(int $limit = 20): array
    {
        $query = $this->database->prepare(
            "SELECT * FROM {$this->tableName()} ORDER BY created_at DESC LIMIT %d",
            max(1, min(100, $limit)),
        );
        $rows = $this->database->get_results($query);

        if (!is_array($rows)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn(mixed $row): ?ImportRun => $row instanceof stdClass ? $this->hydrate($row) : null,
            $rows,
        )));
    }

    public function deleteExpired(int $olderThanTimestamp): int
    {
        $query = $this->database->prepare(
            "DELETE FROM {$this->tableName()} WHERE created_at < %s",
            gmdate('Y-m-d H:i:s', $olderThanTimestamp),
        );
        $deleted = $this->database->query($query);

        return $deleted === false ? 0 : $deleted;
    }

    private function setStatus(int $runId, ImportRunStatus $status, bool $completed): void
    {
        $data = ['status' => $status->value];

        if ($completed) {
            $data['completed_at'] = gmdate('Y-m-d H:i:s');
        }

        $updated = $this->database->update($this->tableName(), $data, ['id' => $runId]);

        if ($updated === false) {
            throw new RuntimeException('The import status could not be updated.');
        }
    }

    private function hydrate(stdClass $row): ?ImportRun
    {
        $contentType = ContentType::tryFrom((string) ($row->record_type ?? ''));
        $status = ImportRunStatus::tryFrom((string) ($row->status ?? ''));
        $duplicatePolicy = DuplicatePolicy::tryFrom((string) ($row->duplicate_policy ?? ''));
        $taxonomyMode = TaxonomyImportMode::tryFrom((string) ($row->taxonomy_mode ?? ''));
        $mapping = json_decode((string) ($row->mapping_json ?? ''), true);

        if ($contentType === null || $status === null || $duplicatePolicy === null || $taxonomyMode === null) {
            return null;
        }

        $safeMapping = [];

        if (is_array($mapping)) {
            foreach ($mapping as $source => $target) {
                if (is_string($source) && is_string($target)) {
                    $safeMapping[$source] = $target;
                }
            }
        }

        return new ImportRun(
            (int) ($row->id ?? 0),
            (int) ($row->user_id ?? 0),
            $contentType,
            $status,
            $duplicatePolicy,
            $taxonomyMode,
            (bool) ($row->allow_term_creation ?? false),
            (string) ($row->source_filename ?? ''),
            (string) ($row->source_path ?? ''),
            (string) ($row->rejected_path ?? ''),
            (string) ($row->delimiter ?? ','),
            $safeMapping,
            (int) ($row->total_rows ?? 0),
            (int) ($row->processed_rows ?? 0),
            (int) ($row->imported_rows ?? 0),
            (int) ($row->updated_rows ?? 0),
            (int) ($row->skipped_rows ?? 0),
            (int) ($row->rejected_rows ?? 0),
        );
    }
}
