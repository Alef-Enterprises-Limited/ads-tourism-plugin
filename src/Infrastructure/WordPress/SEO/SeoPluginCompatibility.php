<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO;

final class SeoPluginCompatibility
{
    public function activePlugin(): string
    {
        if (defined('WPSEO_VERSION') || class_exists('WPSEO_Options')) {
            return 'yoast';
        }

        if (defined('RANK_MATH_VERSION') || class_exists('RankMath')) {
            return 'rank-math';
        }

        return 'none';
    }

    public function isActive(): bool
    {
        return $this->activePlugin() !== 'none';
    }
}
