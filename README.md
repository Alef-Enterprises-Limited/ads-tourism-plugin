[![Current version](https://img.shields.io/badge/version-1.2.0-0A66C2?style=flat-square)](https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/releases/tag/v1.2.0) [![License](https://img.shields.io/badge/license-Apache--2.0-2E8B57?style=flat-square)](LICENSE) [![Developer](https://img.shields.io/badge/developer-ALEF%20DIGITAL%20SOLUTIONS-6F42C1?style=flat-square)](https://github.com/Alef-Enterprises-Limited) [![WordPress](https://img.shields.io/badge/WordPress-6.6%2B-21759B?style=flat-square&logo=wordpress&logoColor=white)](https://wordpress.org/) [![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=flat-square&logo=php&logoColor=white)](https://www.php.net/) [![Continuous integration](https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/actions/workflows/ci.yml)

# ADS Tourism

ADS Tourism is a WordPress plugin for tourism websites. It helps you manage destinations, activities, accommodation, tour operators, and packages in one connected system.

It works with normal WordPress themes, Divi, and other page builders. Maps, multilingual plugins, SEO plugins, and WooCommerce are optional.

## What you can manage

| Content | Default URL |
| --- | --- |
| Places to Go | `/places-to-go/` |
| Things to Do | `/things-to-do/` |
| Places to Stay | `/places-to-stay/` |
| Tour Operators | `/tour-operators/` |
| Packages | `/packages/` |

## Main features

- Connect tourism records to each other.
- Organize records with WordPress tags and categories.
- Add featured images, galleries, fallbacks, and reusable Media Library images.
- Store one or more labelled GPS locations for each record.
- Review and verify information before publishing it.
- Import and export tourism data with CSV files.
- Build pages with WordPress templates, Divi, or shortcodes.
- Add AJAX search, filters, sorting, maps, and pagination.
- Control tourism slugs and URL bases.
- Add tourism SEO data without duplicating Yoast or Rank Math output.
- Link Packages to WooCommerce Products when online payment is needed.

ADS Tourism is not a booking engine. It does not manage room availability, reservations, or booking calendars.

## Install

Requirements:

- WordPress 6.6 or newer
- PHP 8.2 or newer

Download the [latest release ZIP](https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/releases/latest), upload it from **Plugins → Add New Plugin → Upload Plugin**, and activate **ADS Tourism**.

Then open **ADS Tourism → Help** in WordPress or follow the [five-minute setup guide](docs/user/getting-started.md).

## Quick shortcode example

Add a package grid to any page that supports WordPress shortcodes:

```text
[ads_tourism_packages per_page="12" columns="3"]
```

For separate search, filter, results, and pagination components, give every shortcode the same `context`. See the [shortcode guide](docs/user/shortcodes.md).

## Documentation

Start with the [documentation home](docs/README.md).

- [User guides](docs/user/README.md)
- [Developer documentation](docs/developer/README.md)
- [Testing and release checks](docs/testing/README.md)
- [Known limitations](docs/known-limitations.md)
- [Changelog](CHANGELOG.md)
- [Contributing](CONTRIBUTING.md)

## Development

```bash
composer install
composer check
```

The release ZIP does not need Composer. The repository uses Composer only for development tools and tests.

## Support and security

- Report a problem through [GitHub Issues](https://github.com/Alef-Enterprises-Limited/ads-tourism-plugin/issues).
- Report a security concern using the instructions in [SECURITY.md](SECURITY.md).

## License

ADS Tourism is licensed under the [Apache License 2.0](LICENSE).
