<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\Divi\DiviCompatibility;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map\MapProviderRegistry;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Map\MapSettings;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Multilingual\TranslationResolver;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\SEO\SeoPluginCompatibility;

final readonly class SystemStatusPage
{
    public const PAGE_SLUG = 'ads-tourism-system-status';

    public function __construct(
        private DiviCompatibility $divi,
        private MapSettings $maps,
        private MapProviderRegistry $mapProviders,
        private SeoPluginCompatibility $seo,
        private TranslationResolver $translations,
    ) {}

    public function registerMenu(): void
    {
        add_submenu_page(
            AdminMenu::SLUG,
            __('ADS Tourism System Status', 'ads-tourism'),
            __('System Status', 'ads-tourism'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render'],
        );
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to view ADS Tourism system status.', 'ads-tourism'));
        }

        $diviStatus = $this->divi->statusByPostType();
        echo '<div class="wrap"><h1>' . esc_html__('ADS Tourism System Status', 'ads-tourism') . '</h1>';
        echo '<h2>' . esc_html__('Builder compatibility', 'ads-tourism') . '</h2>';
        echo '<table class="widefat striped"><thead><tr><th>';
        echo esc_html__('Check', 'ads-tourism') . '</th><th>' . esc_html__('Status', 'ads-tourism');
        echo '</th></tr></thead><tbody>';
        $this->row(
            __('Divi detected', 'ads-tourism'),
            $this->divi->isActive() ? __('Yes', 'ads-tourism') : __('No — optional', 'ads-tourism'),
        );

        foreach (ContentType::cases() as $contentType) {
            $postType = get_post_type_object($contentType->value);
            $label = $postType === null ? $contentType->value : (string) $postType->labels->name;
            $this->row(
                sprintf(__('Divi post-type integration: %s', 'ads-tourism'), $label),
                $diviStatus[$contentType->value] ? __('Enabled', 'ads-tourism') : __('Disabled', 'ads-tourism'),
            );
        }

        $this->row(
            __('Map provider', 'ads-tourism'),
            $this->mapProviderLabel($this->maps->provider()),
        );
        $this->row(
            __('Map provider available', 'ads-tourism'),
            $this->mapProviders->selected() === null ? __('No', 'ads-tourism') : __('Yes', 'ads-tourism'),
        );
        $this->row(
            __('SEO integration', 'ads-tourism'),
            $this->seoPluginLabel($this->seo->activePlugin()),
        );
        $adapter = $this->translations->adapter();
        $this->row(
            __('Multilingual integration', 'ads-tourism'),
            $adapter->isAvailable()
                ? $this->translationAdapterLabel($adapter->key())
                : __('Not detected — optional', 'ads-tourism'),
        );

        echo '</tbody></table><p>';
        echo esc_html__(
            'All tourism post types are public, REST-enabled, and compatible with standard title, content, featured-image, archive, and taxonomy conditions.',
            'ads-tourism',
        );
        echo '</p></div>';
    }

    private function row(string $check, string $status): void
    {
        echo '<tr><th scope="row">' . esc_html($check) . '</th><td>' . esc_html($status) . '</td></tr>';
    }

    private function mapProviderLabel(string $provider): string
    {
        return match ($provider) {
            'google' => __('Google Maps', 'ads-tourism'),
            'none' => __('Disabled — optional', 'ads-tourism'),
            default => $provider,
        };
    }

    private function seoPluginLabel(string $plugin): string
    {
        return match ($plugin) {
            'yoast' => __('Yoast SEO', 'ads-tourism'),
            'rank-math' => __('Rank Math SEO', 'ads-tourism'),
            default => __('Native ADS Tourism metadata', 'ads-tourism'),
        };
    }

    private function translationAdapterLabel(string $adapter): string
    {
        return match ($adapter) {
            'wpml' => __('WPML', 'ads-tourism'),
            'polylang' => __('Polylang', 'ads-tourism'),
            default => $adapter,
        };
    }
}
