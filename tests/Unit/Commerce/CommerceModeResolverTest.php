<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Tests\Unit\Commerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceModeResolver;
use AlefDigitalSolutions\ADSTourism\Domain\Commerce\CommerceMode;
use PHPUnit\Framework\TestCase;

final class CommerceModeResolverTest extends TestCase
{
    private CommerceModeResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new CommerceModeResolver();
    }

    public function testCatalogueModeNeverBecomesTransactional(): void
    {
        $resolution = $this->resolver->resolve(CommerceMode::CATALOGUE, true, 42);

        self::assertSame(CommerceMode::CATALOGUE, $resolution->effectiveMode);
        self::assertFalse($resolution->usesWooCommerce());
    }

    public function testWooCommerceModeRequiresAvailabilityAndAProduct(): void
    {
        $resolution = $this->resolver->resolve(CommerceMode::WOOCOMMERCE, true, 42);

        self::assertSame(CommerceMode::WOOCOMMERCE, $resolution->effectiveMode);
        self::assertSame(42, $resolution->productId);
        self::assertTrue($resolution->usesWooCommerce());
    }

    public function testInvalidWooCommerceModeUsesConfiguredSafeFallback(): void
    {
        $resolution = $this->resolver->resolve(
            CommerceMode::WOOCOMMERCE,
            false,
            null,
            CommerceMode::ENQUIRY,
        );

        self::assertSame(CommerceMode::ENQUIRY, $resolution->effectiveMode);
        self::assertTrue($resolution->fellBack());
        self::assertNull($resolution->productId);
    }
}
