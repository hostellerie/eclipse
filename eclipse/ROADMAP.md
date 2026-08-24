# Eclipse roadmap

This roadmap is the maintained direction for the Eclipse Geeklog theme.

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
