<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

final class TransferSettings
{
    public const OPTION = 'ads_tourism_transfer_settings';

    public function registerSettings(): void
    {
        register_setting('ads_tourism_transfers', self::OPTION, [
            'type' => 'object',
            'default' => $this->defaults(),
            'sanitize_callback' => [$this, 'sanitize'],
        ]);
    }

    /** @param mixed $value */
    public function sanitize($value): array
    {
        $value = is_array($value) ? $value : [];

        return [
            'maximum_upload_mb' => max(1, min(50, absint($value['maximum_upload_mb'] ?? 5))),
            'batch_size' => max(5, min(100, absint($value['batch_size'] ?? 25))),
            'retention_hours' => max(1, min(168, absint($value['retention_hours'] ?? 24))),
            'allow_term_creation' => rest_sanitize_boolean($value['allow_term_creation'] ?? false),
        ];
    }

    public function maximumBytes(): int
    {
        return $this->integer('maximum_upload_mb') * 1024 * 1024;
    }

    public function batchSize(): int
    {
        return $this->integer('batch_size');
    }

    public function retentionSeconds(): int
    {
        return $this->integer('retention_hours') * HOUR_IN_SECONDS;
    }

    public function allowTermCreation(): bool
    {
        return (bool) $this->values()['allow_term_creation'];
    }

    /** @return array<string, int|bool> */
    public function values(): array
    {
        $value = get_option(self::OPTION, $this->defaults());

        return $this->sanitize($value);
    }

    /** @return array<string, int|bool> */
    private function defaults(): array
    {
        return [
            'maximum_upload_mb' => 5,
            'batch_size' => 25,
            'retention_hours' => 24,
            'allow_term_creation' => false,
        ];
    }

    private function integer(string $key): int
    {
        return (int) $this->values()[$key];
    }
}
