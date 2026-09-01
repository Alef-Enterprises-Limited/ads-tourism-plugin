<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Presentation;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\Divi\DiviCompatibility;

final readonly class SystemStatusPage
{
    public const PAGE_SLUG = 'ads-tourism-system-status';

    public function __construct(private DiviCompatibility $divi) {}

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
}
