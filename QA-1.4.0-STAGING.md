# Eclipse 1.4.0 — staging installation and acceptance test

**Status:** manual acceptance pending. This document is for `develo-1.4.0` only; it is not a stable release approval.

## Scope and archive

- Source: https://github.com/hostellerie/eclipse/tree/develo-1.4.0
- ZIP: `dist/eclipse-1.4.0.zip` from that same branch.
- Minimum Geeklog: **2.2.2**. PHP 5.6 syntax is retained; the release should be verified on the actual PHP environment used by the tester.
- Record the Git commit SHA of the archive and verify that the branch's most recent `Validate Eclipse pull request` and `Build installable Eclipse archive` workflows succeeded before testing.
- **Do not deploy first to a production installation.** Use a separate test/staging installation with backups.

## Before installation

1. Record Geeklog and PHP versions, theme version, Menu plugin version, installed plugins, active language, browser and operating system.
2. Back up the installed Eclipse theme directory, the protected Eclipse storage directory (typically `{path_data}-eclipse/`), and the relevant Geeklog database. Verify that backup recovery is possible.
3. Note whether the current theme is Eclipse or Denim and whether Theme Studio contains custom settings, palettes, footer items or custom CSS.

## Installation and rollback (critical)

- [ ] Install/update Eclipse from the versioned ZIP using the supported theme update path; report any errors or warnings.
- [ ] Confirm the site and administration load after the update; check both PHP and browser-console errors.
- [ ] Verify custom CSS, saved palettes, footer settings and Theme Studio settings survived the update.
- [ ] Save one harmless Theme Studio setting, reload and confirm persistence; then restore its original value.
- [ ] Save and re-open one custom CSS rule; verify the public page reflects it; restore the original rule.
- [ ] Clear Geeklog layout caches using the site controls; confirm pages render and persisted data remains intact.
- [ ] Verify the theme-update backup and demonstrate a rollback **on staging only** if an earlier ZIP is available.
- [ ] Confirm that a previous backup can be restored; do not overwrite an unverified original on production.

## Public and responsive smoke test

- [ ] Home page and one article render without missing blocks/assets.
- [ ] Check center-only, left+center, center+right and three-column layouts where available.
- [ ] Verify navigation and nested menu links for anonymous and logged-in users; no restricted link is exposed.
- [ ] On a narrow viewport (about 360px), open the menu and a submenu, press Escape and confirm focus returns to the menu button; re-open and confirm submenu state is reset.
- [ ] Use the menu with Tab, Enter and Space; confirm visible focus.
- [ ] At 360, 768 and 1024px check for clipping, overlapping content or horizontal scrolling.
- [ ] At 200% zoom, confirm menus, search and key actions remain usable.
- [ ] Confirm reduced-motion preference and contrast/focus visibility where supported.

## Search, editor and administration

- [ ] Open `search.php`: query and submit button are immediately visible, optional filters are initially closed, and entering a query returns results.
- [ ] Open search filters, choose date/topic/type/results and author (when enabled), submit and verify applied filters; compare against Denim when unsure of Core behavior.
- [ ] Open Command & Control, a story list, one editor and Configuration Manager in Modern and Classic modes.
- [ ] Save and cancel a non-destructive configuration/edit action; verify no native controls disappear.
- [ ] On a mobile viewport, inspect administration navigation, its expand/collapse behavior and keyboard focus.
- [ ] If Menu plugin is installed, verify primary, secondary and footer menu slots, nested items, and guest permissions.
- [ ] Test one relevant plugin page (e.g. Static Pages) with the same theme.

## Theme Studio and recovery

- [ ] Save/reload palettes and Design settings; test export/import as a draft only, then cancel or deliberately apply.
- [ ] Verify history restores settings, footer and palette values; record a safe before/after example.
- [ ] Confirm failed saves (when safely reproducible) display errors rather than claiming success; never force filesystem permission failures in production.
- [ ] Check clean and primed cache behavior and multilingual labels if available.

## Manual feedback — 2026-11-05

- Reporter confirmed the initial Eclipse archive installation and initial smoke checks worked without reported defects (exact environment/version and individual checklist evidence still to be recorded).
- **PASS (reported):** with the menu open at a mobile-width viewport, pressing Escape closes the menu. The reporter explicitly confirmed this behavior works.
- Focus restoration, submenu reset, distinct browser/device testing, rollback and full visual/accessibility sign-off are **not independently verified** by this report.

## Manual feedback — 2026-11-06

- **PASS (reported):** Geeklog cache clearing test validated by the tester after the Eclipse 1.4.0 staging installation. This follows the requested Theme Studio setting persistence/cache check; exact saved values, environment versions, and logs were not supplied.
- **PASS (reported, 2026-11-06):** Theme Studio settings/history restoration succeeded on the staging installation after the prior save-and-restore test.
- **PASS (reported, 2026-11-06):** Static Pages, Downloads and Menu plugin compatibility checks completed successfully on the Eclipse staging installation. The reporter did not itemize separate desktop, mobile, admin or permission scenarios, so those finer-grained checks remain unverified.
- **PASS (reported, 2026-11-06):** Tester confirmed the final requested checks as successful: palette-specific restoration, footer restoration, and rollback of a theme update on staging. These results are based on the tester's confirmation; archive SHA, exact starting version and screenshots/logs were not supplied.
- Detailed browser/device, authorization, production-like environment and release-candidate evidence are still to be documented before calling this a fully audited release.

## Evidence and go/no-go

Record: date/time, Geeklog version, PHP version, theme archive SHA, browser, Menu plugin version, list of other tested plugins, passed/failed/not tested checkboxes, screenshots for visible defects, and short excerpts from relevant PHP/console logs (redact credentials, private URLs and personal data).

**Go:** automated CI and archive generation pass, all critical manual smoke tests pass, no data loss/permission or security regression, and any remaining cosmetic issues are documented.

**No-go:** any failed install/rollback, lost settings, cross-site data exposure, broken public navigation, critical admin failure, or unreviewed PHP/JS error.

Do not mark Eclipse 1.4.0 stable solely from CI or from this document's creation.
