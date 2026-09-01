<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

final readonly class HposCompatibility
{
    public function __construct(private string $pluginFile) {}

    public function register(): void
    {
        add_action('before_woocommerce_init', [$this, 'declare']);
    }

    public function declare(): void
    {
        $callback = [
            'Automattic\\WooCommerce\\Utilities\\FeaturesUtil',
            'declare_compatibility',
        ];

        if (!is_callable($callback)) {
            return;
        }

        $callback('custom_order_tables', $this->pluginFile, true);
    }
}
