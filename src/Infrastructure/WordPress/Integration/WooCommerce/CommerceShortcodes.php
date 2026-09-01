<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceProductGatewayInterface;
use AlefDigitalSolutions\ADSTourism\Domain\Commerce\CommerceMode;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\ShortcodeAssets;
use AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Shortcode\ShortcodeDiagnostic;

final readonly class CommerceShortcodes
{
    public function __construct(
        private PackageCommerceResolver $commerce,
        private CommerceProductGatewayInterface $products,
        private CommerceSettings $settings,
        private ShortcodeAssets $assets,
        private ShortcodeDiagnostic $diagnostics,
    ) {}

    public function register(): void
    {
        add_shortcode('ads_tourism_commerce_controls', [$this, 'controls']);
        add_action('ads_tourism_after_structured_content', [$this, 'renderDefault']);
    }

    /** @param array<string, mixed> $attributes */
    public function controls(array $attributes): string
    {
        $attributes = shortcode_atts([
            'id' => 0,
            'action' => 'configured',
            'class' => '',
        ], $attributes, 'ads_tourism_commerce_controls');
        $postId = absint($attributes['id']);
        $postId = $postId > 0
            ? $postId
            : absint(apply_filters('ads_tourism_record_context_id', get_the_ID(), $attributes));

        if (get_post_type($postId) !== ContentType::PACKAGE->value) {
            return $this->diagnostics->render(__('A Package is required for commerce controls.', 'ads-tourism'));
        }

        $this->assets->enqueue();

        $resolution = $this->commerce->forPackage($postId);
        $class = sanitize_html_class((string) $attributes['class']);
        $classAttribute = $class === '' ? '' : ' ' . esc_attr($class);

        if ($resolution->effectiveMode === CommerceMode::CATALOGUE) {
            return $this->nonTransactionalControl($postId, $classAttribute, false);
        }

        if ($resolution->effectiveMode === CommerceMode::ENQUIRY) {
            return $this->nonTransactionalControl($postId, $classAttribute, true);
        }

        if (!$resolution->usesWooCommerce()) {
            return '';
        }

        $action = sanitize_key((string) $attributes['action']);
        $action = $action === 'configured' ? $this->settings->packageControls() : $action;

        if (!in_array($action, ['add_to_cart', 'buy_now', 'both'], true)) {
            return $this->diagnostics->render(__('Choose a valid commerce control action.', 'ads-tourism'));
        }

        $html = '<div class="ads-tourism-commerce-controls' . $classAttribute . '">';
        $productId = (int) $resolution->productId;

        if (in_array($action, ['add_to_cart', 'both'], true)) {
            $url = $this->products->addToCartUrl($productId);

            if ($url !== null) {
                $html .= $this->link($url, __('Add to Cart', 'ads-tourism'), 'ads-tourism-commerce-controls__cart');
            }
        }

        if (in_array($action, ['buy_now', 'both'], true)) {
            $url = $this->products->checkoutUrl($productId);

            if ($url !== null) {
                $html .= $this->link($url, __('Buy Now', 'ads-tourism'), 'ads-tourism-commerce-controls__buy-now');
            }
        }

        return $html === '<div class="ads-tourism-commerce-controls' . $classAttribute . '">'
            ? ''
            : $html . '</div>';
    }

    public function renderDefault(int $postId): void
    {
        if (get_post_type($postId) === ContentType::PACKAGE->value) {
            echo $this->controls(['id' => $postId]);
        }
    }

    private function nonTransactionalControl(int $postId, string $classAttribute, bool $enquiry): string
    {
        $url = get_post_meta($postId, 'ads_tourism_catalogue_cta_url', true);

        if (!is_string($url) || $url === '') {
            return '';
        }

        $label = get_post_meta($postId, 'ads_tourism_catalogue_cta_label', true);
        $label = is_string($label) ? trim($label) : '';

        if ($label === '') {
            $label = $enquiry ? __('Enquire now', 'ads-tourism') : __('View offer', 'ads-tourism');
        }

        $modifier = $enquiry ? 'enquiry' : 'catalogue';

        return '<div class="ads-tourism-commerce-controls ads-tourism-commerce-controls--' . $modifier . $classAttribute . '">'
            . $this->link($url, $label, 'ads-tourism-commerce-controls__' . $modifier)
            . '</div>';
    }

    private function link(string $url, string $label, string $className): string
    {
        return '<a class="button ' . esc_attr($className) . '" href="' . esc_url($url) . '">'
            . esc_html($label) . '</a>';
    }
}
