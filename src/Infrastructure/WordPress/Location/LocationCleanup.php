<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Location;

use AlefDigitalSolutions\ADSTourism\Domain\Map\LocationRepository;

final readonly class LocationCleanup
{
    public function __construct(private LocationRepository $locations) {}

    public function deleteForPost(int $postId): void
    {
        $this->locations->deleteForEntity($postId);
    }
}
