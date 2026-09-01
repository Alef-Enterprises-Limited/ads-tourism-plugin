<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Maintenance;

final readonly class IntegrityRepairResult
{
    public function __construct(
        public int $relationshipsRemoved,
        public int $mediaLinksRemoved,
        public int $packageMappingsDetached,
        public int $productMappingsDetached,
    ) {}

    public function repairedCount(): int
    {
        return $this->relationshipsRemoved
            + $this->mediaLinksRemoved
            + $this->packageMappingsDetached
            + $this->productMappingsDetached;
    }
}
