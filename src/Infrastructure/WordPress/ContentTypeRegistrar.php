<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress;

use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Permalink\PermalinkSettings;

final readonly class ContentTypeRegistrar
{
    public function __construct(private PermalinkSettings $permalinks) {}

    public function register(): void
    {
        foreach (ContentType::cases() as $contentType) {
            register_post_type($contentType->value, $this->arguments($contentType));
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function arguments(ContentType $contentType): array
    {
        return [
            'labels' => $this->labels($contentType),
            'description' => $this->description($contentType),
            'public' => true,
            'hierarchical' => $contentType->isHierarchical(),
            'exclude_from_search' => false,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => AdminMenu::SLUG,
            'show_in_nav_menus' => true,
            'show_in_admin_bar' => true,
            'show_in_rest' => true,
            'menu_icon' => 'dashicons-location-alt',
            'supports' => $contentType->supportedEditorFeatures(),
            'has_archive' => true,
            'rewrite' => [
                'slug' => $this->permalinks->contentTypeBase($contentType),
                'with_front' => false,
                'hierarchical' => $contentType === ContentType::PLACE
                    && $this->permalinks->hierarchicalPlaces(),
            ],
            'query_var' => true,
            'can_export' => true,
            'delete_with_user' => false,
            'capability_type' => 'post',
            'map_meta_cap' => true,
        ];
    }

    /**
     * @return array<string, string>
     */
    private function labels(ContentType $contentType): array
    {
        [$singular, $plural] = match ($contentType) {
            ContentType::PLACE => [__('Place to Go', 'ads-tourism'), __('Places to Go', 'ads-tourism')],
            ContentType::ACTIVITY => [__('Thing to Do', 'ads-tourism'), __('Things to Do', 'ads-tourism')],
            ContentType::STAY => [__('Place to Stay', 'ads-tourism'), __('Places to Stay', 'ads-tourism')],
            ContentType::OPERATOR => [__('Tour Operator', 'ads-tourism'), __('Tour Operators', 'ads-tourism')],
            ContentType::PACKAGE => [__('Package', 'ads-tourism'), __('Packages', 'ads-tourism')],
        };

        return [
            'name' => $plural,
            'singular_name' => $singular,
            'menu_name' => $plural,
            'name_admin_bar' => $singular,
            'add_new' => __('Add New', 'ads-tourism'),
            'add_new_item' => sprintf(__('Add New %s', 'ads-tourism'), $singular),
            'new_item' => sprintf(__('New %s', 'ads-tourism'), $singular),
            'edit_item' => sprintf(__('Edit %s', 'ads-tourism'), $singular),
            'view_item' => sprintf(__('View %s', 'ads-tourism'), $singular),
            'all_items' => sprintf(__('All %s', 'ads-tourism'), $plural),
            'search_items' => sprintf(__('Search %s', 'ads-tourism'), $plural),
            'parent_item_colon' => sprintf(__('Parent %s:', 'ads-tourism'), $singular),
            'not_found' => sprintf(__('No %s found.', 'ads-tourism'), $plural),
            'not_found_in_trash' => sprintf(__('No %s found in Trash.', 'ads-tourism'), $plural),
            'featured_image' => __('Featured image', 'ads-tourism'),
            'set_featured_image' => __('Set featured image', 'ads-tourism'),
            'remove_featured_image' => __('Remove featured image', 'ads-tourism'),
            'use_featured_image' => __('Use as featured image', 'ads-tourism'),
            'archives' => sprintf(__('%s archives', 'ads-tourism'), $singular),
            'attributes' => sprintf(__('%s attributes', 'ads-tourism'), $singular),
            'insert_into_item' => sprintf(__('Insert into %s', 'ads-tourism'), $singular),
            'uploaded_to_this_item' => sprintf(__('Uploaded to this %s', 'ads-tourism'), $singular),
            'filter_items_list' => sprintf(__('Filter %s list', 'ads-tourism'), $plural),
            'items_list_navigation' => sprintf(__('%s list navigation', 'ads-tourism'), $plural),
            'items_list' => sprintf(__('%s list', 'ads-tourism'), $plural),
        ];
    }

    private function description(ContentType $contentType): string
    {
        return match ($contentType) {
            ContentType::PLACE => __('Tourism destinations and geographic places.', 'ads-tourism'),
            ContentType::ACTIVITY => __('Activities, attractions, and experiences for visitors.', 'ads-tourism'),
            ContentType::STAY => __('Accommodation and other places where visitors can stay.', 'ads-tourism'),
            ContentType::OPERATOR => __('Tourism businesses and tour operators.', 'ads-tourism'),
            ContentType::PACKAGE => __('Tourism offers and packages from operators or accommodation providers.', 'ads-tourism'),
        };
    }
}
