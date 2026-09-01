# ADR 0009: Keep Packages independent from WooCommerce Products

- Status: Accepted
- Date: 2026-09-01

## Context

Tourism Packages need rich editorial data and relationships even before online payment is available. WooCommerce already owns mature pricing, tax, stock, cart, checkout, order, refund, payment, customer, and HPOS behavior. Making a Product the tourism source of truth would break Package pages when WooCommerce is absent and duplicate existing commerce responsibilities.

## Decision

- Keep every Package as an `ads_package` record in all commerce modes.
- Add one optional reciprocal Package-to-Product mapping.
- Create, link, synchronize, and detach Products only through explicit editor actions.
- Copy only Product presentation references during synchronization; never copy WooCommerce price or order state into Package fields.
- Resolve invalid WooCommerce mode publicly to a safe non-transactional mode.
- Use WooCommerce CRUD APIs and declare HPOS compatibility without querying order storage.
- Treat accommodation offers as Packages and defer room inventory and booking availability.

## Consequences

Package URLs, builders, taxonomies, relationships, import/export, and media remain stable when WooCommerce is missing. Editors manage commerce values in WooCommerce and tourism values in ADS Tourism. The reciprocal mapping and explicit synchronization require validation and cleanup, but prevent silent Product creation and ambiguous ownership.
