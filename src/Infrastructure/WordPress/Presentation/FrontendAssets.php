<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use AlefDigitalSolutions\ADSTourism\Plugin;

final class FrontendAssets
{
    private const STYLE_HANDLE = 'ads-tourism-frontend';

    private bool $enqueued = false;

    public function __construct(
        private PresentationSettings $settings,
    ) {}

    public function enqueue(): void
    {
        if (!$this->isTourismRequest()) {
            return;
        }

        $this->enqueueComponents();
    }

    public function enqueueComponents(): void
    {
        if ($this->enqueued) {
            return;
        }

        $this->enqueued = true;

        $styleHandles = [];

        if ($this->settings->loadStyles()) {
            foreach ($this->settings->scopes() as $scope => $definition) {
                $handle = self::STYLE_HANDLE . '-' . sanitize_key($scope);
                wp_enqueue_style($handle, $this->settings->assetUrl($scope), [], Plugin::VERSION);
                $styleHandles[] = $handle;
            }
        }

        $customCss = $this->settings->customCssByScope();

        if ($customCss !== []) {
            $overrideHandle = self::STYLE_HANDLE . '-custom';
            wp_register_style($overrideHandle, false, $styleHandles, Plugin::VERSION);
            wp_enqueue_style($overrideHandle);
            wp_add_inline_style($overrideHandle, implode("\n\n", $customCss));
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
