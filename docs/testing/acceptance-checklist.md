# ADS Tourism release acceptance checklist

Record the test site URL, WordPress/PHP/theme versions, commit, tester, and date. Use synthetic content and a disposable WooCommerce test configuration.

| # | Scenario | Result/evidence |
| ---: | --- | --- |
| 1 | Install and activate without Divi, maps, or WooCommerce. | |
| 2 | Create and verify Kokopo as a Place; publish and open its permalink. | |
| 3 | Create a Stay, Activity, Operator, and Package and relate each to Kokopo. | |
| 4 | Retrieve Kokopo's connected records through reverse relationship queries. | |
| 5 | Display a native featured image in listing and single templates. | |
| 6 | Remove the image and confirm configured fallback or clean omission. | |
| 7 | Render an ordered gallery containing Media Library images and an external link. | |
| 8 | Leave optional fields blank and confirm labels/sections are omitted. | |
| 9 | Change a record slug; confirm the old URL redirects and relationships remain. | |
| 10 | Import 100 Draft/Unverified records and download rejected rows for invalid input. | |
| 11 | Export a filtered record type and a complete normalized ZIP. | |
| 12 | Assign a Divi template to all Places and override one specific Place. | |
| 13 | Embed a Package listing on a manually built homepage. | |
| 14 | Coordinate separate search/filter/results/pagination shortcodes through one context. | |
| 15 | Add a second context and confirm complete state isolation. | |
| 16 | Show filtered markers with Google Maps, then disable maps without data loss. | |
| 17 | Verify canonical URLs and one non-duplicated structured-data entity. | |
| 18 | Link an accommodation Package to a Product, add to cart, and reach checkout. | |
| 19 | Disable WooCommerce and confirm Package content remains while cart controls disappear. | |
| 20 | Deactivate ADS Tourism and confirm all data remains. | |
| 21 | Upgrade/reinstall and confirm schema migration and data preservation. | |
| 22 | Build the tagged ZIP, verify checksum/manifest, and install it on a clean WordPress site. | |

Release blockers must be resolved or explicitly moved to a named later milestone. Do not record real customer details, API keys, or production exports as evidence.
