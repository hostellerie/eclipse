## Upgrade compatibility note

Sites running **Eclipse 1.1.0** can upgrade directly to the current 1.2.x archive. The 1.1.0 updater uses the Geeklog-compatible `getStream()` ZIP API.

Sites that already installed an **early Eclipse 1.2.0** package on **Geeklog 2.1.x** may fail before extraction with `Call to undefined method ZipArchive::getFromName()`. Because that failure occurs inside the already-installed updater, a newer archive cannot repair it before extraction. Apply the one-time compatibility hotfix by replacing `layout/eclipse/includes/zip-compat.php` with the current repository version, then retry the normal Theme Studio update. See `docs/HOTFIX-ECLIPSE-1.2.0-GEEKLOG-2.1.1.md`.

Current packages prevent recurrence by detecting `getFromName()` before use and falling back to `getStream()`, while retaining atomic theme-directory replacement.

# Eclipse 1.2.0

Eclipse 1.2.0 advances the theme from a modern Denim child theme into a more interoperable Geeklog administration and presentation layer while preserving the Geeklog 2.1.1 to 2.2.2 transition range and PHP 5.6 parsing baseline.

## Highlights

- Generic capability-driven administration dashboard integration through plugin-declared `dashboard.summary` services.
- Structured provider metrics, management links and operational alerts from modernized plugins such as Documents and Videos.
- Provider `pending` and `drafts` metrics can surface in **Needs attention** without Eclipse querying plugin-private tables.
- Legacy Geeklog plugin statistics remain available as a fallback and are suppressed for a provider when structured dashboard metrics are present, avoiding duplicate presentation.
- Menu plugin integration now supports independently configured primary, secondary, footer and sidebar navigation slots.
- Secondary public navigation is excluded from administration pages while the primary menu remains available for Classic Eclipse administration.
- Continued Modern workspace and Classic Eclipse administration modes.
- Safer Theme Studio update handling, including Geeklog 2.1.x ZIP compatibility and exact theme-directory replacement.
- Versioned installable archive remains compatible with both first installation and Theme Studio updates.
- Repository-level roadmap and automated release/package validation refined for the 1.2 cycle.

## Dashboard interoperability

Eclipse remains a presentation consumer. It does not query Documents, Videos or other plugin-private tables.

For active plugins that declare:

```text
dashboard.summary
```

Eclipse invokes the provider-owned `dashboard_summary` service and consumes bounded `metrics`, `alerts`, `links`, `status` and `updated` data. Invalid, unauthorized or unavailable providers are ignored without breaking the complete dashboard.

Current reference providers include Documents 1.2 and Videos 0.20. Other plugins can integrate with the same dashboard without Eclipse-specific SQL or provider adapters by implementing the shared capability contract documented in the Geeklog memorandum.

## Menu integration

Eclipse keeps Menu plugin data and permissions provider-owned while rendering the navigation presentation.

The theme supports independent primary, secondary, footer and sidebar slots. The public secondary menu is not rendered on administration pages. Classic Eclipse retains the primary navigation behavior expected by the traditional administration shell.

## Update compatibility

The official archive remains:

`eclipse-1.2.0.zip`

The package preserves the historical `eclipse/...` archive layout, contains the theme-package marker and plugin-upload guard, and is validated against the committed integrity manifest.

The Theme Studio updater preserves the existing safety backup and rollback workflow. Current packages also detect whether the installed ZIP API provides `getFromName()` and fall back to the older `getStream()` path required by some Geeklog 2.1.x environments.

See the compatibility note above for sites that installed an early 1.2.0 package before that compatibility fix.

## Compatibility

Eclipse 1.2.0 declares:

- Geeklog 2.1.1 or later;
- PHP 5.6.0 or later as the parsing compatibility baseline;
- Denim installed as the parent compatibility theme;
- `ZipArchive` only when Theme Studio's local update installer is used.

Production sites should use a currently maintained PHP version supported by the selected Geeklog release and hosting environment.

## Release validation

The release branch automatically:

- lints source and packaged PHP files;
- runs the Menu navigation contract test;
- rebuilds and verifies `eclipse/MANIFEST.json`;
- constructs the runtime-only package;
- verifies that every archive entry remains under `eclipse/`;
- checks the theme package marker and plugin-upload guard;
- enforces the release archive size budget.

Before creating the stable `v1.2.0` tag, complete the manual smoke-test matrix on Geeklog 2.1.1 and Geeklog 2.2.2, including public navigation, Modern and Classic administration, Theme Studio update/rollback, Documents/Videos dashboard integration and the supported PHP combinations.

## Installation and update

Use the versioned `eclipse-1.2.0.zip` asset published with the GitHub release. Do not use GitHub's automatic source archive as the install package.

Before updating:

1. Back up the site files and database.
2. Keep the Denim theme installed.
3. Confirm the server meets the compatibility requirements.
4. Install the release ZIP manually or through Theme Studio.
5. Clear the Geeklog template cache once after a first installation.
6. Run the smoke tests from `eclipse/QA-CHECKLIST.md`.

A stable `v1.2.0` tag should point at the reviewed `main` branch state after the 1.2.0 pull request has been merged.
