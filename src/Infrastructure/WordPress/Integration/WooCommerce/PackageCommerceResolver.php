<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceModeResolver;
use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductService;
use AlefDigitalSolutions\ADSTourism\Domain\Commerce\CommerceMode;
use AlefDigitalSolutions\ADSTourism\Domain\Commerce\CommerceResolution;

final readonly class PackageCommerceResolver
{
    public function __construct(
        private CommerceModeResolver $modes,
        private PackageProductService $products,
        private WooCommerceCompatibility $compatibility,
        private CommerceSettings $settings,
    ) {}

    public function forPackage(int $packageId): CommerceResolution
    {
        return $this->modes->resolve(
            CommerceMode::fromStoredValue(get_post_meta($packageId, 'ads_tourism_commerce_mode', true)),
            $this->compatibility->isAvailable(),
            $this->products->validProductId($packageId),
            $this->settings->invalidModeFallback(),
        );
    }
}
