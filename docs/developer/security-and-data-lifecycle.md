# Security and data lifecycle

## Trust boundaries

- Administrator mutations require the appropriate WordPress capability and a nonce.
- Public querying is read-only, limited to published tourism records, allowlisted filters, bounded page sizes, and generation-keyed caching.
- Registered record metadata distinguishes public fields from administrator-only verification fields.
- CSV imports validate types, external IDs, file size, mappings, duplicate policies, and taxonomy behavior before writes.
- Spreadsheet exports neutralize formula-leading cells.
- External media URLs are displayed as links; the plugin does not fetch untrusted remote images.
- Optional APIs are called only after their integrations report that they are available.
- API keys are stored through WordPress options, masked in settings, and never written to diagnostic reports.

## Migration lifecycle

The schema version is independent from the plugin version. `MigrationRunner` executes the idempotent table definitions under an expiring option lock on normal plugin load as well as activation. A successful run records the required schema version. A failure stores only the required version, exception class, and timestamp—never request data or credentials.

An incomplete migration appears in administrator notices and **ADS Tourism → System Status**. The recovery action requires `manage_options` and a nonce. Table-dependent behavior must not be considered healthy until System Status reports **Ready**.

## Uninstall contract

`uninstall.php` returns immediately unless the preservation-first option was explicitly enabled and confirmed. Destructive cleanup is limited to plugin-owned posts, exclusive taxonomy terms, tables, options, cache transients, scheduled transfer cleanup, and mapping metadata. Shared attachments and WooCommerce Products remain outside the deletion boundary.

## Integrity repairs

The integrity scanner is read-only until an administrator submits the confirmed repair action. Automatic repairs are intentionally limited to data that cannot point to a live record. Duplicate external IDs are reported but never guessed or rewritten.
