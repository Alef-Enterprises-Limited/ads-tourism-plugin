# Troubleshooting

Start with **ADS Tourism → System Status**. It shows database readiness and the availability of optional integrations.

## Tourism pages return 404

Open **Settings → Permalinks** and select **Save Changes** once. Also check that tourism URL bases under **ADS Tourism → Settings** are unique and do not use reserved WordPress paths.

## A record will not publish

New records are Unverified by default. Complete the verification fields and set the record to Verified, or ask an administrator to review the publication policy under **ADS Tourism → Settings**.

## Divi does not show a tourism content type

Confirm ADS Tourism is active and the record type is enabled in Divi. Open Divi Theme Builder again after activation. Clear builder and page caches if an old list remains. See [templates and page builders](templates-and-builders.md).

## Search, filters, or pagination do not work together

Separate components must use the same valid `context` value. Each context can contain only one primary results grid or all-in-one listing. See [shortcodes](shortcodes.md).

## No image is shown

Check the record's Featured Image, external media reference, content-type fallback, and global fallback in that order. If every source is empty, ADS Tourism safely shows no image. See [media and permalinks](media-and-permalinks.md).

## A map is empty

Confirm that Google Maps is selected, the browser key is valid and domain-restricted, and the records have visible GPS locations. A map is intentionally omitted when no valid marker is available. See [SEO, maps, and languages](seo-maps-and-languages.md).

## WooCommerce buttons are missing

Confirm WooCommerce is active, the Package uses WooCommerce mode, the Product link is valid, and the Product is purchasable. Catalogue and Enquiry modes do not show cart actions. See [WooCommerce](woocommerce.md).

## A CSV file is rejected

Use the current downloadable template, save the file as UTF-8 CSV, and include a unique `external_id` and a title. Review the dry-run errors and rejected-row report. See [CSV import and export](csv-import-export.md).

## System Status reports an incomplete database update

Use the protected retry action shown in the administrator notice or System Status. If it fails again, record the plugin, WordPress, PHP, and database versions before opening an issue. Do not post credentials, API keys, personal information, or production CSV data.

## Get more help

- Open **ADS Tourism → Help** inside WordPress.
- Search the [user guides](README.md).
- Check [known limitations](../known-limitations.md).
- Report a reproducible problem through [GitHub Issues](https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/issues).
