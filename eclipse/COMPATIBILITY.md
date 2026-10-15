# Eclipse compatibility matrix

This matrix distinguishes declared requirements, static validation and staging tests. A declared baseline is not considered fully verified until the complete `QA-CHECKLIST.md` has been executed on that target.

## Server baseline

- Geeklog 2.2.2 is the minimum supported Geeklog version. Geeklog 2.1.1 is no longer supported by Eclipse 1.3.0.
- PHP 5.6.40 remains the syntax-compatibility baseline for Eclipse 1.3.0; maintained PHP versions are recommended.
- Production PHP must still be a maintained version supported by the hosting environment and by the chosen Geeklog core release. The historical minimum is a parsing baseline, not a security recommendation.
- Denim must remain installed because Eclipse inherits templates and its compatibility stylesheet.
- Because Eclipse declares Geeklog's standard `image_type = png`, it ships the complete Denim PNG compatibility image set under `images/` and `images/icons/`; Eclipse-specific UI can still use SVG assets independently.
- PHP must provide `ZipArchive` only for Theme Studio local updates.
- The parent of `path_data` must allow creation of the protected sibling `{path_data}-eclipse/`; `path_data` remains required only for Geeklog caches and temporary ZIP extraction.
- Eclipse internal interface strings use `geeklog.lang.iso639Code`, supplied by Geeklog before the theme JavaScript; English is the fallback.
- Theme Studio Documentation displays the running Geeklog/PHP versions plus ZipArchive and persistent sibling-storage status without exposing filesystem paths.

| Component | Declared baseline | Validation status |
| --- | --- | --- |
| Geeklog | 2.2.2 | Minimum supported version; final page matrix pending |
| PHP | 5.6.40 syntax baseline | Test on deployed PHP runtime before release |
| Denim | Installed and selected as `theme_default` | Enforced by package configuration and template checks |
| ZipArchive | Optional except for Studio updates | Reported by Studio environment diagnostic |
| Writable sibling JSON storage | Required for settings/footer/palettes/history | Reported by Studio environment diagnostic |

Eclipse 1.3.0 targets Geeklog 2.2.2 only. Confirm the theme and Denim template lookup paths against Geeklog 2.2.2 before removing overrides. Run the manual matrix in `QA-CHECKLIST.md` before production release.

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

## Administration compatibility

| Feature | Classification | 2.1.1 fallback | 2.2.2 behavior |
| --- | --- | --- | --- |
| Scoped admin tokens, forms and tables | SAFE | Existing `_admin_block` markup | Same markup plus `_admin_list` |
| Responsive admin navigation | COMPATIBILITY LAYER REQUIRED | Enhances Geeklog's permission-filtered admin block; block remains usable without JavaScript | Same progressive enhancement |
| `_admin_list` wrapper | GEEKLOG 2.2.2 ONLY | Not registered, preventing missing Denim templates | Registered to Denim's child block template |
| Story editor | COMPATIBILITY LAYER REQUIRED | Native Denim `admin/story/storyeditor*.thtml` plus scoped Eclipse assets | Native Denim `admin/article/articleeditor*.thtml` plus scoped Eclipse assets |

Theme Studio stores settings, palettes, footer data and history as locked JSON documents under `{path_data}-eclipse/`. The sibling derivation isolates sites naturally in monosite and multisite installations and remains outside Geeklog cache cleaning. RC56-RC58 chunked `vars` records and legacy JSON under `path_data` are read only as non-destructive migration sources. Theme backups use the private Geeklog root `backups/eclipse/`; transient archive extraction alone uses `path_data`.
| Hardcoded replacement sidebar | NOT RECOMMENDED | Would duplicate permissions and plugin hooks | Same security and maintenance risk |

The admin layer does not change URLs, form processing, permissions, plugin hooks or CSRF handling. Third-party plugin HTML is styled only beneath `body.eclipse-admin-page`; unusual custom markup degrades to Denim rather than requiring an Eclipse template fork.
