<?php

// Eclipse presentation bridge for the Geeklog Forum plugin.
// Forum/Geeklog will discover this file from layout/eclipse/forum/ when the
// plugin supports CTL plugin theme templates. Keep this file PHP 5.6 safe.

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.php') !== false) {
    die('This file can not be used on its own!');
}

/**
 * Resolve a numeric Forum request parameter across Geeklog generations.
 *
 * Some Forum/Geeklog combinations normalize request variables before the
 * theme template is rendered. Read the normal superglobals first, then fall
 * back to QUERY_STRING / REQUEST_URI so presentation logic does not depend on
 * one particular request-population path.
 *
 * @param string $name
 * @return int
 */
function eclipse_forum_request_id($name)
{
    $allowed = array('category', 'forum', 'showtopic');
    if (!in_array($name, $allowed, true)) {
        return 0;
    }

    $sources = array();
    if (isset($_GET[$name])) {
        $sources[] = $_GET[$name];
    }
    if (isset($_REQUEST[$name])) {
        $sources[] = $_REQUEST[$name];
    }

    foreach ($sources as $value) {
        if (is_scalar($value) && preg_match('/^[0-9]+$/', (string) $value)) {
            return (int) $value;
        }
    }

    $queryStrings = array();
    if (!empty($_SERVER['QUERY_STRING'])) {
        $queryStrings[] = (string) $_SERVER['QUERY_STRING'];
    }
    if (!empty($_SERVER['REQUEST_URI'])) {
        $query = parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_QUERY);
        if (is_string($query) && $query !== '') {
            $queryStrings[] = $query;
        }
    }

    foreach ($queryStrings as $queryString) {
        $params = array();
        parse_str($queryString, $params);
        if (isset($params[$name]) && is_scalar($params[$name]) && preg_match('/^[0-9]+$/', (string) $params[$name])) {
            return (int) $params[$name];
        }
    }

    return 0;
}

/**
 * Forum's remaining Denim-compatible templates rely on Geeklog's bundled
 * UIkit resources. Geeklog 2.1.1 and 2.2.2 expose those resources differently,
 * so keep that compatibility isolated here instead of leaking version checks
 * into templates or presentation CSS.
 *
 * Register Eclipse Forum styles explicitly on Forum pages. The shared
 * css/plugins.css import remains as a Geeklog 2.1.x fallback for Forum blocks
 * rendered outside the Forum route, but it cannot propagate Eclipse's cache
 * key to nested @imports. Explicit versioned URLs make Forum presentation
 * updates visible without manually clearing the browser cache.
 *
 * @return array
 */
function forum_css_eclipse()
{
    global $_CONF, $LANG_DIRECTION;

    $direction = ($LANG_DIRECTION === 'rtl') ? '_rtl' : '';
    $legacy = !defined('VERSION') || version_compare(VERSION, '2.2.0', '<');
    $uikitFile = $legacy
        ? '/vendor/uikit/css' . $direction . '/uikit.gradient.min.css'
        : '/vendor/uikit/css' . $direction . '/uikit.min.css';

    // Geeklog 2.2.x treats query strings on local resource paths as part of
    // the filesystem path. Use same-origin absolute URLs there, matching the
    // main Eclipse theme resource strategy.
    $modernResource = defined('VERSION') && version_compare(VERSION, '2.2.0', '>=');
    $resourceRoot = $modernResource && !empty($_CONF['site_url'])
        ? rtrim($_CONF['site_url'], '/')
        : '';
    $theme = !empty($_CONF['theme']) ? $_CONF['theme'] : 'eclipse';
    $cacheVersion = function_exists('eclipse_theme_version')
        ? eclipse_theme_version()
        : '1.1.0';
    $version = '?v=' . rawurlencode($cacheVersion);
    $forumRoot = $resourceRoot . '/layout/' . $theme . '/forum/';

    return array(
        array(
            'name'       => 'uikit',
            'file'       => $uikitFile,
            'attributes' => array('media' => 'all'),
            'priority'   => 80
        ),
        array(
            'name'       => 'eclipse-forum',
            'file'       => $forumRoot . 'forum.css' . $version,
            'attributes' => array('media' => 'all'),
            'priority'   => 320
        ),
        array(
            'name'       => 'eclipse-forum-blocks',
            'file'       => $forumRoot . 'blocks.css' . $version,
            'attributes' => array('media' => 'all'),
            'priority'   => 321
        ),
        array(
            'name'       => 'eclipse-forum-semantic',
            'file'       => $forumRoot . 'semantic.css' . $version,
            'attributes' => array('media' => 'all'),
            'priority'   => 322
        ),
        array(
            'name'       => 'eclipse-forum-editor',
            'file'       => $forumRoot . 'editor.css' . $version,
            'attributes' => array('media' => 'all'),
            'priority'   => 323
        ),
        array(
            'name'       => 'eclipse-forum-reports',
            'file'       => $forumRoot . 'reports.css' . $version,
            'attributes' => array('media' => 'all'),
            'priority'   => 324
        ),
        array(
            'name'       => 'eclipse-forum-footer',
            'file'       => $forumRoot . 'footer.css' . $version,
            'attributes' => array('media' => 'all'),
            'priority'   => 325
        )
    );
}

/**
 * Return only the JavaScript libraries that exist natively on the active
 * Geeklog generation. UIkit itself is a plain JS file on Geeklog 2.1.1.
 *
 * @return array
 */
function forum_js_libs_eclipse()
{
    $result = array(
        array(
            'library' => 'jquery',
            'footer'  => false
        )
    );

    if (defined('VERSION') && version_compare(VERSION, '2.2.0', '>=')) {
        $result[] = array(
            'library' => 'uikit',
            'footer'  => false
        );
        $result[] = array(
            'library' => 'uikit_modifier',
            'footer'  => false
        );
    }

    return $result;
}

/**
 * Geeklog 2.1.1 Denim loads UIkit as a normal JavaScript file. Newer Geeklog
 * versions register it as a library, so no direct file is needed there.
 *
 * @return array
 */
function forum_js_files_eclipse()
{
    if (!defined('VERSION') || version_compare(VERSION, '2.2.0', '<')) {
        return array(
            array(
                'file'     => '/vendor/uikit/js/uikit.js',
                'footer'   => false,
                'priority' => 100
            )
        );
    }

    return array();
}

/**
 * Theme-item hook kept available for future Forum template refinements.
 *
 * @param string $item
 * @return string
 */
function forum_getThemeItem_eclipse($item)
{
    return '';
}
