# CSV import and export

ADS Tourism uses CSV for bulk record entry while keeping WordPress as the source of truth. Open **ADS Tourism → CSV Import/Export**. You need permission to edit other authors' posts.

## Prepare a file

1. Download the template for Places to Go, Things to Do, Places to Stay, Tour Operators, or Packages.
2. Keep the file as UTF-8 CSV.
3. Enter one record per row.
4. Give every row a stable, unique `external_id`.
5. Keep `title` filled.

The current schema version is `1.0`. Comma, semicolon, tab, and pipe delimiters are detected automatically. Because pipe (`|`) separates taxonomy slugs and gallery URLs inside a cell, comma-separated files are recommended for advanced imports.

Blank cells mean “leave the existing value unchanged” when updating a record. Enter `__CLEAR__` to remove an existing value. New records always begin as **Draft** and **Unverified**, regardless of spreadsheet content.

## Import safely

1. Choose the record type and upload the CSV.
2. Review the detected delimiter and row count.
3. Map each source column to one tourism field. `external_id` and `title` are required and each target may be selected once.
4. Choose what happens when an external ID already exists:
   - **Skip** leaves the existing record unchanged.
   - **Update** changes mapped, non-blank values.
   - **Create new** generates a unique derivative external ID.
5. Choose **Simple** for fields only or **Advanced** for taxonomy columns.
6. Validate and inspect the dry-run sample.
7. Start the import. The browser processes small AJAX batches and reports cumulative counts.

Advanced taxonomy cells contain stable slugs separated by `|`, for example `diving|history`. Unknown terms are rejected by default. An administrator may enable controlled term creation in Transfer settings; the dry-run then shows the name that will be created from each unknown slug.

Terms can also be created manually from **ADS Tourism → Tags & Categories**. Open the relevant taxonomy management link, add the display name and exact CSV slug, then return to the import preview. Place Types, Activity Types, Stay Types, Package Types, Amenities, Traveller Types, Accessibility Features, Tourism Tags, and Geographic Areas each have their own management screen.

Every ADS Tourism taxonomy term also has an optional **Color (RGB hex)** field with a color picker. Leave it blank to keep the default no-color state; clear an existing value to remove its color.

Invalid rows do not stop valid rows. Download the rejected-row report from Recent Imports, fix the errors, and import those rows again. Relationship import is intentionally deferred; connect records in their native Tourism relationships panels.

## Images and media

- `featured_attachment_id` selects an existing WordPress image attachment.
- `featured_media_url` accepts HTTPS or a site-relative path.
- `featured_media_url_type` must be `absolute` or `relative` and must match the URL.
- `gallery_urls` accepts HTTPS or site-relative URLs separated by `|`.

The importer never downloads remote images. Updating a gallery detaches replaced associations but never deletes Media Library attachments.

## Export

The export form can select all records, one record type, a WordPress status, a verification status, a modified-date range, or comma-separated post IDs. It downloads a ZIP containing relevant record CSV files plus:

- `taxonomies.csv` — term definitions and hierarchy;
- `relationships.csv` — stable external IDs, relation keys, primary flags, order, and metadata;
- `media.csv` — normalized attachment and linked-media associations;
- `manifest.json` — schema/plugin versions, timestamp, filters, counts, and SHA-256 file checksums.

Values beginning with spreadsheet formula characters are prefixed with an apostrophe on export. Remove that protective apostrophe only if you are certain the cell is ordinary data.

## Limits and cleanup

Administrators can set the maximum upload size, AJAX batch size, temporary-file retention, and whether term creation may be offered. Uploaded CSVs and generated reports live in a protected uploads subdirectory and are removed automatically after the retention period. Import history retains only operational metadata until the same expiry cleanup.
