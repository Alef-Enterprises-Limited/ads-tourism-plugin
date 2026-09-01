<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Support;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceProductGatewayInterface;
use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductData;

final class InMemoryCommerceProductGateway implements CommerceProductGatewayInterface
{
    public bool $available = true;

    /** @var array<int, int> */
    public array $packageIds = [];

    /** @var array<int, PackageProductData> */
    public array $products = [];

    public function isAvailable(): bool
    {
        return $this->available;
    }

    public function productExists(int $productId): bool
    {
        return isset($this->products[$productId]);
    }

    public function packageIdForProduct(int $productId): ?int
    {
        return $this->packageIds[$productId] ?? null;
    }

    public function createProduct(PackageProductData $package): int
    {
        $productId = $this->products === [] ? 100 : max(array_keys($this->products)) + 1;
        $this->products[$productId] = $package;

        return $productId;
    }

    public function syncProduct(int $productId, PackageProductData $package): void
    {
        $this->products[$productId] = $package;
    }

    public function linkPackage(int $productId, int $packageId): void
    {
        $this->packageIds[$productId] = $packageId;
    }

    public function unlinkPackage(int $productId, int $packageId): void
    {
        if (($this->packageIds[$productId] ?? null) === $packageId) {
            unset($this->packageIds[$productId]);
        }
    }

    public function productUrl(int $productId): ?string
    {
        return $this->productExists($productId) ? 'https://example.com/product/' . $productId : null;
    }

    public function addToCartUrl(int $productId): ?string
    {
        return $this->productExists($productId) ? 'https://example.com/cart/' . $productId : null;
    }

    public function checkoutUrl(int $productId): ?string
    {
        return $this->productExists($productId) ? 'https://example.com/checkout/' . $productId : null;
    }
}
