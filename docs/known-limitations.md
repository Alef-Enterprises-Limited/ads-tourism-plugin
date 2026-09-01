# Known limitations in 1.0

- ADS Tourism is not a booking engine. It does not manage room inventory, availability calendars, capacity, reservations, or booking modifications.
- Enquiry mode renders a configured contact or external enquiry action; it does not store enquiry submissions.
- Google Maps is the first map adapter. Other providers can use the documented provider interface but are not bundled.
- WPML and Polylang adapters focus on resolving related records. Translation authoring remains the responsibility of the selected multilingual plugin.
- CSV imports create and update tourism records, taxonomy values, and media links; relationship linkage is managed in WordPress after import unless supplied through the normalized complete export format.
- Integrity repair does not automatically resolve duplicate external IDs because selecting the canonical identifier requires editorial judgment.
- WooCommerce Product synchronization copies selected Package presentation data. WooCommerce remains the source of truth for price, tax, stock, cart, checkout, orders, and payment.
- The release workflow produces and verifies installable artifacts but does not deploy them to a production WordPress site.
