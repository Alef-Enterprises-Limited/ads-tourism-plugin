<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSecurity;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ExportRequest;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRunRepository;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\LocationCsvSchema;
use RuntimeException;
use Throwable;

final readonly class CsvDownloadController
{
    public const ACTION_EXPORT = 'ads_tourism_export';

    public const ACTION_REJECTED = 'ads_tourism_rejected_rows';

    public const ACTION_TEMPLATE = 'ads_tourism_csv_template';

    public const ACTION_LOCATIONS_TEMPLATE = 'ads_tourism_locations_template';

    public const NONCE_ACTION = 'ads_tourism_csv_download';

    public function __construct(
        private CsvSchema $schema,
        private CsvSecurity $security,
        private CsvExportService $exports,
        private ImportRunRepository $runs,
        private TransferFileManager $files,
        private LocationCsvSchema $locationsSchema,
    ) {}

    public function template(): void
    {
        $this->authorize();
        $contentType = ContentType::tryFrom($this->requestString('record_type'));

        if ($contentType === null) {
            wp_die(esc_html__('Choose a valid tourism record type.', 'ads-tourism'));
        }

        $filename = 'ads-tourism-' . str_replace('ads_', '', $contentType->value) . '-template-v'
            . CsvSchema::SCHEMA_VERSION . '.csv';
        $this->csvHeaders($filename);
        $handle = fopen('php://output', 'wb');

        if ($handle === false) {
            wp_die(esc_html__('The CSV template could not be generated.', 'ads-tourism'));
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv(
            $handle,
            array_map($this->security->escapeForSpreadsheet(...), $this->schema->headers($contentType)),
            ',',
            '"',
            '',
        );
        fclose($handle);
        exit;
    }

    public function locationsTemplate(): void
    {
        $this->authorize();
        $this->csvHeaders('ads-tourism-locations-template-v' . LocationCsvSchema::VERSION . '.csv');
        $handle = fopen('php://output', 'wb');

        if ($handle === false) {
            wp_die(esc_html__('The locations CSV template could not be generated.', 'ads-tourism'));
        }

        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv(
            $handle,
            array_map($this->security->escapeForSpreadsheet(...), $this->locationsSchema->headers()),
            ',',
            '"',
            '',
        );
        fclose($handle);
        exit;
    }

    public function export(): void
    {
        $this->authorize();

        try {
            $typeValue = $this->requestString('record_type');
            $contentType = $typeValue === '' ? null : ContentType::tryFrom($typeValue);

            if ($typeValue !== '' && $contentType === null) {
                throw new RuntimeException('Choose a valid record type filter.');
            }

            $selected = array_values(array_filter(array_map(
                'absint',
                explode(',', $this->requestString('selected_post_ids')),
            )));
            $request = new ExportRequest(
                $contentType,
                $this->allowedStatus($this->requestString('post_status')),
                sanitize_key($this->requestString('verification_status')),
                $this->date($this->requestString('modified_after')),
                $this->date($this->requestString('modified_before')),
                $selected,
            );
            $bundle = $this->exports->createBundle($request);
            nocache_headers();
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . $bundle->filename . '"');
            header('Content-Length: ' . (string) filesize($bundle->path));
            readfile($bundle->path);
            $this->files->delete($bundle->path);
            exit;
        } catch (Throwable $exception) {
            wp_die(esc_html($exception->getMessage()));
        }
    }

    public function rejected(): void
    {
        $this->authorize();
        $run = $this->runs->findForUser(absint($_GET['run_id'] ?? 0), get_current_user_id());

        if ($run === null || $run->rejectedRows < 1 || !$this->files->isManagedPath($run->rejectedPath)) {
            wp_die(esc_html__('The rejected-row report is unavailable.', 'ads-tourism'));
        }

        $this->csvHeaders('ads-tourism-rejected-rows-' . $run->id . '.csv');
        readfile($run->rejectedPath);
        exit;
    }

    private function authorize(): void
    {
        check_admin_referer(self::NONCE_ACTION);

        if (!current_user_can('edit_others_posts')) {
            wp_die(esc_html__('You do not have permission to transfer tourism records.', 'ads-tourism'));
        }
    }

    private function csvHeaders(string $filename): void
    {
        nocache_headers();
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . sanitize_file_name($filename) . '"');
        header('X-Content-Type-Options: nosniff');
    }

    private function requestString(string $key): string
    {
        $value = $_REQUEST[$key] ?? '';

        return is_scalar($value) ? sanitize_text_field(wp_unslash((string) $value)) : '';
    }

    private function allowedStatus(string $value): string
    {
        return in_array($value, ['draft', 'pending', 'publish', 'private', 'trash'], true) ? $value : '';
    }

    private function date(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : '';
    }
}
