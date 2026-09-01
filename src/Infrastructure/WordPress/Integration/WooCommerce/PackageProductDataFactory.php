<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductData;

final class PackageProductDataFactory
{
    public function forPackage(int $packageId): PackageProductData
    {
        $summary = apply_filters('ads_tourism_resolved_field', null, $packageId, 'ads_tourism_summary');
        $imageId = get_post_thumbnail_id($packageId);
        $url = get_permalink($packageId);

        return new PackageProductData(
            $packageId,
            get_the_title($packageId),
            is_string($summary) ? $summary : '',
            is_int($imageId) ? $imageId : 0,
            is_string($url) ? $url : '',
        );
    }
}
