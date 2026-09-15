# Eclipse 1.1.0

Eclipse is a modern, mobile-first child theme for Geeklog based on Denim. It supports Geeklog 2.1.1 and 2.2.2 while preserving Geeklog's native permissions, URLs, form processing and plugin hooks.

## Installation

1. Keep the `denim` theme installed.
2. Copy the `eclipse` directory into Geeklog's `layout` directory, or install the versioned Eclipse ZIP through Theme Studio.
3. Select `eclipse` in Geeklog.
4. Clear Geeklog's template cache once after a first installation.
5. Test the public home page, a full article, login/password recovery and Command and Control.

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

Eclipse 1.1.0 includes responsive story/article editor presentation, SEO diagnostics, slug assistance, local draft recovery, focus mode and unsaved-change protection while retaining Geeklog's native editor contracts on supported core versions.

## Compatibility and validation

Missing templates are inherited from Denim through Geeklog's child-theme mechanism. Eclipse provides compatibility layers only where Geeklog 2.1.1 and 2.2.2 differ materially.

Development documentation and the complete release QA matrix are maintained in the repository and deliberately excluded from the installable archive.

## Version

1.1.0
