# Record editing and workflow

ADS Tourism uses normal WordPress editing screens. Each place, activity, stay, operator, and package remains a native public post type, so the record can use the block editor, revisions, featured images, taxonomies, the REST API, and page-builder templates.

## Add record details

Open a tourism record and use the **Tourism details** panel. All records include source, verification, display, external-media, and SEO override fields. The rest of the fields match the record type—for example, places and stays include coordinates, while packages include pricing, participant limits, catalogue behavior, and a structured itinerary.

Optional values may be left empty. Empty optional values are removed from post metadata so later display components can omit them or apply configured defaults. Relative external-media paths such as `/wp-content/uploads/example.jpg` and absolute HTTPS URLs are accepted.

WordPress continues to own uploaded media. Use the normal Featured Image control for the record's primary image. The external-media field is only a reference and does not download or copy a file.

## Connect related records

Use the **Tourism relationships** panel to connect records.

1. Start typing a title in a relationship search box.
2. Select a matching record.
3. Move records up or down to set their display order.
4. Select a primary record where that relationship supports one.
5. Update or publish the WordPress record to save the selection.

The editor prevents duplicate selections. ADS Tourism stores one directional relationship and resolves the reverse view automatically. For example, connecting an activity to a place also makes that activity available when querying the place.

## Verification workflow

The editorial states are:

| Stage | WordPress status | Verification status |
| --- | --- | --- |
| Draft | Draft | Unverified or another non-verified state |
| In Review | Pending | Pending, needs update, or rejected |
| Verified | Pending | Verified |
| Published | Published | Verified |

By default, an unverified record cannot be published. If publication is attempted, WordPress keeps the record pending and shows an explanatory notice. Each verification-status change is recorded with its time, user, source, reference, and verification note. The latest verified change also updates **Last verified at** and **Verified by user ID**.

Administrators can disable the publication requirement at **ADS Tourism → Settings**. Disabling it does not remove verification history or statuses.

The tourism list screens show workflow stage, verification status, last verification time, and source. Use the dropdown above the list to filter by verification status.
