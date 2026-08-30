# ADR 0004: WordPress owns media files

- Status: Accepted
- Date: 2026-08-30

## Context

Tourism records require reusable galleries, external references, ordering, roles, credits, and record-specific presentation overrides. Duplicating upload or file-lifecycle behavior inside ADS Tourism would conflict with WordPress and make domain changes and multisite/CDN setups brittle.

## Decision

Keep uploads, files, attachment metadata, image editing, and responsive variants in the WordPress Media Library. Store only record-to-media associations and external/site-relative references in an indexed plugin table. Represent internal media by attachment ID. Never download external media and never delete an attachment when an association is detached.

## Consequences

- Attachment IDs remain stable when a domain changes.
- One attachment can be reused by multiple records.
- WordPress remains the authority for file deletion and generated sizes.
- ADS Tourism must remove stale association rows when a record or attachment is permanently deleted.
- External images cannot claim WordPress-generated responsive sizes.
