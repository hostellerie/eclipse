<?php

define('VERSION', '2.2.2');

function eclipse_menu_plugin_active()
{
    return true;
}

function MENU_getResolvedTree($name)
{
    return array(
        array(
            'id' => 1,
            'label' => 'Home',
            'type' => 2,
            'url' => 'https://example.test/',
            'target' => '',
            'selected' => true,
            'resolved' => true,
            'children' => array(),
        ),
        array(
            'id' => 2,
            'label' => 'Submenu',
            'type' => 1,
            'url' => '#',
            'target' => '',
            'selected' => false,
            'resolved' => true,
            'children' => array(
                array(
                    'id' => 3,
                    'label' => 'External',
                    'type' => 6,
                    'url' => 'https://external.test/',
                    'target' => '_blank',
                    'selected' => false,
                    'resolved' => true,
                    'children' => array(),
                ),
            ),
        ),
    );
}

function MENU_getMenu()
{
    return '<div class="legacy-menu">legacy</div>';
}

require_once dirname(__DIR__) . '/eclipse/includes/menu-navigation.php';

function eclipse_test_assert($condition, $message)
{
    if (!$condition) {
        fwrite(STDERR, 'FAIL: ' . $message . PHP_EOL);
        exit(1);
    }
}

$html = eclipse_menu_navigation_resolved();

eclipse_test_assert(strpos($html, 'class="eclipse-menu"') !== false, 'Eclipse wrapper missing');
eclipse_test_assert(strpos($html, 'class="eclipse-menu-root"') !== false, 'Root list class missing');
eclipse_test_assert(strpos($html, 'eclipse-menu-current') !== false, 'Current item class missing');
eclipse_test_assert(strpos($html, 'aria-current="page"') !== false, 'aria-current missing');
eclipse_test_assert(strpos($html, 'eclipse-has-submenu') !== false, 'submenu class missing');
eclipse_test_assert(strpos($html, 'eclipse-menu-parent') !== false, 'submenu link class missing');
eclipse_test_assert(strpos($html, 'target="_blank"') !== false, 'target not preserved');
eclipse_test_assert(strpos($html, 'rel="noopener noreferrer"') !== false, 'noopener not added');
eclipse_test_assert(strpos($html, 'legacy-menu') === false, 'resolved tree unexpectedly used legacy renderer');

function eclipse_test_unresolved_tree()
{
    return array(array('resolved' => false, 'children' => array()));
}

eclipse_test_assert(eclipse_menu_tree_is_resolved(eclipse_test_unresolved_tree()) === false, 'unresolved tree detection failed');

// Geeklog 2.1.x compiles .thtml files into path_data/layout_cache. __DIR__ in a
// compiled template therefore points at the cache rather than layout/eclipse.
$header = file_get_contents(dirname(__DIR__) . '/eclipse/header.thtml');
eclipse_test_assert(strpos($header, "require_once __DIR__") === false, 'header.thtml must not require theme files through __DIR__');
eclipse_test_assert(strpos($header, "\$_CONF['path_layout']") !== false, 'header.thtml must use Geeklog path_layout for theme includes');

echo "Eclipse resolved Menu navigation tests passed" . PHP_EOL;
