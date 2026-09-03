<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress;

final readonly class HelpAdminPage
{
    public const SLUG = 'ads-tourism-help';

    private const DOCUMENTATION_BASE_URL
        = 'https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/blob/main/docs/';

    public function registerMenu(): void
    {
        add_submenu_page(
            AdminMenu::SLUG,
            __('ADS Tourism Help', 'ads-tourism'),
            __('Help', 'ads-tourism'),
            'edit_posts',
            self::SLUG,
            [$this, 'render'],
        );
    }

    public function render(): void
    {
        if (!current_user_can('edit_posts')) {
            wp_die(esc_html__('You do not have permission to view ADS Tourism help.', 'ads-tourism'));
        }

        echo '<div class="wrap"><h1>' . esc_html__('ADS Tourism Help', 'ads-tourism') . '</h1>';
        echo '<p>' . esc_html__(
            'Use this reference to build tourism pages with records, taxonomies, relationships, and shortcodes.',
            'ads-tourism',
        ) . '</p>';
        $this->renderQuickStart();
        $this->renderDocumentationLinks();
        $this->renderShortcodeReference();
        $this->renderLocationAndGalleryHelp();
        $this->renderAdminReferences();
        echo '</div>';
    }

    private function renderQuickStart(): void
    {
        echo '<h2>' . esc_html__('Getting started', 'ads-tourism') . '</h2><ol>';
        echo '<li>' . esc_html__(
            'Create and verify tourism records from the native Places to Go, Things to Do, Places to Stay, Tour Operators, and Packages screens.',
            'ads-tourism',
        ) . '</li>';
        echo '<li>' . esc_html__(
            'Use Tags & Categories to create reusable taxonomy terms, then assign them while editing records.',
            'ads-tourism',
        ) . '</li>';
        echo '<li>' . esc_html__(
            'Use the Tourism relationships panel to connect records such as a place, activity, stay, operator, or package.',
            'ads-tourism',
        ) . '</li>';
        echo '<li>' . esc_html__(
            'Place shortcodes in a Shortcode block, the classic editor, a Divi Code or Text module, or another builder that executes WordPress shortcodes.',
            'ads-tourism',
        ) . '</li></ol>';

        echo '<h3>' . esc_html__('A complete listing recipe', 'ads-tourism') . '</h3>';
        echo '<pre><code>' . esc_html(
            '[ads_tourism_search context="discover"]\n'
            . '[ads_tourism_filters context="discover" fields="area,activity_type,price,duration"]\n'
            . '[ads_tourism_results context="discover" type="activity,package" per_page="12" columns="3"]\n'
            . '[ads_tourism_pagination context="discover" type="activity,package"]',
        ) . '</code></pre>';

        echo '<p><strong>' . esc_html__('Important:', 'ads-tourism') . '</strong> ' . esc_html__(
            'Controls and results that belong together must use the same context. A context may contain only one primary results grid or all-in-one listing.',
            'ads-tourism',
        ) . '</p>';
    }

    private function renderDocumentationLinks(): void
    {
        echo '<h2>' . esc_html__('Documentation and guides', 'ads-tourism') . '</h2>';
        echo '<p>' . esc_html__(
            'Open a detailed guide on GitHub for step-by-step help.',
            'ads-tourism',
        ) . '</p><ul>';

        foreach ([
            [__('Documentation home', 'ads-tourism'), 'README.md'],
            [__('Five-minute setup', 'ads-tourism'), 'user/getting-started.md'],
            [__('Record editing and workflow', 'ads-tourism'), 'user/record-workflow.md'],
            [__('Relationships between records', 'ads-tourism'), 'user/relationships.md'],
            [__('Media, galleries, and permalinks', 'ads-tourism'), 'user/media-and-permalinks.md'],
            [__('CSV Import/Export', 'ads-tourism'), 'user/csv-import-export.md'],
            [__('Templates and page builders', 'ads-tourism'), 'user/templates-and-builders.md'],
            [__('Shortcodes and interactive listings', 'ads-tourism'), 'user/shortcodes.md'],
            [__('SEO, maps, and languages', 'ads-tourism'), 'user/seo-maps-and-languages.md'],
            [__('WooCommerce for Packages', 'ads-tourism'), 'user/woocommerce.md'],
            [__('Maintenance and privacy', 'ads-tourism'), 'user/maintenance-and-privacy.md'],
            [__('Troubleshooting', 'ads-tourism'), 'user/troubleshooting.md'],
        ] as [$label, $path]) {
            echo '<li><a href="' . esc_url(self::DOCUMENTATION_BASE_URL . $path)
                . '" target="_blank" rel="noopener noreferrer">' . esc_html($label) . '</a></li>';
        }

        echo '</ul>';
    }

    private function renderShortcodeReference(): void
    {
        echo '<h2>' . esc_html__('Shortcode reference', 'ads-tourism') . '</h2>';
        echo '<p>' . esc_html__(
            'Every shortcode below uses the ads_tourism_ prefix. Attributes not listed for a shortcode are ignored. Use quoted values for text and comma-separated values where shown.',
            'ads-tourism',
        ) . '</p>';
        echo '<p><a href="' . esc_url(
            self::DOCUMENTATION_BASE_URL . 'user/shortcodes.md',
        ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__(
            'Open the full shortcode guide on GitHub.',
            'ads-tourism',
        ) . '</a></p>';

        foreach ($this->shortcodeReferences() as $group => $references) {
            echo '<h3>' . esc_html($group) . '</h3>';
            echo '<table class="widefat striped"><thead><tr><th scope="col">'
                . esc_html__('Shortcode', 'ads-tourism') . '</th><th scope="col">'
                . esc_html__('Purpose', 'ads-tourism') . '</th><th scope="col">'
                . esc_html__('Example syntax', 'ads-tourism') . '</th><th scope="col">'
                . esc_html__('Supported attributes', 'ads-tourism') . '</th></tr></thead><tbody>';

            foreach ($references as $reference) {
                echo '<tr><th scope="row"><code>[' . esc_html($reference['shortcode']) . ']</code></th>'
                    . '<td>' . esc_html($reference['purpose']) . '</td><td><code>'
                    . esc_html($reference['syntax']) . '</code></td><td>'
                    . esc_html($reference['attributes']) . '</td></tr>';
            }

            echo '</tbody></table>';
        }

        echo '<h3>' . esc_html__('Shortcode rules and field names', 'ads-tourism') . '</h3><ul>';
        echo '<li>' . esc_html__(
            'The filters fields attribute accepts area, place_type, activity_type, stay_type, package_type, amenity, traveller, accessibility, tag, place, activity, stay, operator, package, provider, price, and duration.',
            'ads-tourism',
        ) . '</li>';
        echo '<li>' . esc_html__(
            'CSV taxonomy columns use term slugs, such as history. The filters shortcode exposes a Tourism Tags selector with fields="tag".',
            'ads-tourism',
        ) . '</li>';
        echo '<li>' . esc_html__(
            'The field shortcode accepts public scalar tourism field keys, with or without the ads_tourism_ prefix. Structured arrays and administrator-only fields are not rendered by it.',
            'ads-tourism',
        ) . '</li>';
        echo '<li>' . esc_html__(
            'Maps require a configured map provider and records with valid coordinates. Commerce controls are available only when the optional WooCommerce integration is active and the Package is configured for commerce.',
            'ads-tourism',
        ) . '</li></ul>';
    }

    private function renderAdminReferences(): void
    {
        echo '<h2>' . esc_html__('Administration references', 'ads-tourism') . '</h2><ul>';
        foreach ([
            [__('Places to Go', 'ads-tourism'), 'edit.php?post_type=ads_place'],
            [__('Things to Do', 'ads-tourism'), 'edit.php?post_type=ads_activity'],
            [__('Places to Stay', 'ads-tourism'), 'edit.php?post_type=ads_stay'],
            [__('Tour Operators', 'ads-tourism'), 'edit.php?post_type=ads_operator'],
            [__('Packages', 'ads-tourism'), 'edit.php?post_type=ads_package'],
            [__('Tags & Categories', 'ads-tourism'), 'admin.php?page=' . AdminMenu::TAXONOMY_SLUG],
            [__('CSV Import/Export', 'ads-tourism'), 'admin.php?page=ads-tourism-transfer'],
            [__('Settings', 'ads-tourism'), 'admin.php?page=ads-tourism-settings'],
        ] as [$label, $path]) {
            echo '<li><a href="' . esc_url(admin_url($path)) . '">' . esc_html($label) . '</a></li>';
        }

        echo '</ul>';
    }

    private function renderLocationAndGalleryHelp(): void
    {
        echo '<h2>' . esc_html__('Locations, maps, and galleries', 'ads-tourism') . '</h2>';
        echo '<p>' . esc_html__(
            'Every tourism record has a Tourism locations panel for one or more GPS points. Enter a label, valid latitude and longitude, role, primary status, map visibility, and order. Existing legacy coordinates are migrated as the primary location.',
            'ads-tourism',
        ) . '</p>';
        echo '<p><code>' . esc_html('[ads_tourism_map locations="primary"]') . '</code> ' . esc_html__(
            'shows one primary marker per record. Use locations="all" to show every visible location; marker labels appear in the information window. Maps require an available configured provider.',
            'ads-tourism',
        ) . '</p>';
        echo '<p>' . esc_html__(
            'Use the Tourism gallery panel on each record to select multiple Media Library images, reorder them, set a primary image, role, caption, credit, alt text, and rights notice. Detaching an association never deletes the underlying Media Library file, and one attachment can be reused by multiple records.',
            'ads-tourism',
        ) . '</p>';
        echo '<p><code>' . esc_html('[ads_tourism_gallery columns="3" captions="true" lightbox="true"]') . '</code> '
            . esc_html__('supports id, limit, columns, role, order, size, captions, credits, lightbox, and class overrides.', 'ads-tourism') . '</p>';
    }

    /**
     * @return array<string, list<array{shortcode: string, purpose: string, syntax: string, attributes: string}>>
     */
    private function shortcodeReferences(): array
    {
        $listingAttributes = 'context, query, per_page (1–24), columns (1–6), sort, pagination, class';
        $componentAttributes = 'context, type, query, per_page, sort, pagination, class';

        return [
            __('Listings', 'ads-tourism') => [
                [
                    'shortcode' => 'ads_tourism_places',
                    'purpose' => __('Show a Places to Go listing.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_places per_page="12" columns="3"]',
                    'attributes' => $listingAttributes,
                ],
                [
                    'shortcode' => 'ads_tourism_activities',
                    'purpose' => __('Show a Things to Do listing.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_activities per_page="9" sort="newest"]',
                    'attributes' => $listingAttributes,
                ],
                [
                    'shortcode' => 'ads_tourism_stays',
                    'purpose' => __('Show a Places to Stay listing.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_stays pagination="load_more"]',
                    'attributes' => $listingAttributes,
                ],
                [
                    'shortcode' => 'ads_tourism_operators',
                    'purpose' => __('Show a Tour Operators listing.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_operators]',
                    'attributes' => $listingAttributes,
                ],
                [
                    'shortcode' => 'ads_tourism_packages',
                    'purpose' => __('Show a Packages listing.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_packages sort="price_asc"]',
                    'attributes' => $listingAttributes,
                ],
                [
                    'shortcode' => 'ads_tourism_listing',
                    'purpose' => __('Show a mixed catalogue listing.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_listing type="activity,package" per_page="12" columns="4"]',
                    'attributes' => 'type (place, activity, stay, operator, package, all, or a comma-separated list), ' . $listingAttributes,
                ],
            ],
            __('Composable controls', 'ads-tourism') => [
                [
                    'shortcode' => 'ads_tourism_search',
                    'purpose' => __('Render a keyword search control.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_search context="discover"]',
                    'attributes' => $componentAttributes,
                ],
                [
                    'shortcode' => 'ads_tourism_filters',
                    'purpose' => __('Render taxonomy, relationship, price, and duration filters.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_filters context="discover" fields="area,activity_type,price"]',
                    'attributes' => 'fields plus ' . $componentAttributes,
                ],
                [
                    'shortcode' => 'ads_tourism_sort',
                    'purpose' => __('Render an allowlisted sorting control.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_sort context="discover"]',
                    'attributes' => $componentAttributes,
                ],
                [
                    'shortcode' => 'ads_tourism_results',
                    'purpose' => __('Render the primary results grid for a context.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_results context="discover" type="activity,package"]',
                    'attributes' => $componentAttributes . '; context is required',
                ],
                [
                    'shortcode' => 'ads_tourism_pagination',
                    'purpose' => __('Render pagination for a context.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_pagination context="discover" type="activity,package"]',
                    'attributes' => $componentAttributes . '; context is required',
                ],
            ],
            __('Record components', 'ads-tourism') => [
                [
                    'shortcode' => 'ads_tourism_field',
                    'purpose' => __('Render one public scalar field from the current or selected record.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_field field="summary" label="true"]',
                    'attributes' => 'field, id, label (true/false), class',
                ],
                [
                    'shortcode' => 'ads_tourism_gallery',
                    'purpose' => __('Render the record gallery.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_gallery columns="3" captions="true"]',
                    'attributes' => 'id, limit, columns, role, order, size, captions, credits, lightbox, class',
                ],
                [
                    'shortcode' => 'ads_tourism_related_places',
                    'purpose' => __('Render related Places to Go.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_related_places]',
                    'attributes' => 'id, class',
                ],
                [
                    'shortcode' => 'ads_tourism_related_activities',
                    'purpose' => __('Render related Things to Do.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_related_activities]',
                    'attributes' => 'id, class',
                ],
                [
                    'shortcode' => 'ads_tourism_related_stays',
                    'purpose' => __('Render related Places to Stay.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_related_stays]',
                    'attributes' => 'id, class',
                ],
                [
                    'shortcode' => 'ads_tourism_related_operators',
                    'purpose' => __('Render related Tour Operators.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_related_operators]',
                    'attributes' => 'id, class',
                ],
                [
                    'shortcode' => 'ads_tourism_related_packages',
                    'purpose' => __('Render related Packages.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_related_packages]',
                    'attributes' => 'id, class',
                ],
                [
                    'shortcode' => 'ads_tourism_package_itinerary',
                    'purpose' => __('Render a Package itinerary.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_package_itinerary]',
                    'attributes' => 'id, class',
                ],
                [
                    'shortcode' => 'ads_tourism_package_provider',
                    'purpose' => __('Render the providers related to a Package.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_package_provider]',
                    'attributes' => 'id, class',
                ],
            ],
            __('Maps', 'ads-tourism') => [
                [
                    'shortcode' => 'ads_tourism_map',
                    'purpose' => __('Render one or more tourism map markers.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_map type="place,stay" height="480"]',
                    'attributes' => 'id, ids, context, type, query, per_page, sort, locations (primary/all), zoom (0–22), height (200–1000), marker_limit (1–100), fallback (none/directions), class',
                ],
            ],
            __('Optional commerce', 'ads-tourism') => [
                [
                    'shortcode' => 'ads_tourism_commerce_controls',
                    'purpose' => __('Render catalogue, enquiry, cart, or purchase controls for a Package.', 'ads-tourism'),
                    'syntax' => '[ads_tourism_commerce_controls action="configured"]',
                    'attributes' => 'id, action (configured/add_to_cart/buy_now/both), class; requires the optional WooCommerce integration',
                ],
            ],
        ];
    }
}
