<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceProductGatewayInterface;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class PackageRecordUrlFilter
{
    public function __construct(
        private CommerceSettings $settings,
        private PackageCommerceResolver $commerce,
        private CommerceProductGatewayInterface $products,
    ) {}

    public function register(): void
    {
        add_filter('ads_tourism_record_url', [$this, 'filter'], 10, 2);
    }

    public function filter(string|false $url, int $postId): string|false
    {
        if (
            get_post_type($postId) !== ContentType::PACKAGE->value
            || $this->settings->listingDestination() !== 'product'
        ) {
            return $url;
        }

        $resolution = $this->commerce->forPackage($postId);

        return $resolution->usesWooCommerce()
            ? ($this->products->productUrl((int) $resolution->productId) ?? $url)
            : $url;
    }
}
