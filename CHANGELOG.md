# Changelog

All notable changes to ADS Tourism will be documented in this file.

The project follows [Semantic Versioning](https://semver.org/) and the structure recommended by [Keep a Changelog](https://keepachangelog.com/).

## [Unreleased]

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
