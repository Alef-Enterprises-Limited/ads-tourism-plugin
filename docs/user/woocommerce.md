# WooCommerce for Packages

ADS Tourism works without WooCommerce. Installing or deactivating WooCommerce does not remove Packages, tourism fields, relationships, itineraries, galleries, or saved product mappings.

## Choose a commerce mode

Open a Package and set **Commerce mode** in **Tourism details**:

- **Catalogue** shows only the optional catalogue call-to-action. It never shows cart or checkout controls.
- **Enquiry** uses the Package call-to-action URL and defaults its empty label to **Enquire now**. It does not create an order.
- **WooCommerce** shows transactional controls only when WooCommerce is active and the Package has a valid linked Product.

When WooCommerce mode is invalid, the public page safely uses the Catalogue or Enquiry fallback selected under **ADS Tourism → WooCommerce**.

## Create or link a Product

Use the **Package commerce** panel in the Package editor:

1. Select **Create draft Product** to create and link a new simple Product, or enter an existing Product ID and update the Package.
2. Edit the Product in WooCommerce and set its price, tax, stock or capacity policy, sale settings, and other commerce data.
3. Use **Sync tourism details** when the Package title, summary, featured image, or Package URL changes.
4. Use **Detach Product** to remove the mapping without deleting either record.

A Product can link to only one Package. Product creation is always explicit and never occurs during an ordinary Package save.

## Choose the frontend behavior

Under **ADS Tourism → WooCommerce**, administrators can choose whether Package listing cards open the Package or linked Product page. Package pages may show Add to Cart, Buy Now, or both.

The commerce component is also available in Divi, blocks, and other shortcode-capable builders:

```text
[ads_tourism_commerce_controls]
[ads_tourism_commerce_controls action="add_to_cart"]
[ads_tourism_commerce_controls action="buy_now" class="package-purchase"]
```

On a linked WooCommerce Product page, the existing tourism record shortcodes automatically use the linked Package when no explicit `id` is supplied. For example:

```text
[ads_tourism_field field="summary"]
[ads_tourism_package_itinerary]
[ads_tourism_package_provider]
[ads_tourism_gallery]
```

This works with Packages provided by Tour Operators, Places to Stay, or both. ADS Tourism does not provide room inventory, booking calendars, participant allocation, checkout, payment processing, or order management; WooCommerce and later booking extensions own those responsibilities.
