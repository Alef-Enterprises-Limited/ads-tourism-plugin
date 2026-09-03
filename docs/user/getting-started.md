# Five-minute setup

This guide takes you from installation to a working tourism listing.

## 1. Install ADS Tourism

1. Download the installable ZIP from the [latest GitHub release](https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/releases/latest).
2. In WordPress, open **Plugins → Add New Plugin → Upload Plugin**.
3. Upload the ZIP, install it, and activate **ADS Tourism**.
4. Open **ADS Tourism → System Status** and confirm the database status is **Ready**.

## 2. Check the main settings

Open **ADS Tourism → Settings**.

- Keep verification required if records must be checked before publication.
- Review the default URL bases.
- Choose fallback images only if the site needs them.
- Keep the supplied CSS enabled until the theme or builder replaces it.

Save the settings. If a tourism URL returns a 404 page, open **Settings → Permalinks** and select **Save Changes** once.

## 3. Add useful categories

Open **ADS Tourism → Tags & Categories**. Add the terms that editors will reuse, such as geographic areas, place types, activity types, stay types, amenities, traveller types, accessibility features, and tourism tags.

## 4. Create the first records

Start with a **Place to Go**, because places form the main location structure. Add its title, description, structured tourism details, taxonomies, featured image, gallery, and GPS locations.

Then create related Things to Do, Places to Stay, Tour Operators, or Packages. Use each record's **Tourism relationships** panel to connect them to the place.

New records begin as Draft and Unverified. Complete the verification details before publishing when the publication gate is enabled.

## 5. Show a listing

Create or edit a normal WordPress page and add a Shortcode block, Divi Code/Text module, or another builder element that runs shortcodes.

```text
[ads_tourism_places per_page="12" columns="3"]
```

Publish the page. For search and filters, continue with the [shortcode guide](shortcodes.md).

## Optional next steps

- Set up Divi or another builder with [templates and page builders](templates-and-builders.md).
- Add Google Maps with [SEO, maps, and languages](seo-maps-and-languages.md).
- Import many records with [CSV import and export](csv-import-export.md).
- Connect Packages to Products with [WooCommerce](woocommerce.md).
- Review [media and permalink settings](media-and-permalinks.md).

If something does not work, check [troubleshooting](troubleshooting.md) or **ADS Tourism → Help**.
