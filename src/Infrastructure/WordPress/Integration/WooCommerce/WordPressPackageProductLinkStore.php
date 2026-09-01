<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductLinkStoreInterface;

final class WordPressPackageProductLinkStore implements PackageProductLinkStoreInterface
{
    public const PACKAGE_PRODUCT_META = '_ads_tourism_product_id';

    public function productIdForPackage(int $packageId): ?int
    {
        $productId = absint(get_post_meta($packageId, self::PACKAGE_PRODUCT_META, true));

        return $productId > 0 ? $productId : null;
    }

    public function linkProduct(int $packageId, int $productId): void
    {
        update_post_meta($packageId, self::PACKAGE_PRODUCT_META, $productId);
    }

    public function unlinkProduct(int $packageId, int $productId): void
    {
        delete_post_meta($packageId, self::PACKAGE_PRODUCT_META, $productId);
    }
}
