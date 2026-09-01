# Changelog

All notable changes to ADS Tourism will be documented in this file.

The project follows [Semantic Versioning](https://semver.org/) and the structure recommended by [Keep a Changelog](https://keepachangelog.com/).

## [Unreleased]

No unreleased changes.

## [1.1.0] - 2026-09-02

### Added

- An ADS Tourism Help submenu with shortcode recipes, complete shortcode attributes, usage rules, and administration references.
- Optional RGB hex color metadata and native WordPress color pickers for terms in all nine tourism taxonomies, with no color as the default.

## [1.0.0] - 2026-09-01

### Added

- Initial plugin bootstrap and dependency-free runtime autoloader.
- WordPress registration for five tourism content types and nine shared taxonomies.
- ADS Tourism administration menu and dashboard.
- Activation, deactivation, translation, uninstall, test, analysis, packaging, and release foundations.
- Typed shared and record-specific metadata registered through the WordPress REST API.
- Native record-details panels for place, activity, stay, operator, and package data.
- A canonical indexed relationship table with searchable bidirectional editing and deletion cleanup.
- Draft, review, verified, and published workflow stages with append-only verification history.
- A configurable publication gate that requires verification by default.
- Tourism list-table workflow columns and verification-status filtering.
- An indexed media-association table with Media Library and safe linked-image sources.
- Gallery roles, manual ordering, primary selection, metadata overrides, credits, rights notices, and display defaults.
- Content-type and global fallback images that never overwrite native featured images.
- Central field and featured-media fallback resolvers for future templates, shortcodes, REST presentation, and builder modules.
- Configurable post-type and taxonomy permalink bases with validation, explicit rewrite flushing, slug history, and permanent redirects.
- Versioned CSV templates, automatic or manual column mapping, dry-run previews, and row validation.
- AJAX-batched record imports with external-ID duplicate policies, explicit clearing, and controlled taxonomy terms.
- Per-row transactional writes, rejected-row reports, import history, protected temporary files, and automatic expiry cleanup.
- Filtered ZIP exports containing record files, taxonomy definitions, relationships, media associations, and a checksummed manifest.
- Accessible fallback templates for tourism singles, post-type archives, taxonomy archives, cards, pagination, and empty results.
- Theme overrides under `ads-tourism/` with specific/generic lookup and extension filters.
- Standard, Standard plus Custom Content, and Full Custom per-record layout behavior without structured-data loss.
- A builder-visible scalar metadata registry and optional Divi post-type compatibility hooks with system status.
- Responsive BEM-style fallback CSS with tourism-only loading, a disable setting, and bounded administrator custom CSS.
- Builder-agnostic record, gallery, relationship, itinerary, and provider shortcodes.
- Type-specific and mixed tourism listing shortcodes with bounded columns, page sizes, sorting, and pagination modes.
- Separately placeable search, filter, sort, results, and pagination components joined by strict explicit contexts.
- A versioned, read-only public query REST endpoint restricted to published tourism content and allowlisted filters.
- Progressive AJAX enhancement with debounced and cancellable search, URL state, Back/Forward restoration, load-more and infinite modes, live-region announcements, and no-JavaScript fallbacks.
- Generation-keyed query caching through the WordPress object cache and transients, with content and taxonomy invalidation.
- Native tourism JSON-LD, canonical, breadcrumb, robots, Open Graph, Twitter, Yoast SEO, and Rank Math integration with schema-graph deduplication.
- Resolved SEO inputs for fallback images, summaries, coordinates, price guidance, taxonomies, and language-aware relationships.
- A provider-neutral map contract, optional Google Maps browser-key adapter, and safe directions/no-output fallbacks.
- Single-record, multi-record, and AJAX context-synchronized map shortcodes with bounded markers and accessible status updates.
- WPML and Polylang adapters with configurable original-language fallback for related records and map markers.
- Complete dynamic-label internationalization and deterministic POT generation enforced by CI.
- Optional WooCommerce detection, reciprocal Package/Product mapping, and explicit create, link, synchronize, and detach actions.
- Catalogue, Enquiry, and WooCommerce mode resolution with safe non-transactional fallbacks.
- Builder-agnostic Add to Cart and Buy Now controls, configurable listing destinations, and linked Product-page tourism components.
- WooCommerce CRUD integration, Product/Package deletion cleanup, and HPOS compatibility declaration.
- Migration locking, failure recovery, and database-schema status reporting.
- Administrator integrity scans and safe repair actions for orphaned rows and stale commerce mappings.
- WordPress privacy-policy guidance and explicit preservation-first uninstall controls.
- A 1,000-record performance budget, accessibility regression coverage, and release acceptance checklists.
- Deterministic installable ZIP builds with SHA-256 checksums and file manifests.
