# Templates and page builders

ADS Tourism always stores tourism information in normal WordPress posts, taxonomies, metadata, featured images, and Media Library attachments. A page builder controls presentation; it does not become the source of truth for structured tourism data.

## Layout modes

Choose a mode in the **Tourism details** panel for each record:

| Mode | Structured plugin output | Editor or builder content |
| --- | --- | --- |
| Standard template | Yes | No |
| Standard template with custom content | Yes | Before, after, or in the template slot |
| Fully custom content | Hidden on that page | Used as the complete record body |

Changing modes never deletes structured fields, relationships, taxonomy terms, gallery links, or featured media. Switching back to a standard mode makes the stored information visible again.

Use **Fully custom content** for a one-off landing page built in Divi or the block editor. Use **Standard template with custom content** when the shared record layout is correct but the page needs an additional introduction, promotion, or editorial section.

## Divi Theme Builder

All five tourism record types are public, REST-enabled WordPress post types. Divi can therefore assign templates to:

- all Places to Go, Things to Do, Places to Stay, Tour Operators, or Packages;
- an individual tourism record;
- each tourism post-type archive; and
- public tourism taxonomy archives.

To create a shared Place layout:

1. Open **Divi → Theme Builder**.
2. Add a template and assign it to **All Places to Go**.
3. Build the body with Divi's standard Post Title, Post Content, and Featured Image dynamic content.
4. Save the Theme Builder changes.

To override one Place, add another Theme Builder template and choose that specific Place in the assignment conditions. Divi's more-specific condition takes precedence over the global Place template.

ADS Tourism adds all tourism post types to Divi's supported post-type filters. Check **ADS Tourism → System Status** to confirm detection and integration status. If a type does not appear after changing themes or updating Divi, clear Divi's static CSS/cache and resave **Settings → Permalinks**.

Structured scalar fields are registered WordPress post meta and exposed through REST. Builders that support WordPress custom-field dynamic content can use keys such as `ads_tourism_summary`, `ads_tourism_latitude`, `ads_tourism_phone`, or the type-specific price and duration keys. Complex internal arrays and administrator-only notes are intentionally not part of the builder contract.

## Theme template overrides

Without a builder assignment, ADS Tourism supplies accessible fallback pages. A theme can override them by creating files under:

```text
your-theme/ads-tourism/
```

The lookup order is:

1. builder-managed template;
2. a native WordPress object-specific theme template, such as `single-ads_place.php` in the theme root;
3. object-specific ADS Tourism theme override, such as `ads-tourism/single-ads_place.php`;
4. generic ADS Tourism theme override, such as `ads-tourism/single.php`;
5. bundled ADS Tourism fallback;
6. the template WordPress originally selected if a filtered path is invalid.

The available override names are:

| View | Specific example | Generic override |
| --- | --- | --- |
| Single record | `ads-tourism/single-ads_place.php` | `ads-tourism/single.php` |
| Record archive | `ads-tourism/archive-ads_place.php` | `ads-tourism/archive.php` |
| Taxonomy archive | `ads-tourism/taxonomy-ads_geo_area.php` | `ads-tourism/taxonomy.php` |

Keep override files in a child theme so a parent-theme update cannot replace them.

## Styling

The fallback stylesheet supplies only responsive grids, spacing, media sizing, and basic accessible structure. It does not set a font or brand palette.

Under **ADS Tourism → Settings → Frontend presentation**, administrators can:

- disable the bundled stylesheet when a theme or builder owns all CSS; and
- add up to 50 KB of optional custom CSS.

Custom CSS loads only on tourism singles, archives, and taxonomy pages. Unsafe style/script constructs are removed when the setting is saved. Prefer a child-theme stylesheet for large or version-controlled designs.

## Divi smoke test

After a supported Divi update, verify:

1. each tourism type appears in Divi Post Type Integration;
2. **All Places to Go** can receive a Theme Builder template;
3. one specific Place can receive a more-specific template;
4. Post Title, Post Content, and Featured Image render dynamically;
5. a tourism archive and geographic-area archive can receive templates;
6. Standard, Standard plus Custom Content, and Full Custom modes render as expected;
7. disabling ADS Tourism CSS does not remove data or page-builder styling; and
8. disabling Divi leaves the bundled plugin pages working.
