# Eclipse Forum refactor roadmap

## Goals

The Eclipse Forum integration must stay compatible with Geeklog 2.1.1 and 2.2.2 while reducing theme-specific hacks. The target is a dedicated Forum presentation layer with semantic HTML, mobile-first layouts, accessible controls, and predictable plugin-template overrides.

## Current implementation status

The semantic Eclipse Forum layer now covers the main public and secondary flows:

- forum/category listing;
- topic listing;
- topic header and individual posts;
- new topic/reply editor and preview;
- search;
- user preferences;
- member, notification and topic reports;
- basic moderation confirmations and split/move;
- forum footer, legend, permissions, time and online users.

Public Forum-specific legacy selectors were removed from `css/plugins.css`; that shared file now only imports `forum.css` and keeps generic plugin/admin compatibility rules. Remaining legacy selectors in `forum.css` and `blocks.css` are intentionally retained as compatibility fallbacks until manual QA has been completed on Geeklog 2.1.1 and 2.2.2.

### QA gate before further deletion

Do not remove the remaining legacy UIkit/markup fallbacks, the temporary Forum UIkit resource bridge, or request/markup compatibility selectors until the same archive has been exercised on both supported Geeklog generations. After that test pass, obsolete selectors can be removed based on observed coverage instead of assumptions.

## Architectural rules

1. Geeklog and the Forum plugin remain responsible for data, permissions, actions, pagination, moderation and security.
2. Eclipse owns presentation for the Forum through `layout/eclipse/forum/` template overrides and `forum.css`.
3. Denim is a compatibility/template fallback only. Eclipse must not depend on undocumented Denim markup or on optional Forum blocks to recognize Forum content.
4. Version-specific Geeklog differences must be isolated in PHP/template fallbacks, not scattered through CSS selectors.
5. New Eclipse Forum templates should use stable `eclipse-forum-*` classes. UIkit classes may remain temporarily where the plugin requires them, but must not be the primary styling API.
6. Avoid layout-by-`<br>` and fragile selectors such as `#main-content > .uk-grid:first-of-type` or `:has()` where a template override can provide a stable class instead.
7. Mobile layouts are designed first; desktop tables are an enhancement, not the semantic source of truth.

## SEO and semantics target

- One clear H1 per Forum page: Forum index, forum/category listing, topic, create/reply form, search/report page.
- H2/H3 hierarchy for categories, forum groups and secondary sections.
- Breadcrumbs in a `<nav aria-label="Breadcrumb">` container with an ordered list when the available template variables allow it.
- Forum/category/topic listings use semantic section/article/list structures where practical.
- Individual posts use `<article>` with a stable permalink target, author/meta region and content region.
- Avoid duplicated labels such as `Forum name + Forum Category` when a clean name/description variable is available.
- Preserve existing Geeklog JSON-LD/breadcrumb output and do not duplicate structured data unless the plugin/template layer can supply correct canonical URLs and positions.
- Link text remains descriptive and crawlable; actions such as Reply, New topic and moderation controls remain separate from navigational links.

## Responsive target

### Mobile

- No horizontal page overflow at 320 CSS px.
- Forum and topic rows become cards/stacked rows instead of compressed four-column tables.
- Topic/forum title is primary; counts and last-post metadata move below it.
- Post author metadata collapses above the message rather than consuming a permanent left column.
- Buttons have at least a comfortable touch target and never depend on an icon font for their only accessible name.
- Search, forum jump, paging and actions wrap naturally.

### Desktop

- Forum/topic listings may use grid/table-like presentation for scanability.
- Posts use author/meta + content columns when space allows.
- The DOM order remains useful on mobile and for screen readers.

## Template audit

### Priority 1 — public index and navigation

#### `categorylisting.thtml`
Current Denim template uses UIkit panels, floats, a breadcrumb `<ul>`, an H2 and a four-column table. Forum rows rely on `onclick` on a `<div>` in addition to a normal link.

Eclipse override target:
- semantic breadcrumb nav;
- clean heading/description;
- explicit action area;
- stable `eclipse-forum-category` / `eclipse-forum-list` classes;
- mobile stacked forum records;
- preserve all existing variables and normal links;
- remove presentation-only floats and `<br>` layout.

#### `topiclisting.thtml`
Current template repeats the same UIkit/table pattern for topics and hides Views/Replies at some breakpoints.

Eclipse override target:
- H1 for the forum page;
- breadcrumb nav;
- responsive topic records with subject, pagination, counts and last-post metadata;
- sort controls remain available without depending on tiny image controls on mobile;
- New topic/subscription actions in a dedicated action bar.

#### `topic_navbar.thtml`
Current template uses a breadcrumb list and an H2 containing the subject link.

Eclipse override target:
- semantic breadcrumb;
- topic H1;
- stable action bar and pagination region.

## Priority 2 — individual topic/post

#### `topic.thtml`
Current template is heavily coupled to UIkit grids, overlays and fixed width classes.

Eclipse override target:
- each post becomes an `<article class="eclipse-forum-post">`;
- author/meta region precedes message content in DOM order;
- desktop may use a two-column grid;
- mobile stacks author then message;
- permalink target remains stable;
- edit/quote/like/moderation actions remain available and keyboard accessible;
- avatar is decorative unless a meaningful alternative is available.

#### `topicfooter.thtml` and `footer/*`
Rebuild time/legend/users/rules as compact secondary sections. Avoid empty list items and layout `<br>` tags.

## Priority 3 — posting workflow

#### `submissionform_header.thtml`
Replace UIkit breadcrumb/grid wrapper with semantic breadcrumb + H1/action context while preserving CSRF token and form variables.

#### `submissionform_main.thtml`
Audit labels, fieldsets, help text, validation messages, BBCode controls, touch targets and textarea sizing. Preserve plugin behavior and JavaScript hooks.

#### `submissionform_footer.thtml` and preview templates
Keep submit/preview/cancel actions in a responsive button group with explicit labels.

## Priority 4 — search, preferences and reports

- `forum_search.thtml`
- `userprefs/user_settings.thtml`
- `reports/memberlist.thtml`
- `reports/notifications.thtml`
- `reports/report_results.thtml`
- moderator templates

These should be converted only after index/topic/post flows are stable.

## Resource loading simplification

Eclipse should provide the common resources required by its Denim-compatible template baseline at the theme level. Forum-specific `functions.php` should only request resources that are truly Forum-specific. The temporary Forum UIkit bridge can be removed once Eclipse's global dependency loading is verified on both Geeklog 2.1.1 and 2.2.2.

## Compatibility strategy

Test each phase on both Geeklog 2.1.1 and 2.2.2 with the same Eclipse override files. Do not fork separate 2.1.x and 2.2.x template trees unless a real template-variable/API incompatibility is demonstrated.

Required regression matrix:

- anonymous and logged-in user;
- no sidebars, left, right, both;
- Forum index;
- category/forum topic list;
- individual topic with one and multiple posts;
- new topic, reply, edit and preview;
- locked topic/forum;
- pagination;
- mobile widths 320/375/430 px and desktop;
- light/dark Eclipse schemes;
- keyboard focus and visible action labels.

## Implementation order

1. Stabilize common UIkit/resource loading for compatibility.
2. Add semantic Eclipse overrides for `categorylisting.thtml` and `topiclisting.thtml`.
3. Add `topic_navbar.thtml` and `topic.thtml` overrides.
4. Replace temporary structural CSS selectors with stable Eclipse classes and delete obsolete rules.
5. Rework posting templates.
6. Rework footer/search/preferences/reports.
7. Remove any remaining Forum CSS dependency on optional blocks, request-path detection, or version-specific markup after the QA gate.
