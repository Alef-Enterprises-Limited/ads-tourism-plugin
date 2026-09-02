# SEO, maps, and languages

ADS Tourism supplies focused tourism metadata, optional maps, and integration points for translated records. None of these features changes or removes the underlying tourism content.

## Search metadata

Under **ADS Tourism → Settings → Search and social metadata**, choose who owns tourism structured data:

- **Automatic** emits native schema when no supported SEO plugin is active. With Yoast SEO or Rank Math, ADS Tourism contributes one tourism entity to that plugin's graph and avoids a duplicate entity.
- **Always use ADS Tourism schema** emits the native tourism entity even when an SEO plugin is detected. Use this only when the other plugin's equivalent output has been disabled.
- **Disable ADS Tourism schema** leaves structured data entirely to the theme or another plugin.

When no supported SEO plugin is active, the native social option provides Open Graph and Twitter inputs from the record title, resolved summary, canonical URL, and resolved featured/default image. Empty values are omitted.

Public tourism post types and taxonomies participate in WordPress's normal sitemap, archive, permalink, title, and description behavior. Unverified tourism records and filtered utility URLs are marked `noindex`. Verify a record before expecting it to be indexable.

## Configure Google Maps

1. Create a Google Maps JavaScript API browser key in the Google Cloud project that owns billing.
2. Restrict the key to the website's HTTP referrers and only the required browser API.
3. Open **ADS Tourism → Settings → Maps**.
4. Select **Google Maps**, paste the key, and save.

The saved key is masked in the settings screen and is not autoloaded on every WordPress request. A browser key is visible to site visitors by design, so never enter a server-only secret. Google map assets load only on pages that render a map shortcode.

Disabling maps or removing the key does not remove coordinates. Without a configured provider, a map renders nothing unless `fallback="directions"` is set for a single marker.

## Map shortcode

Show the current tourism record:

```text
[ads_tourism_map]
```

Show one record, several records, all visible locations, or a safe directions fallback:

```text
[ads_tourism_map id="42" zoom="14" height="480"]
[ads_tourism_map ids="42,57,91" marker_limit="50"]
[ads_tourism_map id="42" locations="all"]
[ads_tourism_map id="42" fallback="directions"]
```

Join a map to a results context:

```text
[ads_tourism_results context="discover" type="place,stay" per_page="24"]
[ads_tourism_map context="discover" type="place,stay" per_page="24"]
```

Use the same `context`, `type`, query defaults, and page size on the map and results components. The initial server render uses those attributes; subsequent AJAX searches, filters, and sorting send the current result markers to the map automatically. Invalid or missing coordinates are omitted. Marker limits are bounded to 100.

Supported map attributes are `id`, `ids`, `context`, `type`, `query`, `per_page`, `sort`, `locations` (`primary` or `all`), `zoom`, `height`, `marker_limit`, `fallback`, and `class`. The default is one visible primary location per record. `locations="all"` displays every visible location point up to the marker limit, including its label in the information window.

## Multilingual integration

English is the source language. ADS Tourism does not translate editorial content automatically.

If WPML or Polylang is active, related records, map records, and record-component links resolve to the current-language equivalent when one exists. Under **ADS Tourism → Settings → Multilingual integration**, choose whether a missing translation should fall back to the original-language record or be omitted.

WordPress, WPML, or Polylang owns translated posts, terms, URLs, and language selection. Translators can create interface-language files from `languages/ads-tourism.pot` using their normal WordPress localization workflow.

## Privacy and failure behavior

Google Maps is a third-party service and may receive visitor network and browser information when its JavaScript loads. Disclose the provider in the site's privacy/cookie policy and apply any consent controls required for the audience. Turning maps off prevents ADS Tourism from requesting Google map scripts; listings and coordinates continue to work.
