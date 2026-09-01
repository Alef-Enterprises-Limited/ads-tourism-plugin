<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class ImportRun
{
    /**
     * @param array<string, string> $mapping
     */
    public function __construct(
        public int $id,
        public int $userId,
        public ContentType $contentType,
        public ImportRunStatus $status,
        public DuplicatePolicy $duplicatePolicy,
        public TaxonomyImportMode $taxonomyMode,
        public bool $allowTermCreation,
        public string $sourceFilename,
        public string $sourcePath,
        public string $rejectedPath,
        public string $delimiter,
        public array $mapping,
        public int $totalRows,
        public int $processedRows,
        public int $importedRows,
        public int $updatedRows,
        public int $skippedRows,
        public int $rejectedRows,
    ) {}
}
