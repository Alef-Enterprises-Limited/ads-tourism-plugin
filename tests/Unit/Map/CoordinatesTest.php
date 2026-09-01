<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Map;

use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CoordinatesTest extends TestCase
{
    public function testItAcceptsBoundaryCoordinates(): void
    {
        self::assertSame(-90.0, new Coordinates(-90, -180)->latitude);
        self::assertSame(180.0, new Coordinates(90, 180)->longitude);
    }

    /** @return iterable<string, array{float, float}> */
    public static function invalidCoordinates(): iterable
    {
        yield 'latitude below range' => [-90.1, 0.0];
        yield 'latitude above range' => [90.1, 0.0];
        yield 'longitude below range' => [0.0, -180.1];
        yield 'longitude above range' => [0.0, 180.1];
    }

    #[DataProvider('invalidCoordinates')]
    public function testItRejectsCoordinatesOutsideWorldBounds(float $latitude, float $longitude): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Coordinates($latitude, $longitude);
    }
}
