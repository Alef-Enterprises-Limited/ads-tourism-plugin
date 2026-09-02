# SEO, map, and multilingual integration

Phase 7 keeps every optional integration behind a small WordPress adapter. Core tourism records remain usable when an SEO plugin, map provider, or multilingual plugin is missing.

## SEO data and schema ownership

`SeoDataResolver` exposes a filtered data set through `ads_tourism_seo_data`. It contains the post ID/type, title, resolved description, canonical URL, resolved social image, validated coordinates, price guidance, taxonomy slugs, and language-resolved relationship IDs.

`TourismSchemaMapper` maps only stored facts and then applies `ads_tourism_schema_data`. `SchemaTypeMapper` provides the default Schema.org types. Use `ads_tourism_seo_schema_enabled` to suppress native output for an individual record.

In automatic mode:

- no detected SEO plugin: ADS Tourism emits one native JSON-LD entity plus optional social metadata;
- Yoast SEO: the entity is merged through `wpseo_schema_graph` and canonical/description/image filters feed resolved values to Yoast;
- Rank Math: the entity is merged through `rank_math/json_ld`;
- duplicate `@id` values, or the same URL with an overlapping Schema.org type, are not appended.

This layer intentionally does not replace sitemap editors, redirects, robots policy editors, or the full metadata UI of an SEO plugin. Tourism post types and taxonomies use WordPress-native public registration so core and plugin sitemaps can discover them.

Other integrations may consume or modify:

- `ads_tourism_seo_data`
- `ads_tourism_schema_data`
- `ads_tourism_seo_schema_enabled`
- `ads_tourism_canonical_url`
- `ads_tourism_breadcrumb_items`

## Map providers

`MapProviderInterface` owns configuration availability, assets, script dependencies, marker normalization, single/multiple rendering, attribution, and directions URLs. `MapProviderRegistry` applies `ads_tourism_map_providers` and selects the configured available provider. Provider labels for settings are extended through `ads_tourism_map_provider_labels`.

The Google adapter registers only a browser JavaScript key. The two options are created with autoload disabled:

- `ads_tourism_map_provider`
- `ads_tourism_google_maps_browser_key`

The public map payload contains only published record IDs, coordinates, titles, public URLs, content types, short resolved summaries, and the sanitized location label/role. The marker factory rejects invalid coordinates and caps each map at 100 location points.

`[ads_tourism_map context="name"]` registers `map` in the same strict context registry as search, filters, sorting, results, and pagination. Its `locations` attribute defaults to `primary`; `locations="all"` opts into all visible location points. The REST query response includes both primary and all-location marker arrays for returned records. The listing script dispatches `ads-tourism:results-updated`; the map script selects the configured marker array, replaces markers belonging to the matching context, and announces the change through a live region.

Repeatable locations are stored in the plugin-owned `ads_tourism_locations` table and exposed as the `ads_tourism_locations` REST field. Legacy Place, Stay, Operator, and Package meeting-point coordinate metadata is migrated to a primary point; existing metadata remains readable for compatibility. Google Maps uses a browser-restricted key, renders provider attribution, and safely omits invalid or hidden points.

A third-party provider implements the interface, registers itself with `ads_tourism_map_providers`, and adds its label through `ads_tourism_map_provider_labels`. It should enqueue assets only when available, preserve the public marker contract for AJAX updates, escape markup at output, and document its own attribution and privacy requirements.

## Multilingual adapters

`TranslationAdapterInterface` resolves the current language plus post and term equivalents. The default `WordPressTranslationAdapter` detects WPML filters or Polylang functions without loading either plugin as a dependency. Replace it through `ads_tourism_translation_adapter` when another multilingual system is used.

Canonical relationship rows retain language-neutral record and relation identifiers. `TranslationResolver` maps the related endpoint at presentation time and applies the administrator's original-language fallback setting. Translation software remains responsible for creating translated records and terms; ADS Tourism never calls an automatic translation service.

## Translation catalog

All source UI strings use the `ads-tourism` text domain. Run:

```bash
composer make-pot
composer check:pot
```

The deterministic generator scans literal WordPress translation calls and includes schema-defined field, media-role, query-sort, relationship, and verification labels that are translated dynamically at their output boundary. CI fails when `languages/ads-tourism.pot` is stale.
