# Repository guidance

## Scope

This repository contains the ADS Tourism WordPress plugin. Keep the plugin WordPress-native, builder-agnostic, and compatible with optional integrations rather than making them hard dependencies.

## Coding style

- Follow PHP-FIG PER Coding Style 3.0.
- Run `composer check` before submitting a change.
- Prefer clear names and small, cohesive classes over clever abstractions.
- Add strict types to PHP source and test files.
- Escape output, sanitize input, check capabilities, and verify nonces at WordPress trust boundaries.
- Never call optional WooCommerce, multilingual, SEO, or map APIs without checking that the integration is available.

## Architecture

- Keep business concepts in `src/Domain` and WordPress adapters in `src/Infrastructure/WordPress`.
- Register public record types with `show_in_rest` enabled so they remain visible to Divi and other builders.
- Use WordPress posts, taxonomies, metadata, media attachments, revisions, and statuses wherever they fit.
- Preserve plugin data during uninstall unless the site administrator explicitly opts into deletion.
- Treat `docs/architecture-and-development-plan.md` and accepted ADRs as the implementation contract.

## Changes

- Include tests for domain behavior and regression-prone WordPress behavior.
- Keep README, changelog, and user/developer documentation aligned with implemented behavior.
- Do not claim that a roadmap feature is available until its code and tests are included.
