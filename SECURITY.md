# Security policy

## Supported versions

Security fixes are applied to the latest released minor version.

| Version | Supported |
| --- | --- |
| 1.0.x | Yes |
| Earlier development versions | No |

## Reporting a vulnerability

Please do not open a public issue for a suspected vulnerability. Use GitHub's private vulnerability reporting feature for this repository. Include the affected version, reproduction steps, impact, and any suggested mitigation.

The maintainers will acknowledge a complete report, investigate it, and coordinate disclosure after a fix is available.

## Implementation expectations

All contributions must apply WordPress capability checks and nonces to administrative writes; sanitize imported and submitted data; escape rendered values; use prepared queries when direct SQL is unavoidable; and avoid exposing API keys or private configuration to public responses.
