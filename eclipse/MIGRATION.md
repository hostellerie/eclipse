# Installing, updating and rolling back Eclipse

## Requirements

- Keep the Geeklog `denim` theme installed; Eclipse inherits templates and compatibility styles from it.
- Confirm that Geeklog's `path_data` directory exists and is writable by PHP.
- `ZipArchive` is required only for updates uploaded through Theme Studio.

## Fresh installation

1. Back up the site files and database using the hosting tools.
2. Extract the archive so the resulting directory is `public_html/layout/eclipse/`.
3. Confirm that `theme.ini`, `functions.php`, `header.thtml` and the `css`, `js`, `images` directories are directly inside `eclipse/`.
4. Select Eclipse in Geeklog's theme configuration.
5. Clear the Geeklog template cache once and reload the home page.
6. Check the home page, one full article, login/password recovery and Command and Control before reopening the site to visitors.

## Updating from Eclipse 0.9.x

The preferred method is **Theme Studio → Updates**. Select the versioned ZIP stored on your computer and install it. The installer validates paths, file types and every SHA-256 entry in `MANIFEST.json`, then creates a copy in `path_data/eclipse-backups/` before replacing theme files.

The manifest detects incomplete or accidentally modified packages. It is an integrity check, not a publisher signature; obtain release archives from the project's trusted distribution channel.

Settings remain in `path_data/eclipse-settings.json`; named palettes and history also live outside the theme directory and are not overwritten. After installation, verify the version shown in Theme Studio. Clear the template cache only if an older presentation remains.

For a manual update, first copy the existing `layout/eclipse/` directory to a dated backup, then overlay the complete new `eclipse/` directory. Do not delete Denim.

## Rollback

Use **Theme Studio → Updates → Restore a theme backup** and select the most recent known-good copy. Eclipse creates another safety backup before restoration.

If the administration page cannot render:

1. Restore the dated `layout/eclipse/` directory using the hosting file manager or deployment backup.
2. Alternatively select Denim as the active Geeklog theme through the existing site recovery procedure.
3. Clear Geeklog's template cache.
4. Keep `path_data/eclipse-settings.json` unless a saved setting is known to cause the problem; rename it rather than deleting it so it remains recoverable.

Never overwrite `siteconfig.php`, Geeklog core files, the database or the entire `path_data` directory as part of a theme rollback.
