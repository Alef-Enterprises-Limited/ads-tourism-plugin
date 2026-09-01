<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Query;

use AlefDigitalSolutions\ADSTourism\Domain\Query\ContextName;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class ContextNameTest extends TestCase
{
    public function testItCreatesNamespacedUrlParameters(): void
    {
        $context = new ContextName('Featured_Trips');

        self::assertSame('Featured_Trips', $context->value);
        self::assertSame('ads_featured_trips_page', $context->parameter('page'));
    }

    /** @return iterable<string, array{string}> */
    public static function invalidContextProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'spaces' => ['featured trips'];
        yield 'punctuation' => ['featured.trips'];
        yield 'too long' => [str_repeat('a', ContextName::MAX_LENGTH + 1)];
    }

    #[DataProvider('invalidContextProvider')]
    public function testItRejectsUnsafeContextNames(string $value): void
    {
        $this->expectException(InvalidArgumentException::class);

        new ContextName($value);
    }
}
