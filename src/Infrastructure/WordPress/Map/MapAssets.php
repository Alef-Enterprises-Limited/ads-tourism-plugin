<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map;

use AlefDigitalSolutions\ADSTourism\Application\Map\MapProviderInterface;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendAssets;
use AlefDigitalSolutions\ADSTourism\Plugin;

final readonly class MapAssets
{
    private const SCRIPT_HANDLE = 'ads-tourism-maps';

    public function __construct(
        private string $pluginFile,
        private FrontendAssets $frontendAssets,
    ) {}

    public function enqueue(MapProviderInterface $provider): void
    {
        $this->frontendAssets->enqueueComponents();
        $provider->enqueueAssets();
        wp_register_script(
            self::SCRIPT_HANDLE,
            plugin_dir_url($this->pluginFile) . 'assets/public/maps.js',
            $provider->scriptDependencies(),
            Plugin::VERSION,
            true,
        );
        wp_enqueue_script(self::SCRIPT_HANDLE);
        wp_localize_script(self::SCRIPT_HANDLE, 'adsTourismMaps', [
            'emptyMessage' => __('No mapped tourism records are available.', 'ads-tourism'),
            'updatedMessage' => __('Tourism map updated.', 'ads-tourism'),
            'unavailableMessage' => __('The configured map provider could not be loaded.', 'ads-tourism'),
        ]);
    }
}
