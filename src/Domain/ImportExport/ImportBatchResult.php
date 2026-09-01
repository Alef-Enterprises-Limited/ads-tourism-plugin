<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

final readonly class ImportBatchResult
{
    /**
     * @param list<CsvRowValidationResult> $rejected
     */
    public function __construct(
        public int $processed,
        public int $imported,
        public int $updated,
        public int $skipped,
        public int $rejectedCount,
        public array $rejected,
    ) {}
}
