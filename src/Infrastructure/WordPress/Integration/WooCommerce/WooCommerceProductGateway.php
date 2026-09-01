<?php

declare(strict_types=1);

namespace AlefDigitalSolutions\ADSTourism\Infrastructure\WordPress\Integration\WooCommerce;

use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceMappingException;
use AlefDigitalSolutions\ADSTourism\Application\Commerce\CommerceProductGatewayInterface;
use AlefDigitalSolutions\ADSTourism\Application\Commerce\PackageProductData;
use WC_Product;
use WC_Product_Simple;

final readonly class WooCommerceProductGateway implements CommerceProductGatewayInterface
{
    public const PRODUCT_PACKAGE_META = '_ads_tourism_package_id';

    public function __construct(private WooCommerceCompatibility $compatibility) {}

    public function isAvailable(): bool
    {
        return $this->compatibility->isAvailable();
    }

    public function productExists(int $productId): bool
    {
        return $this->product($productId) instanceof WC_Product;
    }

    public function packageIdForProduct(int $productId): ?int
    {
        $product = $this->product($productId);

        if (!$product instanceof WC_Product) {
            return null;
        }

        $packageId = absint($product->get_meta(self::PRODUCT_PACKAGE_META, true));

        return $packageId > 0 ? $packageId : null;
    }

    public function createProduct(PackageProductData $package): int
    {
        if (!$this->isAvailable()) {
            throw new CommerceMappingException('WooCommerce is not available.');
        }

        $product = new WC_Product_Simple();
        $product->set_name($package->title);
        $product->set_status('draft');
        $product->set_catalog_visibility('visible');
        $this->applyTourismData($product, $package);

        return $product->save();
    }

    public function syncProduct(int $productId, PackageProductData $package): void
    {
        $product = $this->requireProduct($productId);
        $product->set_name($package->title);
        $this->applyTourismData($product, $package);
        $product->save();
    }

    public function linkPackage(int $productId, int $packageId): void
    {
        $product = $this->requireProduct($productId);
        $product->update_meta_data(self::PRODUCT_PACKAGE_META, $packageId);
        $product->save();
    }

    public function unlinkPackage(int $productId, int $packageId): void
    {
        $product = $this->product($productId);

        if (!$product instanceof WC_Product || absint($product->get_meta(self::PRODUCT_PACKAGE_META, true)) !== $packageId) {
            return;
        }

        $product->delete_meta_data(self::PRODUCT_PACKAGE_META);
        $product->save();
    }

    public function productUrl(int $productId): ?string
    {
        if (!$this->productExists($productId)) {
            return null;
        }

        $url = get_permalink($productId);

        return is_string($url) && $url !== '' ? $url : null;
    }

    public function addToCartUrl(int $productId): ?string
    {
        $product = $this->product($productId);

        if (!$product instanceof WC_Product || !$product->is_purchasable()) {
            return null;
        }

        $url = $product->add_to_cart_url();

        return $url !== '' ? $url : null;
    }

    public function checkoutUrl(int $productId): ?string
    {
        $product = $this->product($productId);

        if (!$product instanceof WC_Product || !$product->is_purchasable() || !function_exists('wc_get_checkout_url')) {
            return null;
        }

        return add_query_arg(['add-to-cart' => (string) $productId], wc_get_checkout_url());
    }

    private function applyTourismData(WC_Product $product, PackageProductData $package): void
    {
        $product->set_short_description($package->summary);

        if ($package->featuredImageId > 0) {
            $product->set_image_id($package->featuredImageId);
        }

        $product->update_meta_data(self::PRODUCT_PACKAGE_META, $package->packageId);
        $product->update_meta_data('_ads_tourism_package_url', $package->packageUrl);
    }

    private function requireProduct(int $productId): WC_Product
    {
        $product = $this->product($productId);

        if (!$product instanceof WC_Product) {
            throw new CommerceMappingException('The linked WooCommerce product is unavailable.');
        }

        return $product;
    }

    private function product(int $productId): ?WC_Product
    {
        if (!$this->isAvailable() || $productId < 1) {
            return null;
        }

        $product = wc_get_product($productId);

        return $product instanceof WC_Product ? $product : null;
    }
}
