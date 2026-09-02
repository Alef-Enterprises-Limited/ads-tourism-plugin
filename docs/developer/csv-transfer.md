# CSV transfer architecture

Phase 4 separates portable CSV rules from WordPress persistence.

## Schema and parsing

`Domain/ImportExport/CsvSchema` exposes schema `1.0` for each `ContentType`. Canonical field targets use registered post-meta keys. Taxonomy assignment columns use `taxonomy_{taxonomy_key}`. `CsvReader` detects common delimiters, requires unique headers, strips markup and null bytes, and reads logical batches without loading the whole file into memory.

`CsvRowValidator` validates external IDs, required titles, slugs, numeric and enumerated fields, dates, coordinates, email addresses, safe URLs, JSON structures, taxonomy slugs, featured-media source exclusivity, and URL-type consistency. `__CLEAR__` remains distinct from a blank update cell.

Extensions may filter future schema definitions through the planned CSV hook layer. Until that hook is introduced, add fields to `RecordFieldSchema`; the canonical CSV schema will expose editable registered fields automatically.

## Import boundary

The browser performs three authenticated AJAX operations:

1. upload and automatic mapping;
2. dry-run preview and import-run configuration;
3. repeated bounded batch writes.

All endpoints require the import nonce and `edit_others_posts`. Term creation additionally requires `manage_categories` and the administrator setting. Files must have a `.csv` extension, an allowed textual MIME type, valid UTF-8, and remain within the configured byte limit.

`WordPressTourismRecordImporter` resolves duplicates by `ads_tourism_external_id`. Every row is preflighted, then written inside a database transaction. Post core data, registered metadata, featured media, gallery associations, and controlled taxonomy assignments commit together or roll back together. New posts are always Draft. Verification defaults remain owned by `RecordFieldSchema` and the editorial workflow.

Relationship import remains deferred by design. External IDs and the normalized relationship export format preserve a stable future import boundary.

## Run history and temporary files

`ads_tourism_import_runs` stores user, record type, schema, state, policy, counts, timestamps, and protected file references. It does not store CSV content. Source uploads, rejected reports, and exports are randomized beneath `uploads/ads-tourism-transfers`, which receives direct-access denial files. A daily scheduled event removes expired files and run metadata. Deactivation unschedules this event; reactivation schedules it again.

## Export bundle

`CsvExportService` queries WordPress records using explicit filters and emits:

- one versioned CSV per selected record type;
- a dedicated `locations.csv` for repeatable GPS points, keyed by record type and external ID;
- normalized taxonomy term definitions;
- canonical relationship rows keyed by external IDs;
- normalized media rows;
- `manifest.json` with filters, counts, versions, timestamps, and SHA-256 checksums.

Cells beginning with `=`, `+`, `-`, or `@` after optional whitespace are neutralized before they reach spreadsheet software. ZIP creation uses `ZipArchive` when available and WordPress `PclZip` otherwise.

## Failure model

- A malformed upload is rejected before an import run starts.
- A malformed mapping cannot be configured.
- Invalid or failed rows are appended to a separate CSV report.
- Valid rows in the same batch continue.
- AJAX batch size is bounded between 5 and 100 rows.
- Uploaded files are never treated as executable content and remote image URLs are never fetched.
