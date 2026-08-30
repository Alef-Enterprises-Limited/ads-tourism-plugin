<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

final readonly class ImportPreview
{
    /**
     * @param list<CsvRowValidationResult> $rows
     */
    public function __construct(
        public int $totalRows,
        public int $validRows,
        public int $invalidRows,
        public array $rows,
    ) {}
}
