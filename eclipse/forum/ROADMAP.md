# Eclipse Forum refactor roadmap

## Goals

The Eclipse Forum integration must stay compatible with Geeklog 2.1.1 and 2.2.2 while reducing theme-specific hacks. The target is a dedicated Forum presentation layer with semantic HTML, mobile-first layouts, accessible controls, and predictable plugin-template overrides.

## Current implementation status

The semantic Eclipse Forum layer now covers the main public and secondary flows:

- forum/category listing;
- topic listing;
- topic header and individual posts;
- search;
- user preferences;
- member, notification and topic reports;
- basic moderation confirmations and split/move;
- forum footer, legend, permissions, time and online users.

The posting workflow is intentionally handled differently. Eclipse no longer overrides `submissionform_header.thtml`, `submissionform_main.thtml` or `submissionform_footer.thtml`. The Forum plugin changed its POST contract between the releases used with Geeklog 2.1.1 and 2.2.2, so each installed Forum version must own its native posting templates. Eclipse styles those native forms through `editor.css` only.

Public Forum-specific legacy selectors were removed from `css/plugins.css`; that shared file now only imports `forum.css` and keeps generic plugin/admin compatibility rules. Dead `.eclipse-forum-editor-*` rules were also removed from `semantic.css` after the posting overrides were retired.

## QA status

The same Eclipse development line has now been exercised manually on both Geeklog 2.1.1 and 2.2.2.

Validated so far:

- general Forum rendering is acceptable on both supported Geeklog versions;
- new-topic creation works on both;
- post editing works on both;
- the native Forum posting templates restore the version-specific hidden fields and submit contract required by each plugin generation.

This validates the architectural rule that functional POST/security templates remain plugin-owned while Eclipse owns presentation.

Further deletion of `forum.css`, `blocks.css`, the Forum UIkit bridge, or request/markup compatibility selectors should still be incremental. Native Forum templates, especially the editor and older 2.1.1-era screens, continue to use UIkit markup.

## Architectural rules

1. Geeklog and the Forum plugin remain responsible for data, permissions, actions, pagination, moderation, security and POST contracts.
2. Eclipse owns presentation through `layout/eclipse/forum/` template overrides and dedicated CSS layers.
3. Functional templates whose contract differs between Forum generations must remain plugin-owned unless a single proven cross-version contract exists.
4. Denim is a compatibility/template fallback only. Eclipse must not depend on undocumented Denim markup or on optional Forum blocks to recognize Forum content.
5. Version-specific Geeklog differences must not be scattered through CSS or duplicated template trees unless a real incompatibility is demonstrated.
6. New Eclipse Forum templates use stable `eclipse-forum-*` classes. UIkit classes may remain where native plugin templates still require them, but they are not the primary styling API for migrated screens.
7. Avoid layout-by-`<br>` and fragile selectors such as `#main-content > .uk-grid:first-of-type` or `:has()` where a template override can provide a stable class instead.
8. Mobile layouts are designed first; desktop table-like layouts are an enhancement, not the semantic source of truth.

## CSS ownership

- `semantic.css` — semantic category, topic-list, topic/post and search overrides.
- `editor.css` — CSS-only presentation for the plugin-owned posting forms on all supported Forum versions.
- `reports.css` — preferences, reports and moderation presentation.
- `footer.css` — Forum footer, legend, permissions, time and online users.
- `forum.css` — shared Forum compatibility, remaining native UIkit surfaces and administration.
- `blocks.css` — Forum block and legacy structural compatibility still required by non-migrated markup.

The long-term target is to shrink `forum.css` and `blocks.css` as stable Eclipse templates replace legacy public markup. They must not be reduced by assumption: each removal should follow observed coverage on both Geeklog generations.

## SEO and semantics target

- One clear H1 per Forum page where Eclipse owns the template: Forum/category listing, topic list, topic and search/report pages.
- H2/H3 hierarchy for categories, forum groups and secondary sections.
- Breadcrumbs in a `<nav aria-label="Breadcrumb">` container with an ordered list when the available template variables allow it.
- Forum/category/topic listings use semantic section/article/list structures where practical.
- Individual posts use `<article>` with a stable permalink target, author/meta region and content region.
- Avoid duplicated labels such as `Forum name + Forum Category` when a clean name/description variable is available.
- Preserve existing Geeklog JSON-LD/breadcrumb output and do not duplicate structured data unless the plugin/template layer can supply correct canonical URLs and positions.
- Link text remains descriptive and crawlable; actions such as Reply, New topic and moderation controls remain separate from navigational links.

The native posting templates are an exception to the H1/template-modernization target for now. Their functional compatibility takes priority; presentation and mobile usability are improved through `editor.css` without changing form fields or POST semantics.

## Responsive target

### Mobile

- No horizontal page overflow at 320 CSS px.
- Forum and topic rows become cards/stacked rows instead of compressed four-column tables.
- Topic/forum title is primary; counts and last-post metadata move below it.
- Post author metadata collapses above the message rather than consuming a permanent left column.
- Buttons have comfortable touch targets and never depend on an icon font for their only accessible name.
- Search, forum jump, paging and actions wrap naturally.
- Native posting forms remain plugin-owned but are made full-width and touch-friendly by `editor.css`.

### Desktop

- Forum/topic listings may use grid/table-like presentation for scanability.
- Posts use author/meta + content columns when space allows.
- The DOM order remains useful on mobile and for screen readers.
- Native posting forms may use a label/control grid without altering their input names or hidden fields.

## Template strategy

### Eclipse-owned public templates

The following templates are appropriate for semantic Eclipse overrides because they primarily control presentation:

- `categorylisting.thtml`;
- `topiclisting.thtml`;
- `topic_navbar.thtml`;
- `topic.thtml`;
- `forum_search.thtml`;
- footer templates;
- selected report, preference and moderation templates after compatibility review.

### Plugin-owned functional templates

The posting templates stay native to the installed Forum version:

- `submissionform_header.thtml`;
- `submissionform_main.thtml`;
- `submissionform_footer.thtml`.

This is deliberate. The older Forum line used with Geeklog 2.1.1 expects fields such as `forum`, `editpost`, `editid`, `editpid`, `modedit`, `submit` and `preview`, while the newer Forum line uses a different submission contract. A shared Eclipse copy would couple the theme to one plugin generation and has already been proven unsafe.

## Resource loading simplification

Eclipse currently registers the UIkit resources required by remaining Denim-compatible Forum templates from `forum/functions.php`. This bridge remains necessary while native Forum pages still emit UIkit controls and FontAwesome-based icons.

The long-term goal is still to move truly common UIkit dependency loading to the Eclipse theme level if that can be proven safe on both Geeklog 2.1.1 and 2.2.2. Once that is verified, the Forum-specific resource bridge can be reduced without changing plugin behavior.

## Compatibility strategy

Use the same Eclipse semantic overrides on Geeklog 2.1.1 and 2.2.2. Do not fork separate theme template trees for the two Geeklog versions. Where the Forum plugin itself has a version-specific functional contract, let its native template resolve that difference and style it with stable CSS selectors.

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

## Next cleanup order

1. Keep `editor.css` as the only Eclipse layer responsible for native posting-form presentation.
2. Audit `forum.css` public selectors and remove only rules superseded by stable `eclipse-forum-*` templates.
3. Audit `blocks.css` separately because center/side Forum blocks still use plugin-owned markup.
4. Keep administration compatibility independent from public Forum cleanup.
5. Verify reply, preview, locked-topic and pagination flows on both Geeklog generations before further structural deletions.
6. Revisit global UIkit loading only after those screens are stable.
7. Finish with SEO/accessibility QA: heading hierarchy, breadcrumb semantics, keyboard focus, link labels and 320 px overflow.
