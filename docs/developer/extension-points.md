# Extension points

ADS Tourism provides WordPress actions and filters for themes, child plugins, and custom integrations. Treat arguments as public contracts, validate returned values, and keep optional dependencies guarded.

## Presentation actions

| Hook | Purpose |
| --- | --- |
| `ads_tourism_before_record` | Add output before a tourism record |
| `ads_tourism_after_record` | Add output after a tourism record |
| `ads_tourism_before_structured_content` | Add output before normalized fields |
| `ads_tourism_after_structured_content` | Add output after normalized fields |

## Template filters

| Hook | Purpose |
| --- | --- |
| `ads_tourism_template_is_builder_managed` | Tell ADS Tourism that a builder owns the selected template |
| `ads_tourism_template_candidates` | Change the ordered theme-template candidates |
| `ads_tourism_template_path` | Replace the final resolved template path |

## Data and SEO filters

| Hook | Purpose |
| --- | --- |
| `ads_tourism_resolved_field` | Replace a resolved public tourism field |
| `ads_tourism_featured_media` | Replace resolved featured-media data |
| `ads_tourism_seo_data` | Change normalized tourism SEO inputs |
| `ads_tourism_schema_data` | Change the mapped Schema.org entity |
| `ads_tourism_seo_schema_enabled` | Enable or disable native schema for one record |
| `ads_tourism_canonical_url` | Change the resolved canonical URL |
| `ads_tourism_record_context_id` | Resolve the tourism record used by a component |

## Integration filters

| Hook | Purpose |
| --- | --- |
| `ads_tourism_translation_adapter` | Replace the multilingual adapter |
| `ads_tourism_map_providers` | Register map-provider implementations |
| `ads_tourism_map_provider_labels` | Add provider labels to settings |

## Lifecycle actions

| Hook | Purpose |
| --- | --- |
| `ads_tourism_migrations_completed` | React to a successful schema update |
| `ads_tourism_migrations_failed` | React to a failed schema update without exposing the exception message |
| `ads_tourism_integrity_repaired` | React after safe integrity repairs complete |

Review the class that calls a hook before using it; that class defines argument order and expected return shape. The [developer guides](README.md) explain each subsystem in more detail.
