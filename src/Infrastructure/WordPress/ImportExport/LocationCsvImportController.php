<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use AlefDigitalSolutions\ADSTourism\Application\Location\LocationService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvReader;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\LocationCsvSchema;
use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationPoint;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRole;
use RuntimeException;
use WP_Post;

final readonly class LocationCsvImportController
{
    public const ACTION = 'ads_tourism_import_locations';

    public const NONCE_ACTION = 'ads_tourism_import_locations';

    private const MAX_ROWS = 2000;

    public function __construct(
        private CsvReader $reader,
        private LocationCsvSchema $schema,
        private LocationService $locations,
    ) {}

    public function import(): void
    {
        if (!current_user_can('edit_others_posts')) {
            wp_die(esc_html__('You do not have permission to import tourism locations.', 'ads-tourism'));
        }

        check_admin_referer(self::NONCE_ACTION);
        $file = $_FILES['locations_csv'] ?? null;

        if (
            !is_array($file)
            || (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK
            || !is_scalar($file['tmp_name'] ?? null)
        ) {
            wp_die(esc_html__('Choose a locations CSV file to import.', 'ads-tourism'));
        }

        $path = (string) $file['tmp_name'];

        if (!is_uploaded_file($path)) {
            wp_die(esc_html__('The uploaded locations CSV file is invalid.', 'ads-tourism'));
        }

        try {
            $delimiter = $this->reader->detectDelimiter($path);
            $headers = $this->reader->headers($path, $delimiter);

            $expectedHeaders = $this->schema->headers();

            if (
                count($headers) !== count($expectedHeaders)
                || array_diff($headers, $expectedHeaders) !== []
                || array_diff($expectedHeaders, $headers) !== []
            ) {
                throw new RuntimeException(
                    'The locations CSV headers must be: ' . implode(', ', $expectedHeaders) . '.',
                );
            }

            $rows = $this->reader->readBatch($path, $delimiter, 0, self::MAX_ROWS + 1);

            if (count($rows) > self::MAX_ROWS) {
                throw new RuntimeException('The locations CSV exceeds the maximum of 2,000 rows.');
            }

            $grouped = [];

            foreach ($rows as $row) {
                if (isset($row->values['__row_error'])) {
                    throw new RuntimeException('Row ' . $row->number . ' has an invalid column count.');
                }

                $location = $this->location($row->values, $row->number);
                $key = $location['post']->ID;
                $grouped[$key] ??= [];
                $grouped[$key][] = $location['point'];
            }

            foreach ($grouped as $postId => $locations) {
                $this->locations->replace((int) $postId, $locations);
            }

            $url = add_query_arg([
                'page' => ImportExportAdminPage::SLUG,
                'ads_tourism_locations' => 'success',
                'count' => (string) count($rows),
            ], admin_url('admin.php'));
            wp_safe_redirect($url);
            exit;
        } catch (RuntimeException $exception) {
            wp_die(esc_html($exception->getMessage()));
        }
    }

    /**
     * @param array<string, string> $values
     * @return array{post: WP_Post, point: LocationPoint}
     */
    private function location(array $values, int $rowNumber): array
    {
        $contentType = ContentType::tryFrom(sanitize_key($values['record_type'] ?? ''));
        $externalId = trim($values['external_id'] ?? '');

        if ($contentType === null || $externalId === '') {
            throw new RuntimeException('Row ' . $rowNumber . ' requires a valid record_type and external_id.');
        }

        $posts = get_posts([
            'post_type' => $contentType->value,
            'post_status' => 'any',
            'posts_per_page' => 2,
            'meta_key' => 'ads_tourism_external_id',
            'meta_value' => $externalId,
        ]);

        if (count($posts) !== 1 || !$posts[0] instanceof WP_Post) {
            throw new RuntimeException(
                'Row ' . $rowNumber . ' references an external_id that does not resolve uniquely.',
            );
        }

        $latitude = $this->number($values['latitude'] ?? '');
        $longitude = $this->number($values['longitude'] ?? '');
        $role = LocationRole::tryFrom(sanitize_key($values['role'] ?? 'primary'));

        if ($latitude === null || $longitude === null || $role === null) {
            throw new RuntimeException('Row ' . $rowNumber . ' contains invalid coordinates or role.');
        }

        try {
            return [
                'post' => $posts[0],
                'point' => new LocationPoint(
                    $posts[0]->ID,
                    new Coordinates($latitude, $longitude),
                    sanitize_text_field($values['label'] ?? ''),
                    $role,
                    $this->boolean($values['is_primary'] ?? '0'),
                    $this->boolean($values['show_on_map'] ?? '1'),
                    max(0, (int) ($values['sort_order'] ?? 0)),
                ),
            ];
        } catch (\InvalidArgumentException $exception) {
            throw new RuntimeException('Row ' . $rowNumber . ': ' . $exception->getMessage());
        }
    }

    private function number(string $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }

    private function boolean(string $value): bool
    {
        return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on'], true);
    }
}
