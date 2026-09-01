# Media and permalink administration

## Featured images and galleries

Use the normal WordPress **Featured Image** control for a record's primary uploaded image. WordPress owns the upload, responsive sizes, image editing, and attachment metadata.

Use **Tourism gallery** to associate additional media with a place, activity, stay, operator, or package:

1. Choose one or more images from the WordPress Media Library, or add an HTTPS URL/site-relative path.
2. Select a role such as hero, gallery, room, facility, map, itinerary, or operator logo.
3. Add optional title, alternative text, caption, credit, and rights overrides.
4. Move items up or down to define manual order.
5. Optionally mark one association as primary.
6. Update the record.

**Detach** removes only the association. It never deletes the attachment from the Media Library, and another tourism record may reuse the same attachment. Deleting an attachment through WordPress removes stale associations to that attachment.

Linked media rules:

- Relative paths must begin with one `/`, such as `/wp-content/uploads/example.jpg`.
- Absolute links must use HTTPS.
- ADS Tourism does not download or sideload linked files.
- Invalid or missing sources are omitted rather than rendered as broken images.

Each record also has gallery display defaults in **Tourism details**, including maximum images, columns, order, role filter, WordPress image size, captions, credits, lightbox behavior, featured-image inclusion, and pagination mode. Front-end components added in later phases will use these defaults and allow local overrides.

## Default images

Open **ADS Tourism → Settings → Default images** to select:

- one global tourism fallback image; and
- one fallback for each tourism record type.

Resolution order is:

1. native record featured image;
2. record external featured-media link;
3. record-type default image;
4. global default image;
5. no image and no image container.

Fallback selections do not alter the record's Featured Image field.

## Permalinks

Open **ADS Tourism → Settings → Permalinks** to edit the URL base for every tourism post type and taxonomy. The screen previews an example URL for each base.

ADS Tourism rejects empty, malformed, duplicate, and reserved WordPress bases. Rewrite rules are flushed only after an explicit settings change or plugin activation/deactivation.

When a valid base changes, the previous base is retained for a permanent redirect. Tourism record slug changes are also tracked, and old published URLs redirect to the current WordPress permalink. Relationships and media links continue working because they use post IDs rather than URLs.
