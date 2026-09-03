# Relationships between tourism records

Relationships connect one tourism record directly to another. Use them for facts such as an activity being available at a place, a package including a stay, or an operator offering a package.

Use taxonomies for shared labels such as regions, amenities, activity types, and traveller types. Use relationships when editors need to connect specific records.

## Add a relationship

1. Edit a tourism record.
2. Find the **Tourism relationships** panel.
3. Start typing the title of the related record.
4. Select the correct result.
5. Set the order and primary item when those options are available.
6. Update the record.

ADS Tourism stores each relationship once and shows it from both directions. If an activity is connected to Kokopo, the activity can show Kokopo and Kokopo can list that activity. Editors do not need to create a second reverse relationship.

## Supported connections

- Activities can be available at Places or provided by Tour Operators.
- Places to Stay can be located at or near Places and managed by Tour Operators.
- Packages can cover Places and include Activities or Places to Stay.
- Packages can be offered by Tour Operators or Places to Stay.
- Activities can be shown near Places to Stay.

A record can have many relationships. Editors can order related items and select one primary item where the relationship supports it.

## Show related records

Fallback templates can show connected records automatically. Custom pages can use shortcodes such as:

```text
[ads_tourism_related_places]
[ads_tourism_related_activities]
[ads_tourism_related_stays]
[ads_tourism_related_operators]
[ads_tourism_related_packages]
```

The shortcode uses the current record unless an `id` is supplied. See the [shortcode guide](shortcodes.md) for the complete reference.

Permanent record deletion removes its relationship rows. Deleting a relationship does not delete either tourism record.
