<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

interface TourismRecordImporter
{
    /** @param array<string, string> $values */
    public function import(
        ContentType $contentType,
        array $values,
        DuplicatePolicy $duplicatePolicy,
        TaxonomyImportMode $taxonomyMode,
        bool $allowTermCreation,
    ): ImportRecordResult;
}
