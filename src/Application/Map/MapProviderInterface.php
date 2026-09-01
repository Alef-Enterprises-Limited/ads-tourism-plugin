<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Map;

use AlefDigitalSolutions\ADSTourism\Domain\Map\Coordinates;
use AlefDigitalSolutions\ADSTourism\Domain\Map\MapMarker;

interface MapProviderInterface
{
    public function key(): string;

    public function isAvailable(): bool;

    public function enqueueAssets(): void;

    /** @return list<string> */
    public function scriptDependencies(): array;

    /** @return array<string, int|float|string> */
    public function normalizeMarker(MapMarker $marker): array;

    public function renderSingleMarker(MapMarker $marker, MapView $view): string;

    /** @param non-empty-list<MapMarker> $markers */
    public function renderMultipleMarkers(array $markers, MapView $view): string;

    public function attribution(): string;

    public function directionsUrl(Coordinates $coordinates): string;
}
