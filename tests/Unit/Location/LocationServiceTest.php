<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Location;

use AlefDigitalSolutions\ADSTourism\Application\Location\LocationService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationPoint;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRole;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RecordTypeResolver;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryLocationRepository;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class LocationServiceTest extends TestCase
{
    public function testItNormalizesPrimaryLocationAndOrder(): void
    {
        $repository = new InMemoryLocationRepository();
        $service = new LocationService($repository, $this->resolver());
        $service->replace(42, [
            new LocationPoint(42, new Coordinates(-4.2, 152.1), 'Entrance', LocationRole::ENTRANCE),
            new LocationPoint(42, new Coordinates(-4.3, 152.2), 'Viewpoint', LocationRole::VIEWPOINT, true),
        ]);

        $locations = $service->find(42);

        self::assertCount(2, $locations);
        self::assertFalse($locations[0]->isPrimary);
        self::assertTrue($locations[1]->isPrimary);
        self::assertSame([0, 1], array_map(static fn(LocationPoint $location): int => $location->sortOrder, $locations));
    }

    public function testItRejectsInvalidCoordinatesAndUnknownRecords(): void
    {
        $service = new LocationService(new InMemoryLocationRepository(), $this->resolver());

        $this->expectException(InvalidArgumentException::class);
        $service->replaceFromArray(42, [[
            'latitude' => 91,
            'longitude' => 152,
        ]]);
    }

    private function resolver(): RecordTypeResolver
    {
        return new class implements RecordTypeResolver {
            public function resolve(int $postId): ?ContentType
            {
                return $postId === 42 ? ContentType::PLACE : null;
            }
        };
    }
}
