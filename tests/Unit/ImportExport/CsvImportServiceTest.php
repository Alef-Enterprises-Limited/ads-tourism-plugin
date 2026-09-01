<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\ImportExport;

use AlefDigitalSolutions\ADSTourism\Application\ImportExport\CsvImportService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Field\RecordFieldSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvReader;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRowValidator;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSchema;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSecurity;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\DuplicatePolicy;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\TaxonomyImportMode;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryRejectedRowWriter;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryTourismRecordImporter;
use PHPUnit\Framework\TestCase;

final class CsvImportServiceTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ads-import-');
        self::assertIsString($path);
        $this->path = $path;
        file_put_contents($path, "external_id,title\nplace-1,Kokopo\nreject-me,Rabaul\nbad id,Invalid\n");
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function testBatchImportsValidRowsAndReportsValidationAndWriterFailures(): void
    {
        $security = new CsvSecurity();
        $schema = new CsvSchema(new RecordFieldSchema());
        $importer = new InMemoryTourismRecordImporter();
        $rejected = new InMemoryRejectedRowWriter();
        $service = new CsvImportService(
            new CsvReader($security),
            new CsvRowValidator($schema),
            $importer,
            $rejected,
        );
        $result = $service->importBatch(
            $this->path,
            '/unused.csv',
            ',',
            ContentType::PLACE,
            ['external_id' => 'external_id', 'title' => 'title'],
            DuplicatePolicy::SKIP,
            TaxonomyImportMode::SIMPLE,
            false,
            0,
            25,
        );

        self::assertSame(3, $result->processed);
        self::assertSame(1, $result->imported);
        self::assertSame(2, $result->rejectedCount);
        self::assertCount(2, $rejected->rows);
        self::assertCount(2, $importer->imported);
    }
}
