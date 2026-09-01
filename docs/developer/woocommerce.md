# Optional WooCommerce integration

Phase 8 keeps commerce behind application contracts and a guarded WordPress adapter. No WooCommerce API is called until `WooCommerceCompatibility` confirms the required classes and functions exist.

## Ownership and mapping

The reciprocal one-to-one mapping is:

- Package meta: `_ads_tourism_product_id`
- Product meta: `_ads_tourism_package_id`

`PackageProductService` enforces reciprocal links and rejects a Product already linked to another Package. `WordPressPackageProductLinkStore` owns Package metadata. `WooCommerceProductGateway` uses WooCommerce CRUD objects for Product creation, synchronization, and Product metadata.

Tourism title, summary, itinerary, relationships, Package page URL, and tourism media remain Package-owned. Explicit synchronization copies only the title, short description, featured image, reciprocal Package ID, and Package URL. WooCommerce remains the source of truth for price, tax, stock, cart, checkout, orders, refunds, and payment state.

## Safe mode resolution

`CommerceModeResolver` is independent of WordPress. WooCommerce mode becomes effective only when the adapter is available and the reciprocal Product link is valid. Otherwise it resolves to the configured Catalogue or Enquiry fallback. Catalogue never produces transactional controls.

Deactivation does not delete either mapping. Product deletion clears the Package side when WooCommerce supplies the reciprocal Product metadata. Package deletion detaches the Product when the adapter is available and never deletes the Product.

## Extension points

- `ads_tourism_record_url` filters listing-card destinations. The bundled adapter may replace a Package URL with its Product URL.
- `ads_tourism_record_context_id` resolves record shortcodes on linked Product pages to their Package.
- `[ads_tourism_commerce_controls]` accepts `id`, `action`, and `class`.
- `PackageProductService` depends on `CommerceProductGatewayInterface` and `PackageProductLinkStoreInterface`, allowing another commerce adapter without changing the tourism domain.

The plugin declares `custom_order_tables` compatibility during `before_woocommerce_init`. It never reads WooCommerce order tables directly.

## Manual compatibility checks

Test against the current supported WooCommerce releases with HPOS both enabled and disabled:

1. Activate ADS Tourism while WooCommerce is absent.
2. Create and link a draft Product explicitly.
3. Set a purchasable Product price and verify Add to Cart and Buy Now reach WooCommerce.
4. Confirm Product-page tourism shortcodes resolve the linked Package.
5. Detach without deleting either record.
6. Deactivate WooCommerce and confirm Packages remain public while transactional controls disappear.
7. Reactivate WooCommerce and confirm the saved mapping becomes valid again.
8. Link an accommodation Package to a Product without adding room-inventory behavior.
