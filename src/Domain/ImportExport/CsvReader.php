<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

use RuntimeException;
use SplFileObject;

final readonly class CsvReader
{
    public function __construct(private CsvSecurity $security) {}

    public function detectDelimiter(string $path): string
    {
        $handle = fopen($path, 'rb');

        if ($handle === false) {
            throw new RuntimeException('The CSV file could not be opened.');
        }

        $line = fgets($handle);
        fclose($handle);

        if (!is_string($line)) {
            throw new RuntimeException('The CSV file is empty.');
        }

        $scores = [];

        foreach ([',', ';', "\t", '|'] as $delimiter) {
            $scores[$delimiter] = count(str_getcsv($line, $delimiter, '"', ''));
        }

        arsort($scores);
        $delimiter = array_key_first($scores);

        if ($scores[$delimiter] < 2) {
            throw new RuntimeException('A supported CSV delimiter could not be detected.');
        }

        return $delimiter;
    }

    /**
     * @return list<string>
     */
    public function headers(string $path, string $delimiter): array
    {
        $file = $this->open($path, $delimiter);
        $row = $file->fgetcsv();

        if (!is_array($row)) {
            throw new RuntimeException('The CSV header row could not be read.');
        }

        $headers = array_map(
            fn(mixed $value): string => $this->security->sanitizeImportCell((string) $value),
            $row,
        );

        if (in_array('', $headers, true) || count($headers) !== count(array_unique($headers))) {
            throw new RuntimeException('CSV headers must be present and unique.');
        }

        return $headers;
    }

    /**
     * @return list<CsvRow>
     */
    public function readBatch(
        string $path,
        string $delimiter,
        int $offset,
        int $limit,
    ): array {
        $headers = $this->headers($path, $delimiter);
        $file = $this->open($path, $delimiter);
        $file->rewind();
        $file->fgetcsv();
        $rows = [];
        $logicalIndex = 0;

        while (!$file->eof() && count($rows) < $limit) {
            $raw = $file->fgetcsv();
            $rowNumber = $file->key();

            if (!is_array($raw) || $raw === [null] || $this->isEmpty($raw)) {
                continue;
            }

            if ($logicalIndex++ < max(0, $offset)) {
                continue;
            }

            if (count($raw) !== count($headers)) {
                $rows[] = new CsvRow($rowNumber, ['__row_error' => 'Column count does not match the header.']);
                continue;
            }

            $values = [];

            foreach ($headers as $index => $header) {
                $values[$header] = $this->security->sanitizeImportCell((string) ($raw[$index] ?? ''));
            }

            $rows[] = new CsvRow($rowNumber, $values);
        }

        return $rows;
    }

    public function countRows(string $path, string $delimiter): int
    {
        $file = $this->open($path, $delimiter);
        $count = 0;
        $file->rewind();
        $file->fgetcsv();

        while (!$file->eof()) {
            $row = $file->fgetcsv();

            if (is_array($row) && $row !== [null] && !$this->isEmpty($row)) {
                ++$count;
            }
        }

        return $count;
    }

    private function open(string $path, string $delimiter): SplFileObject
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('The CSV file is unavailable.');
        }

        $file = new SplFileObject($path, 'rb');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($delimiter, '"', '');

        return $file;
    }

    /**
     * @param list<mixed> $row
     */
    private function isEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
