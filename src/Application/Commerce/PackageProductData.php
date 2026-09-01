<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Commerce;

final readonly class PackageProductData
{
    public function __construct(
        public int $packageId,
        public string $title,
        public string $summary,
        public int $featuredImageId,
        public string $packageUrl,
    ) {}
}
