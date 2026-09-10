<?php

// Eclipse presentation bridge for the Geeklog Forum plugin.
// Forum/Geeklog will discover this file from layout/eclipse/forum/ when the
// plugin supports CTL plugin theme templates. Keep this file PHP 5.6 safe.

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.php') !== false) {
    die('This file can not be used on its own!');
}

/**
 * Forum's remaining Denim-compatible templates rely on Geeklog's bundled
 * UIkit resources. Geeklog 2.1.1 and 2.2.2 expose those resources differently,
 * so keep that compatibility isolated here instead of leaking version checks
 * into templates or presentation CSS.
 *
 * All Eclipse Forum presentation CSS is reached through forum.css. Forum loads
 * that stylesheet itself on normal Forum pages, while Eclipse imports it from
 * css/plugins.css as the Geeklog 2.1.x fallback. Keeping one CSS entrypoint
 * avoids depending on whether a particular Forum release invokes this theme
 * hook for every public screen.
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
