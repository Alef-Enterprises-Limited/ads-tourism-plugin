<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode;

use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation\FrontendAssets;
use AlefDigitalSolutions\ADSTourism\Plugin;

final readonly class ShortcodeAssets
{
    private const SCRIPT_HANDLE = 'ads-tourism-listings';

    public function __construct(
        private string $pluginFile,
        private FrontendAssets $frontendAssets,
    ) {}

    public function register(): void
    {
        wp_register_script(
            self::SCRIPT_HANDLE,
            plugin_dir_url($this->pluginFile) . 'assets/public/listings.js',
            [],
            Plugin::VERSION,
            true,
        );
    }

    public function enqueue(): void
    {
        $this->frontendAssets->enqueueComponents();
        wp_enqueue_script(self::SCRIPT_HANDLE);
        wp_localize_script(self::SCRIPT_HANDLE, 'adsTourismListings', [
            'endpoint' => rest_url('ads-tourism/v1/query'),
            'errorMessage' => __('Tourism results could not be updated. Please try again.', 'ads-tourism'),
            'loadingMessage' => __('Updating tourism results…', 'ads-tourism'),
        ]);
    }
}
