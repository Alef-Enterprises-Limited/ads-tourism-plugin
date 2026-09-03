# Architecture overview

ADS Tourism is a WordPress-native plugin with a small layered structure.

## Source of truth

- WordPress posts store Places, Activities, Stays, Operators, and Packages.
- WordPress taxonomies store reusable classifications.
- WordPress post metadata stores structured scalar tourism fields.
- WordPress Media Library owns uploaded files and featured images.
- Plugin tables store record relationships, media associations, import runs, and repeatable locations.
- WooCommerce owns Product, price, tax, stock, cart, checkout, order, and payment data.

ADS Tourism does not copy data into a second standalone content system. Optional integrations can be removed without deleting the core tourism records.

## Code layers

| Directory | Responsibility |
| --- | --- |
| `src/Domain` | Stable tourism rules and value objects without WordPress behavior |
| `src/Application` | Use cases and interfaces that coordinate domain work |
| `src/Infrastructure/WordPress` | WordPress hooks, repositories, screens, templates, REST, and integrations |
| `templates` | Builder-agnostic fallback templates and components |
| `assets` | Administrator and public CSS/JavaScript |
| `tests` | Unit, contract, accessibility, and performance coverage |

## Public presentation

Tourism post types use the normal WordPress template hierarchy. A theme can override files under `ads-tourism/`; Divi and other builders can target the registered post types; shortcodes can place individual components on manually built pages.

Interactive listings use server-rendered HTML first. JavaScript progressively adds AJAX behavior. Search, filters, sorting, results, pagination, and maps share state only when they use the same explicit context.

## Optional integrations

SEO, maps, multilingual support, builders, and WooCommerce use guarded adapters. The plugin checks availability before calling an optional API. Core record pages continue to work when an integration is absent.

For the full specification, see the [architecture and development plan](../architecture-and-development-plan.md) and the [architecture decisions](../decisions/).
