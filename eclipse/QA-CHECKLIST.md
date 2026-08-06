# Eclipse release QA checklist

This checklist must be completed on a Geeklog 2.1.1 staging installation before a release is considered production-ready.

## Critical smoke tests

- Home page renders its stories, navigation and configured right sidebar.
- Full article page renders header, article content, related blocks and footer.
- Anonymous and authenticated navigation both contain their expected links.
- Command and Control renders its cards, icons and administration sidebar.
- Theme Studio retains its layout and can save settings to `data/eclipse-settings.json`.
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
- Named palettes survive a theme update in `data/eclipse-palettes.json` and can be deleted by name.
- Resetting one section leaves every other section unchanged.
- Section-reset buttons retain an opaque background and visible hover/focus state.
- Exported JSON imports as a draft and is not applied site-wide until Save is pressed.
- Saving and restoring defaults create history entries; restoring an entry recovers its settings.
- The Updates tab lists installer backups and creates a new safety backup before rollback.

Record the tested Geeklog version, PHP version, browser versions and any third-party plugins below before release.
