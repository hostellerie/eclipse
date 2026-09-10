<?php

// Eclipse presentation bridge for the Geeklog Forum plugin.
// Forum/Geeklog will discover this file from layout/eclipse/forum/ when the
// plugin supports CTL plugin theme templates. Keep this file PHP 5.6 safe.

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.php') !== false) {
    die('This file can not be used on its own!');
}

/**
 * Add a cache-busting version to an Eclipse Forum stylesheet while keeping the
 * public path independent from the server filesystem layout.
 *
 * @param string $filename
 * @return string
 */
function eclipse_forum_css_file($filename)
{
    global $_CONF;

    $relative = '/layout/' . $_CONF['theme'] . '/forum/' . $filename;
    $path = rtrim($_CONF['path_layout'], '/\\') . '/forum/' . $filename;

    if (is_file($path)) {
        return $relative . '?v=' . rawurlencode((string) @filemtime($path));
    }

    return $relative;
}

/**
 * Forum's remaining Denim-compatible templates rely on Geeklog's bundled
 * UIkit resources. Geeklog 2.1.1 and 2.2.2 expose those resources differently,
 * so keep that compatibility isolated here instead of leaking version checks
 * into templates or presentation CSS.
 *
 * Geeklog 2.1.1 Denim loads uikit.gradient.min.css and the UIkit JavaScript as
 * a file. Geeklog 2.2.2 Denim loads uikit.min.css and registers UIkit through
 * Geeklog's JavaScript-library API.
 *
 * Keep forum.css out of this list: Forum resolves it itself on modern Geeklog
 * and Eclipse also imports it from css/plugins.css for the 2.1.x fallback.
 * semantic.css owns the primary public Forum presentation; editor.css styles
 * the Forum-owned submission templates without changing their POST contract;
 * reports.css keeps preferences/reports/moderation separate, and footer.css
 * handles secondary legend/rules/online information.
 *
 * @return array
 */
function forum_css_eclipse()
{
    global $LANG_DIRECTION;

    $direction = ($LANG_DIRECTION === 'rtl') ? '_rtl' : '';
    $legacy = !defined('VERSION') || version_compare(VERSION, '2.2.0', '<');
    $uikitFile = $legacy
        ? '/vendor/uikit/css' . $direction . '/uikit.gradient.min.css'
        : '/vendor/uikit/css' . $direction . '/uikit.min.css';

    return array(
        array(
            'name'       => 'uikit',
            'file'       => $uikitFile,
            'attributes' => array('media' => 'all'),
            'priority'   => 80
        ),
        array(
            'name'       => 'eclipse-forum-semantic',
            'file'       => eclipse_forum_css_file('semantic.css'),
            'attributes' => array('media' => 'all'),
            'priority'   => 320
        ),
        array(
            'name'       => 'eclipse-forum-editor',
            'file'       => eclipse_forum_css_file('editor.css'),
            'attributes' => array('media' => 'all'),
            'priority'   => 325
        ),
        array(
            'name'       => 'eclipse-forum-reports',
            'file'       => eclipse_forum_css_file('reports.css'),
            'attributes' => array('media' => 'all'),
            'priority'   => 330
        ),
        array(
            'name'       => 'eclipse-forum-footer',
            'file'       => eclipse_forum_css_file('footer.css'),
            'attributes' => array('media' => 'all'),
            'priority'   => 340
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
