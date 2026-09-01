# ADR 0007: Context-isolated shortcode components

- Status: Accepted
- Date: 2026-09-01

## Context

Designers need both compact all-in-one listings and independently placeable search, filter, sort, results, and pagination controls. A page may contain several tourism experiences—for example a destination grid and a package grid—and controls for one experience must never mutate another. The first page must also remain usable to visitors, crawlers, and assistive technology when JavaScript is unavailable.

## Decision

ADS Tourism will identify every interactive listing with a validated context containing only letters, numbers, hyphens, and underscores. Separated components require an explicit context; every component that shares that exact value participates in the same state. All-in-one listing shortcodes generate a unique context unless the author supplies one.

Each context may contain one primary results component and any number of search, filter, sort, or pagination controls. URL parameters are namespaced as `ads_{context}_{property}`. Invalid contexts and duplicate primary components fail safely, with an editor-visible diagnostic and a non-disclosing public comment.

WordPress renders the initial results and ordinary GET forms and pagination links. A small public script progressively enhances the same markup through the versioned read-only query endpoint. Browser history remains the state authority, while requests are debounced, cancellable, bounded, and isolated by context.

## Consequences

- Multiple independent listing experiences can coexist on one page.
- Theme builders can place controls anywhere without relying on DOM proximity or implicit global state.
- Search, filters, sorting, and pagination remain functional without JavaScript.
- URLs are bookmarkable, crawlable, and restorable through Back and Forward.
- Authors must use identical explicit context values for separated components.
- Context and query contracts become public APIs that require backward-compatible evolution.
