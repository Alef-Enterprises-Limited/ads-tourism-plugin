# Shortcodes and interactive listings

ADS Tourism shortcodes work in the WordPress Shortcode block, classic editor, Divi Code or Text modules, and standards-compliant builders that execute WordPress shortcodes.

## All-in-one listings

Use a type-specific shortcode when one results grid is enough:

```text
[ads_tourism_places per_page="12" columns="3"]
[ads_tourism_activities per_page="9" sort="newest"]
[ads_tourism_stays pagination="load_more"]
[ads_tourism_operators]
[ads_tourism_packages sort="price_asc"]
```

Use `ads_tourism_listing` for a mixed catalogue:

```text
[ads_tourism_listing type="activity,package" per_page="12" columns="4"]
```

Supported listing attributes are:

| Attribute | Values | Default |
| --- | --- | --- |
| `context` | Letters, numbers, hyphens, underscores; maximum 64 characters | Unique automatic context |
| `type` | `place`, `activity`, `stay`, `operator`, `package`, `all`, or a comma-separated list | Set by type-specific shortcode; otherwise `all` |
| `query` | Initial keyword, maximum 100 characters | Empty |
| `per_page` | `1` through `24` | `12` |
| `columns` | `1` through `6` | `3` |
| `sort` | `title_asc`, `title_desc`, `newest`, `oldest`, `manual`, `price_asc`, `price_desc`, `duration`, `random` | `title_asc` |
| `pagination` | `numbered`, `previous_next`, `load_more`, `infinite`, `none` | `numbered` |
| `class` | Space-separated CSS class names | Empty |

`infinite` progressively loads the next page when its link approaches the viewport. When JavaScript or `IntersectionObserver` is unavailable, the same control remains a normal Load more link.

## Composable controls

Separated components let a builder place controls and results in different rows or modules. Every cooperating component must use the same explicit `context`:

```text
[ads_tourism_search context="kokopo"]
[ads_tourism_filters context="kokopo" fields="area,activity_type,stay,price,duration"]
[ads_tourism_sort context="kokopo"]
[ads_tourism_results context="kokopo" type="activity,package" per_page="12" columns="3"]
[ads_tourism_pagination context="kokopo" type="activity,package" per_page="12"]
```

Available components are:

| Shortcode | Purpose |
| --- | --- |
| `ads_tourism_search` | Keyword search form |
| `ads_tourism_filters` | Selected taxonomy, relationship, price, and duration controls |
| `ads_tourism_sort` | Allowlisted sorting control |
| `ads_tourism_results` | The context's single primary results grid |
| `ads_tourism_pagination` | Numbered, previous/next, load-more, or infinite navigation |

The `fields` attribute on `ads_tourism_filters` accepts `area`, `place_type`, `activity_type`, `stay_type`, `package_type`, `amenity`, `traveller`, `accessibility`, `tag`, `place`, `activity`, `stay`, `operator`, `package`, `provider`, `price`, and `duration`. Unknown names are omitted. Taxonomy and relationship selectors are limited to 100 options so a public page cannot create an unbounded query.

One context can have only one `ads_tourism_results` or all-in-one listing. Multiple pagination controls are supported—for example above and below a grid. If a context is missing, contains unsafe characters, or owns two result grids, editors see a diagnostic; visitors receive no sensitive detail.

When pagination appears before results in the page source, give it the same `type`, `per_page`, and `pagination` attributes as the results component. The shared cache prevents the matching server query from becoming duplicate database work.

## Maps

`ads_tourism_map` renders the current record by default, accepts `id` or comma-separated `ids`, and can join an interactive listing through the same explicit `context`.

```text
[ads_tourism_results context="discover" type="place,stay" per_page="24"]
[ads_tourism_map context="discover" type="place,stay" per_page="24" height="480"]
```

The map follows AJAX filter, search, and sort updates for `discover`. See [SEO, maps, and languages](seo-maps-and-languages.md) for provider setup, every map attribute, safe fallbacks, and privacy guidance.

## Record components

Record components use the current tourism record unless an `id` is supplied:

```text
[ads_tourism_field field="summary" label="true"]
[ads_tourism_gallery]
[ads_tourism_related_places]
[ads_tourism_related_activities]
[ads_tourism_related_stays]
[ads_tourism_related_operators]
[ads_tourism_related_packages]
[ads_tourism_package_itinerary]
[ads_tourism_package_provider]
```

`ads_tourism_field` exposes only public scalar fields defined by the plugin schema. It applies the normal fallback resolver and omits its markup when no usable value exists. Galleries use WordPress Media Library associations and linked images, and also omit empty output safely. Related components return published linked records only.

The gallery accepts optional `limit` (`0` through `100`), `columns` (`1` through `6`), `role`, `order` (`manual`, `newest`, `oldest`, or `random`), WordPress image `size`, and Boolean `captions`, `credits`, and `lightbox` overrides. Empty attributes inherit the record's gallery settings. A `class` override is sanitized and added to the gallery section.

## URL state and no-JavaScript behavior

State is stored in context-specific query parameters such as `ads_kokopo_query`, `ads_kokopo_sort`, and `ads_kokopo_page`. This prevents one listing from consuming another listing's state. AJAX updates preserve the same URL, so bookmarks and browser Back/Forward navigation restore the selected results and visible controls.

Search and filter controls are ordinary GET forms, and pagination is made of normal links. Disabling JavaScript causes a full page navigation but does not remove core discovery behavior. AJAX errors keep the existing server-rendered results and announce a short error through the results live region.

## Styling

Shortcodes enqueue the same minimal `ads-tourism-` component stylesheet used by fallback templates, even when placed on a normal Page. Disable that stylesheet under **ADS Tourism → Settings → Frontend presentation** if the theme or builder owns all styling. Use the `class` attribute or stable `ads-tourism-` classes for scoped custom CSS.
