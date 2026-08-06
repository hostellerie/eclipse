# Eclipse compatibility matrix

This matrix distinguishes declared requirements, static validation and staging tests. A declared baseline is not considered fully verified until the complete `QA-CHECKLIST.md` has been executed on that target.

## Server baseline

- Geeklog 2.1.1 is the reference installation and minimum declared Geeklog version.
- Geeklog 2.1.1 itself advertised PHP 5.2.0 as its historical minimum. Eclipse requires PHP 5.3.0 or later because its administration helpers use PHP 5.3 language/runtime facilities.
- Production PHP must still be a maintained version supported by the hosting environment and by the chosen Geeklog core release. The historical minimum is a parsing baseline, not a security recommendation.
- Denim must remain installed because Eclipse inherits templates and its compatibility stylesheet.
- PHP must provide `ZipArchive` only for Theme Studio local updates.
- `path_data` must exist and be writable to persist settings and update backups.
- Eclipse internal interface strings use `geeklog.lang.iso639Code`, supplied by Geeklog before the theme JavaScript; English is the fallback.
- Theme Studio Documentation displays the running Geeklog/PHP versions plus ZipArchive and `path_data` status without exposing filesystem paths.

| Component | Declared baseline | Validation status |
| --- | --- | --- |
| Geeklog | 2.1.1 | Active reference site; final page matrix pending |
| PHP | 5.3.0 theme parsing baseline | Runtime version must be recorded during staging sign-off |
| Denim | Installed and selected as `theme_default` | Enforced by package configuration and template checks |
| ZipArchive | Optional except for Studio updates | Reported by Studio environment diagnostic |
| Writable `path_data` | Required for settings/history/backups | Reported by Studio environment diagnostic |

## Browser baseline

Eclipse currently targets browsers with support for CSS custom properties, Grid, Flexbox, `color-mix()`, `aspect-ratio`, `URLSearchParams`, `Element.closest()`, `NodeList.forEach()` and Unicode normalization.

- Current Chromium and Microsoft Edge: target; final version numbers pending recorded captures.
- Current Firefox: target; final version numbers pending recorded captures.
- Current Safari on macOS and iOS: target; final version numbers pending recorded captures.
- Internet Explorer: unsupported.
- Legacy Android browsers: unsupported.

Exact minimum versions will be published only after visual and interaction testing. Until then, use the latest two major browser versions as the practical staging target.

## Required release checks

- Public home, full article, login and password-recovery pages.
- Administration dashboard, lists, filters, editor and Theme Studio.
- Keyboard interaction, 200% zoom and reduced motion.
- Light, dark and automatic color modes.
- Desktop, tablet and mobile widths from `QA-CHECKLIST.md`.

Third-party Geeklog plugins are supported on a best-effort basis until their templates have individual screenshot baselines.
