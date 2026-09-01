<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvReader;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSecurity;
use PHPUnit\Framework\TestCase;

final class CsvReaderTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ads-csv-');
        self::assertIsString($path);
        $this->path = $path;
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function testDelimiterHeadersRowsAndCountsAreReadSafely(): void
    {
        file_put_contents(
            $this->path,
            "\xEF\xBB\xBFExternal ID;Title;Summary\nplace-1;Kokopo;<b>Coastal town</b>\nplace-2;Rabaul;Harbour\n",
        );
        $reader = new CsvReader(new CsvSecurity());
        $delimiter = $reader->detectDelimiter($this->path);

        self::assertSame(';', $delimiter);
        self::assertSame(['External ID', 'Title', 'Summary'], $reader->headers($this->path, $delimiter));
        self::assertSame(2, $reader->countRows($this->path, $delimiter));
        self::assertSame('Coastal town', $reader->readBatch($this->path, $delimiter, 0, 1)[0]->values['Summary']);
    }
}
