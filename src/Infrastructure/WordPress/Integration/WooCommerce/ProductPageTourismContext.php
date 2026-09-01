<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceProductGatewayInterface;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class ProductPageTourismContext
{
    public function __construct(private CommerceProductGatewayInterface $products) {}

    public function register(): void
    {
        add_filter('ads_tourism_record_context_id', [$this, 'resolve'], 10, 2);
    }

    /** @param array<string, mixed> $attributes */
    public function resolve(int $recordId, array $attributes = []): int
    {
        if (absint($attributes['id'] ?? 0) > 0 || get_post_type($recordId) !== 'product') {
            return $recordId;
        }

        $packageId = $this->products->packageIdForProduct($recordId);

        return $packageId !== null && get_post_type($packageId) === ContentType::PACKAGE->value
            ? $packageId
            : $recordId;
    }
}
