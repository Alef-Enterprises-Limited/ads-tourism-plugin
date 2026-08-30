<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

interface RejectedRowWriter
{
    /** @param list<CsvRowValidationResult> $rows */
    public function append(string $path, array $rows): void;
}
