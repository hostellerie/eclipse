# Eclipse roadmap

This roadmap is the maintained direction for the Eclipse Geeklog theme.

It is repository-level development documentation and is intentionally kept outside the installable `eclipse/` theme tree.

## 0.7 — consolidation

- Maintain native Eclipse simple and advanced story-editor templates with automated Geeklog-token comparison.
- Centralize Eclipse editor translations and derive the active language from Geeklog's own labels.
- Keep editor enhancements idempotent and scoped to the visible Geeklog form.
- Prevent competing legacy and Eclipse slug handlers.
- Remove contradictory CSS overrides and load editor/Studio styles only in administration.
- Add a repeatable package validator for versions, required assets, CSS, JavaScript and PHP.
- Document the supported progressive-enhancement approach while templates continue to inherit from Denim.

Implementation status: complete in 0.8.8. Production sign-off still requires the staging checks recorded in `QA-CHECKLIST.md`; those checks cannot be inferred from static package validation.

## 0.8 — professional Theme Studio

- Isolated full-page preview with desktop, tablet and mobile viewports. *(Introduced in 0.8.10.)*
- WCAG contrast validation and protected semantic colors. *(Live WCAG reporting introduced in 0.8.0.)*
- Named custom palettes, import/export and per-section reset. *(Completed in 0.8.11.)*
- Draft/apply workflow, unsaved-change warnings and settings history. *(Completed in 0.8.11.)*
- Backup browser and one-click rollback for local theme updates. *(Completed in 0.8.11.)*
- Options organized in keyboard-accessible tabs. *(Introduced in 0.8.10.)*

Implementation status: complete in 0.8.11. Production sign-off requires the Theme Studio staging checks in `QA-CHECKLIST.md`, especially filesystem permissions, history restoration and rollback.

## 0.9 — editorial and administrative workflow

- SEO assistance, URL preview and slug uniqueness feedback. *(Diagnostics and URL preview introduced in 0.9.0; Geeklog verifies uniqueness on save.)*
- Draft autosave, unsaved-change protection and distraction-free writing. *(Completed in 0.9.2 with browser-local drafts.)*
- Configurable administrative tables, saved filters and bulk actions. *(Progressive browser-side controls completed in 0.9.5; existing Geeklog server actions remain authoritative.)*
- Customizable administration dashboard and keyboard command palette. *(Dashboard visibility and ordering completed in 0.9.14; accessible command palette completed in 0.9.15.)*

Implementation status: feature-complete in 0.9.15. Production sign-off requires the editorial and administration checks in `QA-CHECKLIST.md`.

## Menu plugin integration

- Use the Menu plugin `navigation` menu as the authoritative source for Eclipse primary navigation.
- Prefer the structured `MENU_getResolvedTree('navigation')` API when available.
- Preserve Menu-owned hierarchy, ordering, permissions, activation, item types and resolved destinations.
- Keep Eclipse responsible only for navigation HTML, CSS, responsive behaviour, mobile toggles, dropdown presentation and current-item styling.
- Fall back to legacy `MENU_getMenu()` for older Menu versions and for unresolved legacy PHP callback items.
- Do not duplicate Menu permission checks or URL-resolution rules in Eclipse.
- Validate a reference menu containing Home as Geeklog Action type 2, standard links, a static page, external URL and nested submenu.

Implementation status: structured renderer introduced on `main`; staging validation with Menu 1.3.0 remains required before declaring the integration stable.

## 1.0 — stable release

- WCAG AA review, keyboard and 200% zoom validation.
- Visual regression suite across core Geeklog administration and public pages.
- Performance budget and production CSS/JavaScript bundles.
- Supported Geeklog/browser matrix, migration guide and rollback documentation.

Implementation status: release-candidate work started in 1.0.0-rc1. Static accessibility contracts, legacy editor keyboard controls, current-page semantics, reduced motion and forced colors are covered; staging WCAG, zoom, visual regression, performance and compatibility sign-off remain.


## 1.2 — interoperability and release hardening

- Consume provider-declared `dashboard.summary` capabilities generically in the Modern workspace dashboard.
- Prefer structured provider metrics over duplicate legacy plugin statistics while keeping legacy statistics as a compatibility fallback.
- Surface provider alerts and conventional pending/draft work in **Needs attention** without querying plugin-private tables.
- Use Documents 1.2 and Videos 0.20 as reference dashboard providers while keeping Eclipse provider-neutral.
- Support independent Menu plugin slots for primary, secondary, footer and sidebar navigation.
- Keep public secondary navigation out of administration pages while preserving primary navigation for Classic Eclipse.
- Harden Theme Studio update packaging and Geeklog 2.1.x ZIP compatibility.
- Keep release archive construction, manifest synchronization and package validation automated on `develop-1.2.0`.

Implementation status: feature work is integrated on `develop-1.2.0`. Automated branch package builds are passing. Final PR review and manual Geeklog 2.1.1 / 2.2.2 release smoke tests remain before the stable `v1.2.0` tag.

## 1.3 — resource efficiency and CSS consolidation (planned)

Geeklog 2.2.2 is the minimum supported CMS version. Keep PHP 5.6.40 syntax compatibility. First audit native Geeklog 2.2.2 asset handling and CSS/JS registration; prefer native resource bundling/minification over extra Eclipse-specific mechanisms.

- **Public CSS:** currently ~79.4 KB against the 1.3 validation ceiling of 80 KB. Target **under 70 KB** by removing redundant rules and loading page-specific styles only where required.
- **CSS `!important`:** currently **528** occurrences against the 1.3 ceiling of **600**. Target **under 400** through a documented specificity/cascade audit, retaining rules actually required for Geeklog/Denim/plugin interoperability. Never remove them mechanically.
- **Theme package size:** currently ~**923 KB** uncompressed against the 1.3 ceiling of **950 KB**. Target **under 850 KB**, after identifying large assets, duplicate templates and unneeded bundled content; preserve useful features and compatibility.
- Track baseline and progress with repeatable measurements and visual/function regression checks on public, admin, Theme Studio and plugin pages.
- **These are optimization targets, not new release-blocking limits.** Keep the existing 1.3 validator ceilings (80 KB / 600 / 950 KB) until changes are tested and approved; historic 60 KB / 300 / 410 KB limits remain superseded.

Status: planned; optimization work must not block rebuilding the current 1.3.0 ZIP once its independent release validation is passing.

## 1.4.0 — released and stabilization closed (6 November 2026)

**Status: RELEASED.** PR [#10](https://github.com/hostellerie/eclipse/pull/10) was merged into `main`; the stable [v1.4.0 release](https://github.com/hostellerie/eclipse/releases/tag/v1.4.0) was published on 6 November 2026 with the installable `eclipse-1.4.0.zip` asset (319,139 bytes according to the GitHub release API).

### Release evidence

- [x] Public release `v1.4.0` exists and includes the installable ZIP asset.
- [x] The development branch passed automated QA and package-build checks before merge. The main-branch Actions history inspected on 6 November did not independently show a successful post-merge build; this remains a documented evidence limitation rather than a claim of failure.
- [x] User-reported staging tests passed: initial installation, mobile Escape navigation, settings and history restoration, palette and footer restoration, cache clearing, theme-update rollback, Static Pages, Downloads and Menu.
- [x] User-reported environments: Geeklog 2.2.2 with PHP 8.3.36 and Chrome; PHP 5.6.40 additionally confirmed, but its Geeklog/browser combination was not specified.
- [x] QA reports and draft release notes were recorded in [QA-1.4.0-STAGING.md](QA-1.4.0-STAGING.md) and [RELEASE-NOTES.md](RELEASE-NOTES.md).
- [ ] Release asset checksum and an independent exact source-to-asset comparison; record if needed for a stronger reproducibility audit.
- [ ] Multi-browser, complete accessibility and multi-site integration matrices, plus browser and plugin version evidence, remain outside the completed user-reported acceptance scope.

### 1.4.x maintenance policy

Release 1.4.0 is closed for feature development. Use 1.4.1 only for confirmed regressions, security/compatibility corrections, and documentation or packaging fixes. Open a focused issue with reproducible evidence before undertaking any substantial change. Do not reopen the 1.4.0 release gate or treat untested scenarios as passed.

### Next independent project

[Eclipse Builder M0](https://github.com/hostellerie/eclipsebuilder/blob/main/ROADMAP.md) may now proceed as a **non-invasive architectural feasibility study**. No runtime code migration, Builder plugin implementation or free-theme dependency is authorized by this release decision. The higher-priority LibreOffice P0/P1 work recorded in the [central TODO](https://github.com/hostellerie/todo/blob/main/ROADMAP.md) remains in force.
