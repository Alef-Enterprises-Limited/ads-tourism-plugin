# Relationship architecture

ADS Tourism stores cross-record associations in the indexed table `{$wpdb->prefix}ads_tourism_relations`. Taxonomies remain the correct tool for shared classification; the relationship table represents explicit record-to-record links with ordering, primary selection, and future relationship metadata.

## Invariants

- A relationship is stored once in its canonical direction.
- `(source_post_id, target_post_id, relation_key)` is unique.
- Reverse reads query the same row through `target_post_id`; they do not create mirrored rows.
- Source and target record types are validated before existing rows are replaced.
- Only supported relationships may mark one selected record as primary.
- Permanent deletion removes rows referencing the deleted post, not either surviving post.
- Trashing a post does not destroy its relationship rows.

## Canonical keys

| Key | Source | Target |
| --- | --- | --- |
| `activity_available_at_place` | Activity | Place |
| `stay_located_at_place` | Stay | Place |
| `stay_near_place` | Stay | Place |
| `operator_serves_place` | Operator | Place |
| `activity_provided_by_operator` | Activity | Operator |
| `stay_managed_by_operator` | Stay | Operator |
| `package_covers_place` | Package | Place |
| `package_includes_activity` | Package | Activity |
| `package_includes_stay` | Package | Stay |
| `package_offered_by` | Package | Operator or Stay |
| `package_partner_provider` | Package | Operator or Stay |
| `activity_near_stay` | Activity | Stay |

## Layers

`Domain\Relationship\RelationType` defines valid directions and record types. `Application\Relationship\RelationshipService` validates a complete replacement before calling the repository. `Infrastructure\WordPress\Relationship\WpdbRelationshipRepository` owns SQL and transactions. Admin UI and AJAX search depend on the application and domain contracts rather than constructing SQL.

The main query API is:

```php
$relationships = $relationshipService->find($postId, RelationType::PACKAGE_COVERS_PLACE);
$relatedPostIds = $relationshipService->relatedPostIds(
    $postId,
    RelationType::PACKAGE_COVERS_PLACE,
);
```

Both methods determine whether the supplied record is on the source or target side, so consumers use the same call for forward and reverse reads.

## Table lifecycle

The schema migration runs on activation and on `plugins_loaded` when the stored schema version is behind the plugin schema version. A short-lived migration lock limits concurrent attempts. `dbDelta()` creates or updates the table and its unique and lookup indexes.

`before_delete_post` removes associations for ADS Tourism records. The repository also exposes `deleteOrphans()` for maintenance tooling that may be added later.
