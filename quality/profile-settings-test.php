<?php
/** Native Geeklog user settings and legacy tooltip compatibility. */
$root = dirname(__DIR__);
$css = file_get_contents($root . '/eclipse/css/forms.css');
$header = file_get_contents($root . '/eclipse/header.thtml');
function profileAssert($condition, $message) {
    if (!$condition) { fwrite(STDERR, 'FAIL profile: ' . $message . PHP_EOL); exit(1); }
}
profileAssert(is_file($root . '/eclipse/images/tooltips/tooltip.png'),
    'native Geeklog tooltip PNG missing');
profileAssert(is_file($root . '/eclipse/images/tooltips/tooltip.svg'),
    'SVG tooltip missing');
profileAssert(strpos($css, '#pe_navbar #navlist>li>a') !== false,
    'native tab anchors not styled');
profileAssert(strpos($css, '#profile .form_block') !== false,
    'profile form layout missing');
profileAssert(strpos($css, '@media(max-width:43rem)') !== false,
    'mobile profile form layout missing');
profileAssert(strpos($header, '<meta charset="{charset}"') !== false,
    'theme must honor Geeklog charset, not force a different encoding');
profileAssert(substr_count($css, '{') === substr_count($css, '}'),
    'CSS block delimiters do not balance');
echo "Eclipse native profile settings regression checks passed\n";
