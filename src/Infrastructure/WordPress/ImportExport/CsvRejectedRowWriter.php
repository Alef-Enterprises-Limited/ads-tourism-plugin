<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvRowValidationResult;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSecurity;
use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\RejectedRowWriter;
use RuntimeException;

final readonly class CsvRejectedRowWriter implements RejectedRowWriter
{
    public function __construct(private CsvSecurity $security) {}

    public function append(string $path, array $rows): void
    {
        $isNew = !is_file($path) || filesize($path) === 0;
        $handle = fopen($path, 'ab');

        if ($handle === false) {
            throw new RuntimeException('The rejected-row report could not be written.');
        }

        if ($isNew) {
            fputcsv($handle, ['row_number', 'external_id', 'title', 'errors', 'warnings'], ',', '"', '');
        }

        foreach ($rows as $row) {
            $this->write($handle, $row);
        }

        fclose($handle);
    }

    /** @param resource $handle */
    private function write($handle, CsvRowValidationResult $row): void
    {
        fputcsv($handle, array_map(
            $this->security->escapeForSpreadsheet(...),
            [
                (string) $row->rowNumber,
                $row->values['external_id'] ?? '',
                $row->values['title'] ?? '',
                implode(' | ', $row->errors),
                implode(' | ', $row->warnings),
            ],
        ), ',', '"', '');
    }
}
