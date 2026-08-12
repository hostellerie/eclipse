# Eclipse release QA checklist

This checklist must be completed on both Geeklog 2.1.1 / PHP 5.6 and Geeklog 2.2.2 / PHP 8.1 staging installations before a release is considered production-ready.

## Critical smoke tests

- Home page renders its stories, navigation and configured right sidebar.
- Full article page renders header, article content, related blocks and footer.
- Anonymous and authenticated navigation both contain their expected links.
- Command and Control renders its cards, icons and administration sidebar.
- Theme Studio retains its layout and saves settings persistently under `{path_data}-eclipse/`.
- Theme Studio survives `admin/clearctl.php` and migrates RC56-RC58 `ecl.*` records or legacy JSON without deleting either source.
- Global cache clearing is never invoked after a Theme Studio save or update; only Eclipse entries in layout caches are targeted.
- Verify all four column modes after cache clearing: none, left, right, and left plus right.
- Installing a versioned ZIP creates a backup and keeps Denim inheritance active.

## Editorial workflow

- SEO diagnostics update while editing title, description, slug and article content.
- The URL preview uses the configured Geeklog site URL and the current slug.
- Modified story fields create a browser-local draft within fifteen seconds.
- Advanced-editor Show Topic Icon and Draft checkboxes align with their labels.
- CKEditor-only changes are detected by the autosave comparison timer.
- Reloading offers Restore and Discard when the local draft differs from the server form.
- Focus mode hides surrounding navigation without hiding editor actions and can be exited.
- Leaving with unsaved editor changes triggers the browser navigation warning.
- Native Eclipse simple and advanced editor templates retain every required Geeklog token.
- New story: title is above content and generates a slug until the ID is manually edited.
- Existing story: changing the title never changes the stored ID automatically.
- Text and HTML editors both render Intro Text and Body Text at usable heights.
- Preview, save, cancel and delete actions remain visible and correctly styled.
- Editor sidebars follow the Theme Studio setting.

## Administration pages

- The page has its server-rendered `eclipse-admin-page` class before JavaScript runs.
- Modern workspace does not flash the classic header while loading and reveals the original interface after the fallback delay if enhancement cannot run.
- Modern workspace shows a dark sidebar, compact top bar and full-width work area on dashboard, list, editor, configuration and plugin administration pages.
- Classic Eclipse retains the public header, navigation and original side blocks and does not create the modern sidebar.
- Switching either administration mode in Studio, saving and reloading applies the selected shell on both Geeklog targets.
- Each Geeklog User Functions, Admins Only, Core and plugin block appears as its own folded navigation group.
- Left blocks, Right blocks and Left plus right Studio choices include only links from the selected server-rendered block regions.
- The full-width administration bar remains above both the navigation rail and main content.
- The control below the blocks reduces the desktop column to a narrow rail; its visible icon restores it after navigation or reload.
- Folded block links appear on pointer hover and keyboard focus and can also be pinned open with the block heading.
- Desktop fly-outs begin at the sidebar edge and do not move later block headings.
- Every group has a semantic SVG badge that remains visible and opens an unclipped fly-out in the collapsed rail.
- Geeklog's Core, Plugins and Tools group headings appear as separate navigation groups with only their original links.
- Ordinary User Functions blocks never enter the Modern workspace administration rail.
- A pinned fly-out closes on focus loss and immediately when one of its links is activated.
- A sidebar that fits remains fixed beneath the global bar and reaches the viewport bottom; a taller sidebar remains page-scrollable.
- A restricted administrator never receives a cloned link that Geeklog omitted from the original blocks.
- Relative Geeklog 2.1.1 links and permitted 2.2.2 User Functions links remain available when their source block is selected.
- Configuration Manager retains its native Denim menu, tabs, fields and JavaScript behavior in both administration modes.
- Geeklog 2.2.2 `/admin/article.php?mode=edit` keeps the Modern workspace sidebar at its configured width and renders the editor in the content column.
- At mobile width the modern sidebar opens and closes from the Menu button without trapping or losing keyboard focus.
- The mobile administration toggle exposes only links already emitted by Geeklog and remains keyboard operable.
- With JavaScript disabled, the original Geeklog administration block remains visible and usable.
- Geeklog 2.1.1 resolves `_admin_block` without requesting an `_admin_list` template.
- Geeklog 2.2.2 resolves `_admin_list` through Denim's child-block fallback.
- Geeklog 2.2.2 loads its native `admin/article/articleeditor*.thtml` templates; no legacy `admin/story` template is forced.
- Saved filters persist per administration page and reapply without submitting automatically.
- Geeklog's native filters remain visible; Eclipse saved-filter, density and column tools are collapsed on first visit and their visibility then persists.
- Column visibility and table density persist per table and can be reversed.
- Select-all appears only for repeated named checkbox groups and never submits an action automatically.
- Selection counters follow individual and global checkbox changes.
- Administration sidebar keeps its arrows, hover color and fully visible active-page label.
- Stories, blocks, users, groups, topics, syndication, plugins and configuration lists render.
- Search filters remain compact and usable at desktop, tablet and mobile widths.
- Edit, copy, order, move and delete icons remain visible and consistently sized.
- Destructive actions use the danger color without recoloring ordinary submit buttons.

## Public layout

- Comment notification checkboxes keep a visible gap from their labels and from the preceding controls.
- Article share links appear only on full article pages, follow the three independent Studio settings and load no third-party script before activation.
- Facebook, LinkedIn and X share destinations receive the canonical article URL and open without granting access to the originating window.
- Password recovery and login forms render with a padded center-block title and content area.
- Test center-only, center-right and left-center-right layouts.
- Test light, dark and automatic modes.
- Test each navigation, button, block, header and footer style.
- Test widths near 360, 768, 1024 and 1440 pixels.
- Test keyboard navigation, visible focus, 200% zoom and reduced motion.
- Test Windows forced-colors/high-contrast mode and confirm that cards, dialogs, focus rings and destructive actions remain distinguishable.
- Confirm the skip link moves focus to the main content and the active main-navigation link exposes `aria-current="page"`.
- Confirm every legacy editor resize image responds to Tab, Enter and Space.

## Theme Studio

- Design, Preview, Updates and Documentation tabs work with pointer, Left/Right, Home and End keys.
- Settings actions appear only in Design and Preview.
- The isolated preview changes between desktop, tablet and mobile widths without reloading the page.
- Palette changes update both the administration swatch and the isolated preview.
- Warm Sunset and every custom palette keep destructive actions on the dedicated danger color.
- Installing an update must complete without a newly copied template calling a PHP helper introduced by that same update.
- Every preset updates the live preview and color fields without reloading.
- Cancel preview restores every initial field and CSS color variable.
- Saving removes the unsaved-change warning and persists site-wide.
- Contrast badges update for presets and individual color changes.
- Restore defaults removes the saved data configuration.
- Named palettes survive theme updates and global cache cleaning in protected sibling JSON storage and can be deleted by name.
- Resetting one section leaves every other section unchanged.
- Section-reset buttons retain an opaque background and visible hover/focus state.
- Exported JSON imports as a draft and is not applied site-wide until Save is pressed.
- Saving and restoring defaults create history entries; restoring an entry recovers its settings.
- The Updates tab lists installer backups and creates a new safety backup before rollback.

Record the tested Geeklog version, PHP version, browser versions and any third-party plugins below before release.
# Modern dashboard

- [ ] Needs attention contains only non-zero moderation/submission links visible to the current account.
- [ ] Quick actions contains only actions already authorized by Geeklog.
- [ ] Recent stories and drafts respect topic/story permissions and never exceed their fixed limits.
- [ ] Recent comments is hidden without `comment.moderate`.
- [ ] Recent Static Pages is hidden when the plugin is inactive or the user lacks `staticpages.edit`.
- [ ] Classic Eclipse retains the native Command & Control page.
