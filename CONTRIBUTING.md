# Contributing to ADS Tourism

## Local setup

ADS Tourism requires PHP 8.2 or newer and Composer 2.

```bash
composer install
composer check
```

To apply the repository's PHP-FIG PER Coding Style 3.0 rules:

```bash
composer format
```

Read the [developer documentation](docs/developer/README.md) before changing public behavior or extension points. The [testing guides](docs/testing/README.md) cover the manual checks used alongside the automated suite.

## Change workflow

1. Create a focused branch from `main`.
2. Make the smallest coherent change and add or update tests.
3. Run `composer check`.
4. Update documentation and `CHANGELOG.md` when behavior changes.
5. Open a pull request that explains the user-visible result and verification performed.

## WordPress boundaries

Treat all request data as untrusted. Sanitize on input, validate domain rules, and escape as late as possible on output. Administrative mutations must include both a capability check and nonce verification.

Optional integrations must fail safely. The core plugin must continue working when WooCommerce, a page builder, an SEO plugin, a multilingual plugin, or a map provider is unavailable.

## Releases

Version numbers must agree in `ads-tourism.php`, `src/Plugin.php`, and `readme.txt`. A tag in the form `vX.Y.Z` triggers the release workflow, which verifies the version, runs the quality suite, builds an installable ZIP, and publishes it as a GitHub release asset.

The complete process is documented in the [release guide](docs/developer/release-process.md).
