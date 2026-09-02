=== ADS Tourism ===
Contributors: alefsolutions
Tags: tourism, destinations, accommodation, activities, tour packages
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.2
Stable tag: 1.2.0
License: Apache-2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

A WordPress-native tourism content and discovery foundation for places, activities, stays, operators, and packages.

== Description ==

ADS Tourism provides connected content types for a tourism website while preserving WordPress templates, taxonomies, media, revisions, REST APIs, and page-builder compatibility.

Version 1.2 provides the core content model, structured fields, connected records, a verification-first editorial workflow, reusable media galleries, repeatable GPS locations, fallback resolution, configurable tourism permalinks, secure CSV transfer, editable scoped boilerplate CSS, interactive discovery shortcodes, tourism SEO/schema integration, optional Google Maps, multilingual readiness, and optional WooCommerce Package commerce.

Editors can use native WordPress screens to maintain tourism details, search for related records, order those relationships, and verify content before publication. Administrators may relax the verification publication gate under ADS Tourism > Settings.

The ADS Tourism > Help screen provides shortcode recipes, syntax, supported attributes, usage rules, and administration references. ADS Tourism > Tags & Categories provides the management screens for all tourism taxonomies, including an optional RGB hex color for each term; blank colors remain unstyled.

The plugin leaves uploads in the WordPress Media Library. Gallery associations can be detached without deleting attachments. Administrators can also choose fallback images and edit tourism URL bases under ADS Tourism > Settings.

Editors with permission to manage other authors' posts can use ADS Tourism > CSV Import/Export. Imports provide downloadable templates, explicit column mapping, a validation preview, duplicate policies, controlled taxonomy import, AJAX batching, and rejected-row reports. Exports include normalized records, taxonomies, relationships, media links, and a manifest in one ZIP.

Search, filters, sorting, results, pagination, and maps can be placed independently with explicit shared contexts. Google Maps is optional and loads only when configured and rendered. Native tourism schema avoids duplicate entities when Yoast SEO or Rank Math owns the graph. WPML and Polylang can resolve related records without becoming hard dependencies.

Packages remain usable without WooCommerce. When WooCommerce is active, editors can explicitly create or link a Product, synchronize selected tourism presentation details, and render Add to Cart or Buy Now controls. WooCommerce remains responsible for prices, tax, stock, cart, checkout, orders, and payments.

== Installation ==

1. Upload the `ads-tourism` directory to `/wp-content/plugins/` or install the release ZIP.
2. Activate ADS Tourism through the Plugins screen.
3. Open the ADS Tourism menu and begin adding tourism content.

Before uninstalling, export or back up the site. ADS Tourism preserves data by default. Destructive uninstall must be explicitly enabled and confirmed under ADS Tourism > Settings; shared Media Library attachments and WooCommerce Products are never deleted.

== Changelog ==

= 1.0.0 =

* Initial development foundation.
* Structured tourism fields and REST metadata.
* Indexed bidirectional relationships and editorial verification workflow.
* Linked media galleries, safe fallbacks, and configurable permalink bases.
* Secure CSV templates, preview, batched import, history, rejected-row reports, and normalized ZIP exports.
* Builder-agnostic interactive listings and context-synchronized maps.
* Tourism SEO/schema integration plus WPML and Polylang readiness.
* Optional reciprocal Package/Product mapping and WooCommerce commerce controls.
* Migration recovery, integrity scanning, privacy guidance, safe uninstall controls, and reproducible release artifacts.
