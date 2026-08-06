# Eclipse 1.0.0-rc53

This is the first public testing release candidate of Eclipse for Geeklog. Install it on a staging site or after creating a complete backup, then report regressions through GitHub Issues.

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

- Corrected mobile submenu colors and removed inherited text shadows for all bundled palettes.
- Guaranteed WCAG AA contrast for white text in Gradient capsule.
- Constrained oversized public edit, print and email action icons.
- Removed orphan metadata bullets when author or view information is hidden.
- Added an Eclipse archive-story template so archive pages receive the same metadata safeguards.

## Before reporting a problem

Clear Geeklog's template cache after the first installation. Updates uploaded through Theme Studio clear the native template and generated CSS caches automatically. If only an old CSS or JavaScript asset remains, refresh the browser cache once.

See `eclipse/QA-CHECKLIST.md`, `eclipse/COMPATIBILITY.md` and `eclipse/MIGRATION.md` for testing and recovery guidance.
