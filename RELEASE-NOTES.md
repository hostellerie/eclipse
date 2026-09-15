# Eclipse 1.1.0

Eclipse 1.1.0 is a major refinement of the modern, responsive Denim child theme for Geeklog. It keeps Geeklog's native permissions, URLs, form processing and plugin hooks while improving the public theme, administration workspace, editorial workflow and compatibility across Geeklog 2.1.1 and 2.2.2.

## Highlights

- Modern administration workspace built from links already rendered by Geeklog for the current user.
- Classic Eclipse administration mode retained as a fallback.
- Responsive administration dashboard, navigation, lists, filters and configuration pages.
- Improved Geeklog 2.1.1 and 2.2.2 compatibility layers without replacing core permissions or workflows.
- Dedicated article/story editor presentation with responsive sidebars and SEO assistance.
- Local draft recovery, slug assistance, focus mode and unsaved-change protection.
- Theme Studio with persistent protected JSON storage, live preview, palettes, import/export, history and rollback.
- Safer local ZIP updates with integrity validation, automatic backup and targeted cache clearing.
- Improved responsive public layout, navigation, footer controls and plugin presentation.
- Accessible focus states, keyboard navigation, reduced-motion support and high-contrast safeguards.
- Optional article sharing without loading third-party scripts before activation.
- Geeklog 2.2.2 email footer compatibility while preserving Geeklog 2.1.1's native mail behavior.
- Forum presentation and SEO helpers integrated at theme level.

## Compatibility

Eclipse 1.1.0 declares:

- Geeklog 2.1.1 or later.
- PHP 5.6.0 or later, with PHP 5.6 retained as the parsing compatibility baseline.
- Denim installed as the parent compatibility theme.
- `ZipArchive` only when Theme Studio's local update installer is used.

Production installations should use a currently maintained PHP version supported by the selected Geeklog release and hosting environment.

See `eclipse/COMPATIBILITY.md` for the detailed compatibility matrix and `eclipse/QA-CHECKLIST.md` for the final release checks.

## Administration

The Modern workspace progressively enhances Geeklog's own permission-filtered administration navigation instead of hardcoding destinations. If enhancement cannot initialize, the native interface remains available.

Eclipse 1.1.0 also adds or refines:

- responsive sidebar and mobile administration navigation;
- permission-aware dashboard modules and quick actions;
- compact table controls, saved filters and column visibility;
- improved Configuration Manager presentation while retaining native behavior;
- Geeklog 2.2.2 article editor compatibility;
- Geeklog 2.1.1 story editor compatibility;
- plugin administration styling scoped to Eclipse administration pages.

## Theme Studio and storage

Theme Studio stores settings, footer links, palettes and history in protected JSON documents under the multisite-safe sibling directory `{path_data}-eclipse/`.

The update workflow includes:

- integrity manifest validation;
- safety backups;
- rollback support;
- import/export;
- migration from legacy Eclipse storage sources;
- targeted Eclipse cache invalidation rather than global Geeklog cache clearing.

## Public theme and accessibility

The release improves responsive layouts, navigation, forms, article metadata, pagination, footer controls and plugin presentation across desktop, tablet and mobile widths.

Accessibility work includes visible focus states, keyboard-operable navigation, skip-link support, reduced-motion handling, forced-colors/high-contrast safeguards and preservation of semantic attributes supplied by Geeklog.

## Installation and update

Use the versioned `eclipse-1.1.0.zip` asset published with the GitHub release. Do not use GitHub's automatic source archive as the install package.

Before updating:

1. Back up the site files and database.
2. Keep the Denim theme installed.
3. Confirm the server meets the compatibility requirements.
4. Install the release ZIP manually or through Theme Studio.
5. Clear the Geeklog template cache once after a first installation.
6. Run the smoke tests from `eclipse/QA-CHECKLIST.md`.

## Release validation

The repository validates the installable theme contract before publishing release assets. A stable `v1.1.0` tag must point at the reviewed `main` branch state after the 1.1.0 pull request has been merged.
