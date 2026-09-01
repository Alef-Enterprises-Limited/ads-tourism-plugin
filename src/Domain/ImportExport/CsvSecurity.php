<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\ImportExport;

final class CsvSecurity
{
    public function sanitizeImportCell(string $value): string
    {
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value) ?? $value;
        $value = str_replace("\0", '', $value);
        $value = preg_replace('/<[^>]*>/', ' ', $value) ?? $value;
        $value = preg_replace('/[ \t]+/', ' ', $value) ?? $value;
        $value = trim($value);

        if (preg_match('/^\'\s*[=+\-@]/', $value) === 1) {
            $value = substr($value, 1);
        }

        return $value;
    }

    public function escapeForSpreadsheet(string $value): string
    {
        if (preg_match('/^\s*[=+\-@]/', $value) === 1) {
            return "'" . $value;
        }

        return $value;
    }
}
