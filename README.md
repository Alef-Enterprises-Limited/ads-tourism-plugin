# ADS Tourism

ADS Tourism is a WordPress-native foundation for tourism websites. It models destinations as the spine of a connected catalogue of things to do, places to stay, tour operators, and packages while leaving page composition to WordPress, Divi, or another builder.

> **Status:** early foundation development. The current code registers the core content types, shared taxonomies, administration shell, and project quality/release tooling. Relationships, structured fields, CSV transfer, front-end components, maps, and WooCommerce commerce adapters remain planned work.

## Core content model

| Record | WordPress key | Default URL base | Notes |
| --- | --- | --- | --- |
| Places to Go | `ads_place` | `/places/` | Hierarchical destination spine |
| Things to Do | `ads_activity` | `/things-to-do/` | Activities and experiences |
| Places to Stay | `ads_stay` | `/places-to-stay/` | Accommodation listings |
| Tour Operators | `ads_operator` | `/tour-operators/` | Provider profiles |
| Packages | `ads_package` | `/packages/` | Catalogued offers; commerce remains optional |

Every content type is public, revision-enabled, featured-image enabled, available through the REST API, and exposed to the normal WordPress template hierarchy. That gives Divi Theme Builder and other standards-compliant builders stable post-type conditions to target.

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
- `src/Infrastructure/WordPress` adapts those concepts to WordPress hooks and APIs.
- `tests` contains fast unit tests and static-analysis stubs.
- `docs/decisions` records architectural decisions.
- `docs/architecture-and-development-plan.md` is the complete implementation plan.

See [CONTRIBUTING.md](CONTRIBUTING.md) for development rules and [CHANGELOG.md](CHANGELOG.md) for delivered behavior.

## Builds and releases

Pull requests and pushes run formatting, static analysis, and unit tests against supported PHP versions. Successful checks also produce an installable ZIP as a workflow artifact. Pushing a SemVer tag such as `v0.1.0` verifies version consistency and publishes the ZIP to a GitHub release.

## License

ADS Tourism is licensed under the Apache License 2.0. See [LICENSE](LICENSE).
