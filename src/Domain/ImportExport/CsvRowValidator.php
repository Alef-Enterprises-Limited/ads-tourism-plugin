<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldDefinition;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldType;

final readonly class CsvRowValidator
{
    public function __construct(private CsvSchema $schema) {}

    /**
     * @param array<string, string> $mapping Source header to canonical column.
     */
    public function validate(ContentType $contentType, CsvRow $row, array $mapping): CsvRowValidationResult
    {
        if (isset($row->values['__row_error'])) {
            return new CsvRowValidationResult($row->number, [], [$row->values['__row_error']]);
        }

        $allowed = $this->schema->columns($contentType);
        $values = [];
        $errors = [];
        $warnings = [];

        foreach ($mapping as $source => $target) {
            if ($target === '' || !array_key_exists($target, $allowed)) {
                continue;
            }

            $values[$target] = $row->values[$source] ?? '';
        }

        $externalId = $values['external_id'] ?? '';
        $title = $values['title'] ?? '';

        if ($externalId === '' || preg_match('/^[A-Za-z0-9._:-]{1,190}$/', $externalId) !== 1) {
            $errors[] = 'external_id is required and may contain letters, numbers, dots, underscores, colons, and hyphens.';
        }

        if ($title === '' || $title === CsvSchema::CLEAR_VALUE) {
            $errors[] = 'title is required.';
        }

        foreach ($values as $column => $value) {
            if ($value === '' || $value === CsvSchema::CLEAR_VALUE) {
                continue;
            }

            $field = $this->schema->field($contentType, $column);

            if ($field !== null) {
                $this->validateField($field, $value, $errors);
                continue;
            }

            if ($this->schema->isTaxonomyColumn($column)) {
                $this->validateTaxonomySlugs($column, $value, $errors);
                continue;
            }

            $this->validateSystemValue($column, $value, $errors, $warnings);
        }

        $this->validateFeaturedMedia($values, $errors);

        return new CsvRowValidationResult($row->number, $values, $errors, $warnings);
    }

    /**
     * @param list<string> $errors
     */
    private function validateField(FieldDefinition $field, string $value, array &$errors): void
    {
        $valid = match ($field->type) {
            FieldType::EMAIL => filter_var($value, FILTER_VALIDATE_EMAIL) !== false,
            FieldType::URL => $this->isSafeUrl($value),
            FieldType::DATE => preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1,
            FieldType::DATETIME => strtotime($value) !== false,
            FieldType::INTEGER => filter_var($value, FILTER_VALIDATE_INT) !== false,
            FieldType::NUMBER => is_numeric($value),
            FieldType::BOOLEAN => in_array(strtolower($value), ['0', '1', 'false', 'true', 'no', 'yes'], true),
            FieldType::SELECT => array_key_exists($value, $field->options),
            FieldType::OBJECT, FieldType::ARRAY => is_array(json_decode($value, true)),
            default => true,
        };

        if (!$valid) {
            $errors[] = sprintf('%s has an invalid value.', $field->key);
        }

        if ($field->type === FieldType::INTEGER && (int) $value < 0) {
            $errors[] = sprintf('%s cannot be negative.', $field->key);
        }

        if (str_ends_with($field->key, 'latitude') && ((float) $value < -90 || (float) $value > 90)) {
            $errors[] = 'Latitude must be between -90 and 90.';
        }

        if (str_ends_with($field->key, 'longitude') && ((float) $value < -180 || (float) $value > 180)) {
            $errors[] = 'Longitude must be between -180 and 180.';
        }
    }

    /**
     * @param list<string> $errors
     * @param list<string> $warnings
     */
    private function validateSystemValue(string $column, string $value, array &$errors, array &$warnings): void
    {
        if ($column === 'slug' && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $value) !== 1) {
            $errors[] = 'slug must use lowercase letters, numbers, and single hyphens.';
        }

        if ($column === 'featured_attachment_id' && ((int) $value <= 0 || !ctype_digit($value))) {
            $errors[] = 'featured_attachment_id must be a positive integer.';
        }

        if ($column === 'featured_media_url' && !$this->isSafeUrl($value)) {
            $errors[] = 'featured_media_url must be HTTPS or a site-relative path.';
        }

        if ($column === 'featured_media_url_type' && !in_array($value, ['absolute', 'relative'], true)) {
            $errors[] = 'featured_media_url_type must be absolute or relative.';
        }

        if ($column === 'gallery_urls') {
            foreach (explode(CsvSchema::GALLERY_DELIMITER, $value) as $url) {
                if (!$this->isSafeUrl(trim($url))) {
                    $errors[] = 'gallery_urls contains a URL that is not HTTPS or site-relative.';
                    break;
                }
            }
        }

        if ($column === 'parent_external_id' && $value !== '' && preg_match('/^[A-Za-z0-9._:-]{1,190}$/', $value) !== 1) {
            $warnings[] = 'parent_external_id is malformed and will not be linked.';
        }
    }

    /**
     * @param list<string> $errors
     */
    private function validateTaxonomySlugs(string $column, string $value, array &$errors): void
    {
        foreach (explode(CsvSchema::GALLERY_DELIMITER, $value) as $slug) {
            if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', trim($slug)) !== 1) {
                $errors[] = sprintf('%s contains an invalid term slug.', $column);
                break;
            }
        }
    }

    private function isSafeUrl(string $value): bool
    {
        if (str_starts_with($value, '/') && !str_starts_with($value, '//')) {
            return true;
        }

        return str_starts_with($value, 'https://') && filter_var($value, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * @param array<string, string> $values
     * @param list<string>          $errors
     */
    private function validateFeaturedMedia(array $values, array &$errors): void
    {
        $attachment = $values['featured_attachment_id'] ?? '';
        $url = $values['featured_media_url'] ?? '';
        $urlType = $values['featured_media_url_type'] ?? '';

        if (
            $attachment !== ''
            && $attachment !== CsvSchema::CLEAR_VALUE
            && $url !== ''
            && $url !== CsvSchema::CLEAR_VALUE
        ) {
            $errors[] = 'Use either featured_attachment_id or featured_media_url, not both.';
        }

        if ($url !== '' && $url !== CsvSchema::CLEAR_VALUE) {
            $expectedType = str_starts_with($url, '/') ? 'relative' : 'absolute';

            if ($urlType !== $expectedType) {
                $errors[] = sprintf('featured_media_url_type must be %s for this URL.', $expectedType);
            }
        }
    }
}
