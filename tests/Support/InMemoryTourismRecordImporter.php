<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Support;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\DuplicatePolicy;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRecordResult;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\TaxonomyImportMode;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\TourismRecordImporter;

final class InMemoryTourismRecordImporter implements TourismRecordImporter
{
    /** @var list<array<string, string>> */
    public array $imported = [];

    public function import(
        ContentType $contentType,
        array $values,
        DuplicatePolicy $duplicatePolicy,
        TaxonomyImportMode $taxonomyMode,
        bool $allowTermCreation,
    ): ImportRecordResult {
        $this->imported[] = $values;

        if (($values['external_id'] ?? '') === 'reject-me') {
            return new ImportRecordResult(ImportRecordResult::REJECTED, null, ['Rejected by test importer.']);
        }

        return new ImportRecordResult(ImportRecordResult::CREATED, count($this->imported));
    }
}
