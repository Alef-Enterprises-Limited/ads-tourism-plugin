<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

final readonly class CsvRow
{
    /**
     * @param array<string, string> $values
     */
    public function __construct(
        public int $number,
        public array $values,
    ) {}
}
