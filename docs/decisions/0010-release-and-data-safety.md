# ADR 0010: Release and data safety boundaries

## Status

Accepted for 1.0.

## Decision

ADS Tourism releases use a single allowlist-based builder that creates one WordPress-installable ZIP, a SHA-256 checksum, and a per-file manifest. Version metadata must agree before an authorized tag can publish a release.

Plugin data is preserved on deactivation and uninstall by default. Destructive uninstall requires an option plus an exact confirmation phrase saved before uninstall. Even then, shared Media Library attachments and WooCommerce Products remain outside the deletion boundary.

Database upgrades run under a recoverable lock on normal plugin load, record only non-sensitive failure metadata, and expose their state to administrators. Integrity repairs are restricted to orphaned plugin-owned rows and stale mappings; ambiguous duplicate identifiers remain a human decision.

## Consequences

- GitHub source archives are not supported installation artifacts.
- Releases cannot be published when the plugin header, constant, stable tag, changelog, or Git tag disagree.
- Administrators must deliberately opt into destructive cleanup and should export or back up first.
- Release publication does not authorize deployment to a production WordPress site.
