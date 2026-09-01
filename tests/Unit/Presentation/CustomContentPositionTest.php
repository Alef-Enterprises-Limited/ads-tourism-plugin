<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Presentation\CustomContentPosition;
use PHPUnit\Framework\TestCase;

final class CustomContentPositionTest extends TestCase
{
    public function testItUsesTheStoredPosition(): void
    {
        self::assertSame(
            CustomContentPosition::TEMPLATE_SLOT,
            CustomContentPosition::fromStoredValue('template_slot'),
        );
    }

    public function testItSafelyDefaultsToAfter(): void
    {
        self::assertSame(CustomContentPosition::AFTER, CustomContentPosition::fromStoredValue('invalid'));
        self::assertSame(CustomContentPosition::AFTER, CustomContentPosition::fromStoredValue([]));
    }
}
