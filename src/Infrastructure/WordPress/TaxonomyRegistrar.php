<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress;

use AlefDigitalSolutions\ADSTourism\Domain\Taxonomy\TourismTaxonomy;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\PermalinkSettings;

final readonly class TaxonomyRegistrar
{
    public function __construct(private PermalinkSettings $permalinks) {}

    public function register(): void
    {
        foreach (TourismTaxonomy::cases() as $taxonomy) {
            register_taxonomy($taxonomy->value, $taxonomy->objectTypes(), $this->arguments($taxonomy));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function arguments(TourismTaxonomy $taxonomy): array
    {
        return [
            'labels' => $this->labels($taxonomy),
            'public' => true,
            'publicly_queryable' => true,
            'hierarchical' => $taxonomy->isHierarchical(),
            'show_ui' => true,
            'show_admin_column' => true,
            'show_in_nav_menus' => true,
            'show_tagcloud' => true,
            'show_in_quick_edit' => true,
            'show_in_rest' => true,
            'rewrite' => [
                'slug' => $this->permalinks->taxonomyBase($taxonomy),
                'with_front' => false,
                'hierarchical' => $taxonomy->isHierarchical(),
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    private function labels(TourismTaxonomy $taxonomy): array
    {
        [$singular, $plural] = match ($taxonomy) {
            TourismTaxonomy::PLACE_TYPE => [__('Place Type', 'ads-tourism'), __('Place Types', 'ads-tourism')],
            TourismTaxonomy::ACTIVITY_TYPE => [__('Activity Type', 'ads-tourism'), __('Activity Types', 'ads-tourism')],
            TourismTaxonomy::STAY_TYPE => [__('Stay Type', 'ads-tourism'), __('Stay Types', 'ads-tourism')],
            TourismTaxonomy::PACKAGE_TYPE => [__('Package Type', 'ads-tourism'), __('Package Types', 'ads-tourism')],
            TourismTaxonomy::AMENITY => [__('Amenity', 'ads-tourism'), __('Amenities', 'ads-tourism')],
            TourismTaxonomy::TRAVELLER => [__('Traveller Type', 'ads-tourism'), __('Traveller Types', 'ads-tourism')],
            TourismTaxonomy::ACCESSIBILITY => [__('Accessibility Feature', 'ads-tourism'), __('Accessibility Features', 'ads-tourism')],
            TourismTaxonomy::TOURISM_TAG => [__('Tourism Tag', 'ads-tourism'), __('Tourism Tags', 'ads-tourism')],
            TourismTaxonomy::GEOGRAPHIC_AREA => [__('Geographic Area', 'ads-tourism'), __('Geographic Areas', 'ads-tourism')],
        };

        return [
            'name' => $plural,
            'singular_name' => $singular,
            'search_items' => sprintf(__('Search %s', 'ads-tourism'), $plural),
            'popular_items' => sprintf(__('Popular %s', 'ads-tourism'), $plural),
            'all_items' => sprintf(__('All %s', 'ads-tourism'), $plural),
            'parent_item' => sprintf(__('Parent %s', 'ads-tourism'), $singular),
            'parent_item_colon' => sprintf(__('Parent %s:', 'ads-tourism'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'ads-tourism'), $singular),
            'view_item' => sprintf(__('View %s', 'ads-tourism'), $singular),
            'update_item' => sprintf(__('Update %s', 'ads-tourism'), $singular),
            'add_new_item' => sprintf(__('Add New %s', 'ads-tourism'), $singular),
            'new_item_name' => sprintf(__('New %s Name', 'ads-tourism'), $singular),
            'separate_items_with_commas' => sprintf(__('Separate %s with commas', 'ads-tourism'), $plural),
            'add_or_remove_items' => sprintf(__('Add or remove %s', 'ads-tourism'), $plural),
            'choose_from_most_used' => sprintf(__('Choose from the most used %s', 'ads-tourism'), $plural),
            'not_found' => sprintf(__('No %s found.', 'ads-tourism'), $plural),
            'no_terms' => sprintf(__('No %s', 'ads-tourism'), $plural),
            'items_list_navigation' => sprintf(__('%s list navigation', 'ads-tourism'), $plural),
            'items_list' => sprintf(__('%s list', 'ads-tourism'), $plural),
            'back_to_items' => sprintf(__('Back to %s', 'ads-tourism'), $plural),
        ];
    }
}
