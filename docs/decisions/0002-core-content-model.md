# ADR 0002: Model tourism records with WordPress content primitives

- Status: Accepted
- Date: 2026-08-30

## Context

ADS Tourism needs editable public pages, builder-specific templates, taxonomies, featured images, revisions, REST access, stable permalinks, and future compatibility with SEO, multilingual, import/export, and WooCommerce tools.

## Decision

The five public tourism records are WordPress custom post types:

- `ads_place` for Places to Go
- `ads_activity` for Things to Do
- `ads_stay` for Places to Stay
- `ads_operator` for Tour Operators
- `ads_package` for Packages

Places to Go are hierarchical and form the destination spine. The other records are non-hierarchical. Shared classification uses registered WordPress taxonomies. Structured fields will use registered post metadata, and relationships will be added through an indexed relationship store while preserving WordPress object IDs.

All public record types use normal single and archive routes, `show_in_rest`, featured images, excerpts, revisions, custom fields, and native statuses. Templates can therefore target a record type globally, while an editor can override an individual entry through builder-supported post content or a later explicit override mode.

## Consequences

- Divi and other builders can discover the record types through standard WordPress APIs.
- WordPress media, revisions, statuses, REST, taxonomies, and SEO integrations remain available.
- The plugin does not create one physical WordPress Page for every tourism entry; the content entry itself owns the generated route.
- Rewrite bases begin as code defaults and will become administrator-configurable before the public permalink contract is considered stable.
