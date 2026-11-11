<?php
/**
 * Header density remains independent of the optional Menu plugin and applies
 * on public and admin pages. G3 uses a site name without a slogan.
 */
function hd_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL header density: ' . $message . PHP_EOL);
        exit(1);
    }
}
$root = dirname(__DIR__) . '/eclipse/';
$fn = file_get_contents($root . 'functions.php');
$css = file_get_contents($root . 'css/header-density.css');
$menu = file_get_contents($root . 'css/menu-refinements.css');
$header = file_get_contents($root . 'header.thtml');
hd_assert(is_string($fn) && is_string($css) && is_string($menu)
    && is_string($header), 'theme source readable');
$start = strpos($fn, 'function theme_css_eclipse()');
$end = strpos($fn, 'function theme_js_libs_eclipse()', $start);
hd_assert($start !== false && $end !== false, 'CSS registry found');
$registry = substr($fn, $start, $end - $start);
$entry = "array('name' => 'eclipse-header-density'";
hd_assert(substr_count($registry, $entry) === 1, 'global header CSS registered exactly once');
hd_assert(strpos($registry, $entry) > strpos($registry, 'if (eclipse_menu_plugin_active())'),
    'header CSS registration not nested under optional Menu plugin');
hd_assert(strpos($registry, "'priority' => 320") !== false,
    'header CSS loads after menu refinements');
hd_assert(strpos($registry, 'eclipse_asset_cache_version') !== false,
    'new CSS shares theme cache-busting version');
hd_assert(strpos($css, 'body.theme-eclipse.header-density-compact #header-content') !== false,
    'compact dimensions defined globally');
hd_assert(strpos($css, 'body.theme-eclipse.header-density-compact .site_slogan:empty')
    !== false, 'empty slogan hides without reserving space');
hd_assert(strpos($css, ':not(.eclipse-admin-page)') === false,
    'admin pages must honor compact header');
hd_assert(strpos($menu, 'header-density-compact') === false,
    'optional Menu CSS must not own header density');
hd_assert(strpos($header, 'eclipse_theme_classes()') !== false,
    'saved density class included in body markup');
hd_assert(strpos($header, '{site_slogan}') !== false,
    'empty site slogan rendered without placeholder text');
echo "PASS: global compact header, admin parity and empty slogan" . PHP_EOL;
