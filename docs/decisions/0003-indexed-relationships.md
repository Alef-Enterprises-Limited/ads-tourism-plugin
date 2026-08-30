# ADR 0003: Store record relationships in one indexed table

- Status: Accepted
- Date: 2026-08-30

## Context

Tourism records have many-to-many connections, reverse discovery requirements, explicit ordering, and occasional primary-provider semantics. Duplicated post metadata would make reverse queries expensive and could allow the two directions to disagree. Taxonomies express classification, not an ordered association between two specific records.

## Decision

Store each supported association once in `{$wpdb->prefix}ads_tourism_relations`, using a canonical source type, target type, and relation key. Enforce uniqueness at the database level and query the same row from either direction. Keep validation in the application/domain layers and SQL in a WordPress repository adapter.

## Consequences

- Forward and reverse reads remain consistent.
- Indexed filters avoid scanning serialized post metadata.
- Relationship ordering, primary selection, and metadata have explicit columns.
- Schema changes require versioned migrations.
- Permanent deletion must clean association rows because WordPress does not own the custom table.
