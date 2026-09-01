<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceMappingException;
use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductService;
use AlefDigitalSolutions\ADSTourism\Domain\Content\ContentType;

final readonly class CommerceActionController
{
    public const ACTION = 'ads_tourism_woocommerce_product';

    private const NONCE_ACTION = 'ads_tourism_woocommerce_product_action';

    public function __construct(
        private PackageProductService $products,
        private PackageProductDataFactory $packages,
    ) {}

    public function handle(): void
    {
        $packageId = absint($_GET['package_id'] ?? 0);
        $operation = sanitize_key((string) wp_unslash($_GET['operation'] ?? ''));
        check_admin_referer(self::NONCE_ACTION . '_' . $packageId);

        if (
            $packageId < 1
            || get_post_type($packageId) !== ContentType::PACKAGE->value
            || !current_user_can('edit_post', $packageId)
        ) {
            wp_die(esc_html__('You cannot manage commerce for this Package.', 'ads-tourism'));
        }

        try {
            if ($operation === 'create') {
                $this->products->create($this->packages->forPackage($packageId));
            } elseif ($operation === 'sync') {
                $this->products->sync($this->packages->forPackage($packageId));
            } elseif ($operation === 'detach') {
                $this->products->detach($packageId);
            } else {
                throw new CommerceMappingException('Choose a valid commerce action.');
            }

            $notice = $operation . '_success';
        } catch (CommerceMappingException) {
            $notice = 'action_error';
        }

        $url = add_query_arg([
            'post' => (string) $packageId,
            'action' => 'edit',
            'ads_tourism_commerce_notice' => $notice,
        ], admin_url('post.php'));
        wp_safe_redirect($url);
        exit;
    }

    public function url(int $packageId, string $operation): string
    {
        $url = add_query_arg([
            'action' => self::ACTION,
            'package_id' => (string) $packageId,
            'operation' => $operation,
        ], admin_url('admin-post.php'));

        return wp_nonce_url($url, self::NONCE_ACTION . '_' . $packageId);
    }
}
