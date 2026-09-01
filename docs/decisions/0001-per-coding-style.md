# ADR 0001: Use PHP-FIG PER Coding Style 3.0

- Status: Accepted
- Date: 2026-08-30

## Context

The architecture plan originally proposed WordPress Coding Standards. The implementation request explicitly requires PHP-FIG PER coding standards and emphasizes code that a human maintainer can understand.

## Decision

PHP code will follow PHP-FIG PER Coding Style 3.0. PHP CS Fixer enforces the `@PER-CS3x0` rule set plus strict types, ordered imports, removal of unused imports, and optimized native function calls in namespaced code.

WordPress security and API conventions still apply at WordPress boundaries. In particular, code must use capability checks, nonces, sanitization, late escaping, internationalization, and prepared database queries when applicable. Those behavioral requirements complement the formatting standard.

## Consequences

- Contributors have one automated formatting command: `composer format`.
- Continuous integration rejects formatting drift with `composer check:style`.
- Formatting may differ from WordPress Core style, but public hooks and integration behavior remain idiomatic WordPress.
- Readability takes priority when an optional automatic rule would obscure intent.
