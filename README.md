# ADS Tourism

ADS Tourism is a WordPress-native foundation for tourism websites. It models destinations as the spine of a connected catalogue of things to do, places to stay, tour operators, and packages while leaving page composition to WordPress, Divi, or another builder.

> **Status:** early development. The plugin currently provides the core content model, structured record fields, canonical relationships, an editorial verification workflow, linked galleries, fallback resolution, configurable permalinks, secure CSV import/export, builder-compatible fallback templates, interactive shortcode listings, and project quality/release tooling. Maps, multilingual adapters, and WooCommerce commerce adapters remain planned work.

## Core content model

| Record | WordPress key | Default URL base | Notes |
| --- | --- | --- | --- |
| Places to Go | `ads_place` | `/places-to-go/` | Hierarchical destination spine |
| Things to Do | `ads_activity` | `/things-to-do/` | Activities and experiences |
| Places to Stay | `ads_stay` | `/places-to-stay/` | Accommodation listings |
| Tour Operators | `ads_operator` | `/tour-operators/` | Provider profiles |
| Packages | `ads_package` | `/packages/` | Catalogued offers; commerce remains optional |

Every content type is public, revision-enabled, featured-image enabled, available through the REST API, and exposed to the normal WordPress template hierarchy. That gives Divi Theme Builder and other standards-compliant builders stable post-type conditions to target.

## Structured records and relationships

Each tourism record has a native **Tourism details** panel. Shared fields cover sources, verification, display overrides, external media references, and SEO overrides; type-specific fields cover coordinates, contact details, visitor information, accommodation pricing, and package itineraries.

The **Tourism relationships** panel connects records through one canonical relationship row. Editors can search, order, remove, and—where applicable—choose a primary related record. The same relationship can be queried from either side without duplicating data.

New records begin as unverified. By default, WordPress keeps an unverified record in review if an editor attempts to publish it. Administrators can change this policy under **ADS Tourism → Settings**. See [Record editing and workflow](docs/user/record-workflow.md) and [Relationship architecture](docs/developer/relationships.md).

## Media, fallbacks, and URLs

WordPress continues to own uploaded files and featured images. The **Tourism gallery** panel associates reusable Media Library images—or safe HTTPS/site-relative links—with a record without copying or deleting files. Editors can order images and maintain roles, overrides, captions, credits, rights notices, and gallery display defaults.

Featured-media resolution follows a safe chain: the record's featured image, its external image reference, the record-type default, the global tourism default, and finally no image. Empty field values similarly resolve through record overrides, record-type defaults, and global defaults before returning `null` so future templates can omit empty markup.

Administrators can configure fallback images and all tourism post-type/taxonomy URL bases under **ADS Tourism → Settings**. Bases are checked for duplicates and reserved WordPress paths. Changed bases and record slugs retain redirect history. See [Media and permalink administration](docs/user/media-and-permalinks.md) and [Media and fallback architecture](docs/developer/media-and-fallbacks.md).

## CSV transfer

**ADS Tourism → CSV Import/Export** provides a template-led spreadsheet workflow. Imports use stable external IDs, explicit column mapping, a dry-run preview, duplicate policies, controlled taxonomy slugs, and bounded AJAX batches. Invalid rows are isolated in a downloadable rejected-row report; valid rows continue safely. New records always begin as Draft and Unverified.

Exports may be filtered by record type, workflow state, modified date, or selected IDs. A full ZIP contains record files, taxonomy definitions, relationships, media associations, and a checksummed JSON manifest. See the [CSV import/export guide](docs/user/csv-import-export.md) and [CSV transfer architecture](docs/developer/csv-transfer.md).

## Templates and builders

Every public tourism single, post-type archive, and taxonomy archive has a responsive fallback template. Themes can override templates under `your-theme/ads-tourism/`, while Divi Theme Builder and other standards-compliant builders can assign global content-type, archive, taxonomy, and individual-record designs.

Per-record layout modes support the shared structured template, the structured template plus editor/builder content, or a fully custom body. Presentation choices never delete the normalized tourism fields. Minimal plugin CSS can be disabled, and administrators can add scoped custom CSS under **ADS Tourism → Settings**. See [Templates and page builders](docs/user/templates-and-builders.md) and [Presentation architecture](docs/developer/presentation.md).

## Shortcodes and interactive discovery

Listings can be placed in any shortcode-capable editor or builder. Type-specific shortcodes include `ads_tourism_places`, `ads_tourism_activities`, `ads_tourism_stays`, `ads_tourism_operators`, and `ads_tourism_packages`; `ads_tourism_listing` can query more than one type.

For custom layouts, compose separate search, filter, sort, results, and pagination components with the same explicit context:

```text
[ads_tourism_search context="discover"]
[ads_tourism_filters context="discover" fields="area,activity_type,price"]
[ads_tourism_sort context="discover"]
[ads_tourism_results context="discover" type="activity,package" per_page="12"]
[ads_tourism_pagination context="discover"]
```

The initial response is rendered by WordPress, forms and links work without JavaScript, and the public script progressively adds debounced search, AJAX filters and sorting, pagination or load-more behavior, independent multi-context state, and Back/Forward restoration. The public REST query accepts only allowlisted tourism fields and published records. See [Shortcodes and interactive listings](docs/user/shortcodes.md) and the [public query API](docs/developer/public-query-api.md).

## Install for development

Requirements:

- WordPress 6.6 or newer
- PHP 8.2 or newer
- Composer 2 for development commands

```bash
composer install
composer check
```

Install the repository as `wp-content/plugins/ads-tourism`, activate **ADS Tourism**, and resave **Settings → Permalinks** if routes do not resolve in a development environment.

## Architecture

The runtime deliberately has no Composer dependency. A small PSR-4-compatible loader makes the release ZIP work immediately after installation, while Composer provides developer autoloading and quality tools.

- `src/Domain` contains stable tourism concepts.
- `src/Application` coordinates relationship and workflow use cases.
- `src/Infrastructure/WordPress` adapts those concepts to WordPress hooks and APIs.
- `tests` contains fast unit tests and static-analysis stubs.
- `docs/decisions` records architectural decisions.
- `docs/architecture-and-development-plan.md` is the complete implementation plan.

See [CONTRIBUTING.md](CONTRIBUTING.md) for development rules and [CHANGELOG.md](CHANGELOG.md) for delivered behavior.

## Builds and releases

Pull requests and pushes run formatting, static analysis, and unit tests against supported PHP versions. Successful checks also produce an installable ZIP as a workflow artifact. Pushing a SemVer tag such as `v0.1.0` verifies version consistency and publishes the ZIP to a GitHub release.

## License

ADS Tourism is licensed under the Apache License 2.0. See [LICENSE](LICENSE).
