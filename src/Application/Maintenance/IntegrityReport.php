<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Maintenance;

final readonly class IntegrityReport
{
    public function __construct(
        public int $orphanedRelationships,
        public int $invalidMediaLinks,
        public int $missingMappedProducts,
        public int $missingMappedPackages,
        public int $duplicateExternalIds,
    ) {}

    public function issueCount(): int
    {
        return $this->orphanedRelationships
            + $this->invalidMediaLinks
            + $this->missingMappedProducts
            + $this->missingMappedPackages
            + $this->duplicateExternalIds;
    }

    public function isHealthy(): bool
    {
        return $this->issueCount() === 0;
    }
}
