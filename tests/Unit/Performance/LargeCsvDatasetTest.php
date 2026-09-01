<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Performance;

use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvReader;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSecurity;
use PHPUnit\Framework\Attributes\Large;
use PHPUnit\Framework\TestCase;

#[Large]
final class LargeCsvDatasetTest extends TestCase
{
    private string $path;

    protected function setUp(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'ads-tourism-performance-');
        self::assertIsString($path);
        $this->path = $path;
    }

    protected function tearDown(): void
    {
        if (is_file($this->path)) {
            unlink($this->path);
        }
    }

    public function testOneThousandRowsStayWithinTheReleaseBudget(): void
    {
        $contents = "external_id,title,summary\n";

        for ($index = 1; $index <= 1000; ++$index) {
            $contents .= sprintf("place-%04d,Place %04d,Representative tourism record %04d\n", $index, $index, $index);
        }

        file_put_contents($this->path, $contents);
        $reader = new CsvReader(new CsvSecurity());
        $startMemory = memory_get_usage(true);
        $startedAt = hrtime(true);
        $count = $reader->countRows($this->path, ',');
        $rows = [];

        for ($offset = 0; $offset < 1000; $offset += 100) {
            array_push($rows, ...$reader->readBatch($this->path, ',', $offset, 100));
        }

        $elapsedSeconds = (hrtime(true) - $startedAt) / 1_000_000_000;
        $memoryIncrease = memory_get_usage(true) - $startMemory;

        self::assertSame(1000, $count);
        self::assertCount(1000, $rows);
        self::assertLessThan(5.0, $elapsedSeconds, 'The 1,000-row CSV budget exceeded five seconds.');
        self::assertLessThan(64 * 1024 * 1024, $memoryIncrease, 'The 1,000-row CSV budget exceeded 64 MiB.');
    }
}
