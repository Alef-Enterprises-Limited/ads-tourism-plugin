<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\ImportExport;

use AlefDigitalSolutions\ADSTourism\Domain\ImportExport\CsvSecurity;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CsvSecurityTest extends TestCase
{
    #[DataProvider('formulaValues')]
    public function testSpreadsheetFormulasAreNeutralized(string $value): void
    {
        self::assertSame("'" . $value, (new CsvSecurity())->escapeForSpreadsheet($value));
    }

    /** @return iterable<string, array{string}> */
    public static function formulaValues(): iterable
    {
        yield 'equals' => ['=2+2'];
        yield 'plus' => ['+SUM(A1:A2)'];
        yield 'minus' => ['-10+20'];
        yield 'at' => ['@IMPORTDATA("https://example.com")'];
        yield 'leading whitespace' => ['  =2+2'];
    }

    public function testMarkupAndNullBytesAreRemovedOnImport(): void
    {
        self::assertSame('Hello alert(1)', (new CsvSecurity())->sanitizeImportCell(" <b>Hello</b>\0<script>alert(1)</script> "));
    }

    public function testProtectedExportsCanBeImportedAgainAsText(): void
    {
        $security = new CsvSecurity();

        self::assertSame('-4.341', $security->sanitizeImportCell($security->escapeForSpreadsheet('-4.341')));
        self::assertSame('=literal text', $security->sanitizeImportCell("'=literal text"));
    }
}
