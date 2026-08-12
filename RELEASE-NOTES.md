# Eclipse 1.0.0

Eclipse 1.0.0 is the first stable release of the modern, responsive Denim child theme for Geeklog. It supports Geeklog 2.1.1 and 2.2.2 with PHP 5.6 through 8.1.

The stable release includes the public responsive theme, persistent Theme Studio storage outside Geeklog's destructible cache directory, import/export and rollback, permission-aware Modern workspace administration, Classic Eclipse fallback, editorial and SEO helpers, plugin-aware navigation, footer links, cache-safe asset versioning and compatibility validation against both supported Denim contracts.

## Release candidate history

- Remove every Eclipse Calendar layout override introduced in RC80 through RC84.
- Return Calendar tables, widths, typography, spacing, wrapping, selectors and buttons to the plugin's native CSS so Calendar maintainers can control its presentation.
- Retain the independent fixes that constrain oversized Eclipse action icons.

- Override Geeklog's inline 100% width on the Calendar's central month-control cell with explicit 24% / 52% / 24% regions.
- Keep both readable mini-calendars inside the center column instead of compressing July and pushing September beneath the right sidebar.

- Restore readable mini-calendar typography and cell spacing after the RC82 overflow correction.
- Retain the fixed three-region calendar header that protects the right sidebar, while increasing mini-calendar text to 0.8rem and restoring a comfortable line height.

- Reduce Calendar typography and cell padding specifically inside month views.
- Give the previous/current/next month header a fixed internal layout so the right mini-calendar cannot widen the center column beneath the site sidebar.
- Use a smaller scale for mini-calendar dates, selectors and calendar action buttons while keeping two-digit dates intact.

- Stop Calendar cells from breaking two-digit dates and weekday labels character by character while retaining the bounded center-column layout.
- Keep day cells and date states on one line, compact the central calendar controls and scale the main weekday header for constrained center columns.

- Constrain Eclipse edit, copy and delete action SVGs wherever Geeklog or a plugin renders them, including Links listings outside aligned side-block controls.
- Keep Calendar month and day tables within the center column using fixed table layout, bounded nested tables and wrapping cells, preventing overlap beneath the right sidebar.

- Constrain public-facing Geeklog list-sort arrows to a compact inline size, including Poll Created headers outside administration pages.
- Constrain Eclipse action SVGs placed in UIkit-aligned side-block controls, fixing the Poll edit icon without affecting poll content, article images or plugin illustrations.

- Reduce the Studio navigation fly-out to the single label Theme Studio instead of cloning the launch card description.
- Hide whitespace-only story metadata rows after rendering, preventing empty author/view bullets on Geeklog 2.2.2 while preserving populated metadata and structured-data markup.

- Align Modern workspace navigation group labels and fly-out links consistently to the left of their available text column.
- Add a dedicated Search and identifiers panel to the Geeklog 2.2.2 advanced Article Editor.
- Move the article ID, meta description and meta keywords into that panel and attach the existing live SEO overview with search preview and four diagnostics.

- Make desktop administration fly-outs hover-only and remove their plus/minus state indicators; retain explicit tap/click expansion on mobile.
- Restore the Geeklog 2.2.2 advanced Article Editor navbar as a compact wrapping tab row.
- Give the plain-text editor resize controls visible plus/minus glyphs without depending on UIkit's icon font.
- Separate and equalize the mobile Menu and View site controls.
- Render Studio color inputs as rectangular swatches and reorganize the mobile save bar into readable rows.

- Compact the Modern dashboard and prevent an empty Needs attention card from stretching to the height of Quick actions.
- Present quick actions as responsive action tiles and tighten recent-content cards without reducing their readable hit targets.
- Remove the duplicated native Command & Control heading after the Modern workspace has initialized; the persistent administration bar remains the page title.
- Keep dashboard cards aligned to their own content height and collapse both dashboard grids cleanly on narrow screens.

- Replace generic command-group counters with permission-aware Needs attention and Quick actions cards derived only from links rendered by Geeklog for the current administrator.
- Add bounded recent stories, comments, drafts and Static Pages widgets through a dedicated PHP dashboard include.
- Apply Geeklog story and Static Pages permission SQL, feature/right checks and strict result limits; omit unavailable optional widgets without breaking Command & Control.
- Keep the richer editorial dashboard exclusive to Modern workspace and preserve Classic Eclipse's native Command & Control presentation.

- Prevent the Article Editor's `editor-sidebars-hidden` rule from replacing the two-column Modern workspace grid.
- Keep the one-column wrapper override only in Classic Eclipse while retaining the editor's internal responsive workspace in both modes.

- Render Geeklog's permission-aware `COM_adminMenu()` as a hidden navigation source on every administration page, restoring Modern workspace beyond Command & Control.
- Avoid caching or hardcoding administration destinations; permissions and plugin actions are recomputed by Geeklog on every request.
- Give the native Tools group priority over keywords found in its child links so it receives the wrench icon rather than the Users icon.
- Expose Theme Studio in the persistent navigation source only to Root users.

- Build the Command & Control navigation directly from Geeklog's permission-filtered Core, Plugins, Tools and Users dashboard groups.
- Move those command groups out of the Modern workspace content and retain Theme Studio as its own navigation group.
- Replace the duplicated command cards with a compact CMS overview using only counts and actions already rendered by Geeklog.
- Keep native administrative side blocks as the fallback source on other administration pages.

- Restrict the Modern workspace column to blocks and links carrying Geeklog's native administrative markers; exclude ordinary User Functions links.
- Fall back across physical left/right regions only when the selected region contains no administrative block on that Geeklog branch.
- Close a pinned fly-out when keyboard focus leaves its group and close it immediately when a contained link is activated.

- Stop the collapsed rail from clipping navigation fly-outs.
- Preserve Geeklog's native Core, Plugins and Tools headings as distinct fly-out groups instead of flattening their links into Admins Only.
- Replace first-letter badges with semantic inline SVG icons inferred from the rendered block title and link vocabulary, with a neutral grid fallback.
- Keep icon classification presentation-only: it neither creates destinations nor changes Geeklog permissions.

- Keep a fully visible Modern workspace sidebar fixed beneath the global administration bar and let an over-height menu follow normal page scrolling.
- Present desktop block links as edge-aligned fly-outs so opening a group never displaces the blocks below it.
- Add compact first-letter block badges that remain usable in the collapsed navigation rail.
- Reduce the bottom collapse control to a single chevron with an accessible label and tooltip.
- Keep mobile submenus in normal document flow inside the off-canvas navigation.

- Move the Modern workspace header above both the sidebar and main content and stretch it across the complete administration viewport.
- Move the desktop collapse control below the navigation blocks and retain a narrow rail with a visible reopen glyph.
- Reveal folded block links on hover, keyboard focus or explicit activation.
- Always retain Geeklog-rendered administration blocks even when their physical 2.1.1/2.2.2 region is outside the selected supplementary block source.
- Restore native Core, Plugins and Tools card images in Classic Eclipse while Modern workspace continues using its SVG card system.

- Keep Theme Studio legends and labels light on its dark surface in both Classic Eclipse and Modern workspace.
- Accept every server-rendered link in a selected Geeklog block instead of requiring an `/admin/` URL fragment, restoring 2.1.1 mode switching and 2.2.2 left-block navigation.
- Return Configuration Manager to Denim's native layout, tabs and JavaScript behavior by removing the experimental workspace restructuring.
- Continue deriving navigation exclusively from Geeklog-rendered blocks, so broad URL acceptance does not bypass permissions.

- Add a Studio choice for deriving modern administration navigation from Geeklog's left blocks, right blocks or both.
- Keep every navigation destination permission-safe by cloning only links Geeklog already rendered for the current user and installed plugins.
- Add a desktop header control that hides or restores the complete navigation column and remembers the browser-local preference.
- Retain the responsive Menu control on narrow screens and keep every native block group folded initially.

- Clear only Eclipse template and generated CSS cache entries after every successful settings, palette, history, rollback or archive-update write.
- Split the modern sidebar by the actual Geeklog block containers instead of merging every link under User Functions.
- Fold every sidebar group by default and expose accessible group headings for on-demand navigation.
- Add a darker WordPress-like administration header carrying the site identity and current page title.
- Suppress the classic-to-modern flash while retaining a delayed CSS fallback when the enhancement cannot start.

- Restore the complete public header, navigation and layout dimensions in Classic Eclipse instead of retaining RC61's compact administration header.
- Give Theme Studio explicit high-contrast labels, legends and controls inside Modern workspace.
- Make Modern workspace sidebar groups accessible collapsible controls, opening the current group by default and otherwise the first available group.

- Add a Studio-selectable administration mode: **Modern workspace** or **Classic Eclipse**.
- Give Modern workspace a WordPress-like shell with a dark responsive sidebar, compact top bar and full-width work area.
- Build modern navigation exclusively from Geeklog's existing permission-filtered links; no permissions, destinations or form processing are replaced.
- Keep the classic administration chrome as a fail-safe unless the modern shell is built successfully.
- Scope modern dashboard and administration JavaScript enhancements to Modern workspace.

- Make Eclipse Admin UI explicit on every administration page with a compact contextual bar and safe View site action.
- Strengthen the isolated admin presentation for dashboard cards, navigation blocks, forms, lists, tables and plugin administration without changing Geeklog permissions, URLs or form processing.
- Document the Admin UI behavior, command palette, responsive navigation and browser-local dashboard customizer directly in Theme Studio.

- Add the required Geeklog 2.2.2 `index.thtml` document contract instead of assuming the legacy `header.thtml` and `footer.thtml` files are loaded.
- Restore Eclipse page structure, controlled icon sizing, native/Menu Plugin navigation, inline design variables, JSON-LD and persistent Studio footer links on Geeklog 2.2.2.
- Preserve Denim 2.2.2 WebPage, WPHeader, SiteNavigationElement, mainContentOfPage and WPFooter microdata alongside Eclipse JSON-LD.

- Move Theme Studio state from chunked Geeklog `vars` records to protected JSON documents in the multisite-safe sibling directory `{path_data}-eclipse/`.
- Migrate non-destructively from RC56-RC58 `vars` records first, then legacy `path_data` JSON, with a locked migration report and no source deletion.
- Add shared/exclusive locking, a 5 MiB limit, schema normalization, validated temporary writes, atomic replacement, restrictive permissions and automatic `.bak` recovery.
- Export and import a versioned complete state containing settings, footer links and palettes while retaining compatibility with legacy flat settings exports.
- Store complete twenty-entry snapshots and restore settings, footer links and palettes together, with a safety snapshot first.

- Restore explicit `?v=1.0.0-rc58` browser cache keys on Geeklog 2.2.x by registering theme assets as same-origin absolute resources; this avoids the local-path query-string rejection while preserving CSS cascade order.
- Expand Theme Studio documentation and diagnostics so the active release, persistent `vars` storage, legacy JSON migration and cache recovery procedure can be verified directly.

- Fix Geeklog 2.2.2 resource registration: query-string cache busters are now used only with Geeklog 2.1.x, whose resource loader strips them before checking the filesystem. Geeklog 2.2.x receives clean local paths and applies its own resource fingerprints.
- Let Geeklog 2.2.2 resolve Eclipse templates first and Denim only as the declared child-theme fallback; retain the legacy template override solely for Geeklog 2.1.x.
- Correct Theme Studio storage help: settings, footer links, palettes and history persist in Geeklog's `vars` table rather than depending on destructible files under `path_data`.

The entries above record the release-candidate stabilization work that led to Eclipse 1.0.0.

## Included

- Responsive public theme with light, dark and automatic color modes.
- Five palette presets and four navigation presentations.
- Hierarchical Menu-plugin rendering for desktop, keyboard and mobile use.
- Theme Studio with isolated preview, persistent settings, named palettes, import/export, history and local ZIP updates.
- Update integrity manifest, automatic safety backup, rollback and Geeklog template/CSS cache clearing.
- Improved administration dashboard, tables, filters, configuration workspace and story editor.
- SEO overview, slug assistance, local editorial drafts and focus mode.
- Configurable article sharing and update-safe footer link rows.
- Accessible focus states, reduced-motion handling and forced-colors support.

## Recent release-candidate fixes

- Fixed Theme Studio mojibake by using encoding-neutral HTML entities.
- Made `supported_version_theme` match Geeklog's active `min_theme_gl_version`, allowing 2.2.2 to discover Eclipse.
- Moved Theme Studio state to chunked Geeklog `vars` records compatible with the 2.1.1 and 2.2.2 schemas.
- Removed global cache clearing from theme updates and retained Denim editor/comment templates.
- Added an admin-only stylesheet and script while retaining Denim as the compatibility parent.
- Centralized administration, story-editor and Configuration Manager context detection.
- Added server-rendered administration body classes and a responsive, permission-derived menu toggle.
- Registered Geeklog 2.2's `_admin_list` wrapper only when the core capability is available; 2.1.1 continues to use `_admin_block` and Denim fallbacks.
- Corrected mobile submenu colors and removed inherited text shadows for all bundled palettes.
- Guaranteed WCAG AA contrast for white text in Gradient capsule.
- Constrained oversized public edit, print and email action icons.
- Removed orphan metadata bullets when author or view information is hidden.
- Added an Eclipse archive-story template so archive pages receive the same metadata safeguards.

## Before reporting a problem

Clear Geeklog's template cache after the first installation. Updates uploaded through Theme Studio clear the native template and generated CSS caches automatically. If only an old CSS or JavaScript asset remains, refresh the browser cache once.

See `eclipse/QA-CHECKLIST.md`, `eclipse/COMPATIBILITY.md` and `eclipse/MIGRATION.md` for testing and recovery guidance.
