=== ADS Tourism ===
Contributors: alefsolutions
Tags: tourism, destinations, accommodation, activities, tour packages
Requires at least: 6.6
Tested up to: 6.8
Requires PHP: 8.2
Stable tag: 0.1.0
License: Apache-2.0
License URI: https://www.apache.org/licenses/LICENSE-2.0

A WordPress-native tourism content and discovery foundation for places, activities, stays, operators, and packages.

== Description ==

ADS Tourism provides connected content types for a tourism website while preserving WordPress templates, taxonomies, media, revisions, REST APIs, and page-builder compatibility.

The current early-development release establishes the core content model, structured fields, connected records, a verification-first editorial workflow, reusable media galleries, fallback resolution, configurable tourism permalinks, secure CSV transfer, interactive discovery shortcodes, tourism SEO/schema integration, optional Google Maps, and multilingual readiness. Additional roadmap features are documented in the project repository.

Editors can use native WordPress screens to maintain tourism details, search for related records, order those relationships, and verify content before publication. Administrators may relax the verification publication gate under ADS Tourism > Settings.

The plugin leaves uploads in the WordPress Media Library. Gallery associations can be detached without deleting attachments. Administrators can also choose fallback images and edit tourism URL bases under ADS Tourism > Settings.

Editors with permission to manage other authors' posts can use ADS Tourism > CSV Import/Export. Imports provide downloadable templates, explicit column mapping, a validation preview, duplicate policies, controlled taxonomy import, AJAX batching, and rejected-row reports. Exports include normalized records, taxonomies, relationships, media links, and a manifest in one ZIP.

Search, filters, sorting, results, pagination, and maps can be placed independently with explicit shared contexts. Google Maps is optional and loads only when configured and rendered. Native tourism schema avoids duplicate entities when Yoast SEO or Rank Math owns the graph. WPML and Polylang can resolve related records without becoming hard dependencies.

== Installation ==

1. Upload the `ads-tourism` directory to `/wp-content/plugins/` or install the release ZIP.
2. Activate ADS Tourism through the Plugins screen.
3. Open the ADS Tourism menu and begin adding tourism content.

== Changelog ==

= 0.1.0 =

* Initial development foundation.
* Structured tourism fields and REST metadata.
* Indexed bidirectional relationships and editorial verification workflow.
* Linked media galleries, safe fallbacks, and configurable permalink bases.
* Secure CSV templates, preview, batched import, history, rejected-row reports, and normalized ZIP exports.
* Builder-agnostic interactive listings and context-synchronized maps.
* Tourism SEO/schema integration plus WPML and Polylang readiness.
