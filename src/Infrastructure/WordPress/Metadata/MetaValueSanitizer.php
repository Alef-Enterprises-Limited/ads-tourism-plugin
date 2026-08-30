<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Metadata;

use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldDefinition;
use AlefDigitalSolutions\ADSTourism\Domain\Field\FieldType;

final class MetaValueSanitizer
{
    public function sanitize(FieldDefinition $field, mixed $value): mixed
    {
        return match ($field->type) {
            FieldType::TEXT => sanitize_text_field((string) $value),
            FieldType::TEXTAREA => sanitize_textarea_field((string) $value),
            FieldType::EMAIL => sanitize_email((string) $value),
            FieldType::URL => $this->sanitizeUrl((string) $value),
            FieldType::DATE => $this->sanitizeDate((string) $value),
            FieldType::DATETIME => $this->sanitizeDateTime((string) $value),
            FieldType::INTEGER => absint($value),
            FieldType::NUMBER => is_numeric($value) ? (float) $value : null,
            FieldType::BOOLEAN => rest_sanitize_boolean($value),
            FieldType::SELECT => $this->sanitizeOption($field, (string) $value),
            FieldType::OBJECT => $this->sanitizeStructure($value, false),
            FieldType::ARRAY => $this->sanitizeStructure($value, true),
        };
    }

    private function sanitizeUrl(string $value): string
    {
        $value = trim($value);

        if (str_starts_with($value, '/') && !str_starts_with($value, '//')) {
            return '/' . ltrim(sanitize_text_field($value), '/');
        }

        return esc_url_raw($value, ['http', 'https']);
    }

    private function sanitizeDate(string $value): string
    {
        $value = sanitize_text_field($value);

        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1 ? $value : '';
    }

    private function sanitizeDateTime(string $value): string
    {
        $value = sanitize_text_field($value);

        return strtotime($value) !== false ? $value : '';
    }

    private function sanitizeOption(FieldDefinition $field, string $value): string
    {
        if (array_key_exists($value, $field->options)) {
            return $value;
        }

        return is_string($field->default) ? $field->default : '';
    }

    /**
     * @return array<array-key, mixed>
     */
    private function sanitizeStructure(mixed $value, bool $asList): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (!is_array($value)) {
            return [];
        }

        $sanitized = [];

        foreach ($value as $key => $item) {
            $safeKey = is_int($key) ? $key : sanitize_key($key);
            $sanitized[$safeKey] = is_array($item)
                ? $this->sanitizeStructure($item, array_is_list($item))
                : $this->sanitizeScalar($item);
        }

        return $asList ? array_values($sanitized) : $sanitized;
    }

    private function sanitizeScalar(mixed $value): string|int|float|bool|null
    {
        if (is_bool($value) || is_int($value) || is_float($value) || $value === null) {
            return $value;
        }

        return sanitize_textarea_field((string) $value);
    }
}
