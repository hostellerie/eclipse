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
            'selected' => false,
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
                    'selected' => true,
                    'resolved' => true,
                    'children' => array(),
                ),
                array(
                    'id' => 4,
                    'label' => '&lt;span class=&quot;uk-text-danger&quot;&gt;reCAPTCHA&lt;/span&gt; (N/A)',
                    'status' => 'warning',
                    'type' => 4,
                    'url' => 'https://example.test/admin/plugins/recaptcha/',
                    'target' => '',
                    'selected' => false,
                    'resolved' => true,
                    'children' => array(),
                ),
                array(
                    'id' => 5,
                    'label' => '&lt;strong&gt;Hello&lt;/strong&gt; (N/A)',
                    'status' => '',
                    'type' => 4,
                    'url' => 'https://example.test/admin/plugins/hello/',
                    'target' => '',
                    'selected' => false,
                    'resolved' => true,
                    'children' => array(),
                ),
            ),
        ),
        array(
            'id' => 6,
            'label' => 'Legacy callback',
            'type' => 7,
            'url' => '#',
            'target' => '',
            'selected' => false,
            'resolved' => false,
            'children' => array(),
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
eclipse_test_assert(strpos($html, 'eclipse-menu-active-trail') !== false, 'Active ancestor trail class missing');
eclipse_test_assert(strpos($html, 'aria-current="page"') !== false, 'aria-current missing');
eclipse_test_assert(substr_count($html, 'aria-current="page"') === 1, 'aria-current must only mark the actual current link');
eclipse_test_assert(strpos($html, 'eclipse-has-submenu') !== false, 'submenu class missing');
eclipse_test_assert(strpos($html, 'eclipse-menu-parent') !== false, 'submenu link class missing');
eclipse_test_assert(strpos($html, 'target="_blank"') !== false, 'target not preserved');
eclipse_test_assert(strpos($html, 'rel="noopener noreferrer"') !== false, 'noopener not added');
eclipse_test_assert(strpos($html, 'eclipse-menu-status-warning') !== false, 'semantic warning class missing');
eclipse_test_assert(strpos($html, 'reCAPTCHA (N/A)') !== false, 'warning label missing');
eclipse_test_assert(strpos($html, 'Hello (N/A)') !== false, 'generic encoded plugin label missing');
eclipse_test_assert(strpos($html, '&lt;span') === false, 'encoded warning markup must not be rendered as text');
eclipse_test_assert(strpos($html, '&lt;strong') === false, 'generic encoded markup must not be rendered as text');
eclipse_test_assert(strpos($html, 'uk-text-danger') === false, 'Geeklog presentation class must not leak into Eclipse output');
eclipse_test_assert(eclipse_menu_plain_label('&lt;em&gt;Example&lt;/em&gt;') === 'Example', 'encoded markup normalization failed');
eclipse_test_assert(eclipse_menu_node_status(array('status' => 'warning')) === 'warning', 'warning status not accepted');
eclipse_test_assert(eclipse_menu_node_status(array('status' => 'custom-class')) === '', 'unknown status must be ignored');
eclipse_test_assert(strpos($html, 'legacy-menu') === false, 'one unresolved callback must not force legacy rendering');
eclipse_test_assert(strpos($html, 'Legacy callback') === false, 'unresolved callback must be omitted from structured rendering');

function eclipse_test_unresolved_tree()
{
    return array(array('resolved' => false, 'children' => array()));
}

eclipse_test_assert(eclipse_menu_tree_is_resolved(eclipse_test_unresolved_tree()) === false, 'unresolved tree detection failed');
$filtered = eclipse_menu_filter_resolved_nodes(MENU_getResolvedTree('navigation'));
eclipse_test_assert(count($filtered) === 2, 'unresolved top-level node was not filtered');

// Geeklog 2.1.x compiles .thtml files into path_data/layout_cache. __DIR__ in a
// compiled template therefore points at the cache rather than layout/eclipse.
$header = file_get_contents(dirname(__DIR__) . '/eclipse/header.thtml');
eclipse_test_assert(strpos($header, "require_once __DIR__") === false, 'header.thtml must not require theme files through __DIR__');
eclipse_test_assert(strpos($header, "\$_CONF['path_layout']") !== false, 'header.thtml must use Geeklog path_layout for theme includes');

echo "Eclipse resolved Menu navigation tests passed" . PHP_EOL;
