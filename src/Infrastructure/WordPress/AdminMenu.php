<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress;

final class AdminMenu
{
    public const SLUG = 'ads-tourism';

    public function register(): void
    {
        add_menu_page(
            __('ADS Tourism', 'ads-tourism'),
            __('ADS Tourism', 'ads-tourism'),
            'edit_posts',
            self::SLUG,
            [$this, 'renderDashboard'],
            'dashicons-palmtree',
            25,
        );
    }

    public function renderDashboard(): void
    {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have permission to access ADS Tourism.', 'ads-tourism'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('ADS Tourism', 'ads-tourism') . '</h1>';
        echo '<p>';
        echo esc_html__(
            'Manage destinations, activities, accommodation, tour operators, and packages from the ADS Tourism menu.',
            'ads-tourism',
        );
        echo '</p>';
        echo '<p>';
        echo esc_html__(
            'This foundation release uses native WordPress content screens. Relationship, import, display, and integration tools will be added in later milestones.',
            'ads-tourism',
        );
        echo '</p>';
        echo '</div>';
    }
}
