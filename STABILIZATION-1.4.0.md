# Eclipse 1.4.0 — Stabilization audit log

**Started:** 2026-10-21  
**Branch:** `develo-1.4.0`  
**Scope:** targeted code audit, first focused corrective change and regression tests; do not mistake this for a successful staging test.

## Verified facts (GitHub source inspection)

1. **Build artifact:** the latest archive-changing commit inspected was [`ca114de7`](https://github.com/hostellerie/eclipse/commit/ca114de7a09ef76a4eabbab709598d1cf3ce5ac5) (2026-10-20 07:39 UTC). Archive generation had resumed after the release validator changes; no independent install test or latest-run log was verified in this audit.
2. **Search template:** `eclipse/searchform.thtml` implements a primary keyword field and a native `<details>` section for advanced parameters. It retains Geeklog's `{author_form_element}` and named author-template blocks.
3. **Geeklog upstream dependency — search:** the author-block rendering issue is expected to be addressed by the next Geeklog release according to the project owner. Eclipse retains its current template for compatibility; do not add more hiding workarounds. Confirm the Core fix in a test build, then remove any obsolete Eclipse masking safely. **Search presentation technical debt:** `eclipse/css/forms.css` currently includes `.eclipse-search-author-slot{min-width:0;font-size:0;line-height:0}`. This visually suppresses potential unwanted author-slot text rather than proving correct template block rendering. **Priority: investigate server-side Geeklog author-block selection and fix at the appropriate source.** Do not remove this CSS blindly before verifying production output.
4. **Custom CSS:** `eclipse/includes/custom-css.php` stores one `eclipse-custom.css` under protected Eclipse storage, checks a 65,536-byte limit, performs permission/token checks for writes, maintains a backup and writes through temporary files plus rename. This is a useful future Builder design-engine interface but **must not be moved or refactored until Eclipse 1.4 is stable**.
5. **Release validator:** `quality/validate-eclipse-release.ps1` still wraps the legacy validator with a maintained exception list and an explicit 85,000-byte public CSS ceiling. Review historical exceptions and replace with appropriate 1.4 contracts rather than relaxing limits again.

## Stabilization work applied (2026-10-21)

- [x] **Real defect fixed — Custom CSS 64 KiB boundary:** the validator previously accepted exactly 65,536 bytes, then appended a final newline, creating 65,537 bytes; saved content could fail read-back. Validation now verifies the normalized persisted CSS size as well.
- [x] Added `quality/custom-css-test.php` with accepted/rejected input, newline normalization, control characters, closing-style rejection and 64 KiB boundary regressions.
- [x] Wired the new regression suite into **both** `build-dist.yml` and `validate-pr.yml`; updated the latter's 1.4 validation label.
- [ ] Record passing GitHub Actions runs; commits pushed do not independently prove CI execution or successful runtime installation.

## Storage hardening — 2026-10-21 (follow-up)

- [x] Reviewed the existing source for `eclipse_storage_root()`, `eclipse_storage_normalize()`, `eclipse_storage_write_raw()` and `eclipse_data_json()`: protected sibling `path_data-eclipse` root, allowlisted JSON filenames, named palettes capped at 50, history capped at 20 records, backup fallback, advisory file locking and staging/rename already exist.
- [x] Fixed a potential **JSON persistence boundary failure**: the 5 MiB size check previously excluded the newline added to the on-disk JSON. It now measures the actual persisted payload and verifies a byte-for-byte match before replacing the primary file.
- [ ] Verify PHP lint / CI against this change, and test actual write / read / backup recovery, permissions, concurrent writes, failed rename behavior, storage migrations and multisite directory isolation on Geeklog 2.2.2 staging.
- [ ] Review consistency of the final rename and backup restoration paths under simulated filesystem errors before release.
- [ ] Do not extract or rename the storage engine for Builder before these tests pass.

## Next verification steps

- [ ] Capture latest Build/Validate action run conclusions and inspect any failing job; verify archive matches current tree and list SHA-256/size.
- [ ] Once the next Geeklog Core version supplies its author-block correction, validate it against Eclipse's advanced search with author selection enabled/disabled and remove the obsolete visual masking only after confirming correct HTML output.
- [ ] Test search submission and all closed-by-default filters, including GET propagation, keyboard/mobile behavior, and localized labels.
- [ ] Test Theme Studio save/reset/import/history and custom CSS lifecycle with backups and multiple Geeklog data directories.
- [ ] Check CSS cascade and budget, UI visual regressions, cache_resource settings, compatibility and update/rollback.
- [ ] Document test environment, console/PHP logs and release approval in this log.

## Gate

No stable-release claim and no Builder implementation until the [Eclipse 1.4.0 stabilization roadmap](ROADMAP.md#140--stability-gate-before-independent-eclipse-builder-21-october-2026) is signed off. The central [hostellerie/todo](https://github.com/hostellerie/todo) LibreOffice priority remains unchanged.
