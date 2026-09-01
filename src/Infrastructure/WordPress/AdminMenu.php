<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress;

use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;

final class AdminMenu
{
    public const SLUG = 'ads-tourism';

    public const TAXONOMY_SLUG = 'ads-tourism-tags-categories';

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

        add_submenu_page(
            self::SLUG,
            __('Tags & Categories', 'ads-tourism'),
            __('Tags & Categories', 'ads-tourism'),
            'manage_categories',
            self::TAXONOMY_SLUG,
            [$this, 'renderTaxonomyDashboard'],
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
            'Use the native content screens to add structured details, connect related records, and manage verification before publication.',
            'ads-tourism',
        );
        echo '</p>';
        echo '</div>';
    }

    public function renderTaxonomyDashboard(): void
    {
        if (!current_user_can('manage_categories')) {
            wp_die(esc_html__('You do not have permission to manage tourism taxonomies.', 'ads-tourism'));
        }

        echo '<div class="wrap">';
        echo '<h1>' . esc_html__('Tags & Categories', 'ads-tourism') . '</h1>';
        echo '<p>' . esc_html__(
            'Manage the taxonomy terms used to classify places, activities, accommodation, operators, and packages.',
            'ads-tourism',
        ) . '</p>';
        echo '<ul>';

        foreach ($this->taxonomyManagementItems() as $item) {
            echo '<li><a href="' . esc_url(admin_url($item['url'])) . '">'
                . esc_html($item['label']) . '</a></li>';
        }

        echo '</ul>';
        echo '</div>';
    }

    /**
     * @return list<array{label: string, url: string}>
     */
    private function taxonomyManagementItems(): array
    {
        $items = [];

        foreach (TourismTaxonomy::cases() as $taxonomy) {
            $items[] = [
                'label' => $this->taxonomyLabel($taxonomy),
                'url' => $this->taxonomyManagementUrl($taxonomy),
            ];
        }

        return $items;
    }

    private function taxonomyManagementUrl(TourismTaxonomy $taxonomy): string
    {
        $objectType = $taxonomy->objectTypes()[0];

        return 'edit-tags.php?taxonomy=' . rawurlencode($taxonomy->value)
            . '&post_type=' . rawurlencode($objectType);
    }

    private function taxonomyLabel(TourismTaxonomy $taxonomy): string
    {
        return match ($taxonomy) {
            TourismTaxonomy::PLACE_TYPE => __('Place Types', 'ads-tourism'),
            TourismTaxonomy::ACTIVITY_TYPE => __('Activity Types', 'ads-tourism'),
            TourismTaxonomy::STAY_TYPE => __('Stay Types', 'ads-tourism'),
            TourismTaxonomy::PACKAGE_TYPE => __('Package Types', 'ads-tourism'),
            TourismTaxonomy::AMENITY => __('Amenities', 'ads-tourism'),
            TourismTaxonomy::TRAVELLER => __('Traveller Types', 'ads-tourism'),
            TourismTaxonomy::ACCESSIBILITY => __('Accessibility Features', 'ads-tourism'),
            TourismTaxonomy::TOURISM_TAG => __('Tourism Tags', 'ads-tourism'),
            TourismTaxonomy::GEOGRAPHIC_AREA => __('Geographic Areas', 'ads-tourism'),
        };
    }
}
