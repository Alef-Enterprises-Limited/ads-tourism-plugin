<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

final class WooCommerceCompatibility
{
    public function isAvailable(): bool
    {
        return class_exists('WooCommerce')
            && class_exists('WC_Product')
            && class_exists('WC_Product_Simple')
            && function_exists('wc_get_product');
    }

    public function version(): string
    {
        return defined('WC_VERSION') ? (string) WC_VERSION : '';
    }
}
