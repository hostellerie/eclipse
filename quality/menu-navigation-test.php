<?php

define('VERSION', '2.2.2');


define('PLG_RET_OK', 0);

$GLOBALS['eclipse_test_service_calls'] = array();

function eclipse_data_json($name, $fallback = array())
{
    if ($name === 'eclipse-settings.json') {
        return array(
            'menu_primary' => 'navigation',
            'menu_secondary' => 'Menu secondaire été',
            'menu_footer' => 'footer',
        );
    }
    return $fallback;
}


function PLG_invokeService($type, $action, $args, &$output, &$svc_msg)
{
    $GLOBALS['eclipse_test_service_calls'][] = $action;
    $svc_msg = array();

    if ($type !== 'menu') {
        return -1;
    }

    if ($action === 'getMenuList') {
        $output = array(
            'provider' => 'menu',
            'provider_family' => 'navigation',
            'provider_contract_version' => 1,
            'menus' => array(
                array('id' => 1, 'name' => 'navigation', 'type' => 1),
                array('id' => 2, 'name' => 'footer', 'type' => 2),
                array('id' => 6, 'name' => 'Menu secondaire été', 'type' => 1),
            ),
        );
        return PLG_RET_OK;
    }

    if ($action === 'getMenuTree') {
        $name = isset($args['name']) ? $args['name'] : '';
        $output = array(
            'provider' => 'menu',
            'provider_family' => 'navigation',
            'name' => $name,
            'provider_contract_version' => 1,
            'nodes' => MENU_getResolvedTree($name),
        );
        return PLG_RET_OK;
    }

    return -1;
}

function eclipse_menu_plugin_active()
{
    return true;
}


function MENU_getAvailableMenus()
{
    return array(
        array('id' => 1, 'name' => 'navigation', 'type' => 1),
        array('id' => 2, 'name' => 'footer', 'type' => 2),
        array('id' => 6, 'name' => 'Menu secondaire été', 'type' => 1),
    );
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

$menus = eclipse_menu_available_menus();
eclipse_test_assert(count($menus) === 3, 'Menu service discovery count mismatch');
eclipse_test_assert($menus[0]['name'] === 'navigation', 'Primary menu discovery missing');
eclipse_test_assert($menus[1]['name'] === 'footer', 'Footer menu discovery missing');
eclipse_test_assert($menus[2]['name'] === 'Menu secondaire été', 'UTF-8/spaced menu discovery missing');

eclipse_test_assert(eclipse_menu_slot_name('primary') === 'navigation', 'Persisted primary slot was not used');
eclipse_test_assert(eclipse_menu_slot_name('secondary') === 'Menu secondaire été', 'Persisted secondary slot was not used');
eclipse_test_assert(eclipse_menu_slot_name('footer') === 'footer', 'Persisted footer slot was not used');

$footerHtml = eclipse_menu_render('footer', 'footer');
eclipse_test_assert(strpos($footerHtml, 'eclipse-menu-context-footer') !== false, 'Footer context class missing');
eclipse_test_assert(strpos($footerHtml, 'eclipse-menu-root-footer') !== false, 'Footer root context class missing');
eclipse_test_assert(strpos($footerHtml, 'data-menu-name="footer"') !== false, 'Rendered menu name metadata missing');

$secondaryHtml = eclipse_menu_render('navigation', 'secondary');
eclipse_test_assert(strpos($secondaryHtml, 'eclipse-menu-context-secondary') !== false, 'Secondary context class missing');
eclipse_test_assert(strpos($secondaryHtml, 'eclipse-menu-root-secondary') !== false, 'Secondary root context class missing');


$namedSecondaryHtml = eclipse_menu_render('Menu secondaire été', 'secondary');
eclipse_test_assert(strpos($namedSecondaryHtml, 'data-menu-name="Menu secondaire été"') !== false, 'Spaced UTF-8 menu name was not preserved for rendering');

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

$headerTemplate = file_get_contents(dirname(__DIR__) . '/eclipse/header.thtml');
eclipse_test_assert(strpos($headerTemplate, 'eclipse_menu_debug_comment()') !== false, 'Root-only Menu diagnostics are not wired into header');
$footerTemplate = file_get_contents(dirname(__DIR__) . '/eclipse/footer.thtml');
$previewProvider = file_get_contents(dirname(__DIR__) . '/eclipse/includes/plugin-presentation-preview.php');
eclipse_test_assert(strpos($previewProvider, "eclipse_menu_render(\$resource, \$menuContext)") !== false, 'Theme preview must render the requested Menu resource');
eclipse_test_assert(strpos($previewProvider, "case 2:") !== false && strpos($previewProvider, "\$menuContext = 'footer'") !== false, 'Horizontal simple menus must preview in footer context');
eclipse_test_assert(strpos($previewProvider, "case 3:") !== false && strpos($previewProvider, "\$menuContext = 'sidebar'") !== false, 'Vertical menus must preview in sidebar context');
eclipse_test_assert(strpos($previewProvider, "strcasecmp((string) \$resource, 'navigation')") === false, 'Eclipse preview must not be restricted to navigation');

eclipse_test_assert(strpos($headerTemplate, "eclipse_menu_render_slot('secondary')") !== false, 'Secondary Menu slot is not wired into header');
eclipse_test_assert(strpos($footerTemplate, "eclipse_menu_render_slot('footer')") !== false, 'Footer Menu slot is not wired into footer');

// Geeklog 2.1.x compiles .thtml files into path_data/layout_cache. __DIR__ in a
// compiled template therefore points at the cache rather than layout/eclipse.
$header = file_get_contents(dirname(__DIR__) . '/eclipse/header.thtml');
eclipse_test_assert(strpos($header, "require_once __DIR__") === false, 'header.thtml must not require theme files through __DIR__');
eclipse_test_assert(strpos($header, "\$_CONF['path_layout']") !== false, 'header.thtml must use Geeklog path_layout for theme includes');

eclipse_test_assert(!in_array('getMenuList', $GLOBALS['eclipse_test_service_calls'], true), 'Eclipse should prefer direct Menu list API when available');
eclipse_test_assert(!in_array('getMenuTree', $GLOBALS['eclipse_test_service_calls'], true), 'Eclipse should prefer direct Menu tree API when available');

echo "Eclipse resolved Menu navigation tests passed" . PHP_EOL;
