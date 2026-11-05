<?php
/**
 * Isolated regression test for Eclipse layout cache invalidation.
 * Tests the production function without deleting any real files.
 * Run: php quality/theme-cache-test.php
 */
error_reporting(E_ALL);
$source = file_get_contents(dirname(__DIR__) . '/eclipse/functions.php');
$begin = strpos($source, 'function eclipse_clear_theme_cache(');
$end = strpos($source, 'function eclipse_footer_plain_text(', $begin === false ? 0 : $begin);
if (!is_string($source) || $begin === false || $end === false || $end <= $begin) {
    fwrite(STDERR, "FAIL: missing cache invalidation function\n");
    exit(1);
}
eval(substr($source, $begin, $end - $begin));

$GLOBALS['cache_calls'] = array();
function CTL_clearCacheDirectories($path)
{
    $GLOBALS['cache_calls'][] = $path;
}
function cacheAssert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL theme cache: ' . $message . PHP_EOL);
        exit(1);
    }
}

$_CONF = array();
cacheAssert(eclipse_clear_theme_cache() === false, 'missing path_data should be rejected');
cacheAssert($GLOBALS['cache_calls'] === array(), 'must not clear caches without configured path');

$_CONF['path_data'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'geeklog-data' . DIRECTORY_SEPARATOR;
$GLOBALS['cache_calls'] = array();
cacheAssert(eclipse_clear_theme_cache() === true, 'configured cache invalidation failed');
$expected = array(
    rtrim($_CONF['path_data'], '/\\') . DIRECTORY_SEPARATOR . 'layout_cache',
    rtrim($_CONF['path_data'], '/\\') . DIRECTORY_SEPARATOR . 'layout_css',
);
cacheAssert($GLOBALS['cache_calls'] === $expected, 'expected only layout_cache and layout_css');
foreach ($GLOBALS['cache_calls'] as $path) {
    cacheAssert(strpos($path, '-eclipse') === false, 'protected Eclipse storage must never be cleared');
}

$GLOBALS['cache_calls'] = array();
$_CONF['path_data'] = '/test/site-b/data';
cacheAssert(eclipse_clear_theme_cache() === true, 'second site invalidation failed');
cacheAssert($GLOBALS['cache_calls'] === array(
    '/test/site-b/data/layout_cache',
    '/test/site-b/data/layout_css',
), 'cache paths must follow the current site, without leaking another site');
echo "Eclipse theme cache isolation tests passed\n";
