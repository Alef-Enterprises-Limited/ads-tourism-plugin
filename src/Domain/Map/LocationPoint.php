<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Map;

use InvalidArgumentException;

final readonly class LocationPoint
{
    public function __construct(
        public int $entityPostId,
        public Coordinates $coordinates,
        public string $label = '',
        public LocationRole $role = LocationRole::PRIMARY,
        public bool $isPrimary = false,
        public bool $showOnMap = true,
        public int $sortOrder = 0,
    ) {
        if ($this->entityPostId <= 0 || $this->sortOrder < 0 || strlen($this->label) > 255) {
            throw new InvalidArgumentException('A location requires a valid record and non-negative order.');
        }
    }

    public function sourceKey(): string
    {
        return implode(':', [
            $this->coordinates->latitude,
            $this->coordinates->longitude,
            $this->role->value,
            $this->label,
        ]);
    }

    /** @return array<string, bool|float|int|string> */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'role' => $this->role->value,
            'latitude' => $this->coordinates->latitude,
            'longitude' => $this->coordinates->longitude,
            'is_primary' => $this->isPrimary,
            'show_on_map' => $this->showOnMap,
            'sort_order' => $this->sortOrder,
        ];
    }
}
