# Eclipse performance budget

Eclipse keeps its own resources within explicit uncompressed limits. Denim compatibility CSS, Geeklog core scripts, content images and third-party plugins are outside these theme-only measurements.

## Enforced budgets

- Public Eclipse CSS: 59,000 bytes maximum.
- Eclipse JavaScript: 65,000 bytes maximum.
- Complete installable Eclipse directory: 343,000 bytes maximum.
- Theme Studio CSS loads only on `admin/index.php`.
- Story-editor CSS loads only on `admin/story.php`.
- Comment CSS loads only on `article.php` and `comment.php`.
- Menu-renderer CSS loads only while the Menu plugin is active.
- JavaScript is loaded in the footer and requires no third-party library.
- Eclipse ships no webfont and makes no external font request.

The external validator fails when a budget is exceeded. The RC21 allowance covers the isolated comment templates and stylesheet without adding them to unrelated pages; production measurements must additionally record transferred sizes with server compression enabled, cache headers, and a representative home/article/admin page waterfall.

## Release measurements still required

- First and repeat view with Brotli or gzip enabled.
- Largest Contentful Paint using the site's real header and article images.
- Cumulative Layout Shift with advertisements and plugins enabled.
- Interaction responsiveness for navigation, Studio tabs and the command palette.

Performance results depend on hosting, Geeklog plugins and content. The theme budget prevents silent package growth but does not replace server-side measurement.
