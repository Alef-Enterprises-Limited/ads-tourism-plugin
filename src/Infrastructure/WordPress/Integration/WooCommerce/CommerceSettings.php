<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Domain\Commerce\CommerceMode;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\AdminMenu;

final readonly class CommerceSettings
{
    public const OPTION = 'ads_tourism_woocommerce_settings';

    public const PAGE_SLUG = 'ads-tourism-woocommerce';

    public function __construct(private WooCommerceCompatibility $compatibility) {}

    public function registerMenu(): void
    {
        if (!$this->compatibility->isAvailable()) {
            return;
        }

        add_submenu_page(
            AdminMenu::SLUG,
            __('ADS Tourism WooCommerce', 'ads-tourism'),
            __('WooCommerce', 'ads-tourism'),
            'manage_options',
            self::PAGE_SLUG,
            [$this, 'render'],
        );
    }

    public function registerSettings(): void
    {
        if (!$this->compatibility->isAvailable()) {
            return;
        }

        register_setting('ads_tourism_woocommerce', self::OPTION, [
            'type' => 'object',
            'default' => $this->defaults(),
            'sanitize_callback' => [$this, 'sanitize'],
        ]);
        add_settings_section(
            'ads_tourism_woocommerce_display',
            __('Commerce display', 'ads-tourism'),
            [$this, 'renderSection'],
            self::PAGE_SLUG,
        );
        add_settings_field(
            'ads_tourism_listing_destination',
            __('Package listing destination', 'ads-tourism'),
            [$this, 'renderListingDestination'],
            self::PAGE_SLUG,
            'ads_tourism_woocommerce_display',
        );
        add_settings_field(
            'ads_tourism_package_controls',
            __('Package page controls', 'ads-tourism'),
            [$this, 'renderControls'],
            self::PAGE_SLUG,
            'ads_tourism_woocommerce_display',
        );
        add_settings_field(
            'ads_tourism_invalid_mode_fallback',
            __('Invalid WooCommerce-mode fallback', 'ads-tourism'),
            [$this, 'renderFallback'],
            self::PAGE_SLUG,
            'ads_tourism_woocommerce_display',
        );
    }

    /**
     * @param array<string, mixed> $input
     *
     * @return array{listing_destination: string, package_controls: string, invalid_mode_fallback: string}
     */
    public function sanitize(array $input): array
    {
        $listingDestination = (string) ($input['listing_destination'] ?? '');
        $packageControls = (string) ($input['package_controls'] ?? '');
        $fallback = (string) ($input['invalid_mode_fallback'] ?? '');

        return [
            'listing_destination' => in_array($listingDestination, ['package', 'product'], true)
                ? $listingDestination
                : 'package',
            'package_controls' => in_array($packageControls, ['add_to_cart', 'buy_now', 'both'], true)
                ? $packageControls
                : 'add_to_cart',
            'invalid_mode_fallback' => $fallback === CommerceMode::ENQUIRY->value
                ? CommerceMode::ENQUIRY->value
                : CommerceMode::CATALOGUE->value,
        ];
    }

    public function listingDestination(): string
    {
        return (string) $this->values()['listing_destination'];
    }

    public function packageControls(): string
    {
        return (string) $this->values()['package_controls'];
    }

    public function invalidModeFallback(): CommerceMode
    {
        return CommerceMode::fromStoredValue($this->values()['invalid_mode_fallback']);
    }

    public function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You do not have permission to manage WooCommerce integration settings.', 'ads-tourism'));
        }

        echo '<div class="wrap"><h1>' . esc_html__('ADS Tourism WooCommerce', 'ads-tourism') . '</h1>';
        echo '<p>' . esc_html__(
            'WooCommerce owns prices, tax, stock, cart, checkout, orders, and payment. ADS Tourism keeps the Package description, itinerary, relationships, and tourism media.',
            'ads-tourism',
        ) . '</p><form action="options.php" method="post">';
        settings_fields('ads_tourism_woocommerce');
        do_settings_sections(self::PAGE_SLUG);
        submit_button();
        echo '</form></div>';
    }

    public function renderSection(): void
    {
        echo '<p>' . esc_html__(
            'These settings apply only to Packages in WooCommerce mode with a valid linked product.',
            'ads-tourism',
        ) . '</p>';
    }

    public function renderListingDestination(): void
    {
        $this->select('listing_destination', [
            'package' => __('Package page', 'ads-tourism'),
            'product' => __('WooCommerce product page', 'ads-tourism'),
        ]);
    }

    public function renderControls(): void
    {
        $this->select('package_controls', [
            'add_to_cart' => __('Add to Cart', 'ads-tourism'),
            'buy_now' => __('Buy Now', 'ads-tourism'),
            'both' => __('Add to Cart and Buy Now', 'ads-tourism'),
        ]);
    }

    public function renderFallback(): void
    {
        $this->select('invalid_mode_fallback', [
            CommerceMode::CATALOGUE->value => __('Catalogue', 'ads-tourism'),
            CommerceMode::ENQUIRY->value => __('Enquiry', 'ads-tourism'),
        ]);
    }

    /** @param array<string, string> $options */
    private function select(string $key, array $options): void
    {
        $value = (string) $this->values()[$key];
        echo '<select name="' . esc_attr(self::OPTION . '[' . $key . ']') . '">';

        foreach ($options as $optionValue => $label) {
            echo '<option value="' . esc_attr($optionValue) . '" ' . selected($value, $optionValue, false) . '>';
            echo esc_html($label) . '</option>';
        }

        echo '</select>';
    }

    /** @return array{listing_destination: string, package_controls: string, invalid_mode_fallback: string} */
    private function values(): array
    {
        $stored = get_option(self::OPTION, []);
        $stored = is_array($stored) ? $stored : [];

        return array_replace($this->defaults(), $this->sanitize($stored));
    }

    /** @return array{listing_destination: string, package_controls: string, invalid_mode_fallback: string} */
    private function defaults(): array
    {
        return [
            'listing_destination' => 'package',
            'package_controls' => 'add_to_cart',
            'invalid_mode_fallback' => CommerceMode::CATALOGUE->value,
        ];
    }
}
