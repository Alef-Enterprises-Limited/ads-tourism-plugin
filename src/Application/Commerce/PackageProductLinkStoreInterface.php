<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Commerce;

interface PackageProductLinkStoreInterface
{
    public function productIdForPackage(int $packageId): ?int;

    public function linkProduct(int $packageId, int $productId): void;

    public function unlinkProduct(int $packageId, int $productId): void;
}
