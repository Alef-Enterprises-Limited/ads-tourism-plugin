# Public query and shortcode architecture

Phase 6 introduces an application-level query contract shared by server-rendered shortcodes and the progressively enhanced browser client.

## REST endpoint

`GET /wp-json/ads-tourism/v1/query` is public and read-only. It returns only published records rendered through the normal public card component.

| Parameter | Contract |
| --- | --- |
| `context` | Required validated context; maximum 64 characters |
| `type` | Allowlisted tourism type, `all`, or a comma-separated list |
| `query` | Plain-text keyword; maximum 100 characters |
| `page` | Positive integer |
| `per_page` | Integer from 1 through 24 |
| `sort` | One of the `QuerySort` values |
| `pagination` | One of the `PaginationMode` values |
| `columns` | Integer clamped from 1 through 6 |
| `taxonomies` | JSON object keyed only by registered tourism taxonomy names, containing safe term slugs |
| `relationships` | JSON object keyed by tourism record type, containing positive record IDs |
| price and duration bounds | Non-negative numbers or integers; a minimum cannot exceed its maximum |

A successful response contains `context`, `html`, `pagination_html`, `total`, `total_pages`, `page`, and normalized `state`. Invalid input returns `ads_tourism_invalid_query` with HTTP 400. Unknown post types, taxonomies, relationship types, sorting values, and pagination values are rejected rather than passed into `WP_Query`.

The controller deliberately accepts no caller-provided post status, metadata key, raw taxonomy query, raw SQL fragment, template path, or arbitrary renderer. The endpoint is safe without authentication because its output contract is equivalent to a public archive.

## Query execution and caching

`TourismQueryFactory` validates transport input and constructs an immutable `TourismQuery`. `WordPressQueryService` translates only that typed contract into `WP_Query`, requests IDs, and primes post, term, and metadata caches before rendering cards.

Results are stored in the `ads_tourism_queries` object-cache group and mirrored to a five-minute transient. Random ordering uses a one-minute lifetime. Keys include normalized query state, current locale, and an integer generation. Tourism record saves or deletions and taxonomy changes advance the generation, making all previous entries unreachable without an expensive wildcard delete. Normal transient expiry later removes stale rows.

## Shortcode contexts

`ShortcodeContextRegistry` is request-local. It enforces one primary `results` or `listing` component per context while permitting multiple controls and pagination components. It also shares the initial `QueryResult` with separated pagination shortcodes rendered later in the page.

All GET parameters follow `ads_{lowercase-context}_{property}`. Separated components require an explicit context. All-in-one listings obtain monotonically unique `listing-{n}` contexts unless configured explicitly.

The browser client groups nodes exclusively by `data-ads-tourism-context`. Each context owns its request cancellation, debounce timer, query configuration, URL namespace, and updates. A response never selects or replaces another context's results.

## Progressive enhancement and accessibility

Initial HTML, forms, and pagination links are complete before the script runs. The browser client intercepts supported interactions, writes namespaced state with the History API, aborts stale requests, and restores both controls and results on `popstate`. Search waits 300 milliseconds after the last input. Load-more appends cards; infinite mode observes the same accessible next-page link.

Results receive focus after a replacement, expose `aria-busy` during a request, and contain a polite atomic live region with the result count or request failure. Pagination is labelled navigation with current-page and relationship attributes. When JavaScript, `fetch`, or `IntersectionObserver` is unavailable, ordinary GET behavior remains available.

## Extension rules

Add a public filter only by extending the typed domain query, validating it in `TourismQueryFactory`, translating it in `WordPressQueryService`, adding a namespaced control state mapping, and documenting the REST field. Never forward arbitrary shortcode or request arrays into `WP_Query`.

Record-field output continues through the `ads_tourism_resolved_field` filter. Presentation extensions should decorate the existing card renderer or stable `ads-tourism-` classes rather than duplicating the query endpoint.
