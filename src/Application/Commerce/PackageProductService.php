<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Application\Commerce;

final readonly class PackageProductService
{
    public function __construct(
        private PackageProductLinkStoreInterface $links,
        private CommerceProductGatewayInterface $products,
    ) {}

    public function mappedProductId(int $packageId): ?int
    {
        return $this->links->productIdForPackage($packageId);
    }

    public function validProductId(int $packageId): ?int
    {
        $productId = $this->mappedProductId($packageId);

        if (
            !$this->products->isAvailable()
            || $productId === null
            || !$this->products->productExists($productId)
            || $this->products->packageIdForProduct($productId) !== $packageId
        ) {
            return null;
        }

        return $productId;
    }

    public function create(PackageProductData $package): int
    {
        $this->requireAvailable();

        if ($this->mappedProductId($package->packageId) !== null) {
            throw new CommerceMappingException('The Package already has a mapped product.');
        }

        $productId = $this->products->createProduct($package);

        if ($productId < 1) {
            throw new CommerceMappingException('WooCommerce did not create a product.');
        }

        $this->links->linkProduct($package->packageId, $productId);
        $this->products->linkPackage($productId, $package->packageId);

        return $productId;
    }

    public function link(int $packageId, int $productId): void
    {
        $this->requireAvailable();

        if (!$this->products->productExists($productId)) {
            throw new CommerceMappingException('Choose a valid WooCommerce product.');
        }

        $linkedPackageId = $this->products->packageIdForProduct($productId);

        if ($linkedPackageId !== null && $linkedPackageId !== $packageId) {
            throw new CommerceMappingException('That product is already linked to another Package.');
        }

        $existingProductId = $this->mappedProductId($packageId);

        if ($existingProductId !== null && $existingProductId !== $productId) {
            $this->detach($packageId);
        }

        $this->links->linkProduct($packageId, $productId);
        $this->products->linkPackage($productId, $packageId);
    }

    public function sync(PackageProductData $package): void
    {
        $productId = $this->validProductId($package->packageId);

        if ($productId === null) {
            throw new CommerceMappingException('The Package does not have a valid linked product.');
        }

        $this->products->syncProduct($productId, $package);
    }

    public function detach(int $packageId): void
    {
        $productId = $this->mappedProductId($packageId);

        if ($productId === null) {
            return;
        }

        $this->links->unlinkProduct($packageId, $productId);

        if ($this->products->isAvailable()) {
            $this->products->unlinkPackage($productId, $packageId);
        }
    }

    public function handleProductDeletion(int $productId): void
    {
        $packageId = $this->products->packageIdForProduct($productId);

        if ($packageId !== null) {
            $this->links->unlinkProduct($packageId, $productId);
        }
    }

    private function requireAvailable(): void
    {
        if (!$this->products->isAvailable()) {
            throw new CommerceMappingException('WooCommerce is not available.');
        }
    }
}
