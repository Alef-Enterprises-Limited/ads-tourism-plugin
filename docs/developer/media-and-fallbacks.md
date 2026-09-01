# Media and fallback architecture

## Ownership boundary

WordPress owns attachments and files. ADS Tourism owns associations in `{$wpdb->prefix}ads_tourism_media_links`. Replacing or detaching associations never calls a WordPress attachment-deletion API.

Each row contains exactly one source:

- `attachment_id` for a WordPress Media Library item; or
- `media_url` plus `url_type` for an HTTPS or site-relative reference.

The domain `MediaLink` enforces this exclusive-source invariant. `MediaLinkService` validates the tourism record, verifies attachment existence, removes duplicate sources, assigns deterministic ordering, and allows at most one primary association before making one transactional repository replacement.

Permanent tourism-record deletion removes its association rows. Permanent attachment deletion removes rows that reference that attachment. Neither cleanup path deletes another post or file.

## Fallback resolution

`Application\Fallback\FallbackResolver` implements the field order:

```text
record value
→ record override
→ content-type default
→ global default
→ null
```

It treats `null`, blank strings, whitespace-only strings, and empty arrays as missing while preserving meaningful `0` and `false` values.

`FeaturedMediaResolver` implements:

```text
native featured-image attachment
→ external featured-media reference
→ content-type default attachment
→ global default attachment
→ null
```

The services are wired to `ads_tourism_resolved_field` and `ads_tourism_featured_media` filters so future templates, shortcodes, REST presentation data, and builder modules use one resolution policy.

## URL stability

`PermalinkSettings` supplies registered post-type and taxonomy rewrite bases. Its domain validator rejects duplicates, reserved paths, and malformed slugs before settings are stored. Rewrite rules flush only on explicit option changes and lifecycle events.

Changed base aliases are stored in `ads_tourism_permalink_base_redirects`. Changed record slugs are stored in protected `_ads_tourism_previous_slugs` metadata. The 404 redirect adapter resolves either history source to a WordPress-generated current permalink and responds with HTTP 301.

Relations and media associations use integer IDs, so slug or base changes do not mutate those records.
