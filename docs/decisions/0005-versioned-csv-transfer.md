# ADR 0005: Versioned, transactional CSV transfer

- Status: Accepted
- Date: 2026-08-30

## Context

Tourism administrators need to preload and move substantial record sets without manually creating one WordPress post at a time. CSV data is untrusted, may be large, and must not bypass the same record, taxonomy, relationship, or media invariants used by the editor.

## Decision

Use a versioned canonical CSV schema with stable external IDs. Require upload validation, explicit mapping, a dry-run sample, a selected duplicate policy, and bounded AJAX batches. Apply each valid row transactionally through a WordPress adapter. Isolate rejected rows and retain only short-lived operational history.

Exports are normalized ZIP bundles containing records, taxonomy definitions, canonical relationships, media associations, and a checksummed manifest. Relationship import remains deferred, but exported external IDs preserve its future contract.

## Consequences

- Large imports avoid one long HTTP request.
- Re-import behavior is explicit and repeatable.
- A failed row cannot leave a partially updated tourism record.
- Spreadsheet formulas, markup, oversized files, invalid MIME types, and unsafe media URLs are contained at trust boundaries.
- Schema evolution must remain versioned and documented.
