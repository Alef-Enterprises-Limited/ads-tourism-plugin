# Testing and release checks

Use these documents before merging or publishing a release.

- [Release acceptance checklist](acceptance-checklist.md) covers installation, records, relationships, media, CSV, builders, shortcodes, maps, SEO, WooCommerce, upgrades, and uninstall behavior.
- [Accessibility and builder checks](accessibility-and-builders.md) covers keyboard use, responsive layouts, Divi, and shortcode context isolation.
- [Performance budgets](performance.md) covers listing, REST, map, cache, and CSV limits.
- [Release process](../developer/release-process.md) explains version checks, ZIP creation, checksums, manifests, tags, and GitHub Releases.

Run the automated suite before starting manual checks:

```bash
composer install
composer audit
composer check
```

Return to the [documentation home](../README.md).
