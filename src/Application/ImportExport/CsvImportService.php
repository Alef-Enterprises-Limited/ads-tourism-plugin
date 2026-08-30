<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvReader;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRow;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRowValidationResult;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRowValidator;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\DuplicatePolicy;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportBatchResult;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportPreview;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\ImportRecordResult;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\RejectedRowWriter;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\TaxonomyImportMode;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\TourismRecordImporter;

final readonly class CsvImportService
{
    public function __construct(
        private CsvReader $reader,
        private CsvRowValidator $validator,
        private TourismRecordImporter $importer,
        private RejectedRowWriter $rejectedRows,
    ) {}

    /**
     * @param array<string, string> $mapping
     */
    public function preview(
        string $path,
        string $delimiter,
        ContentType $contentType,
        array $mapping,
        int $limit = 100,
    ): ImportPreview {
        $rows = $this->reader->readBatch($path, $delimiter, 0, max(1, min(250, $limit)));
        $validated = array_map(
            fn(CsvRow $row): CsvRowValidationResult => $this->validator->validate($contentType, $row, $mapping),
            $rows,
        );
        $valid = count(array_filter(
            $validated,
            static fn(CsvRowValidationResult $result): bool => $result->isValid(),
        ));

        return new ImportPreview(
            $this->reader->countRows($path, $delimiter),
            $valid,
            count($validated) - $valid,
            $validated,
        );
    }

    /**
     * @param array<string, string> $mapping
     */
    public function importBatch(
        string $path,
        string $rejectedPath,
        string $delimiter,
        ContentType $contentType,
        array $mapping,
        DuplicatePolicy $duplicatePolicy,
        TaxonomyImportMode $taxonomyMode,
        bool $allowTermCreation,
        int $offset,
        int $limit,
    ): ImportBatchResult {
        $rows = $this->reader->readBatch($path, $delimiter, $offset, max(1, min(100, $limit)));
        $rejected = [];
        $created = 0;
        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $validation = $this->validator->validate($contentType, $row, $mapping);

            if (!$validation->isValid()) {
                $rejected[] = $validation;
                continue;
            }

            $result = $this->importer->import(
                $contentType,
                $validation->values,
                $duplicatePolicy,
                $taxonomyMode,
                $allowTermCreation,
            );

            if ($result->outcome === ImportRecordResult::REJECTED) {
                $rejected[] = new CsvRowValidationResult(
                    $row->number,
                    $validation->values,
                    $result->errors,
                    $validation->warnings,
                );
            } elseif ($result->outcome === ImportRecordResult::CREATED) {
                ++$created;
            } elseif ($result->outcome === ImportRecordResult::UPDATED) {
                ++$updated;
            } else {
                ++$skipped;
            }
        }

        if ($rejected !== []) {
            $this->rejectedRows->append($rejectedPath, $rejected);
        }

        return new ImportBatchResult(
            count($rows),
            $created,
            $updated,
            $skipped,
            count($rejected),
            $rejected,
        );
    }
}
