<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Support;

use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRowValidationResult;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\RejectedRowWriter;

final class InMemoryRejectedRowWriter implements RejectedRowWriter
{
    /** @var list<CsvRowValidationResult> */
    public array $rows = [];

    public function append(string $path, array $rows): void
    {
        $this->rows = [...$this->rows, ...$rows];
    }
}
