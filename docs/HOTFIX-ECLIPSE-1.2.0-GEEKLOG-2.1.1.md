# Eclipse 1.2.0 / Geeklog 2.1.1 ZIP updater hotfix

This hotfix is only needed for sites that already installed an early Eclipse 1.2.0 package on Geeklog 2.1.x and now fail with:

```
Fatal error: Call to undefined method ZipArchive::getFromName()
.../layout/eclipse/includes/theme-update.php
```

## Why this happens

The affected Eclipse 1.2.0 updater calls `ZipArchive::getFromName()` before it can extract a newer Eclipse archive.
On Geeklog 2.1.x, Eclipse may be using its Archive_Zip compatibility class instead of PHP's native ZipArchive implementation.
Early Eclipse 1.2.0 packages exposed `getStream()` but not `getFromName()`.

Because the failure happens before extraction, a later Eclipse ZIP cannot repair the already-installed updater by itself.

## One-time repair

Replace this file on the affected site:

```
layout/eclipse/includes/zip-compat.php
```

with the current file from this repository:

```
eclipse/includes/zip-compat.php
```

The corrected compatibility class provides both:

- `getFromName()`
- `getStream()`

No database change is required.

After replacing this single file, return to Eclipse Theme Studio and upload the current Eclipse archive normally.

## Future upgrades

Current Eclipse packages also generate an updater that:

- checks whether `getFromName()` exists before using it;
- falls back to `getStream()` on Geeklog 2.1.x;
- replaces the Eclipse theme directory atomically so removed files do not survive upgrades;
- verifies the package manifest before activation.

A clean Eclipse 1.1.0 installation can upgrade directly using its existing `getStream()` based updater and does not require this hotfix.
