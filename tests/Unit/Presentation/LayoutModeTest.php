<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Presentation\LayoutMode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class LayoutModeTest extends TestCase
{
    /** @return iterable<string, array{LayoutMode, bool, bool}> */
    public static function behaviorProvider(): iterable
    {
        yield 'standard' => [LayoutMode::STANDARD, true, false];
        yield 'standard and custom' => [LayoutMode::STANDARD_CUSTOM, true, true];
        yield 'full custom' => [LayoutMode::FULL_CUSTOM, false, true];
    }

    #[DataProvider('behaviorProvider')]
    public function testItDefinesStructuredAndCustomContentBehavior(
        LayoutMode $layoutMode,
        bool $structured,
        bool $custom,
    ): void {
        self::assertSame($structured, $layoutMode->includesStructuredContent());
        self::assertSame($custom, $layoutMode->includesCustomContent());
    }

    public function testItSafelyDefaultsUnknownStoredValues(): void
    {
        self::assertSame(LayoutMode::STANDARD, LayoutMode::fromStoredValue('unknown'));
        self::assertSame(LayoutMode::STANDARD, LayoutMode::fromStoredValue(null));
    }
}
