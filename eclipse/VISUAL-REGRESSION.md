# Eclipse visual regression matrix

Reference captures must use stable content, the same account and an empty browser cache state appropriate to the comparison. Record browser/version, Geeklog/PHP versions, palette and viewport with every run.

## Public reference pages

| Reference | Route | Required states |
| --- | --- | --- |
| Home | `/` | anonymous/authenticated; right sidebar; light/dark |
| Full article | `/article.php/{known-story-id}` | article body, metadata, edit link for Story Admin, comments |
| Password recovery | `/users.php?mode=getpassword` | center block title, form and member sidebar |
| Login | `/users.php?mode=login` | errors, labels, keyboard focus |
| Search | `/search.php` | empty form and populated results |

## Administration reference pages

| Reference | Route | Required states |
| --- | --- | --- |
| Dashboard | `/admin/index.php` | default cards, customized cards, Studio collapsed/targeted |
| Story list | `/admin/story.php` | Eclipse tools closed/open, compact/comfortable table |
| Story editor | `/admin/story.php?mode=edit` | advanced editor, SEO block, focus mode, local-draft notice |
| Blocks | `/admin/block.php` | list icons, filters and ordering controls |
| Users/groups | `/admin/user.php`, `/admin/group.php` | wide tables at 200% zoom |
| Topics/syndication | `/admin/topic.php`, `/admin/syndication.php` | legacy icons and forms |
| Plugins/configuration | `/admin/plugins.php`, `/admin/configuration.php` | cards, tables and nested controls |
| Theme Studio | `/admin/index.php#eclipse-theme-studio` | every tab, each preview viewport and update/rollback forms |

## Viewports and preferences

- 360 × 800: mobile navigation, single-column forms and no horizontal page overflow.
- 768 × 1024: tablet layout and wrapped administrative filters.
- 1024 × 768: compact desktop/tablet boundary.
- 1440 × 1000: wide desktop with configured content maximum.
- Browser zoom at 200% with a 1280 CSS-pixel window.
- `prefers-reduced-motion: reduce`.
- Light, dark and automatic color modes.
- Windows forced-colors/high-contrast mode.

## Acceptance rules

- No missing text, icon, image or primary content.
- No overlap, clipping or page-level horizontal scrolling at the reference widths.
- Focus remains visible and follows a logical order.
- Reflow may move controls but must not hide actions or labels.
- Differences caused only by timestamps, counters or user content must be masked or recorded; unexplained structural differences block the stable release.

Authenticated administration captures must be produced on staging and must never store session cookies, private form values or personal data in the theme archive.

Automated public capture must stop on HTTP 429 and must not retry aggressively. If the site's request protection rejects headless browsers or parallel asset loading, record the limitation and use deliberately paced manual captures instead of bypassing that protection.
