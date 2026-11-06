<?php
/** Administration CSS regression checks. Run: php quality/admin-css-test.php */
error_reporting(E_ALL);
$css = file_get_contents(dirname(__DIR__) . '/eclipse/css/admin/admin.css');
function adminCssAssert($condition, $message) {
    if (!$condition) { fwrite(STDERR, 'FAIL admin CSS: ' . $message . PHP_EOL); exit(1); }
}
adminCssAssert(is_string($css), 'missing CSS');
adminCssAssert(strpos($css, '\\nbody.') === false, 'escaped newline corrupts a selector');
foreach (array(
    'body.eclipse-admin-page .eclipse-admin-list-search-label{',
    'body.eclipse-admin-page .eclipse-admin-list-filter-primary label,body.eclipse-admin-page .eclipse-admin-list-filter-limit{',
    '.eclipse-admin-list-search-field input[type=search]',
    '@media(max-width:47.99rem)',
) as $fragment) {
    adminCssAssert(strpos($css, $fragment) !== false, 'missing selector: ' . $fragment);
}
adminCssAssert(substr_count($css, '{') === substr_count($css, '}'), 'unbalanced block delimiters');
echo "Eclipse administration CSS regression tests passed\n";
