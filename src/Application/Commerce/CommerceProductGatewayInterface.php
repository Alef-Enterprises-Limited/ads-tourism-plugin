<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Commerce;

interface CommerceProductGatewayInterface
{
    public function isAvailable(): bool;

    public function productExists(int $productId): bool;

    public function packageIdForProduct(int $productId): ?int;

    public function createProduct(PackageProductData $package): int;

    public function syncProduct(int $productId, PackageProductData $package): void;

    public function linkPackage(int $productId, int $packageId): void;

    public function unlinkPackage(int $productId, int $packageId): void;

    public function productUrl(int $productId): ?string;

    public function addToCartUrl(int $productId): ?string;

    public function checkoutUrl(int $productId): ?string;
}
