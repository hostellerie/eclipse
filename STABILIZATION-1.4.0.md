# Eclipse 1.4.0 — Stabilization audit log

**Started:** 2026-10-21  
**Branch:** `develo-1.4.0`  
**Scope:** read-only code inspection and prioritized QA findings; do not mistake this for a successful staging test.

## Verified facts (GitHub source inspection)

1. **Build artifact:** the latest archive-changing commit inspected was [`ca114de7`](https://github.com/hostellerie/eclipse/commit/ca114de7a09ef76a4eabbab709598d1cf3ce5ac5) (2026-10-20 07:39 UTC). Archive generation had resumed after the release validator changes; no independent install test or latest-run log was verified in this audit.
2. **Search template:** `eclipse/searchform.thtml` implements a primary keyword field and a native `<details>` section for advanced parameters. It retains Geeklog's `{author_form_element}` and named author-template blocks.
3. **Search presentation technical debt:** `eclipse/css/forms.css` currently includes `.eclipse-search-author-slot{min-width:0;font-size:0;line-height:0}`. This visually suppresses potential unwanted author-slot text rather than proving correct template block rendering. **Priority: investigate server-side Geeklog author-block selection and fix at the appropriate source.** Do not remove this CSS blindly before verifying production output.
4. **Custom CSS:** `eclipse/includes/custom-css.php` stores one `eclipse-custom.css` under protected Eclipse storage, checks a 65,536-byte limit, performs permission/token checks for writes, maintains a backup and writes through temporary files plus rename. This is a useful future Builder design-engine interface but **must not be moved or refactored until Eclipse 1.4 is stable**.
5. **Release validator:** `quality/validate-eclipse-release.ps1` still wraps the legacy validator with a maintained exception list and an explicit 85,000-byte public CSS ceiling. Review historical exceptions and replace with appropriate 1.4 contracts rather than relaxing limits again.

## Next verification steps

- [ ] Capture latest Build/Validate action run conclusions and inspect any failing job; verify archive matches current tree and list SHA-256/size.
- [ ] On Geeklog 2.2.2 staging, reproduce search author rendering when author selection is enabled/disabled; check actual HTML output and remove visual masking only after a verified fix.
- [ ] Test search submission and all closed-by-default filters, including GET propagation, keyboard/mobile behavior, and localized labels.
- [ ] Test Theme Studio save/reset/import/history and custom CSS lifecycle with backups and multiple Geeklog data directories.
- [ ] Check CSS cascade and budget, UI visual regressions, cache_resource settings, compatibility and update/rollback.
- [ ] Document test environment, console/PHP logs and release approval in this log.

## Gate

No stable-release claim and no Builder implementation until the [Eclipse 1.4.0 stabilization roadmap](ROADMAP.md#140--stability-gate-before-independent-eclipse-builder-21-october-2026) is signed off. The central [hostellerie/todo](https://github.com/hostellerie/todo) LibreOffice priority remains unchanged.
