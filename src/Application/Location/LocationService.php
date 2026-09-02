<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Location;

use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationPoint;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRepository;
use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRole;
use AlefDigitalSolutions\ADSTourism\Domain\Relationship\RecordTypeResolver;
use InvalidArgumentException;

final readonly class LocationService
{
    public function __construct(
        private LocationRepository $repository,
        private RecordTypeResolver $recordTypes,
    ) {}

    /** @param list<LocationPoint> $locations */
    public function replace(int $entityPostId, array $locations): void
    {
        if ($this->recordTypes->resolve($entityPostId) === null) {
            throw new InvalidArgumentException('The selected post is not an ADS Tourism record.');
        }

        $validated = [];
        $seen = [];

        foreach ($locations as $inputOrder => $location) {
            if ($location->entityPostId !== $entityPostId) {
                throw new InvalidArgumentException('A location belongs to a different tourism record.');
            }

            if (isset($seen[$location->sourceKey()])) {
                continue;
            }

            $seen[$location->sourceKey()] = true;
            $validated[] = ['location' => $location, 'input_order' => (int) $inputOrder];
        }

        usort(
            $validated,
            static fn(array $left, array $right): int => $left['location']->sortOrder <=> $right['location']->sortOrder
                ?: $left['input_order'] <=> $right['input_order'],
        );
        $normalized = [];
        $primaryFound = false;

        foreach ($validated as $sortOrder => $item) {
            $location = $item['location'];
            $isPrimary = $location->isPrimary && !$primaryFound;
            $primaryFound = $primaryFound || $isPrimary;
            $normalized[] = new LocationPoint(
                $entityPostId,
                $location->coordinates,
                $location->label,
                $location->role,
                $isPrimary,
                $location->showOnMap,
                (int) $sortOrder,
            );
        }

        if ($normalized !== [] && !$primaryFound) {
            $first = $normalized[0];
            $normalized[0] = new LocationPoint(
                $first->entityPostId,
                $first->coordinates,
                $first->label,
                $first->role,
                true,
                $first->showOnMap,
                $first->sortOrder,
            );
        }

        $this->repository->replaceForEntity($entityPostId, $normalized);
    }

    /** @return list<LocationPoint> */
    public function find(int $entityPostId, bool $mapOnly = false): array
    {
        if ($this->recordTypes->resolve($entityPostId) === null) {
            return [];
        }

        return $this->repository->findForEntity($entityPostId, $mapOnly);
    }

    /** @param list<mixed> $values */
    public function replaceFromArray(int $entityPostId, array $values): void
    {
        $locations = [];

        foreach ($values as $value) {
            if (!is_array($value)) {
                throw new InvalidArgumentException('Each location must be an object.');
            }

            $latitude = $this->number($value['latitude'] ?? null);
            $longitude = $this->number($value['longitude'] ?? null);

            if ($latitude === null || $longitude === null) {
                throw new InvalidArgumentException('Each location requires numeric latitude and longitude.');
            }

            $roleValue = is_scalar($value['role'] ?? null) ? (string) $value['role'] : 'primary';
            $role = LocationRole::tryFrom(sanitize_key($roleValue));

            if ($role === null) {
                throw new InvalidArgumentException('Each location requires a valid role.');
            }

            $label = is_scalar($value['label'] ?? null) ? (string) $value['label'] : '';
            $sortOrder = is_numeric($value['sort_order'] ?? null)
                ? max(0, (int) $value['sort_order'])
                : count($locations);
            $locations[] = new LocationPoint(
                $entityPostId,
                new Coordinates($latitude, $longitude),
                sanitize_text_field($label),
                $role,
                rest_sanitize_boolean($value['is_primary'] ?? false),
                rest_sanitize_boolean($value['show_on_map'] ?? true),
                $sortOrder,
            );
        }

        $this->replace($entityPostId, $locations);
    }

    private function number(mixed $value): ?float
    {
        return is_numeric($value) ? (float) $value : null;
    }
}
