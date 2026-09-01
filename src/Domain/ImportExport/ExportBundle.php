<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

final readonly class ExportBundle
{
    public function __construct(
        public string $path,
        public string $filename,
        public int $recordCount,
    ) {}
}
