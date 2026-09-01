<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Fallback;

use AlefDigitalSolutions\ADSTourism\Domain\Media\ResolvedMedia;

final class MediaFallbackResolver
{
    /** @param list<ResolvedMedia|null> $candidates */
    public function resolve(array $candidates): ?ResolvedMedia
    {
        foreach ($candidates as $candidate) {
            if ($candidate !== null) {
                return $candidate;
            }
        }

        return null;
    }
}
