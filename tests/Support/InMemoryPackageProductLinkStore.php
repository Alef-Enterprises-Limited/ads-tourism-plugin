<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Support;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductLinkStoreInterface;

final class InMemoryPackageProductLinkStore implements PackageProductLinkStoreInterface
{
    /** @var array<int, int> */
    public array $links = [];

    public function productIdForPackage(int $packageId): ?int
    {
        return $this->links[$packageId] ?? null;
    }

    public function linkProduct(int $packageId, int $productId): void
    {
        $this->links[$packageId] = $productId;
    }

    public function unlinkProduct(int $packageId, int $productId): void
    {
        if (($this->links[$packageId] ?? null) === $productId) {
            unset($this->links[$packageId]);
        }
    }
}
