<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class PackageProductCleanup
{
    public function __construct(private PackageProductService $products) {}

    public function beforeDelete(int $postId): void
    {
        $postType = get_post_type($postId);

        if ($postType === ContentType::PACKAGE->value) {
            $this->products->detach($postId);
        } elseif ($postType === 'product') {
            $this->products->handleProductDeletion($postId);
        }
    }
}
