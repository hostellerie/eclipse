# Eclipse 1.0.0

Eclipse is an experimental mobile-first child theme for Geeklog, based on Denim.

## Installation

1. Keep the `denim` theme installed.
2. Copy the `eclipse` directory into Geeklog's `layout` directory.
3. Select `eclipse` in Geeklog.
4. Clear Geeklog/template caches if the old presentation remains visible.

## Customization

Edit `themeconfig.php`. Colors must use six-digit hexadecimal notation. Widths accept `px`, `rem`, or `ch`. No build step is required.

Root administrators can also use **Theme studio** at the bottom of Geeklog's main administration page. Validated settings, footer links, palettes and history are stored as protected JSON documents in the sibling directory `{path_data}-eclipse/`, outside Geeklog's cache-cleaning scope. Existing RC56-RC58 records in `vars` and legacy JSON files under `path_data` are copied automatically without deleting their source.

Under **Appearance > Administration interface**, **Modern workspace** provides a compact top bar and permission-derived dark sidebar, while **Classic Eclipse** retains the traditional site header, navigation and side blocks. Save and reload the administration page after switching.

The Modern workspace home page presents permission-aware attention links and quick actions derived from Geeklog's own administration menu, plus recent stories, drafts, comments and Static Pages when the corresponding feature and access right are available. Empty or unauthorized optional widgets are omitted.

Theme studio also accepts a versioned Eclipse ZIP selected from the administrator's computer. The installer validates paths and file types, rejects links, creates a backup under Geeklog's private `backups/eclipse/` directory, and then overlays the new `eclipse/` tree. PHP needs ZipArchive and write access to the theme directory.

The logo and header image options accept only local paths inside the Eclipse theme. For example: `images/logo.svg`.

## Roadmap

The maintained release plan is available in `ROADMAP.md`. See `COMPATIBILITY.md` for the platform baseline, `MIGRATION.md` for installation and rollback, and `PERFORMANCE.md` for resource budgets. Development validation tools are intentionally excluded from installable archives.

## Compatibility notes

- Missing templates are inherited from `denim` through Geeklog's template override mechanism.
- Denim's main CSS is loaded first to preserve compatibility with inherited admin and plugin templates. Eclipse CSS then replaces the visible design.
- Eclipse itself does not load UIkit, jQuery, Google Fonts, Respond.js, or HTML5 Shiv.
- This first release must be validated on a complete Geeklog installation, especially admin pages, RTL, Forum, and third-party plugins.

## Version

1.0.0-rc54 â€” introduces an isolated administration asset layer, centralized request context, responsive permission-derived administration navigation and a Geeklog 2.2 `_admin_list` compatibility mapping.

1.0.0-rc52 â€” fixes inherited mobile submenu colors and text shadows across all menu styles, and darkens Gradient capsule consistently enough for AA white-text contrast with every bundled palette.

1.0.0-rc51 â€” compacts and clarifies the public footer while reorganizing the footer-link editor into responsive fields, option chips and quieter destructive actions.

1.0.0-rc50 â€” clears Geeklog's native template and generated CSS caches only after a successful archive update, and gives the footer-link editor more breathing room with clearer action placement.

1.0.0-rc49 â€” adds an update-safe Studio manager for discreet footer link rows and optional legal text, rendered immediately above the standard footer content.

1.0.0-rc48 â€” adds Studio overrides for the HTML language and sitemap path, supplies a safe automatic language fallback, and omits Eclipse sitemap, AdSense, public meta tags and JSON-LD from administration pages.

1.0.0-rc47 â€” identifies the optional AdSense integration with a readable `<!-- Google AdSense -->` source comment; future optional scripts follow the same convention.

1.0.0-rc46 â€” adds Geeklog-configured sitemap discovery, optional validated Google AdSense loading, JSON-LD output and an optional database-backed H1 for topic index pages; the new controls persist through Theme Studio.

1.0.0-rc45 â€” increases Gradient capsule's desktop inline padding to 1.35rem so the first and last labels remain visually clear of the rounded ends.

1.0.0-rc44 â€” prevents structural `href="#"` Menu-plugin branches from being marked as the current page, removing the repeated active underlines that made Gradient capsule appear crowded.

1.0.0-rc43 â€” restores the Studio-selected typography across every Menu-plugin style, adds balanced end padding to Gradient capsule and reduces Contrast dock's internal side padding and item gaps.

1.0.0-rc42 â€” replaces button-like Menu-plugin navigation with one minimalist component: unboxed labels, generous rhythm, subtle active lines and quiet animated dropdowns; the four Studio styles now vary only their shared surface, typography and accent.

1.0.0-rc41 â€” gives all four Menu-plugin compositions more breathing room and turns Contrast dock into a contained floating panel, explicitly neutralizing the legacy plugin's outlined text and conflicting hover backgrounds.

1.0.0-rc40 â€” refines all four Eclipse menu compositions for Menu-plugin trees: quieter floating glass, a true gradient capsule, editorial underline states and a stronger contrast dock, with roomier vertical dropdowns and plugin-aware active-page detection.

1.0.0-rc39 â€” targets the Menu plugin's actual `#gl_menu > .eclipse-menu > ul` wrapper structure so only the root list uses a horizontal flex layout while every dropdown remains vertical.

1.0.0-rc38 â€” neutralizes the Menu plugin's floating child items, presents dropdowns as compact vertical lists, clarifies submenu chevrons and keeps the final dropdown inside the viewport.

1.0.0-rc37 â€” closes every nested Menu-plugin list directly in CSS before JavaScript runs, opens desktop branches through hover or keyboard focus, and removes the plugin wrapper's legacy panel presentation.

1.0.0-rc36 â€” makes Menu-plugin styling independent of the numeric suffixes generated by `MENU_getMenu()`, resets legacy separators, restores desktop dropdowns and adds accessible mobile submenu toggles.

1.0.0-rc35 â€” renders the Menu plugin's existing navigation tree exclusively through `MENU_getMenu()` with Eclipse-owned semantic classes, responsive submenus and Studio navigation styles; legacy plugin rendering is no longer exposed.

1.0.0-rc34 â€” integrates Geeklog's Menu plugin through `{header_navigation}` when the active plugin list contains `menu`, while retaining the complete native `{menu_elements}` navigation as an automatic fallback.

1.0.0-rc33 â€” removes the temporary forced-open tooltip loop now that positioning is fixed, restoring Geeklog's native 300-millisecond dismissal after leaving both the help trigger and its panel.

1.0.0-rc32 â€” restores the missing absolute positioning contract for Geeklog's configuration tooltip, allowing its calculated coordinates to place the populated panel beside the active setting instead of at the end of the document.

1.0.0-rc31 â€” preserves Geeklog's populated configuration tooltip while the pointer remains over its trigger or panel, preventing the legacy mouseout timer from immediately restoring `display:none`.

1.0.0-rc30 â€” supports both Geeklog configuration-help formats: documentation-backed `.tooltip` panels and inline `COM_getTooltip()` `.gl-tooltip` hints, fixing inline help that remained positioned off-screen.

1.0.0-rc29 â€” delegates configuration help loading and events entirely to Geeklog's proven Denim mechanism now that local documentation is present, while retaining Eclipse styling for Variable, Default and Description.

1.0.0-rc28 â€” replaces Geeklog's unreliable legacy configuration tooltip with an isolated local-document loader that extracts Variable, Default and Description on first hover or keyboard focus without navigation.

1.0.0-rc27 â€” restores the complete configuration help payload at hover, presenting Variable and Default side by side above the full-width Description while keeping help-link navigation disabled.

1.0.0-rc26 â€” restores Geeklog's contextual configuration descriptions when local documentation is installed, reduces the hover panel to description text only, and cancels help-link clicks before navigation.

1.0.0-rc22 â€” stabilizes spacing and vertical alignment in comment action rows and gives generated HTML/autotag guidance its own padded inner panel so inherited Denim rules cannot collapse the text against the accent border.

1.0.0-rc21 â€” modernizes article comments and both comment editors with card-based threads, clearer metadata and actions, responsive sorting controls, calmer HTML guidance, structured posting advice, taller writing areas and distinct primary, preview and cancel actions.

1.0.0-rc20 â€” prevents mobile configuration values from being vertically clipped by overriding Denim's compact select height with a 48-pixel control, balanced padding and an explicit line height.

1.0.0-rc19 â€” scopes the high-contrast native configuration-select correction to mobile widths, preserving the selected desktop palette treatment.

1.0.0-rc18 â€” keeps selected configuration values visible by giving native selects, their options and disabled states an explicit high-contrast light control scheme across browser and theme modes.

1.0.0-rc17 â€” prevents Geeklog's legacy overflow handler from destructively removing configuration tabs, leaving every native tab in a responsive wrapped bar without generating More.

1.0.0-rc16 â€” replaces Geeklog's unreliable single-tab mobile overflow with a complete wrapped tab bar and watches for delayed or resize-triggered More reconstruction.

1.0.0-rc15 â€” limits tab reinsertion to wide screens and preserves Geeklog's native, newly styled More overflow menu on mobile to avoid conflicts with its resize handler.

1.0.0-rc14 â€” uses Geeklog's own delayed More-menu construction to recover hidden tabs, reinserts them into the primary bar and refreshes the native jQuery UI tab widget on desktop and mobile.

1.0.0-rc13 â€” normalizes Geeklog's separately generated More container by moving its real tab items back into the primary responsive tab bar and then removing the obsolete overflow control.

1.0.0-rc12 â€” expands Geeklog's legacy More tab into the available desktop tab row and removes list bullets while retaining the overflow menu on smaller screens.

1.0.0-rc11 â€” turns the configuration editor into a wider workspace: the administration sidebar is hidden, its configuration menu is wider and collapsible, and a Command & Control return link preserves quick navigation.

1.0.0-rc10 â€” gives the configuration search autocomplete an opaque, layered and keyboard-visible fallback skin so results no longer overlap page content as bare links.

1.0.0-rc9 â€” restores visible, responsive secondary tabs in Geeklog's configuration editor without depending on the legacy jQuery UI visual skin.

1.0.0-rc8 â€” constrains the inherited system-message icon to a predictable 32-pixel box and gives privacy-preserving social sharing its own clearly explained Studio section.

1.0.0-rc7 â€” adds independently configurable, privacy-preserving article share links for Facebook, LinkedIn and X; restores `sysmessage.svg`; and gives Studio section-reset controls an opaque interactive background.

1.0.0-rc6 â€” declares the active Geeklog language on the root HTML element using the native `lang_attribute` template token, fixing the first WCAG issue found by public-page capture.

1.0.0-rc5 â€” makes the PHP baseline explicit and adds a privacy-safe Studio environment diagnostic for Geeklog/PHP versions, ZipArchive availability and persistent-data writability.

1.0.0-rc4 â€” adds a SHA-256 package manifest verified before the updater creates a backup or changes installed files; inconsistent, missing and unexpected files are rejected.

1.0.0-rc3 â€” defines the complete public/admin visual-regression matrix and automatically verifies AA text, link and primary-button contrast for every built-in palette.

1.0.0-rc2 â€” adds enforceable resource budgets and migration/rollback guidance, and limits Theme Studio CSS to the administration dashboard where the Studio is actually rendered.

1.0.0-rc1 â€” begins stable-release accessibility hardening with keyboard-operable legacy editor resize controls, semantic current-page navigation, forced-colors support and explicit reduced-motion coverage.

0.9.19 â€” scopes the settings action bar to the Design and Preview tabs, preventing unrelated save, reset and preview controls from appearing in Updates or Documentation.

0.9.18 â€” adds a keyboard-accessible Theme Studio Documentation tab with a first-run checklist and compact guides to previews, palettes, accessibility, portability, updates, storage, shortcuts and troubleshooting.

0.9.17 â€” places the Theme Studio entry above dashboard customization and personalizes the isolated preview with the current Geeklog site name and slogan instead of demonstration branding.

0.9.16 â€” refines the Theme Studio type scale and density, adds a prominent dashboard entry point with direct anchored navigation, a return link, and exposes the Studio in the administration command palette.

0.9.15 â€” adds an accessible administration command palette powered by the links available to the signed-in administrator, with Ctrl/Command+K, live search, arrow navigation, Enter and Escape support.

0.9.14 â€” adds a browser-local administration-dashboard customizer with accessible visibility controls, ordering buttons, immediate preview, persistence and one-click reset.

0.9.13 â€” reliably merges list tools even when Geeklog renders its search controls and results table in separate forms, by associating the table with the nearest preceding Eclipse toolbar.

0.9.12 â€” merges saved filters, density and column controls into one compact toolbar; the persistent disclosure control is now a clearly bordered button with a directional chevron and a larger click target.

0.9.11 â€” neutralizes more specific Geeklog button and summary rules so both Eclipse utility rows share identical container height, control height, typography, weight, padding and line height.

0.9.10 â€” gives Compact, Normal, Comfortable and Columns the exact same type size, height, padding, radius and line height as the saved-filter controls.

0.9.9 â€” turns Eclipse administration-list controls into a strongly reduced secondary utility bar with minimal height, spacing, typography and visual weight.

0.9.8 â€” keeps Geeklog's native search filters visible while collapsing only Eclipse's saved-filter, density and column tools by default, with a smaller dedicated control type scale.

0.9.7 â€” makes administration filters hidden by default and ensures the HTML hidden state overrides the inherited flex layout so Show/Hide filters works reliably.

0.9.6 â€” adds a persistent Show/Hide filters control to administration lists and slightly compacts filter typography and controls; story-list dates continue to follow Geeklog's global `daytime` format.

0.9.5 â€” progressively enhances Geeklog administration lists with browser-saved filters, persistent column visibility and density, plus safe select-all and selection counts for existing checkbox groups.

0.9.4 â€” aligns advanced-editor checkbox labels and controls as paired cells with the same height and vertical center instead of offsetting the checkbox alone.

0.9.3 â€” vertically aligns advanced-editor topic/draft checkboxes and makes the autosave timer detect CKEditor content changes even when no native textarea input event is emitted.

0.9.2 â€” adds fifteen-second browser-local story drafts, explicit recovery/discard controls, unsaved-work navigation protection and a reversible distraction-free writing mode.

0.9.1 â€” refines the SEO assistant into a properly padded responsive card with a structured search preview, two-column diagnostic tiles and explicit automatic-excerpt feedback.

0.9.0 â€” opens the editorial workflow phase with native-editor SEO diagnostics, live search-result and article-URL previews, title/description guidance, slug-format feedback and approximate content length.

0.8.11 â€” completes the planned professional Studio feature set with named palettes in `data`, per-section resets, JSON import/export, twenty-entry settings history, a backup browser and protected one-click theme rollback.

0.8.10 â€” begins the professional Studio phase with keyboard-accessible tabs and a sandboxed full-page preview switchable between desktop, tablet and mobile widths.

0.8.9 â€” completes the 0.7 staging corrections with accessible spacing for comment notifications and a palette-independent warning color for destructive actions on every page.

0.8.8 â€” closes the implementation side of the 0.7 consolidation: Studio CSS is restricted to administration, editor CSS to the story editor, remaining Denim overrides are audited, and release contracts cover conditional assets and the consolidation baseline.

0.8.7 â€” keeps a single generated icon on Command & Control cards and supplies native Eclipse tooltip overrides that reference existing SVG assets without 404 fallbacks.

0.8.6 â€” makes in-request theme updates safe by removing calls from newly copied templates to newly introduced PHP helpers; native editors reuse Geeklog language variables and JavaScript reads Geeklog's active language directly.

0.8.5 â€” introduces native simple and advanced Eclipse story-editor templates, preserves all Denim template tokens, moves editor grouping into server-rendered markup, and provides PHP-backed English/French interface strings to JavaScript.

0.8.4 â€” consolidates the final Command & Control SVG sizing without changing its rendered dimensions and adds a provisional Geeklog/browser compatibility matrix.

0.8.3 â€” removes three sidebar declarations that were always superseded by the established v3 active-page, arrow and padding rules, without changing the final selectors.

0.8.2 â€” restores a complete public center-block card design for login and password-recovery pages and clarifies that manual cache clearing is only a fallback after updates.

0.8.1 â€” adds a staging QA checklist and CSS consolidation audit, validates SVG/default-setting contracts externally, and removes only unreachable navigation variants plus an obsolete command-card placeholder icon.

0.8.0 â€” adds Theme Studio draft-state protection, cancelable live previews and real-time WCAG contrast reporting for text, links and primary buttons.

0.7.2 â€” restores the required theme configuration return value and unconditional CSS registrations, recovering Denim inheritance, public content, sidebars, navigation, the story editor and Theme Studio layout.

0.7.1 â€” packages the optional PowerShell validator as an allowed text source so Theme Studio can install the release without weakening its upload policy.

0.7.0 â€” consolidates editor localization and initialization, removes competing slug behavior and contradictory CSS, conditionally loads administration styles, and adds package validation plus a maintained roadmap.

0.6.1 â€” separates the Warm sunset primary action from danger red and gives the Studio preview a compact, stable responsive size.

0.6.0 â€” adds live preset palettes to Theme Studio and increases the default intro/body editor heights.

0.5.9 â€” derives editor headings from Geeklog's active interface language and compacts the textarea resize controls.

0.5.8 â€” compacts administrative list filters and keeps their desktop layout on one line with controlled responsive wrapping.

0.5.7 â€” keeps the title above article content, localizes advanced editor panel headings, and styles destructive actions as warnings.

0.5.6 â€” supports the distinct field IDs used by Geeklog's advanced editor for title placement and automatic new-story slugs.

0.5.5 â€” places the article title before the content and generates the ID slug from the title for new stories until the ID is edited manually.

0.5.4 â€” fixes the advanced Article details panel padding conflict and restores consistent internal spacing.
