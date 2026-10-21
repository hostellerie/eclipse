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

## 1.4.0 — stability gate before independent Eclipse Builder (21 October 2026)

**Status: stabilization started, not signed off.** The branch is `develo-1.4.0`. The packaging workflow generated `dist/eclipse-1.4.0.zip` at commit `ca114de7a09ef76a4eabbab709598d1cf3ce5ac5` on 20 October 2026, after the CI validation corrections; this proves archive generation, **not** staging/production compatibility.

### S0 — build, release and package reliability

- [x] Confirm an archive-generation commit exists after the search-template changes and CI validator corrections.
- [ ] Re-run latest CI and record outcome, checksum, archive size, root directory, PHP lint, package integrity and committed manifest consistency.
- [ ] Review 1.4 release validator and historical 1.3 exception handling: remove obsolete checks only after replacement with focused 1.4 tests; ensure new failures cannot be silently ignored.
- [ ] Review the public CSS budget (currently 85,000 bytes; observed baseline 82,809) and compatibility overrides against measured output; **reduce unnecessary CSS rather than repeatedly raising limits**.
- [ ] Verify update/rollback using the installable archive in Geeklog 2.2.2 on test infrastructure (backups, permissions, assets, cache invalidation).
- [ ] Test workflow concurrency and bot-push behavior with rapid commits, proving the published ZIP matches the final source head.

### S1 — native Theme Studio and design-engine baseline

- [ ] Inventory exact sources of truth for every setting, palette, named palette, custom footer and layout option; document the current persisted JSON schema, migration and backup behavior.
- [ ] Validate saving/reopening the Theme Studio state, named palettes, import/export, history restore and multi-site storage isolation.
- [ ] Test `eclipse/includes/custom-css.php` as-is: private CSS file read/write, validation, maximum 64 KiB, atomic save, backup and theme cache invalidation. Do not refactor it for Builder yet.
- [ ] Document CSS tokens, light/dark palettes, contrast controls, asset cache keys, Denim dependency and public/admin stylesheet loading.
- [ ] Define the future shared versioned design-engine contract **without moving runtime code or making the free Eclipse theme depend on Builder**.

### S2 — Geeklog pages and presentation quality

- [ ] Test keyword-first `searchform.thtml`: visible query + submit button, closed-by-default native `details` filters, actual query filtering, mobile and keyboard use.
- [ ] Investigate Geeklog `author_form_element_disabled` output at its source; do not rely on invisible text or `font-size: 0` to mask invalid template output.
- [ ] Test title-only and title-plus-site modes, home-page titles, technical pages, anonymous welcome localization, and Static Pages title/meta description counters.
- [ ] Regression-check public and admin forms, configuration manager, content pages, menus, CSS breakpoints, accessibility, browser consoles, PHP logs, and Geeklog plugin integrations.
- [ ] Verify behavior with `cache_resource=0/1`, language variants and clean/primed caches.

### S3 — release decision

- [ ] Publish a dated QA report listing CI, tested Geeklog/PHP environments, visual and functional checks, unresolved defects and explicit sign-off.
- [ ] Release 1.4.0 only after the QA gate is satisfied; carry remaining improvements into 1.4.x maintenance or independently prioritized follow-up issues.
- [ ] After stabilization, greenlight Builder M0 design-engine feasibility and only then the independent plugin's implementation.

**Related:** [Eclipse Builder roadmap](https://github.com/hostellerie/eclipsebuilder/blob/main/ROADMAP.md) · [central priorities](https://github.com/hostellerie/todo/blob/main/ROADMAP.md). This gate does not supersede the current absolute LibreOffice priority.
