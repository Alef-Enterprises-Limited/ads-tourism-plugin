# ADR 0008: Keep SEO, maps, and multilingual support behind optional adapters

- Status: Accepted
- Date: 2026-09-01

## Context

Tourism records need search metadata, location maps, and translated equivalents, but sites choose different SEO, map, and multilingual products. Making any one product mandatory would weaken WordPress interoperability and could make otherwise public content unavailable when an integration is disabled.

## Decision

- Keep canonical tourism data in WordPress posts, taxonomies, metadata, relationships, and Media Library references.
- Use a provider interface for map asset registration, marker normalization, rendering, attribution, and directions.
- Ship Google Maps as the first optional provider, using a browser-restricted key stored with autoload disabled.
- Keep structured-data ownership configurable and merge a single tourism entity into supported SEO-plugin graphs in automatic mode.
- Detect WPML and Polylang through guarded adapters and resolve translated relationship endpoints only at presentation time.
- Keep English as the source language, generate a deterministic POT file, and do not add automatic translation to core.

## Consequences

The plugin remains operational when every optional integration is absent. Additional providers and translation systems have stable extension points. Administrators must configure keys, schema ownership, privacy disclosures, translations, and fallback policy explicitly. Compatibility code and graph deduplication require focused regression tests as supported third-party plugins evolve.
