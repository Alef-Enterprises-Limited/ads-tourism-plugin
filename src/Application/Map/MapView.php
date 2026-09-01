<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Map;

final readonly class MapView
{
    public function __construct(
        public int $height,
        public int $zoom,
        public string $cssClass,
        public string $accessibleLabel,
        public ?string $context = null,
    ) {}
}
