# Eclipse 1.2.0

Eclipse is a modern, mobile-first child theme for Geeklog based on Denim. It supports Geeklog 2.1.1 and 2.2.2 while preserving Geeklog's native permissions, URLs, form processing and plugin hooks.

## Installation

Eclipse uses one official archive for both first installation and later updates:

`eclipse-<version>.zip`

The archive keeps the historical `eclipse/...` layout for permanent compatibility with existing Eclipse uploaders. It contains `eclipse/ECLIPSE_THEME_PACKAGE.txt` as the theme-package marker and `eclipse/admin/install.php` as a guard against accidental submission through Geeklog's plugin uploader.

For a first installation:

1. Keep the `denim` theme installed.
2. Extract or copy the `eclipse/` directory from the official ZIP into Geeklog's `layout` directory.
3. Select `eclipse` in Geeklog.
4. Clear Geeklog's template cache once.
5. Test the public home page, a full article, login/password recovery and Command and Control.

For later updates, upload that same ZIP from Eclipse Theme Studio. Current Theme Studio validates the package marker and integrity manifest, while older Eclipse uploaders remain compatible because every ZIP entry is still under `eclipse/`.

If the archive is accidentally submitted to Geeklog's plugin uploader, the sentinel `$pi_name` in `eclipse/admin/install.php` prevents Geeklog from treating the theme's `admin/` directory as the administration directory of a plugin named `eclipse`. The incorrect upload may still leave a stray `plugins/eclipse/` directory because that behavior is controlled by Geeklog Core.

## Requirements

- Geeklog 2.1.1 or later.
- PHP 5.6.0 or later as the parsing compatibility baseline; use a maintained PHP version in production.
- Denim installed as the parent compatibility theme.
- Permission to create the protected sibling storage directory `{path_data}-eclipse/`.
- PHP `ZipArchive` only when using Theme Studio's local ZIP updater.

## Theme Studio

Root administrators can access **Theme Studio** from Geeklog administration. It provides design settings, live preview, palettes, footer controls, import/export, history, update installation and rollback.

Validated settings, footer links, palettes and history are stored as protected JSON documents in the sibling directory `{path_data}-eclipse/`, outside Geeklog's cache-cleaning scope. Legacy Eclipse storage sources are read as non-destructive migration inputs.

Theme updates validate the package, create a safety backup under Geeklog's private `backups/eclipse/` directory and clear only Eclipse-related layout caches after a successful update.

## Administration modes

Under **Appearance > Administration interface**:

- **Modern workspace** progressively enhances Geeklog's permission-filtered administration navigation with a responsive sidebar, top bar and dashboard.
- **Classic Eclipse** retains the traditional site header, navigation and side blocks.

The Modern workspace uses only links and actions already exposed by Geeklog to the current administrator. Eclipse does not replace Geeklog permissions or form processing.

## Editorial workflow

Eclipse 1.2.0 includes responsive story/article editor presentation, SEO diagnostics, slug assistance, local draft recovery, focus mode and unsaved-change protection while retaining Geeklog's native editor contracts on supported core versions.

## Compatibility and validation

Missing templates are inherited from Denim through Geeklog's child-theme mechanism. Eclipse provides compatibility layers only where Geeklog 2.1.1 and 2.2.2 differ materially.

Development documentation and the complete release QA matrix are maintained in the repository and deliberately excluded from the installable archive.

## Version

1.2.0
