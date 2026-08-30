<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

interface ImportRunRepository
{
    public function createUpload(
        int $userId,
        ContentType $contentType,
        string $sourceFilename,
        string $sourcePath,
        string $delimiter,
        int $totalRows,
    ): int;

    /** @param array<string, string> $mapping */
    public function configure(
        int $runId,
        array $mapping,
        DuplicatePolicy $duplicatePolicy,
        TaxonomyImportMode $taxonomyMode,
        bool $allowTermCreation,
        string $rejectedPath,
    ): void;

    public function findForUser(int $runId, int $userId): ?ImportRun;

    public function recordBatch(int $runId, ImportBatchResult $result): void;

    public function complete(int $runId): void;

    public function fail(int $runId): void;

    /** @return list<ImportRun> */
    public function recent(int $limit = 20): array;

    public function deleteExpired(int $olderThanTimestamp): int;
}
