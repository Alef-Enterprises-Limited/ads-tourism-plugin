<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRunRepository;

final readonly class TransferMaintenance
{
    public const HOOK = 'ads_tourism_cleanup_transfers';

    public function __construct(
        private TransferFileManager $files,
        private TransferSettings $settings,
        private ImportRunRepository $runs,
    ) {}

    public function cleanup(): void
    {
        $this->files->cleanupExpired();
        $this->runs->deleteExpired(time() - $this->settings->retentionSeconds());
    }

    public function schedule(): void
    {
        if (wp_next_scheduled(self::HOOK) === false) {
            wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', self::HOOK);
        }
    }

    public function unschedule(): void
    {
        wp_clear_scheduled_hook(self::HOOK);
    }
}
