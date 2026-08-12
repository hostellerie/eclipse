# Eclipse CSS consolidation audit

This audit records why the remaining `!important` declarations exist. They are compatibility boundaries with Denim, not a general styling convention. New Eclipse-owned components must not add `!important` unless this document is updated with a reproducible inherited conflict.

## Retained compatibility groups

- `v3.css`: resets Denim floats, fixed heights, background arrows and mobile block-title skins; protects administration block geometry and legacy action-image dimensions.
- `ui-fixes.css`: defeats Denim command-card images, sidebar backgrounds and administrative filter display rules.
- `story-editor.css`: replaces the inherited definition-list floats, editor widths, tab-panel spacing and image-input sizing. These rules apply only on `/admin/story.php`.
- `layout.css`: sidebar visibility is an explicit Theme Studio setting and must override Geeklog's layout class output.
- `studio.css`: notice colors and cancel-preview controls protect semantic status colors from the global button skin.

## Consolidation rules

- `studio.css` is registered only for Geeklog administration requests.
- `story-editor.css` is registered only for `/admin/story.php`.
- Public navigation and sidebars remain in the shared sheets because they are used outside administration.
- Exact duplicate rules and unreachable experimental variants must be removed rather than overridden later.
- Consolidation removed 15 redundant declarations from the 0.8.7 baseline (280 to 265).
- The SEO assistant needs one scoped padding override because Geeklog's inherited definition-list reset applies `padding: 0 !important` to every editor `<dd>`.
- Focus mode uses seven scoped overrides to neutralize Denim's fixed region visibility and width declarations while the body opt-in class is active.
- The paired advanced-editor checkbox cells need one padding reset to neutralize Denim's label offset.
- The Eclipse table-tools expanded state needs one display override because its collapsed state is declared after the base flex layout.
- The validator fixes the current compatibility ceiling at 300 `!important` declarations. The RC77 allowance covers the scoped Geeklog 2.2.2 advanced-editor definition-list overrides; a further increase fails validation until this audit and the ceiling are deliberately reviewed.
