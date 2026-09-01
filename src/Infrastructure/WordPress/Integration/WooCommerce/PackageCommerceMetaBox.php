<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceMappingException;
use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductService;
use AlefDigitalSolutions\ADSTourism\Domain\Commerce\CommerceMode;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use WP_Post;

final class PackageCommerceMetaBox
{
    private const NONCE_ACTION = 'ads_tourism_link_woocommerce_product';

    private const NONCE_NAME = 'ads_tourism_commerce_nonce';

    private ?string $saveNotice = null;

    public function __construct(
        private readonly PackageProductService $products,
        private readonly WooCommerceCompatibility $compatibility,
        private readonly CommerceActionController $actions,
    ) {}

    public function register(): void
    {
        add_meta_box(
            'ads-tourism-package-commerce',
            __('Package commerce', 'ads-tourism'),
            [$this, 'render'],
            ContentType::PACKAGE->value,
            'side',
            'default',
        );
    }

    public function render(WP_Post $post): void
    {
        $mappedProductId = $this->products->mappedProductId($post->ID);

        if (!$this->compatibility->isAvailable()) {
            echo '<p class="notice notice-info inline">';
            echo esc_html__(
                'WooCommerce is not active. Package content and any saved product mapping remain available for later reactivation.',
                'ads-tourism',
            );
            echo '</p>';

            if ($mappedProductId !== null) {
                echo '<p>' . esc_html(sprintf(
                    /* translators: %d is a saved WooCommerce product ID. */
                    __('Saved product ID: %d', 'ads-tourism'),
                    $mappedProductId,
                )) . '</p>';
            }

            return;
        }

        wp_nonce_field(self::NONCE_ACTION, self::NONCE_NAME);
        echo '<p><label for="ads-tourism-product-id"><strong>';
        echo esc_html__('WooCommerce product ID', 'ads-tourism') . '</strong></label></p>';
        echo '<input id="ads-tourism-product-id" name="ads_tourism_product_id" type="number" min="1" step="1"';
        echo ' value="' . esc_attr((string) ($mappedProductId ?? '')) . '" class="widefat">';
        echo '<p class="description">' . esc_html__(
            'Enter an existing product ID and update the Package to link it. A product can belong to only one Package.',
            'ads-tourism',
        ) . '</p>';

        if ($mappedProductId === null) {
            $this->button($this->actions->url($post->ID, 'create'), __('Create draft Product', 'ads-tourism'), true);

            return;
        }

        $productUrl = get_edit_post_link($mappedProductId, 'raw');

        if (is_string($productUrl)) {
            $this->button($productUrl, __('Edit Product', 'ads-tourism'));
        }

        $this->button($this->actions->url($post->ID, 'sync'), __('Sync tourism details', 'ads-tourism'));
        $this->button($this->actions->url($post->ID, 'detach'), __('Detach Product', 'ads-tourism'), false, true);
    }

    public function save(int $postId): void
    {
        if (!$this->requestCanSave($postId) || !array_key_exists('ads_tourism_product_id', $_POST)) {
            return;
        }

        $productId = absint(wp_unslash($_POST['ads_tourism_product_id']));
        $existingProductId = $this->products->mappedProductId($postId);

        if ($productId < 1 || $productId === $existingProductId) {
            return;
        }

        try {
            $this->products->link($postId, $productId);
            $this->saveNotice = 'link_success';
        } catch (CommerceMappingException) {
            $this->saveNotice = 'link_error';
        }
    }

    public function filterRedirect(string $location, int $postId): string
    {
        if (get_post_type($postId) !== ContentType::PACKAGE->value) {
            return $location;
        }

        $notice = $this->saveNotice;
        $mode = CommerceMode::fromStoredValue(get_post_meta($postId, 'ads_tourism_commerce_mode', true));

        if ($notice === null && $mode === CommerceMode::WOOCOMMERCE && $this->products->validProductId($postId) === null) {
            $notice = 'missing_product';
        }

        return $notice === null
            ? $location
            : add_query_arg('ads_tourism_commerce_notice', $notice, $location);
    }

    public function renderNotice(): void
    {
        $notice = sanitize_key((string) wp_unslash($_GET['ads_tourism_commerce_notice'] ?? ''));
        $message = match ($notice) {
            'create_success' => __('Draft WooCommerce product created and linked.', 'ads-tourism'),
            'sync_success' => __('Linked product tourism details synchronized.', 'ads-tourism'),
            'detach_success' => __('WooCommerce product detached. The product was not deleted.', 'ads-tourism'),
            'link_success' => __('Existing WooCommerce product linked.', 'ads-tourism'),
            'missing_product' => __('WooCommerce mode requires an active linked product. Public output will use the configured safe fallback.', 'ads-tourism'),
            'link_error' => __('The selected product could not be linked. Confirm that it exists and is not linked elsewhere.', 'ads-tourism'),
            'action_error' => __('The WooCommerce product action could not be completed.', 'ads-tourism'),
            default => '',
        };

        if ($message === '') {
            return;
        }

        $type = str_contains($notice, 'error') || $notice === 'missing_product' ? 'warning' : 'success';
        echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
    }

    private function requestCanSave(int $postId): bool
    {
        if (
            get_post_type($postId) !== ContentType::PACKAGE->value
            || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
            || wp_is_post_revision($postId) !== false
        ) {
            return false;
        }

        $nonce = sanitize_text_field((string) wp_unslash($_POST[self::NONCE_NAME] ?? ''));

        return $nonce !== ''
            && wp_verify_nonce($nonce, self::NONCE_ACTION) !== false
            && current_user_can('edit_post', $postId)
            && $this->compatibility->isAvailable();
    }

    private function button(string $url, string $label, bool $primary = false, bool $destructive = false): void
    {
        $classes = ['button'];

        if ($primary) {
            $classes[] = 'button-primary';
        }

        if ($destructive) {
            $classes[] = 'button-link-delete';
        }

        echo '<p><a class="' . esc_attr(implode(' ', $classes)) . '" href="' . esc_url($url) . '">';
        echo esc_html($label) . '</a></p>';
    }
}
