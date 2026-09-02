<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Domain\Map;

interface LocationRepository
{
    /** @param list<LocationPoint> $locations */
    public function replaceForEntity(int $entityPostId, array $locations): void;

    /** @return list<LocationPoint> */
    public function findForEntity(int $entityPostId, bool $mapOnly = false): array;

    public function deleteForEntity(int $entityPostId): int;
}
