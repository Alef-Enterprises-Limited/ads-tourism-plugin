<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

final readonly class CsvRowValidationResult
{
    /**
     * @param array<string, string> $values
     * @param list<string>          $errors
     * @param list<string>          $warnings
     */
    public function __construct(
        public int $rowNumber,
        public array $values,
        public array $errors = [],
        public array $warnings = [],
    ) {}

    public function isValid(): bool
    {
        return $this->errors === [];
    }
}
