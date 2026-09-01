# Maintenance, privacy, and uninstall

## Integrity checks

Administrators can open **ADS Tourism → Maintenance** to scan for:

- relationship rows whose source or target record no longer exists;
- media links whose tourism record or Media Library attachment no longer exists;
- Package/Product mappings whose linked record no longer exists; and
- duplicate external IDs.

**Repair safe issues** removes only orphaned plugin-table rows and stale mapping metadata. It never deletes tourism records, Media Library attachments, or WooCommerce Products. Duplicate external IDs require a human decision and are deliberately report-only.

## Privacy

ADS Tourism adds suggested wording to **Settings → Privacy**. The plugin does not store customer orders or payments. WooCommerce owns that information when it is active.

Verification notes and import diagnostics are administrator data and are not exposed through the public tourism REST endpoint. Temporary CSV files and import diagnostics use the retention period configured for transfers.

## Deactivation and uninstall

Deactivation preserves all records, taxonomies, relationships, media links, options, and Package/Product mappings.

Uninstall also preserves data by default. To request destructive cleanup, an administrator must first open **ADS Tourism → Settings**, select the uninstall deletion option, type `DELETE ADS TOURISM DATA` exactly, and save.

When that confirmed option is active, uninstall removes ADS Tourism posts, plugin-exclusive taxonomy terms, plugin tables, plugin options, temporary transients, and reciprocal WooCommerce mapping metadata. It does not delete shared Media Library attachments or WooCommerce Products. Export and back up the site before enabling this option.
