<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map;

use AlefDigitalSolutions\ADSTourism\Application\Map\MapProviderInterface;

final readonly class MapProviderRegistry
{
    /** @param list<MapProviderInterface> $defaults */
    public function __construct(
        private array $defaults,
        private MapSettings $settings,
    ) {}

    public function selected(): ?MapProviderInterface
    {
        $providers = apply_filters('ads_tourism_map_providers', $this->defaults);

        if (!is_array($providers)) {
            return null;
        }

        foreach ($providers as $provider) {
            if (
                $provider instanceof MapProviderInterface
                && $provider->key() === $this->settings->provider()
                && $provider->isAvailable()
            ) {
                return $provider;
            }
        }

        return null;
    }
}
