# ADS Tourism

## Comprehensive Development and Architecture Plan

**Project name:** ADS Tourism  
**WordPress plugin slug:** `ads-tourism`  
**Proposed repository name:** `ads-tourism`  
**PHP namespace:** `AlefDigitalSolutions\ADSTourism`  
**Text domain:** `ads-tourism`  
**Document status:** Implementation specification  
**Primary initial use case:** East New Britain Tourism Authority tourism website  

---

## 1. Purpose

ADS Tourism will be a WordPress-native tourism information and catalogue plugin. It will provide one structured administration system for destinations and attractions, things to do, places to stay, tour operators and packages.

The plugin must work as a complete tourism-content system without WooCommerce, Divi, a map provider or a multilingual plugin. Optional integrations add presentation, mapping, translation or commerce features without becoming hard dependencies.

The plugin must:

- Keep tourism data structured, reusable and independent of the active theme.
- Use **Places to Go** as the main relationship spine.
- Give every public record its own WordPress URL and fallback template.
- Expose records to Divi Theme Builder and other WordPress builders.
- Support global templates and per-record custom-layout overrides.
- Provide comprehensive shortcodes for fields, components and interactive listings.
- Support AJAX search, filtering, sorting and pagination with explicit shortcode contexts.
- Use WordPress Media Library attachments and media links without operating a separate file store.
- Support CSV bulk import and export.
- Provide safe empty-field and missing-image fallbacks.
- Provide configurable slugs and permalink bases.
- Include editorial review and verification stages.
- Provide SEO, security, privacy, performance, integrity, migration and uninstall controls.
- Support Google Maps first through a provider-neutral map layer.
- Be translation-ready and compatible with established multilingual plugins.
- Add optional WooCommerce catalogue, cart, checkout and payment capabilities.
- Produce installable ZIP releases automatically through GitHub Actions.

---

## 2. Locked Product Decisions

These decisions are requirements unless a later approved architecture decision record explicitly replaces them.

1. The product name is **ADS Tourism**. The longer display name **ADS Tourism Plugin** may be used in explanatory copy, but code and packaging use `ads-tourism`.
2. The plugin is WordPress-native and builder-agnostic.
3. Divi is the initial builder integration target, but core data must not depend on Divi.
4. Places to Go is the primary relationship spine.
5. The five main public records are Places to Go, Things to Do, Places to Stay, Tour Operators and Packages.
6. Each public record receives an automatic WordPress single-record URL; no duplicate ordinary Page is created.
7. The plugin provides minimal fallback templates. Builders control the intended production design.
8. Each record supports Standard, Standard plus Custom Content, and Full Custom layout modes.
9. Shortcodes are a first-class feature, including separated controls connected by an explicit `context` attribute.
10. Interactive shortcodes use AJAX/REST without full-page reloads and retain a non-JavaScript fallback.
11. WordPress taxonomies classify records; record-to-record relationships connect named entities.
12. Internal media uses WordPress attachment IDs. External and relative media links are supported without downloading the files.
13. Featured images are optional. Missing media must never produce broken image elements or empty visual sections.
14. CSV import initially covers record fields and optional controlled taxonomies. Relationship linking remains manual in the first implementation.
15. Imported records default to Draft and Unverified.
16. WooCommerce is optional. Deactivating or not installing WooCommerce must not break Package pages.
17. A Package may be offered by a Tour Operator, a Place to Stay, both, or another provider type added through an extension.
18. Accommodation Packages are supported. Direct room inventory and availability management are deferred.
19. No custom user-role system is required initially.
20. Booking calendars, capacity allocation and reservation availability are outside the initial scope.

---

## 3. Goals and Non-Goals

### 3.1 Goals

- Centralize verified tourism records in WordPress.
- Enter a real-world record once and reuse it across pages, archives, maps, searches, shortcodes and related-content displays.
- Allow editors to work through familiar WordPress screens.
- Allow designers to use Divi or another builder without embedding critical data in builder layouts.
- Allow bulk loading from spreadsheets exported as CSV.
- Make packages marketable now and purchasable later.
- Provide a stable extension surface for future ADS modules.
- Produce predictable, reproducible plugin releases.

### 3.2 Non-Goals for the Initial Release

- A hotel property-management system.
- Real-time room inventory.
- Booking calendars or resource allocation.
- Airline, vehicle or transport inventory.
- Automatic translation or a proprietary translation engine.
- A separate image hosting or media management platform.
- A custom WordPress page builder.
- A custom payment processor.
- Custom editorial roles and organizational permissions.
- Direct production deployment from GitHub Actions.

---

## 4. Compatibility Baseline

Finalize exact minimum versions during repository bootstrap after auditing the target ENBTA WordPress environment. Use this default engineering baseline unless the audit requires broader support:

- WordPress: current stable release and the previous two major/minor compatibility lines.
- PHP: minimum 8.2; CI on 8.2, 8.3 and 8.4 where WordPress supports them.
- Database: MySQL 8.0+ or MariaDB 10.11+ target.
- HTTPS required for production.
- WooCommerce: current stable and previous supported major line when the integration module is tested.
- Divi: current production Divi version, plus a documented manual smoke test against the ENBTA-installed version.
- Browsers: current and previous major versions of Chrome, Edge, Firefox and Safari; current Android and iOS browsers.

The plugin header and README must state the final tested minimums. CI compatibility matrices must be updated intentionally, not silently.

---

## 5. Architectural Principles

### 5.1 One Source of Truth

- Tourism editorial data belongs to ADS Tourism records.
- Classification belongs to WordPress taxonomies.
- Record relationships belong to the ADS Tourism relationship service.
- Internal media files belong to the WordPress Media Library.
- Commerce fields, cart, checkout, orders and payment state belong to WooCommerce.
- Presentation belongs to the active theme, builder, plugin fallback template or shortcode component.

### 5.2 Progressive Enhancement

- Initial listing HTML is rendered on the server.
- AJAX enhances search, filters, sorting and pagination.
- Public content remains navigable without JavaScript.
- Maps, lightboxes and advanced controls fail safely.

### 5.3 Optional Integrations

Integrations must be isolated behind adapters and feature detection:

- `Integration\Divi`
- `Integration\WooCommerce`
- `Integration\SEO`
- `Integration\Multilingual`
- `Maps\Providers\GoogleMaps`

No optional integration class may be loaded in a way that causes a fatal error when its external plugin is absent.

### 5.4 WordPress-First Storage

Use WordPress posts, post meta, taxonomies, options and attachments where practical. Use custom tables only where many-to-many querying, ordering, history or batch processing requires indexed relational storage.

### 5.5 Stable Identifiers

- WordPress post IDs identify records internally.
- Every tourism record also has a stable, unique `external_id` for CSV operations and external integrations.
- Relationships never store display names or permalinks as keys.
- URLs are generated through WordPress APIs rather than saved as fixed internal absolute URLs.

---

## 6. High-Level Component Architecture

```text
ADS Tourism Bootstrap
├── Domain
│   ├── Records
│   ├── Fields and validation
│   ├── Taxonomies
│   ├── Relationships
│   ├── Media associations
│   └── Verification workflow
├── WordPress Integration
│   ├── Post types
│   ├── Meta registration
│   ├── Permalinks
│   ├── Templates
│   ├── Admin screens
│   └── REST controllers
├── Presentation
│   ├── Field renderer and fallbacks
│   ├── Shortcodes
│   ├── Listings and filters
│   ├── AJAX context store
│   └── Minimal optional CSS
├── Data Operations
│   ├── CSV import
│   ├── CSV export
│   ├── integrity tools
│   └── migrations
└── Optional Integrations
    ├── Divi
    ├── Maps
    ├── SEO
    ├── Multilingual
    └── WooCommerce
```

Use constructor injection or a small service container only where it improves testability. Avoid a global service locator throughout domain code.

---

## 7. WordPress Record Model

### 7.1 Public Post Types

| Admin label | Post type key | Hierarchical | Public archive |
| --- | --- | ---: | ---: |
| Places to Go | `ads_place` | Yes | Yes |
| Things to Do | `ads_activity` | No | Yes |
| Places to Stay | `ads_stay` | No | Yes |
| Tour Operators | `ads_operator` | No | Yes |
| Packages | `ads_package` | No | Yes |

All public post types must use standard `register_post_type()` configuration with:

- `public => true`
- `publicly_queryable => true`
- `show_ui => true`
- `show_in_menu => 'ads-tourism'` or equivalent submenu registration
- `show_in_nav_menus => true`
- `show_in_rest => true`
- `has_archive => true`
- configurable `rewrite`
- `query_var => true`
- support for title, editor, excerpt, thumbnail, revisions and custom fields
- page attributes for hierarchical Places and manual ordering where required

This registration is required for WordPress search, REST, sitemaps, navigation menus, SEO plugins, Divi Theme Builder and other builders to recognize the records.

### 7.2 Common Record Fields

Register simple fields through `register_post_meta()` with explicit REST schemas, sanitization and authorization callbacks.

Common fields:

- `external_id`
- `summary`
- `layout_mode`: `standard`, `standard_custom`, `full_custom`
- `custom_content_position`: before, after or configured template slot
- `verification_status`: unverified, pending, verified, needs_update, rejected
- `source_name`
- `source_reference`
- `source_url`
- `date_collected`
- `last_verified_at`
- `verified_by_user_id`
- `verification_notes` (admin only)
- `publication_notes` (admin only)
- `manual_order`
- `external_featured_media_url`
- `external_featured_media_url_type`: absolute, relative
- `display_fallback_overrides`
- `seo_schema_override` where supported

Do not duplicate native WordPress title, slug, excerpt, content, featured image, author, status or revision fields.

### 7.3 Places to Go

Places are the main tourism relationship spine. A Place may be a broad destination, town, island, attraction, natural site, historical site or cultural site. Parent/child structure supports broad-to-specific navigation.

Fields:

- Native title, excerpt and content
- Parent Place
- Place Type taxonomy
- Physical address
- Latitude and longitude
- Optional altitude
- Map zoom preference
- Opening hours or visitor-access notes
- Entry fee information
- Contact telephone and email
- Website and social links
- Best time to visit
- Visitor advice
- Accessibility notes
- Safety notes
- Featured image and gallery associations
- Verification and source fields

Example hierarchy:

```text
East New Britain
├── Kokopo
│   ├── Kokopo Market
│   └── Bitapaka War Cemetery
├── Rabaul
└── Duke of York Islands
```

Do not hardcode ENBTA destinations into the plugin. Provide them through seed/import data so ADS Tourism remains reusable.

### 7.4 Things to Do

Fields:

- Activity Type taxonomy
- Description and summary
- Typical duration
- Difficulty level
- Minimum age
- Fitness requirements
- Equipment required
- What to bring
- Season or availability notes
- Price guidance, not transactional pricing
- Safety information
- Accessibility information
- Contact or enquiry link
- Featured image and gallery
- Verification fields

Relationships:

- Available at one or more Places
- Provided by zero or more Operators
- Included in zero or more Packages
- Optionally associated with nearby Stays

### 7.5 Places to Stay

Fields:

- Accommodation Type taxonomy
- Description and summary
- Address
- Latitude and longitude
- Telephone and email
- Website and social links
- Check-in and check-out notes
- Informational price-from value and price notes
- Room summary, not live room inventory
- Amenities taxonomy
- Accessibility information
- Airport transfer information
- External booking/enquiry URL
- Featured image and gallery
- Verification fields

Relationships:

- Located at one primary Place
- Related to zero or more nearby Places
- Related to Activities
- Owned or managed by an Operator where applicable
- Included in Packages
- May act as the primary provider of Accommodation Packages

### 7.6 Tour Operators

Fields:

- Business and trading name
- Description and summary
- Contact person
- Telephone and email
- Website and social links
- Office address
- Latitude and longitude
- Logo through native featured image or media role
- Licence or registration notes
- Booking instructions
- Operating hours
- Verification fields

Relationships:

- Serves Places
- Provides Activities
- Owns or works with Places to Stay
- Offers or partners on Packages

### 7.7 Packages

The Package remains an `ads_package` record even when WooCommerce is absent.

Fields:

- Package Type taxonomy
- Description and summary
- Duration days and nights
- Start and end Places
- Meeting-point text and coordinates
- Minimum and maximum participant guidance
- Informational adult, child, group or from-price fields
- Price basis: per person, per group, per night, fixed or contact provider
- Inclusions and exclusions
- What to bring
- Booking and cancellation conditions
- Availability notes, not availability inventory
- Catalogue call-to-action label and URL
- Commerce mode: catalogue, enquiry, WooCommerce
- Featured image and gallery
- Verification fields

Relationships:

- Covers one or more Places
- Includes zero or more Activities
- Includes zero or more Places to Stay
- Offered by one primary Operator or Stay
- Supports additional partner providers

Package types may include:

- Tour Package
- Accommodation Package
- Activity Package
- Cultural Package
- Combination Package

### 7.8 Structured Package Itinerary

Provide a repeatable itinerary editor. Store itinerary rows as structured data with stable row IDs. If post meta is used initially, define and version a normalized JSON schema; if querying itinerary rows becomes necessary, migrate them to a dedicated table through a versioned migration.

Each row supports:

- Day number
- Sequence
- Title
- Description
- Start and end time
- Related Place
- Related Activity
- Related Stay
- Meal, transfer or transport notes

The renderer must omit missing subfields without leaving empty labels.

---

## 8. Taxonomy Model

Use custom WordPress taxonomies rather than mixing tourism classifications into blog categories.

| Taxonomy | Key | Applies to | Hierarchical |
| --- | --- | --- | ---: |
| Place Type | `ads_place_type` | Places | Yes |
| Activity Type | `ads_activity_type` | Activities | Yes |
| Accommodation Type | `ads_stay_type` | Stays | Yes |
| Package Type | `ads_package_type` | Packages | Yes |
| Amenities | `ads_amenity` | Stays | Yes |
| Traveller Type | `ads_traveller` | Activities, Packages | Yes |
| Accessibility Features | `ads_accessibility` | Places, Activities, Stays | Yes |
| Tourism Tags | `ads_tourism_tag` | All public records | No |
| Geographic Area | `ads_geo_area` | All public records | Yes |

`ads_geo_area` is a provider-neutral geographic classification for province, district, LLG, town or service area. It does not replace direct relationships to Places to Go.

All public taxonomies must be public, queryable, exposed through REST, have configurable rewrite bases and be visible to builders and SEO plugins.

Taxonomies classify; they must not duplicate named records. For example, a specific hotel is a Stay relationship target, not an Accommodation taxonomy term.

---

## 9. Relationship Model

### 9.1 Storage

Use an indexed custom table named with the active WordPress prefix:

```text
{$wpdb->prefix}ads_tourism_relations
```

Proposed columns:

```text
id                  bigint unsigned primary key
source_post_id      bigint unsigned not null
target_post_id      bigint unsigned not null
relation_key        varchar(64) not null
is_primary          tinyint(1) not null default 0
sort_order          int not null default 0
metadata_json       longtext null
created_at          datetime not null
updated_at          datetime not null
```

Indexes and constraints:

- Unique index on source, target and relation key.
- Index on source and relation key.
- Index on target and relation key.
- Index on relation key and sort order.

Do not assume the database supports reliable cross-table foreign keys in all WordPress environments. Enforce integrity in the relationship repository and cleanup hooks.

### 9.2 Canonical Relation Keys

- `activity_available_at_place`
- `stay_located_at_place`
- `stay_near_place`
- `operator_serves_place`
- `activity_provided_by_operator`
- `stay_managed_by_operator`
- `package_covers_place`
- `package_includes_activity`
- `package_includes_stay`
- `package_offered_by`
- `package_partner_provider`
- `activity_near_stay`

Use directional canonical storage and provide reverse-query methods. Never create a duplicate inverse row.

### 9.3 Relationship Administration

Each editor screen must provide searchable, paginated relationship selectors. Selected relationships support manual ordering and primary designation where relevant.

Saving a relationship once must power both directions. For example, connecting Package A to Kokopo makes Package A available on Kokopo-related queries without manually editing Kokopo.

### 9.4 Deletion and Archiving

- Trashing a record hides it from public related-content queries.
- Permanent deletion removes its relation rows but never deletes connected records.
- Archiving is preferred where historical traceability matters.
- Provide an integrity scanner to identify orphaned rows and invalid post types.

---

## 10. Editorial and Verification Workflow

No custom user roles are required initially. Use WordPress capability checks and existing administrators/editors.

Represent the requested workflow through native post status plus verification metadata:

| Displayed stage | WordPress post status | Verification status |
| --- | --- | --- |
| Draft | `draft` | unverified |
| In Review | `pending` | pending |
| Verified | `pending` | verified |
| Published | `publish` | verified |

Rules:

- Imported records start as Draft and Unverified.
- Verification captures source, date and verifier.
- Publishing an unverified record is blocked by default, with an administrator setting to relax the gate if required.
- Any material imported update to a verified record can optionally change it to Needs Update.
- Verification history must be append-only through a log service.
- Public queries exclude unpublished records automatically.
- Public output never displays private verification notes.

Create an admin list column for Workflow Stage, Verification, Last Verified and Source.

---

## 11. Media and Gallery Model

### 11.1 Responsibilities

WordPress Media Library owns uploads, files, image editing, responsive sizes, alt text and deletion. ADS Tourism owns only record-to-media associations and external/internal media links.

The plugin must not download or sideload external images.

### 11.2 Native Featured Images

All five public post types support the native WordPress Featured Image field.

Resolution order:

1. Record's native featured image attachment.
2. Record's external featured-media link.
3. Content-type default image.
4. Global tourism default image.
5. Render no image and no image container.

Fallback images are display choices and must not be written into the record as its actual featured image.

### 11.3 Media Associations

Use:

```text
{$wpdb->prefix}ads_tourism_media_links
```

Proposed columns:

```text
id                  bigint unsigned primary key
entity_post_id      bigint unsigned not null
attachment_id       bigint unsigned null
media_url           text null
url_type            varchar(16) null
media_role          varchar(32) not null default 'gallery'
custom_title        text null
custom_alt_text     text null
custom_caption      longtext null
credit              text null
rights_notice       text null
is_primary          tinyint(1) not null default 0
sort_order          int not null default 0
created_at          datetime not null
updated_at          datetime not null
```

Exactly one of `attachment_id` or `media_url` is populated.

Supported roles include featured, hero, gallery, thumbnail, room, activity, landscape, facility, map, itinerary and operator logo.

Internal attachment associations store attachment IDs. Relative links begin with `/` and are resolved through WordPress site/content URL functions. External media requires a valid HTTPS URL.

### 11.4 Gallery Administration

- Select multiple attachments through the native Media Library picker.
- Add absolute or relative links manually.
- Reorder by drag and drop.
- Set role, title, caption, alt override, credit and rights.
- Detach without deleting the Media Library file.
- Reuse the same attachment across multiple records.

Gallery display controls must be available as record defaults and shortcode/module overrides:

- Maximum number of images
- Columns or list/grid layout
- Manual, newest, oldest or random order
- Media-role filter
- WordPress image size
- Captions on/off
- Credits on/off
- Lightbox on/off
- Include/exclude the featured image

### 11.6 Repeatable Locations

All five tourism record types use the plugin-owned locations table for repeatable GPS points. Each point has a label, latitude, longitude, role, primary flag, map visibility, and manual order. Legacy Place, Stay, Operator, and Package meeting-point coordinate metadata is migrated to a primary point while the original metadata remains readable. The normalized points are exposed through REST, a dedicated `locations.csv`, the record editor, and map shortcodes.
- Numbered pagination, Load More or no pagination

### 11.5 Safe Rendering

- Do not render empty image tags.
- Skip invalid URLs.
- Hide the gallery heading and container when no valid images exist.
- Use WordPress responsive sizes and `srcset` for attachments.
- External images receive no false promise of WordPress-generated sizes.
- Lazy-load non-critical images.

---

## 12. Field Defaults and Empty-State Policy

All visual output must use one central field-rendering service.

Resolution order:

```text
Record value
→ record override
→ content-type default
→ global default
→ hide field or section
```

Supported empty behaviours:

- Hide field.
- Hide entire section.
- Use configured default text.
- Use configured default media.
- Inherit global value.
- Calculate only when calculation is factual and deterministic.

Never invent factual values such as price, opening hours, availability, accessibility or contact information.

Default safe policies:

- Price: hide or display configured “Contact for pricing”.
- Missing coordinates: hide map.
- Empty itinerary: hide itinerary section.
- Empty relationship list: hide related-content section.
- Missing image: use configured fallback or hide completely.
- Missing phone/email/website: hide the field and label.

The same renderer must serve PHP templates, shortcodes, REST presentation data and optional builder modules.

---

## 13. Permalinks and Slugs

### 13.1 Default Bases

- Places: `places-to-go`
- Activities: `things-to-do`
- Stays: `places-to-stay`
- Operators: `tour-operators`
- Packages: `packages`

Expose editable bases under **ADS Tourism → Settings → Permalinks**.

### 13.2 Requirements

- Native editable record slugs.
- Editable taxonomy bases and term slugs.
- Optional hierarchical Place URLs.
- Duplicate and reserved-slug validation.
- Preview URLs in settings.
- Follow WordPress trailing-slash behaviour.
- Flush rewrite rules only on activation, deactivation or explicit permalink-setting changes.
- Track old record slugs and configured bases for permanent redirects.
- Generate canonical URLs through WordPress APIs.
- Include `slug` in CSV import/export.
- Relationship links remain valid when a slug changes because they use IDs.

When WooCommerce integration is active, Woo product URLs remain controlled by WooCommerce permalink settings. Do not create competing product rewrite rules.

---

## 14. Templates and Builder Integration

### 14.1 Fallback Templates

Bundle minimal, accessible templates for:

- `single-ads_place`
- `single-ads_activity`
- `single-ads_stay`
- `single-ads_operator`
- `single-ads_package`
- each archive
- public taxonomy archives
- search/no-results components

Allow theme overrides under:

```text
active-theme/ads-tourism/
```

Document the lookup order and provide filters for template resolution.

### 14.2 Layout Modes

Each record supports:

- **Standard:** global theme/builder/plugin template.
- **Standard plus Custom Content:** standard template plus record editor/builder content in a defined slot.
- **Full Custom:** use record content as the main body while structured data remains stored and queryable.

Changing layout mode must not delete structured fields.

### 14.3 Builder-Agnostic Requirements

- Public/queryable post types and taxonomies.
- REST-visible registered meta.
- Standard `the_title`, `the_content`, featured image and archive APIs.
- Shortcodes for complex/repeatable fields.
- Template hierarchy and override support.
- No critical tourism data saved only inside a builder layout.

### 14.4 Divi Integration

- Detect Divi without making it a dependency.
- Ensure post types can be enabled under Divi Post Type Integration.
- Confirm post types and public taxonomy archives appear as Theme Builder assignment targets.
- Provide a system-status check that reports whether ADS Tourism types are enabled in Divi.
- Ensure Title, Post Content and Featured Image work through standard Divi dynamic content.
- Provide shortcodes for relationships, galleries, itinerary and interactive listings.
- Consider native Divi modules only after the shortcode and REST contracts are stable.
- Maintain a manual Divi smoke-test checklist for each supported Divi release.

### 14.5 Styling

- Minimal responsive CSS only.
- Setting to disable all frontend plugin CSS.
- Stable BEM-style classes prefixed `ads-tourism-`.
- CSS custom properties for spacing, colours, radius and breakpoints.
- No forced fonts or brand colours.
- Optional administrator-only custom CSS setting.
- Per-shortcode `class` attribute for designer control.
- Enqueue CSS/JS only when an ADS Tourism component is present.

---

## 15. Shortcode System

### 15.1 Naming

Prefix all public shortcodes with `ads_tourism_` to avoid collisions.

### 15.2 Listing Shortcodes

- `[ads_tourism_places]`
- `[ads_tourism_activities]`
- `[ads_tourism_stays]`
- `[ads_tourism_operators]`
- `[ads_tourism_packages]`
- `[ads_tourism_listing]` as the all-in-one generic listing

### 15.3 Separated Interactive Components

- `[ads_tourism_search]`
- `[ads_tourism_filters]`
- `[ads_tourism_sort]`
- `[ads_tourism_results]`
- `[ads_tourism_pagination]`
- `[ads_tourism_map]`

Example:

```text
[ads_tourism_search context="homepage-packages"]
[ads_tourism_filters context="homepage-packages" fields="place,activity,provider,price"]
[ads_tourism_sort context="homepage-packages"]
[ads_tourism_results context="homepage-packages" type="package" per_page="6" columns="3"]
[ads_tourism_pagination context="homepage-packages"]
```

### 15.4 Record Components

- `[ads_tourism_field]`
- `[ads_tourism_gallery]`
- `[ads_tourism_related_places]`
- `[ads_tourism_related_activities]`
- `[ads_tourism_related_stays]`
- `[ads_tourism_related_operators]`
- `[ads_tourism_related_packages]`
- `[ads_tourism_package_itinerary]`
- `[ads_tourism_package_provider]`
- `[ads_tourism_commerce_controls]`

Inside a single-record template, components default to the current queried record. An explicit record ID or slug may override the context where authorized and valid.

### 15.5 Context Contract

- Individually placed interactive controls require `context`.
- Context names allow letters, numbers, hyphens and underscores.
- One primary result/grid component is allowed per context.
- Multiple pagination controls may share a context.
- Controls communicate only with the matching context.
- Context state includes query, filters, sorting, page, per-page and map viewport where applicable.
- Search/filter/sort changes reset page to one.
- Missing, duplicate or incompatible contexts produce an administrator-visible diagnostic, not a public PHP warning.
- The all-in-one listing may generate a unique context automatically if none is supplied.

### 15.6 Query Features

Allowlisted filters:

- Keyword
- Place
- Activity
- Stay
- Operator/provider
- Package type/theme
- Record-specific taxonomies
- Amenities
- Accessibility features
- Price guidance/range where applicable
- Duration
- Verified/public records only for anonymous users

Allowlisted sorting:

- Title ascending/descending
- Newest/oldest
- Manual order
- Price ascending/descending for packages with valid price data
- Duration
- Popularity only after a documented metric exists
- Random with cache limitations documented

Pagination modes:

- Numbered
- Previous/next
- Load more
- Infinite scroll
- None

### 15.7 AJAX and REST

- Initial results are server-rendered.
- Enhanced requests use namespaced WordPress REST endpoints, for example `/ads-tourism/v1/query`.
- Validate all query arguments through a shared schema.
- Use allowlists; never pass raw request values into `WP_Query` or SQL.
- Namespace browser query parameters by context.
- Support Back/Forward navigation and shareable filtered URLs.
- Debounce search input.
- Cancel stale requests.
- Provide loading, empty and error states.
- Announce updates through an accessible live region.
- Cache identical public queries and invalidate on relevant record, taxonomy or relationship changes.
- Multiple contexts on one page must remain isolated.

---

## 16. Administration Experience

Top-level menu: **ADS Tourism**

Submenus:

- Dashboard
- Places to Go
- Things to Do
- Places to Stay
- Tour Operators
- Packages
- Taxonomies
- Import / Export
- Settings
- Tools / System Status
- Documentation / Help

### 16.1 Dashboard

Show:

- Record counts by type and workflow stage
- Unverified and Needs Update counts
- Recently imported records
- Broken relationship/media warnings
- WooCommerce status
- Map provider status
- Divi integration status when Divi is active
- Database schema version
- Plugin version

### 16.2 Record Editor

Organize fields into clear panels/tabs:

- Core information
- Location and map
- Relationships
- Contact and visitor information
- Media and gallery
- Verification
- Presentation and fallback overrides
- Commerce for Packages when WooCommerce is available

Use searchable selectors for relationships and Media Library selectors for attachments. Do not load thousands of records into one unpaginated select element.

### 16.3 Settings

Settings sections:

- General
- Content labels
- Permalinks
- Display defaults
- Default images
- CSS
- Workflow
- Maps
- SEO
- Multilingual integrations
- WooCommerce
- Import/export
- Privacy and retention
- Uninstall/data retention
- Developer/debug settings

Use the WordPress Settings API, capability checks and autoload settings only when they are required on most requests.

---

## 17. CSV Import and Export

### 17.1 Import Workflow

1. Select record type.
2. Download the matching CSV template.
3. Upload UTF-8 CSV.
4. Detect delimiter and header row.
5. Map columns to fields.
6. Validate and preview without writing.
7. Choose duplicate policy.
8. Import in AJAX batches.
9. Show counts and row errors.
10. Download rejected rows.

### 17.2 Import Rules

- One row equals one record.
- `external_id` is the stable import key.
- Duplicate policies: skip, update, or create new.
- New records default to Draft and Unverified.
- Blank value and explicit-clear value must be distinguishable during update imports.
- Validate coordinates, URLs, emails, numeric fields and enumerated values.
- Limit file type and size through settings and server capability.
- Delete temporary files after completion or expiry.
- Never execute formulas or markup from CSV cells.
- Never download image URLs.

### 17.3 Taxonomies

Provide two modes:

- **Simple:** record fields only.
- **Advanced:** controlled taxonomy columns.

Advanced taxonomy values use stable slugs and a documented delimiter. Unknown term creation is disabled by default. If enabled, show a preview and normalization warnings before creation.

### 17.4 Relationships

Relationship import is deferred from the first implementation. Editors link records through the admin panel.

Design external IDs so a later `relationships.csv` can safely contain:

```text
source_external_id,relation_key,target_external_id,is_primary,sort_order
```

### 17.5 Media Columns

Support:

- `featured_attachment_id`
- `featured_media_url`
- `featured_media_url_type`
- gallery URLs using a documented delimiter for simple import

For full normalized export, media associations use a separate `media.csv`.

### 17.6 Export

Support:

- All records
- One record type
- Current admin filters
- Selected records
- Workflow or verification status
- Modified date range
- Taxonomies
- Relationships
- Media links

Full export produces a ZIP such as:

```text
places.csv
activities.csv
stays.csv
operators.csv
packages.csv
taxonomies.csv
relationships.csv
media.csv
manifest.json
```

Escape values that spreadsheet software may interpret as formulas. Include schema version and export timestamp in the manifest.

### 17.7 Import History

Store non-sensitive run metadata in:

```text
{$wpdb->prefix}ads_tourism_import_runs
```

Record user ID, type, status, counts, timestamps and error-report reference. Do not retain uploaded files indefinitely.

---

## 18. Maps

### 18.1 Provider-Neutral Contract

Create `MapProviderInterface` with methods for:

- Availability/configuration check
- Script/style registration
- Single marker rendering
- Multiple marker rendering
- Marker data normalization
- Provider attribution
- Optional directions URL

Register providers through a filter such as:

```php
apply_filters( 'ads_tourism_map_providers', $providers );
```

### 18.2 Google Maps First

- Provide Google Maps as the first supported provider.
- Store provider settings through the Options API with autoload disabled.
- Mask keys in admin display.
- Explain browser-key referrer restrictions.
- Do not expose server-only secrets in page source.
- Allow administrators to disable maps without losing coordinates.

### 18.3 Map Components

- Single record map
- Multi-record map
- Listing-context map that responds to filters
- Marker click opens the record or a configured preview card
- Configurable height, zoom, marker limit and clustering when supported

The default location mode is `primary`, producing at most one visible marker per record. `locations="all"` opts into all visible points, retaining their labels and roles in marker payloads. Invalid coordinates and hidden points are omitted, and context maps receive both primary and all-location marker arrays during AJAX updates.

If no provider, key or valid coordinates exists, render nothing or a configured directions link. Core content must remain functional.

---

## 19. SEO

SEO is a core requirement.

### 19.1 Native SEO Foundations

- Public post types and taxonomies included in WordPress sitemaps.
- Clean configurable permalinks.
- Canonical single-record and archive URLs.
- Correct archive titles and descriptions.
- Breadcrumb data and hooks.
- Featured/default image resolution available to social previews.
- Noindex unpublished, unverified preview, filtered utility and internal tool URLs as appropriate.

### 19.2 Structured Data

Provide an extensible schema mapper for relevant tourism, place, lodging, organization, trip and product concepts. Only emit properties supported by actual stored data.

When WooCommerce or an SEO plugin already emits Product or canonical schema, merge through documented hooks or suppress duplicate ADS Tourism output.

### 19.3 SEO Plugin Compatibility

Test with at least one widely used SEO plugin and expose filters that allow others to read:

- Resolved featured image
- Record summary
- Coordinates
- Provider/operator
- Price guidance
- Taxonomy and relationship data
- Canonical URL

Do not attempt to replace a full SEO plugin.

---

## 20. Multilingual Readiness

English is the base content language.

From the first commit:

- Use WordPress internationalization functions for every interface string.
- Load the `ads-tourism` text domain.
- Include a POT generation command in the build process.
- Avoid concatenated translatable sentences.
- Make REST and shortcode labels filterable/translation-ready.
- Store language-neutral IDs and relation keys.

Optional integration phase:

- Detect established multilingual plugins without hard dependencies.
- Allow translated record and taxonomy equivalents.
- Resolve related records into the current language where mappings exist.
- Fall back to the base-language record according to an administrator setting.
- Document what is translated by WordPress/plugin integration versus external human or machine translation.

Do not build an automatic translation service into core.

---

## 21. Optional WooCommerce Integration

### 21.1 Activation Behaviour

- Core Packages work when WooCommerce is missing or inactive.
- Hide Add to Cart, Buy Now, checkout and Woo settings when unavailable.
- Never throw a fatal error because WooCommerce classes are absent.
- Show a non-blocking admin status notice on the Packages/Woo settings screen.

### 21.2 Package-to-Product Mapping

Use a one-to-one optional link:

- Package meta: `_ads_tourism_product_id`
- Product meta: `_ads_tourism_package_id`

Source of truth:

- Tourism description, relationships, itinerary and tourism media: Package.
- Commerce price, tax, stock/capacity setting, cart, checkout, order and payment state: WooCommerce Product.

Provide explicit Create Product, Link Existing Product, Sync Product and Detach actions. Do not silently create products during ordinary Package saves.

### 21.3 Commerce Modes

- **Catalogue:** no transactional controls.
- **Enquiry:** configurable contact/request CTA; no order.
- **WooCommerce:** linked product and transactional controls.

When WooCommerce mode is selected without a valid linked product, show an admin validation error and fall back publicly to Catalogue or Enquiry.

### 21.4 Frontend Behaviour

Configuration options:

- Package listing opens Package page.
- Package listing opens linked Product page.
- Package page displays Add to Cart.
- Package page displays Buy Now/direct-checkout action.
- Product page renders linked tourism fields using shortcodes/components.

Use Divi Woo modules where the site chooses a Woo product page. ADS Tourism must still provide builder-agnostic output.

### 21.5 Providers and Package Types

The provider relation accepts a primary Tour Operator or Place to Stay. This supports accommodation packages without treating the Stay listing itself as live room inventory.

### 21.6 WooCommerce Engineering Rules

- Use WooCommerce CRUD objects and APIs.
- Declare and test HPOS compatibility.
- Do not query Woo order tables directly.
- Do not duplicate checkout or payment processing.
- Product deletion detaches mapping but preserves the Package.
- Package deletion never silently deletes a Product; require a separate confirmed action.
- WooCommerce deactivation hides controls but preserves mappings for reactivation.

Direct purchasable accommodation inventory may be designed later as a separate module.

---

## 22. Security

Apply WordPress security practices throughout:

- Capability checks on every write/admin action.
- Nonces on state-changing admin and frontend actions.
- REST permission callbacks on every endpoint.
- Schema-based REST validation.
- Sanitize input at the boundary.
- Escape output at the final rendering context.
- Prepared SQL for all custom queries.
- Allowlisted sorting, filters, relation keys and field keys.
- CSRF protection for imports, settings, relationship changes and product linking.
- CSV MIME, extension, size and row validation.
- Spreadsheet-formula injection protection on export.
- No fetching of untrusted external image URLs.
- Strict API-key handling and masked settings.
- No secrets or personal data in logs.
- Rate-limit or cache expensive anonymous query endpoints.
- Avoid exposing unpublished or private fields through REST.
- Dependency audits in CI.
- Security reporting instructions in `SECURITY.md`.

Custom CSS settings require `manage_options`; document that administrators are trusted to add CSS.

---

## 23. Privacy

The initial tourism records are public business/destination data, but contact people, future enquiries and WooCommerce customers may contain personal information.

Requirements:

- Mark fields as public or admin-only in their schema.
- Do not expose verification notes, internal contacts or import logs publicly.
- Provide suggested privacy-policy text when personal information is collected.
- If Enquiry storage is implemented, add WordPress personal-data exporter and eraser integration.
- Configure retention for enquiries and diagnostic logs.
- Avoid logging names, emails, phone numbers, API keys or request bodies.
- WooCommerce remains responsible for its customer/order privacy handling.
- Uninstall deletion must respect the explicit data-retention setting.

---

## 24. Performance

- Index custom relationship and media tables for actual query patterns.
- Avoid N+1 relationship and meta queries.
- Prime post, term and attachment caches for result sets.
- Cache normalized public query results by query hash and language.
- Invalidate caches on record save, status change, taxonomy edit, relationship edit, media edit and relevant settings changes.
- Use persistent object cache when available; otherwise use bounded transients.
- Cap `per_page`, marker counts and unpaginated exports.
- Process imports/exports in bounded batches.
- Enqueue assets only on relevant admin or frontend screens.
- Split map-provider assets from core listing assets.
- Use responsive images and lazy loading.
- Debounce search and cancel stale AJAX requests.
- Measure query count, response time and memory with representative seed data.
- Ensure multiple contexts on one page do not duplicate identical network requests unnecessarily.

Define performance budgets before release, including a representative 1,000-record test dataset and multi-context homepage test.

---

## 25. Data Integrity and Maintenance

- Use repository/service methods for all relationship writes.
- Enforce unique external IDs at the application layer and validate imports before commit.
- Use unique database indexes where possible.
- Clean custom-table rows on permanent post/attachment deletion.
- Never delete connected tourism records as a cascade.
- Provide integrity scans for orphaned relations, invalid media links, missing mapped products and duplicate external IDs.
- Provide repair actions that require confirmation and generate a report.
- Prefer trash/archive over permanent deletion.
- Treat base URL changes as migration events with redirect records.
- Include database schema version in System Status.
- Provide a WP-CLI integrity command in a later hardening phase if time permits.

---

## 26. Migrations, Activation, Deactivation and Uninstall

### 26.1 Versions

Maintain separately:

- Plugin semantic version.
- Database schema integer/version.
- Export schema version.
- REST API version.

### 26.2 Activation

- Check minimum PHP and WordPress requirements.
- Register post types/taxonomies before flushing rewrites.
- Create/update required tables using WordPress-supported database upgrade methods.
- Seed default options only, not ENBTA content.
- Record installed schema version.
- Schedule no recurring task unless a feature requires it.

### 26.3 Upgrades

- Ordered, idempotent migration classes.
- Run migrations under a lock.
- Never rely only on activation hooks because plugin updates do not reactivate the plugin.
- Prefer additive/backward-compatible migrations.
- Log migration success/failure without sensitive data.
- Block features that require an incomplete migration and show an administrator recovery notice.
- Test fresh install and every supported upgrade path.

### 26.4 Deactivation

- Preserve all content, options and tables.
- Clear only scheduled jobs and transient caches where safe.
- Flush rewrite rules.
- Do not remove Woo mappings or media associations.

### 26.5 Uninstall

Default behaviour: preserve data.

Provide an explicit setting:

```text
Delete all ADS Tourism data during uninstall
```

Enabling it requires confirmation. If enabled, `uninstall.php` removes plugin options, custom tables, post meta, taxonomy terms created exclusively by the plugin, and ADS Tourism posts according to documented rules. It must not delete shared Media Library attachments or WooCommerce Products without a separate explicit policy.

---

## 27. Extensibility

### 27.1 PHP Hooks

Document actions and filters for:

- Post type/taxonomy arguments
- Field schemas
- Record validation
- Workflow transitions
- Relationship types
- Relationship query results
- Field fallback resolution
- Template lookup
- Shortcode attributes and rendered output
- Query arguments before execution
- Map providers and marker data
- SEO schema
- CSV column definitions and row transformations
- Woo product synchronization
- Cache invalidation

Prefix hooks with `ads_tourism_`.

### 27.2 REST API

Versioned namespace:

```text
/wp-json/ads-tourism/v1/
```

Initial endpoint groups:

- Public query/listing
- Public record data where WordPress core endpoints are insufficient
- Public map markers
- Authenticated relationship search
- Authenticated import/export operations
- Authenticated integrity tools
- Optional Woo mapping operations

Publish JSON schemas and permission rules in developer documentation.

### 27.3 Template API

- Theme override directory.
- Template-locate filters.
- Stable view-model arrays/objects.
- Escaped defaults.
- Version template contracts when breaking changes are unavoidable.

### 27.4 CLI

Plan optional WP-CLI commands after core stabilization:

- `wp ads-tourism status`
- `wp ads-tourism migrate`
- `wp ads-tourism integrity-check`
- `wp ads-tourism import`
- `wp ads-tourism export`
- `wp ads-tourism cache clear`

---

## 28. Proposed Repository Structure

```text
ads-tourism/
├── .github/
│   ├── ISSUE_TEMPLATE/
│   ├── PULL_REQUEST_TEMPLATE.md
│   ├── dependabot.yml
│   └── workflows/
│       ├── ci.yml
│       ├── compatibility.yml
│       ├── build-artifact.yml
│       └── release.yml
├── assets/
│   ├── src/
│   │   ├── css/
│   │   └── js/
│   └── dist/
├── bin/
│   ├── build-release.php
│   ├── generate-pot.sh
│   └── verify-version.php
├── config/
│   ├── phpstan.neon
│   ├── phpcs.xml.dist
│   └── test-config.php
├── docs/
│   ├── architecture.md
│   ├── data-model.md
│   ├── admin-guide.md
│   ├── builder-integration.md
│   ├── shortcodes.md
│   ├── ajax-contexts.md
│   ├── csv-import-export.md
│   ├── maps.md
│   ├── seo.md
│   ├── multilingual.md
│   ├── woocommerce.md
│   ├── security-privacy.md
│   ├── hooks-rest-api.md
│   ├── testing.md
│   ├── release-process.md
│   └── adr/
├── languages/
├── src/
│   ├── Admin/
│   ├── Application/
│   ├── Domain/
│   ├── Infrastructure/
│   ├── Integration/
│   ├── Maps/
│   ├── Presentation/
│   ├── REST/
│   └── Support/
├── templates/
│   ├── single/
│   ├── archive/
│   ├── taxonomy/
│   └── components/
├── tests/
│   ├── Unit/
│   ├── Integration/
│   ├── E2E/
│   ├── Fixtures/
│   └── bootstrap.php
├── views/
│   └── admin/
├── ads-tourism.php
├── uninstall.php
├── composer.json
├── composer.lock
├── package.json
├── package-lock.json
├── phpunit.xml.dist
├── readme.txt
├── README.md
├── CHANGELOG.md
├── CONTRIBUTING.md
├── SECURITY.md
├── LICENSE
└── AGENTS.md
```

Do not commit `vendor/`, development `node_modules/`, generated test databases or local environment secrets. The release ZIP includes production dependencies and compiled assets.

---

## 29. Coding Standards and Tooling

- Composer PSR-4 autoloading.
- WordPress Coding Standards through PHPCS.
- PHPStan at an agreed level increased over time without suppressing real issues.
- PHPUnit for PHP tests.
- ESLint and Prettier for JavaScript/CSS/JSON/Markdown where applicable.
- No frontend framework unless a demonstrated requirement justifies it; use small modular JavaScript and WordPress APIs.
- Strictly prefix global functions, option names, transients, database tables, hooks, script handles and CSS classes.
- Avoid direct access to plugin PHP files.
- Store all dates in WordPress-compatible UTC/database formats and format for display through WordPress settings.
- Add docblocks to public extension points.
- Keep generated assets reproducible from committed source and lockfiles.

---

## 30. Testing Strategy

### 30.1 Unit Tests

- Field schemas and validation
- Fallback resolver
- Permalink/base validation
- Context-name validation
- Query argument allowlists
- Relation direction and reverse-query logic
- Media URL normalization
- CSV row parsing and duplicate rules
- Workflow transitions
- Woo mapping logic with adapters mocked
- Map provider registry

### 30.2 WordPress Integration Tests

- Post type and taxonomy registration
- REST schemas and permissions
- Record CRUD
- Metadata sanitization
- Relationship table repositories
- Media association cleanup
- Rewrite rules and canonical URLs
- Template selection and theme override
- Shortcode rendering
- Cache invalidation
- Import/export writes
- Migration paths
- Activation/deactivation/uninstall behaviour

### 30.3 WooCommerce Integration Tests

Run both without WooCommerce and with supported WooCommerce versions:

- No fatal errors when absent
- Create/link/sync/detach product
- Commerce controls visibility
- Product deletion/package deletion separation
- Add to Cart and checkout link generation
- HPOS compatibility declaration
- Woo CRUD use

### 30.4 Browser/E2E Tests

- Admin creates each record type
- Relationship selectors and reverse display
- CSV preview/import/error report
- Divi-independent frontend fallback pages
- Two independent shortcode contexts on one page
- AJAX search/filter/sort/pagination
- Back/Forward URL restoration
- No-JavaScript pagination fallback
- Gallery empty-state behaviour
- Featured-image default behaviour
- Google map configured/unconfigured behaviour
- Package catalogue and Woo modes

### 30.5 Accessibility Tests

- Keyboard-only filters and galleries
- Focus management after AJAX updates
- Accessible labels and errors
- Live-region result counts
- Colour-independent states
- Reduced-motion support
- Semantic headings and landmarks

### 30.6 Performance Tests

Use generated fixtures with at least:

- 1,000 Places
- 1,000 Activities
- 1,000 Stays
- 250 Operators
- 1,000 Packages
- Representative relationships and media links

Measure REST response time, query count, memory, import throughput and a homepage containing multiple independent contexts.

### 30.7 Manual Compatibility Matrix

- Current target WordPress environment
- Divi Theme Builder post type assignments
- Divi dynamic title/content/featured image
- A non-Divi default theme
- WooCommerce off/on
- Google Maps off/on
- Selected SEO plugin off/on
- Selected multilingual plugin off/on when that phase is delivered

---

## 31. Git and GitHub Workflow

### 31.1 Repository Setup

- Create the repository in the approved ADS GitHub account or organization.
- Default branch: `main`.
- Protect `main`: pull request required, status checks required, no force push, no deletion.
- Use short-lived branches: `feature/...`, `fix/...`, `docs/...`, `release/...`.
- Use pull requests even for coding-assistant work unless an explicitly approved emergency process applies.
- Require the CI workflow before merge.
- Add issue and pull request templates.
- Configure Dependabot for Composer, npm and GitHub Actions.
- Add CODEOWNERS only when maintainers are confirmed.

### 31.2 Commit and Pull Request Rules

- One coherent change per commit.
- Use clear imperative commit subjects.
- Include tests with behaviour changes.
- Update relevant docs with public API, shortcode, schema or workflow changes.
- Include migration notes for schema changes.
- PR description must list scope, tests, screenshots for admin/frontend changes, migrations and compatibility impact.
- Do not commit secrets, API keys, exported production data or media files unnecessarily.

### 31.3 Versioning

Use Semantic Versioning:

- Patch: compatible bug fix.
- Minor: backward-compatible feature.
- Major: breaking public API, data or template contract.

Maintain the same version in:

- Main plugin header
- Version constant
- `readme.txt` stable tag where applicable
- `CHANGELOG.md`
- Git tag `vX.Y.Z`

The release workflow must fail if versions disagree.

---

## 32. Continuous Integration

### 32.1 `ci.yml`

Triggers:

- Pull requests
- Pushes to `main`

Jobs:

1. **Composer validation**
   - Validate `composer.json` and lockfile.
   - Install dependencies with locked versions.
   - Audit dependencies.

2. **PHP quality**
   - Syntax check.
   - PHPCS WordPress Coding Standards.
   - PHPStan.

3. **JavaScript/assets**
   - `npm ci`.
   - Lint.
   - Unit tests if present.
   - Production build.
   - Fail on uncommitted generated output if compiled assets are committed by policy.

4. **PHP tests**
   - PHP and WordPress compatibility matrix.
   - Unit and integration tests.
   - Coverage artifact on the primary matrix job.

5. **Woo compatibility**
   - Core tests without WooCommerce.
   - Supported WooCommerce matrix.

6. **Package smoke test**
   - Build release ZIP using the same release script.
   - Inspect exclusions and root directory.
   - Install ZIP in a clean WordPress test environment.
   - Activate with WooCommerce absent.
   - Run basic health assertions.

Use least-privilege workflow permissions. Pin third-party actions to reviewed commit SHAs and update them through controlled dependency updates.

### 32.2 `compatibility.yml`

Scheduled and manually dispatchable:

- Latest WordPress stable.
- Supported PHP versions.
- Latest WooCommerce stable.
- Optional WordPress nightly job allowed to fail but must create an issue/notification policy before enabling.

### 32.3 `build-artifact.yml`

Manually dispatchable and optionally run on `main`:

- Produce an unsigned development ZIP.
- Name it `ads-tourism-{version}-{shortsha}.zip`.
- Upload as a time-limited GitHub Actions artifact.
- Never label a development artifact as a production release.

---

## 33. Automated Release ZIP Workflow

### 33.1 Build Script

`bin/build-release.php` is the single packaging implementation used locally and in CI.

It must:

1. Read and validate the requested version.
2. Verify a clean generated-asset state.
3. Install Composer production dependencies with optimized autoloading.
4. Build production frontend assets.
5. Generate/update translation template where policy requires.
6. Copy allowlisted production files into a staging directory named `ads-tourism`.
7. Exclude tests, source maps if not shipped, development configs, `.git`, `.github`, node modules, local files and secrets.
8. Create `ads-tourism-{version}.zip` containing one top-level `ads-tourism/` directory.
9. Generate `ads-tourism-{version}.zip.sha256`.
10. Produce a manifest of included files.
11. Install and activate the ZIP in a clean smoke-test environment.

The GitHub-generated source ZIP is not the installable plugin artifact because it may omit production dependencies or include development files.

### 33.2 `release.yml`

Trigger:

- Push of an annotated tag matching `v*.*.*`, or a guarded manual dispatch referencing an existing tag.

Required flow:

1. Checkout the exact tag.
2. Verify tag, plugin header, version constant, changelog and stable tag match.
3. Run the complete required CI suite or call a reusable validated workflow.
4. Build the ZIP through `bin/build-release.php`.
5. Generate checksum and optional software bill of materials.
6. Create a **draft** GitHub Release for the tag.
7. Attach ZIP, SHA-256 checksum, manifest and optional SBOM.
8. Populate release notes from `CHANGELOG.md` plus compatibility and migration notes.
9. Verify attached artifact name and checksum.
10. Publish the release only after all assets exist; use a manual approval environment if repository policy supports it.

Use the repository `GITHUB_TOKEN` with `contents: write` only in the release job. Other jobs use read-only permissions. Prefer GitHub CLI/official APIs over unreviewed release actions.

### 33.3 Release Artifact Contract

Example:

```text
ads-tourism-1.0.0.zip
ads-tourism-1.0.0.zip.sha256
ads-tourism-1.0.0-manifest.json
ads-tourism-1.0.0-sbom.spdx.json
```

The ZIP must be installable through **WordPress → Plugins → Add New → Upload Plugin**.

### 33.4 Release Checklist

- All required CI checks green.
- Fresh install passes.
- Supported upgrade path passes.
- Database schema version correct.
- Changelog and upgrade notes complete.
- README/readme shortcode and setting references current.
- No secrets or development-only files in ZIP.
- Translation template current.
- Divi manual smoke test recorded.
- Woo off/on smoke tests recorded.
- CSV import/export smoke test recorded.
- Security/privacy review completed for new inputs or endpoints.
- Checksum verified.
- Release notes identify known limitations.

No automatic deployment to the live ENBTA site is included initially. Production installation remains a deliberate, backed-up administrative action.

---

## 34. Documentation Plan

### 34.1 `README.md`

Keep the repository landing page concise and written in simple English:

1. What ADS Tourism does.
2. Main record types.
3. Requirements.
4. Quick installation.
5. Five-minute setup.
6. Short example using a listing shortcode.
7. Optional Divi, Maps and WooCommerce integration.
8. Development commands.
9. Documentation links.
10. Support/security links.

Do not put the entire technical specification in the README.

### 34.2 WordPress `readme.txt`

Include plugin metadata, description, installation, FAQ, screenshots, changelog, upgrade notices, requirements and privacy/external-service disclosures appropriate to distribution policy.

### 34.3 User Documentation

- Admin guide
- Creating each record type
- Relationships and reverse listings
- Verification workflow
- Media and featured-image fallbacks
- Permalinks
- CSV import/export
- Building templates in Divi
- Shortcode recipes
- Maps setup
- WooCommerce setup
- Troubleshooting/system status

### 34.4 Developer Documentation

- Architecture and source-of-truth rules
- Complete data dictionary
- Database tables and indexes
- Hook/filter reference
- REST API schemas
- Template override contract
- Shortcode attributes and context contract
- Cache and invalidation rules
- Migration authoring rules
- Integration adapter guide
- Testing guide
- Release process

### 34.5 Decision Records

Use `docs/adr/` for material decisions, beginning with:

- Places to Go as relationship spine
- Core Package record plus optional Woo product mapping
- Custom indexed relationship table
- Media Library ownership plus media-link associations
- Server-rendered listings enhanced through REST/AJAX
- Explicit shortcode context isolation
- Builder-agnostic core with optional Divi adapter

### 34.6 Coding Assistant Instructions

`AGENTS.md` must state:

- Read architecture and relevant ADRs before edits.
- Preserve public API and migration compatibility.
- Do not bypass repositories with direct relationship-table writes.
- Do not add a hard dependency on Divi, WooCommerce, Google Maps or multilingual plugins.
- Add tests and docs for behaviour changes.
- Run the documented quality commands before handoff.
- Do not create releases or push tags without explicit authorization.

---

## 35. Executable Development Phases

Each phase should be implemented through one or more small pull requests. Do not begin a dependent phase until the prior exit criteria pass.

### Phase 0 — Repository and Decision Baseline

Tasks:

- Confirm repository owner and visibility.
- Confirm distribution licence and copyright owner.
- Audit target WordPress, PHP, Divi and WooCommerce versions.
- Create repository structure and `AGENTS.md`.
- Add Composer/npm tooling and lockfiles.
- Add initial CI, branch protection plan and contribution templates.
- Create ADRs for locked architecture decisions.
- Create a minimal plugin bootstrap with requirement checks.

Exit criteria:

- Plugin activates/deactivates in a clean WordPress install.
- CI passes.
- Version and namespace conventions documented.
- No optional integration is required for activation.

### Phase 1 — WordPress Domain Foundation

Tasks:

- Register five post types.
- Register taxonomies.
- Register common meta schemas.
- Add configurable labels and permalink bases.
- Implement activation/deactivation and schema-version framework.
- Add basic admin menu and System Status.
- Add fixtures for tests.

Exit criteria:

- All record types can be created, revised, published and viewed.
- Public archives and REST endpoints work.
- Post types appear in navigation-menu and builder-compatible contexts.
- Fresh install and migration framework tests pass.

### Phase 2 — Entity Fields, Relationships and Workflow

Tasks:

- Implement record-specific field schemas and editor panels.
- Create relationship table and repository.
- Implement searchable relationship selectors and reverse queries.
- Implement Draft/Review/Verified/Published workflow.
- Add verification history.
- Add list-table columns and filters.
- Add integrity cleanup hooks.

Exit criteria:

- Every required relationship can be created once and queried both directions.
- Unverified records cannot publish under the default policy.
- Permanent deletion removes only association rows.
- Integrity tests pass.

### Phase 3 — Media, Fallbacks and Permalinks

Tasks:

- Enable native featured images.
- Build media association table and Media Library selector.
- Add internal/external/relative media links.
- Add gallery ordering, roles, captions, alt overrides and credits.
- Implement central field/image fallback resolver.
- Implement configurable default images.
- Complete permalink settings, validation and redirect behaviour.

Exit criteria:

- Missing data/media produces no empty or broken visual elements.
- Attachment IDs survive domain changes.
- Slug changes preserve relations and redirect old URLs.
- Media detachment never deletes attachments.

### Phase 4 — CSV Import and Export

Tasks:

- Define versioned CSV schemas and downloadable templates.
- Implement mapping, dry-run preview and validation.
- Implement AJAX batch import and duplicate policies.
- Implement controlled taxonomy import.
- Implement filtered and full export bundles.
- Add rejected-row reports, manifest and import history.
- Add CSV security protections.

Exit criteria:

- Representative files import without timeouts.
- Invalid rows do not partially corrupt valid records.
- Re-import by external ID follows chosen policy.
- Full export can reconstruct all supported fields, taxonomies, relations and media links.

### Phase 5 — Templates and Builder Compatibility

Tasks:

- Build fallback single/archive/taxonomy templates.
- Implement theme override lookup.
- Implement layout modes.
- Register simple dynamic meta for builder access.
- Implement Divi detection/status and compatibility hooks.
- Document Theme Builder assignment and individual override workflows.
- Add minimal CSS and disable/custom CSS settings.

Exit criteria:

- Records render without a builder.
- Divi Theme Builder can target all public post types and relevant archives.
- A global Place template and specific Place override work.
- Full Custom mode preserves structured data.

### Phase 6 — Shortcodes, AJAX and Contexts

Tasks:

- Implement field and relationship component shortcodes.
- Implement listing shortcodes.
- Implement separated search/filter/sort/results/pagination controls.
- Implement strict context registry.
- Add versioned public query REST endpoint.
- Add server initial render, AJAX enhancement and URL-state handling.
- Add caching, accessibility and multi-context tests.

Exit criteria:

- Two or more listing contexts operate independently on one page.
- Search/filter/sort/pagination work without reload.
- Back/Forward restores state.
- No-JavaScript fallback works.
- Invalid shortcode attributes fail safely.

### Phase 7 — SEO, Maps and Multilingual Readiness

Tasks:

- Implement SEO metadata/schema integration layer.
- Verify sitemap, canonical, breadcrumb and social-image inputs.
- Implement map provider interface and Google Maps provider.
- Implement single/multi/context map shortcodes.
- Complete all interface internationalization and POT generation.
- Add optional multilingual-plugin adapters or document a later sub-release if compatibility testing is not ready.

Exit criteria:

- No duplicate schema with tested SEO integration.
- Map works with a valid key and disappears safely without one.
- Map context follows listing filters.
- Translation template contains all plugin strings.

### Phase 8 — Optional WooCommerce Integration

Tasks:

- Add optional Woo adapter and status detection.
- Implement Package/Product mapping.
- Add commerce modes and admin validation.
- Add product synchronization actions.
- Add commerce controls shortcode/component.
- Support operator- and accommodation-provided Packages.
- Add product-page tourism components.
- Declare/test HPOS compatibility.

Exit criteria:

- Core plugin works unchanged with Woo absent.
- Catalogue mode never renders cart controls.
- Linked package can add its product to cart and reach checkout.
- Woo deactivation hides controls without losing Package data.
- Accommodation Package works without room-inventory features.

### Phase 9 — Hardening, Documentation and 1.0 Release

Tasks:

- Complete security/privacy review.
- Run large-data performance tests.
- Complete accessibility and builder compatibility tests.
- Complete migration and uninstall tests.
- Finish README, readme.txt, user and developer documentation.
- Finish build and release workflows.
- Build release candidate ZIP and run acceptance checklist.
- Resolve all release-blocking defects.
- Tag and publish 1.0.0 through the approved release workflow.

Exit criteria:

- Definition of Done passes.
- Installable ZIP and checksum are attached to GitHub Release.
- Known limitations are documented.
- No production deployment occurs without a separate authorized deployment plan.

---

## 36. Acceptance Scenarios

The following scenarios are mandatory for 1.0 unless explicitly moved to a named later milestone.

1. Install ADS Tourism without Divi, maps or WooCommerce; activate successfully.
2. Create Kokopo as a Place and publish it after verification; receive a valid single-record URL.
3. Create a Stay, Activity, Operator and Package and relate them to Kokopo.
4. View Kokopo and retrieve all related records through reverse queries.
5. Assign a native featured image; see it in a listing and single template.
6. Remove the image; see configured fallback or no image with no broken markup.
7. Link multiple Media Library images and an external URL; render ordered gallery.
8. Leave optional fields empty; their labels and sections disappear.
9. Change a record slug; old URL redirects and relations remain valid.
10. Import 100 records from CSV as Draft/Unverified and receive a rejected-row report for invalid entries.
11. Export a filtered content type and a complete normalized ZIP.
12. Create a Divi Theme Builder template for all Places and a specific override for one Place.
13. Add a package listing to the homepage using shortcodes.
14. Place search, filter, results and pagination separately with the same context and observe coordinated AJAX behaviour.
15. Place a second context on the same page; verify no cross-control.
16. Configure Google Maps and show filtered Place/Stay markers; disable it and retain all content.
17. Confirm published records have correct canonical URLs and non-duplicated structured data.
18. Enable WooCommerce, link an Accommodation Package to a Product, add it to cart and reach checkout.
19. Disable WooCommerce; Package page remains and commerce controls disappear.
20. Deactivate ADS Tourism; data remains.
21. Reinstall/upgrade ADS Tourism; migrations preserve data.
22. Build ZIP from tag; install and activate it on a clean WordPress test site.

---

## 37. Definition of Done

A feature is done only when:

- Acceptance criteria are implemented.
- Input is validated and output escaped.
- Capability and REST permission checks exist.
- Unit/integration/E2E tests appropriate to the feature pass.
- Empty, error and missing-integration states are handled.
- Cache invalidation is implemented where needed.
- Accessibility is checked.
- Public hooks, REST schemas, shortcodes and template changes are documented.
- Database changes have a versioned migration and upgrade test.
- WooCommerce-off behaviour is tested if the feature touches commerce.
- Divi-independent behaviour is preserved.
- CI passes and the release packaging smoke test remains green.

---

## 38. Principal Risks and Mitigations

| Risk | Mitigation |
| --- | --- |
| Builder lock-in | Standard WordPress records, REST meta, fallback templates and shortcodes remain canonical. |
| Package/Product duplication | Explicit source-of-truth split and one-to-one mapping; no silent product creation. |
| Slow many-to-many filters | Indexed relationship table, bounded queries and cache invalidation. |
| External images disappear | No download promise; validation, safe hide fallback and Media Library preference. |
| CSV creates duplicates | Stable external IDs, preview and explicit duplicate policy. |
| Taxonomy spelling fragmentation | Controlled slugs; unknown-term creation disabled by default. |
| Two AJAX listings interfere | Mandatory explicit context contract and namespaced URL state. |
| SEO plugins duplicate markup | Adapter detection, filters and documented ownership of schema/canonical output. |
| Map-provider lock-in/cost | Provider interface; coordinates stored independently. |
| WooCommerce deactivation breaks Packages | Core Package CPT remains independent; optional adapter only. |
| Rewrite changes cause 404s | Validate bases, controlled rewrite flush and redirect history. |
| Plugin updates corrupt relationships | Versioned, idempotent migrations plus fresh/upgrade-path tests. |
| Uninstall destroys content | Preserve by default; destructive uninstall requires explicit prior confirmation. |
| Custom-table orphan data | Cleanup hooks, integrity scanner and repair reports. |

---

## 39. Coding Assistant Execution Protocol

When Codex or another coding assistant implements this plan:

1. Read `AGENTS.md`, this architecture plan and relevant ADRs.
2. Inspect the current repository and existing changes before editing.
3. Work on one numbered phase or bounded issue at a time.
4. State assumptions when a field, label or compatibility version is unresolved.
5. Do not change locked architecture decisions silently.
6. Add or update tests before declaring implementation complete.
7. Run the phase-specific checks and the relevant CI-equivalent commands.
8. Update README/docs/changelog when public behaviour changes.
9. Use versioned migrations for data changes; never edit production data manually as part of code generation.
10. Keep optional integrations isolated and test their absence.
11. Produce a concise handoff with files changed, tests run, migrations and remaining risks.
12. Do not push, tag, publish a GitHub Release or deploy to production unless explicitly authorized.

Recommended issue format:

```text
Phase:
Goal:
In scope:
Out of scope:
Dependencies:
Implementation tasks:
Tests:
Documentation:
Acceptance criteria:
```

---

## 40. Phase 0 Decisions Required Before Coding Begins

These do not alter the architecture but must be recorded:

- GitHub repository owner/account or organization.
- Public or private repository visibility.
- Licence and copyright holder.
- Target ENBTA WordPress, PHP and Divi versions.
- Whether WooCommerce is already installed on the target site.
- Initial Google Maps key and billing/restriction owner.
- Whether the initial 1.0 release includes a stored Enquiry form or only external/contact CTAs.
- Final public terminology: retain **Things to Do** as the admin label with **Activity** as the internal technical term.
- Initial supported SEO and multilingual plugins for compatibility testing.

---

## 41. Official Technical References

- [WordPress Plugin Handbook](https://developer.wordpress.org/plugins/)
- [WordPress recommended requirements](https://wordpress.org/about/requirements/)
- [Creating tables with WordPress plugins](https://developer.wordpress.org/plugins/creating-tables-with-plugins/)
- [WordPress plugin security](https://developer.wordpress.org/plugins/security/)
- [WordPress plugin privacy guidance](https://developer.wordpress.org/plugins/privacy/)
- [WordPress REST API guidance](https://developer.wordpress.org/plugins/rest-api/)
- [WooCommerce extension development best practices](https://developer.woocommerce.com/docs/extensions/best-practices-extensions/extension-development-best-practices/)
- [WooCommerce data stores](https://developer.woocommerce.com/docs/best-practices/data-management/data-stores/)
- [WooCommerce HPOS compatibility](https://developer.woocommerce.com/docs/features/orders/high-performance-order-storage/recipe-book/)
- [Divi Theme Builder](https://www.elegantthemes.com/documentation/divi/the-divi-theme-builder/)
- [Divi Post Type Integration](https://www.elegantthemes.com/documentation/divi/theme-options/)
- [Divi Dynamic Content](https://www.elegantthemes.com/documentation/divi/divi-dynamic-content-options/)
- [GitHub Actions artifacts](https://docs.github.com/en/actions/tutorials/store-and-share-data)
- [GitHub Releases](https://docs.github.com/en/repositories/releasing-projects-on-github/about-releases)
- [Managing GitHub Releases](https://docs.github.com/en/repositories/releasing-projects-on-github/managing-releases-in-a-repository)
- [Verifying release integrity](https://docs.github.com/en/code-security/how-tos/secure-your-supply-chain/secure-your-dependencies/verify-release-integrity)

---

## 42. Final Architecture Summary

ADS Tourism is a standalone WordPress tourism information platform with optional presentation, mapping, translation and commerce integrations.

- **Places to Go** is the main relationship spine.
- **Things to Do, Places to Stay, Operators and Packages** are independent reusable records.
- **Taxonomies** classify; **relationships** connect actual records.
- **WordPress** owns content, revisions, taxonomies, media files, URLs and public page lifecycle.
- **ADS Tourism** owns tourism fields, relationships, verification, media associations, fallbacks, import/export, shortcodes and AJAX query behaviour.
- **Builders** own visual design while plugin fallback templates guarantee basic output.
- **Maps** are provider-neutral, with Google Maps first.
- **WooCommerce** is optional and owns commerce only.
- **GitHub Actions** validates, packages and releases a reproducible installable ZIP.

This separation is the central rule that keeps the plugin maintainable, builder-compatible and extensible.
