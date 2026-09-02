# Presentation architecture

Phase 5 provides a usable frontend while keeping the domain model independent of themes and page builders.

## Runtime flow

`TemplateLoader` participates in WordPress's `single_template`, `archive_template`, and `taxonomy_template` filters. `TemplateCandidateResolver` produces deterministic child/active-theme override names. When no override exists, the loader selects the bundled template for that view.

Bundled templates are intentionally thin. They obtain `FrontendRenderer` through the `ads_tourism_frontend_renderer` filter and keep normal `get_header()`, Loop, pagination, and `get_footer()` behavior. The renderer owns safe field formatting, empty-state omission, resolved featured media, taxonomy links, relationships, and gallery output.

`LayoutMode` and `CustomContentPosition` interpret stored presentation metadata. Full Custom changes rendering only: the post's structured metadata remains untouched and available through queries, REST, exports, and later shortcodes.

## Override and extension hooks

| Hook | Type | Purpose |
| --- | --- | --- |
| `ads_tourism_template_is_builder_managed` | Filter | Return `true` when an integration owns the selected template. |
| `ads_tourism_template_candidates` | Filter | Reorder or add relative theme override candidates. |
| `ads_tourism_template_path` | Filter | Replace the final absolute template path. Invalid/unreadable results safely fall back. |
| `ads_tourism_frontend_renderer` | Filter | Supply or decorate the fallback renderer. |
| `ads_tourism_builder_meta_keys` | Filter | Read/extend the scalar metadata contract for a post type. |
| `ads_tourism_before_record` | Action | Run before one fallback record. |
| `ads_tourism_after_record` | Action | Run after one fallback record. |
| `ads_tourism_before_structured_content` | Action | Run before details, taxonomy, relations, and gallery output. |
| `ads_tourism_after_structured_content` | Action | Run after structured output. |

Template-path filters receive the view kind and object name. The final path is accepted only when it is a readable string; this prevents a broken integration from blanking the page.

## Builder metadata

`MetadataRegistrar` remains the canonical registration point. Every non-private structured field is registered for its post type with a REST schema and sanitization callback. `BuilderMetaRegistry` publishes the scalar subset: text, textarea, email, URL, date, datetime, integer, number, boolean, and select fields. Object/array fields and administrator-only editorial notes are excluded.

Do not duplicate structured values into builder layout JSON. New fields should be added to `RecordFieldSchema`; the metadata registrar and builder registry will then expose eligible fields consistently.

## Divi adapter

`Integration\Divi\DiviCompatibility` is defensive and dependency-free. It extends Divi's post-type filters only if Divi consumes them, detects the theme/plugin without calling unavailable APIs, and adds stable body classes. Core registration already makes post-type and taxonomy conditions available to Theme Builder.

Native Divi modules are intentionally deferred until shortcode and public query contracts are stable. The manual compatibility checklist lives in the user guide and must be run against the production Divi release before declaring it supported.

## Styling and security

`FrontendAssets` enqueues only on tourism singles, post-type archives, and taxonomy archives. The stylesheet uses `ads-tourism-` BEM-style classes and custom properties and does not impose fonts or brand colours.

Custom CSS is administrator-only because it is part of the existing `manage_options` settings page. The page exposes commented boilerplate editors for global CSS, all five content types, and listing/card/control/gallery/map/related widgets. `CustomCssSanitizer` removes tags, NUL bytes, style/script breakouts, imports, JavaScript URLs, legacy expression/behavior constructs, and bounds storage to 50 KB. Reset removes only saved ADS Tourism CSS and re-enables the current bundled fallback files; theme, child-theme, Divi, and other plugin CSS are not touched. Larger designs belong in a child theme or versioned builder stylesheet.
