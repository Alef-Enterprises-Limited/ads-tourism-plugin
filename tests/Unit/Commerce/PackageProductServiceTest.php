<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Commerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceMappingException;
use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductData;
use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductService;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryCommerceProductGateway;
use AlefDigitalSolutions\ADSTourism\Tests\Support\InMemoryPackageProductLinkStore;
use PHPUnit\Framework\TestCase;

final class PackageProductServiceTest extends TestCase
{
    private InMemoryPackageProductLinkStore $links;

    private InMemoryCommerceProductGateway $products;

    private PackageProductService $service;

    protected function setUp(): void
    {
        $this->links = new InMemoryPackageProductLinkStore();
        $this->products = new InMemoryCommerceProductGateway();
        $this->service = new PackageProductService($this->links, $this->products);
    }

    public function testCreateBuildsAReciprocalOneToOneMapping(): void
    {
        $productId = $this->service->create($this->package(10));

        self::assertSame($productId, $this->links->links[10]);
        self::assertSame(10, $this->products->packageIds[$productId]);
        self::assertSame($productId, $this->service->validProductId(10));
    }

    public function testAProductCannotBeSilentlyTakenFromAnotherPackage(): void
    {
        $this->products->products[200] = $this->package(10);
        $this->products->packageIds[200] = 10;
        $this->expectException(CommerceMappingException::class);

        $this->service->link(20, 200);
    }

    public function testDeactivationPreservesTheStoredMappingButMakesItNonTransactional(): void
    {
        $productId = $this->service->create($this->package(10));
        $this->products->available = false;

        self::assertSame($productId, $this->service->mappedProductId(10));
        self::assertNull($this->service->validProductId(10));
    }

    public function testDetachNeverDeletesEitherRecord(): void
    {
        $productId = $this->service->create($this->package(10));
        $this->service->detach(10);

        self::assertArrayNotHasKey(10, $this->links->links);
        self::assertArrayNotHasKey($productId, $this->products->packageIds);
        self::assertArrayHasKey($productId, $this->products->products);
    }

    private function package(int $packageId): PackageProductData
    {
        return new PackageProductData(
            $packageId,
            'Kokopo Escape',
            'A two-day tourism package.',
            0,
            'https://example.com/packages/kokopo-escape/',
        );
    }
}
