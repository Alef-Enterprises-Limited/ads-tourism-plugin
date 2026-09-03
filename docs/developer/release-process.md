# Release process

ADS Tourism uses Semantic Versioning. The same version must appear in the plugin header, `Plugin::VERSION`, `readme.txt`, the first released entry in `CHANGELOG.md`, and the Git tag.

## Candidate validation

From a clean checkout:

```bash
composer install
composer audit
composer check
php bin/verify-version.php
php bin/build-release.php
php bin/verify-release.php
```

The build produces:

```text
build/ads-tourism-X.Y.Z.zip
build/ads-tourism-X.Y.Z.zip.sha256
build/ads-tourism-X.Y.Z-manifest.json
```

The ZIP contains one top-level `ads-tourism/` directory and only allowlisted production files. File modification times are normalized so identical source content produces reproducible archive bytes. The manifest records every shipped file's size and SHA-256 digest.

## GitHub workflow

1. Merge an approved release PR after all required CI checks and the manual acceptance checklist pass.
2. Obtain explicit authorization to create the version tag and GitHub Release.
3. Create and push the annotated `vX.Y.Z` tag from the approved commit.
4. The release workflow verifies metadata, audits dependencies, runs the full suite, builds and verifies all artifacts, and creates a draft GitHub Release.
5. The workflow checks that the ZIP, checksum, and manifest are attached before publishing the release.
6. Install the attached ZIP on a clean WordPress site and record the result in the release checklist.

GitHub's repository source archive is not the installable plugin artifact. Production deployment is a separate operation and requires its own authorized deployment plan.
