<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Maintenance;

use AlefDigitalSolutions\ADSTourism\Domain\Maintenance\DataRetentionPolicy;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class DataRetentionPolicyTest extends TestCase
{
    #[DataProvider('confirmationCases')]
    public function testDestructiveUninstallRequiresBothExplicitSignals(
        bool $requested,
        string $confirmation,
        bool $expected,
    ): void {
        $policy = new DataRetentionPolicy();

        self::assertSame($expected, $policy->deletionIsConfirmed($requested, $confirmation));
    }

    /** @return iterable<string, array{bool, string, bool}> */
    public static function confirmationCases(): iterable
    {
        yield 'preserve by default' => [false, '', false];
        yield 'phrase alone is insufficient' => [false, DataRetentionPolicy::CONFIRMATION, false];
        yield 'checkbox alone is insufficient' => [true, '', false];
        yield 'phrase is exact and case-sensitive' => [true, 'delete ads tourism data', false];
        yield 'surrounding whitespace is harmless' => [true, '  ' . DataRetentionPolicy::CONFIRMATION . '  ', true];
        yield 'both signals permit deletion' => [true, DataRetentionPolicy::CONFIRMATION, true];
    }
}
