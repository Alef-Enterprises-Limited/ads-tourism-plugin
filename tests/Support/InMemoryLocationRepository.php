<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Support;

use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationPoint;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRepository;

final class InMemoryLocationRepository implements LocationRepository
{
    /** @var array<int, list<LocationPoint>> */
    private array $locations = [];

    public function replaceForEntity(int $entityPostId, array $locations): void
    {
        $this->locations[$entityPostId] = array_values($locations);
    }

    public function findForEntity(int $entityPostId, bool $mapOnly = false): array
    {
        return array_values(array_filter(
            $this->locations[$entityPostId] ?? [],
            static fn(LocationPoint $location): bool => !$mapOnly || $location->showOnMap,
        ));
    }

    public function deleteForEntity(int $entityPostId): int
    {
        $count = count($this->locations[$entityPostId] ?? []);
        unset($this->locations[$entityPostId]);

        return $count;
    }
}
