# Developer documentation

ADS Tourism keeps its core WordPress-native and places optional integrations behind guarded adapters.

## Start here

- [Architecture overview](architecture.md)
- [Extension points](extension-points.md)
- [Full architecture and development plan](../architecture-and-development-plan.md)
- [Architecture decisions](../decisions/)

## Main technical guides

| Guide | Subject |
| --- | --- |
| [Relationships](relationships.md) | Canonical many-to-many records and indexed storage |
| [Media and fallbacks](media-and-fallbacks.md) | WordPress-owned media, linked images, and fallback resolution |
| [CSV transfer](csv-transfer.md) | Schemas, validation, batching, imports, and exports |
| [Presentation](presentation.md) | Template loading, builder compatibility, layout modes, and CSS |
| [Public query API](public-query-api.md) | REST queries, shortcode contexts, filters, and caching |
| [SEO, maps, and multilingual](seo-maps-and-multilingual.md) | Optional integration contracts and adapters |
| [WooCommerce](woocommerce.md) | Package/Product ownership and guarded commerce behavior |
| [Security and data lifecycle](security-and-data-lifecycle.md) | Trust boundaries, migrations, uninstall, and integrity repairs |
| [Release process](release-process.md) | Versioning, build verification, tags, and release assets |

Development rules are in [CONTRIBUTING.md](../../CONTRIBUTING.md). Testing requirements are in the [testing index](../testing/README.md).

Return to the [documentation home](../README.md).
