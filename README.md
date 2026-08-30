# ADS Tourism

ADS Tourism is a WordPress-native foundation for tourism websites. It models destinations as the spine of a connected catalogue of things to do, places to stay, tour operators, and packages while leaving page composition to WordPress, Divi, or another builder.

> **Status:** early development. The plugin currently provides the core content model, structured record fields, canonical relationships, an editorial verification workflow, and project quality/release tooling. CSV transfer, front-end components, maps, multilingual adapters, and WooCommerce commerce adapters remain planned work.

## Core content model

| Record | WordPress key | Default URL base | Notes |
| --- | --- | --- | --- |
| Places to Go | `ads_place` | `/places/` | Hierarchical destination spine |
| Things to Do | `ads_activity` | `/things-to-do/` | Activities and experiences |
| Places to Stay | `ads_stay` | `/places-to-stay/` | Accommodation listings |
| Tour Operators | `ads_operator` | `/tour-operators/` | Provider profiles |
| Packages | `ads_package` | `/packages/` | Catalogued offers; commerce remains optional |

Every content type is public, revision-enabled, featured-image enabled, available through the REST API, and exposed to the normal WordPress template hierarchy. That gives Divi Theme Builder and other standards-compliant builders stable post-type conditions to target.

## Structured records and relationships

Each tourism record has a native **Tourism details** panel. Shared fields cover sources, verification, display overrides, external media references, and SEO overrides; type-specific fields cover coordinates, contact details, visitor information, accommodation pricing, and package itineraries.

The **Tourism relationships** panel connects records through one canonical relationship row. Editors can search, order, remove, and—where applicable—choose a primary related record. The same relationship can be queried from either side without duplicating data.

New records begin as unverified. By default, WordPress keeps an unverified record in review if an editor attempts to publish it. Administrators can change this policy under **ADS Tourism → Settings**. See [Record editing and workflow](docs/user/record-workflow.md) and [Relationship architecture](docs/developer/relationships.md).

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
