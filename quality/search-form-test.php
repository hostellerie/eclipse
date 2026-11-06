<?php
/**
 * Static compatibility contracts for Geeklog's advanced search form.
 * This complements (but does not replace) interactive Geeklog 2.2.2 tests.
 * Run: php quality/search-form-test.php
 */
error_reporting(E_ALL);
$root = dirname(__DIR__);
$template = file_get_contents($root . '/eclipse/searchform.thtml');
$formsCss = file_get_contents($root . '/eclipse/css/forms.css');
if (!is_string($template) || !is_string($formsCss)) {
    fwrite(STDERR, "FAIL: search form sources unavailable\n");
    exit(1);
}
function searchAssert($condition, $description)
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL: search form ' . $description . PHP_EOL);
        exit(1);
    }
}
// Core expects a GET form with a named query, mode and submit control.
searchAssert(preg_match('~<form\\b[^>]*action="\\{site_url\\}/search\\.php"[^>]*method="get"~i', $template) === 1,
    'must submit to Geeklog search.php using GET');
searchAssert(substr_count($template, '<form ') === 1 && substr_count($template, '</form>') === 1,
    'must contain one complete form');
searchAssert(preg_match('~<input\\b[^>]*type="search"[^>]*value="\\{query\\}"[^>]*name="query"~', $template) === 1,
    'must preserve entered query');
searchAssert(preg_match('~<input\\b[^>]*type="hidden"[^>]*name="mode"[^>]*value="search"~', $template) === 1,
    'must preserve search mode');
searchAssert(strpos($template, '<button type="submit"') !== false, 'must keep visible submit button');

// Filter disclosure must be collapsed by default, and all Geeklog field
// names must stay inside the form so selections reach search.php.
searchAssert(preg_match('~<details\\s+class="eclipse-search-options"\\s*>~', $template) === 1,
    'filters must default to collapsed');
foreach (array('keyType','title','datestart','dateend','topic','type','author','results') as $field) {
    searchAssert(strpos($template, 'name="' . $field . '"') !== false,
        'missing Geeklog filter: ' . $field);
}
foreach (array('{key_phrase_selected}', '{key_all_selected}', '{key_any_selected}',
    '{title_checked}', '{topic_option_list}', '{plugin_types}',
    '{author_form_element}', '{author_option_list}', '{search_limits}') as $token) {
    searchAssert(strpos($template, $token) !== false, 'missing template token: ' . $token);
}
searchAssert(strpos($template, '<!-- BEGIN author_form_element -->') !== false
    && strpos($template, '<!-- BEGIN author_form_element_disabled -->') !== false,
    'author filter compatibility blocks missing');
searchAssert(strpos($template, 'name="author" value="0"') !== false,
    'disabled author filter must submit default value');
searchAssert(strpos($formsCss, '.eclipse-search-primary-row') !== false
    && strpos($formsCss, '.eclipse-search-grid') !== false
    && strpos($formsCss, '@media(max-width:45rem)') !== false,
    'public/mobile layout contracts missing');
echo "Eclipse search form compatibility tests passed\n";
