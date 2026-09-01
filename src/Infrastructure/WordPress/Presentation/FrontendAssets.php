<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use AlefDigitalSolutions\ADSTourism\Plugin;

final readonly class FrontendAssets
{
    private const STYLE_HANDLE = 'ads-tourism-frontend';

    public function __construct(
        private string $pluginFile,
        private PresentationSettings $settings,
    ) {}

    public function enqueue(): void
    {
        if (!$this->isTourismRequest()) {
            return;
        }

        $customCss = $this->settings->customCss();

        if ($this->settings->loadStyles()) {
            wp_enqueue_style(
                self::STYLE_HANDLE,
                plugin_dir_url($this->pluginFile) . 'assets/public/tourism.css',
                [],
                Plugin::VERSION,
            );
        } elseif ($customCss !== '') {
            wp_register_style(self::STYLE_HANDLE, false, [], Plugin::VERSION);
            wp_enqueue_style(self::STYLE_HANDLE);
        }

        if ($customCss !== '') {
            wp_add_inline_style(self::STYLE_HANDLE, $customCss);
        }
    }

    private function isTourismRequest(): bool
    {
        $postTypes = array_map(
            static fn(ContentType $contentType): string => $contentType->value,
            ContentType::cases(),
        );
        $taxonomies = array_map(
            static fn(TourismTaxonomy $taxonomy): string => $taxonomy->value,
            TourismTaxonomy::cases(),
        );

        return is_singular($postTypes) || is_post_type_archive($postTypes) || is_tax($taxonomies);
    }
}
