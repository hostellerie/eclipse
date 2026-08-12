# Eclipse Admin UI architecture

Eclipse remains one Geeklog theme with two presentation contexts. PHP detects the request before assets render; public pages keep the existing layer, while administration pages additionally receive `css/admin/admin.css` and `js/admin.js`.

Theme Studio exposes two administration modes under **Appearance > Administration interface**:

- **Modern workspace** (default) hides the public chrome only after JavaScript has successfully built a WordPress-like administration shell. Its site-aware bar spans the viewport above both columns. A sidebar that fits remains sticky beneath that bar; a taller one scrolls normally. Desktop fly-outs remain visible from both the expanded column and collapsed rail. Native Geeklog Core, Plugins and Tools headings become distinct groups.

Block badges are semantic inline SVGs inferred from the block title and its already-rendered link text/URLs: document for content, people for users/groups, puzzle for plugins/extensions, gear for site/configuration/blocks, wrench for tools/logs/cache, and a neutral grid when no category matches. These heuristics select only artwork; they never add or authorize a link.
- **Classic Eclipse** keeps the complete traditional site header, navigation, dimensions and side blocks and does not run the modern administration enhancements.

Save the complete Studio state and reload an administration page after changing mode. The setting is site-specific in multisite installations because it is part of the protected `{path_data}-eclipse/` state.

**Admin navigation blocks** selects the left region, right region or both as sources, but only blocks and links carrying Geeklog's native `.adminoption` / `.adminoption_off` markers enter the Modern workspace column. Ordinary User Functions links are excluded. If the chosen region has no administrative block on a supported Geeklog branch, Eclipse falls back across regions to avoid an empty administration rail. A chevron below the groups reduces the column to a narrow rail.

Fly-outs close automatically when keyboard focus leaves their group. Activating any contained link also closes the panel before navigation begins. Hover-only panels disappear naturally as soon as the pointer leaves the group and panel.

Configuration Manager deliberately retains Denim's native layout, tabs and JavaScript behavior on both supported Geeklog branches. It still appears inside the Modern workspace shell, but Eclipse does not rearrange its internal form or suppress its native menu.

## Feasibility decisions

| Area | Decision | Method and risk |
| --- | --- | --- |
| Dashboard, common forms, lists and plugin pages | SAFE | Scoped CSS over stable Geeklog/Denim markup. No business logic or URL changes; low public regression risk. |
| Responsive administration navigation | COMPATIBILITY LAYER REQUIRED | JavaScript discovers Geeklog's existing permission-filtered blocks and clones their real links into the modern sidebar. Original links remain usable unless enhancement succeeds; moderate plugin-markup risk. |
| Story editor and Configuration Manager | SAFE | Preserve the existing Eclipse overrides and contextual styles; avoid another core-template fork. |
| `_admin_list` | GEEKLOG 2.2.2 ONLY | Capability check registers Denim's `blockheader-child.thtml`; 2.1.1 deliberately retains `_admin_block`. |
| Replacement router, core patch or hardcoded menu taxonomy | NOT RECOMMENDED | Would duplicate permissions/plugin hooks and create high compatibility and maintenance risk. |

## Compatibility contract

`eclipse_request_path()`, `eclipse_is_admin_request()`, `eclipse_admin_page()`, `eclipse_is_story_editor()` and `eclipse_is_configuration_page()` are the only routing helpers. They use PHP 5.6-compatible syntax and normalized `PHP_SELF`, so installations at a domain root or subdirectory are handled identically.

The administration stylesheet owns Eclipse admin tokens, focus treatment, responsive table containment and mobile navigation presentation. Selectors are rooted at `.eclipse-admin-page`; public rendering never loads this file. `admin.js` is also admin-only and progressive enhancement: it neither invents destination links nor submits or mutates Geeklog forms. A short CSS preparation phase prevents the classic shell flashing before enhancement; if the script or expected markup is unavailable, a delayed CSS fallback reveals the original interface.

## Template overrides

- `admin/commandcontrol.thtml`: retained because the dashboard card grouping and Theme Studio entry need markup unavailable through CSS alone.
- Editor and comment templates are deliberately not overridden in this release. Geeklog 2.1.1 inherits Denim `admin/story/storyeditor*.thtml`; Geeklog 2.2.2 inherits Denim `admin/article/articleeditor*.thtml`; both inherit their native comment templates.

No new Geeklog core template overrides are introduced by the Admin UI isolation work.
# Dashboard information

The Modern workspace dashboard replaces generic group totals with **Needs attention** and **Quick actions**. These links are cloned from Geeklog's current, permission-filtered administration menu. A dedicated `includes/admin-dashboard.php` provider adds bounded recent stories, drafts, comments and Static Pages queries. Each optional panel is omitted when its feature, table or access right is unavailable; Classic Eclipse keeps the native dashboard.
