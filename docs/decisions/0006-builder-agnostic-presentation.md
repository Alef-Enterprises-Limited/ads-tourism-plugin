# ADR 0006: Builder-agnostic presentation

- Status: Accepted
- Date: 2026-09-01

## Context

Tourism records need usable pages without Divi, but ENBTA designers also need global Theme Builder layouts and one-off record overrides. Storing canonical tourism information inside builder layout data would make queries, imports, exports, integrations, and future builder changes unreliable.

## Decision

ADS Tourism will provide minimal fallback single, post-type archive, and taxonomy templates through the WordPress template hierarchy. Themes may override these files under `ads-tourism/`, and builders may take ownership through standard public post-type conditions.

Each record has Standard, Standard plus Custom Content, and Full Custom modes. These modes control output only and never delete structured data. Scalar structured metadata remains registered with WordPress and REST so standards-compliant builders can use it as dynamic content.

Divi support is an optional adapter made of capability detection, supported-post-type filters, status reporting, and documented smoke tests. The domain and normal frontend continue to work when Divi is absent.

## Consequences

- A new installation has complete, accessible pages before a designer creates templates.
- Designers can assign one global template per content type and more-specific record templates.
- Full Custom enables a bespoke page without sacrificing normalized data.
- The plugin must maintain stable markup classes and template hooks.
- Divi-specific modules remain deferred until public shortcode and query contracts are stable.
