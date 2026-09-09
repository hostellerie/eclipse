<?php

// Eclipse presentation bridge for the Geeklog Forum plugin.
// Forum/Geeklog will discover this file from layout/eclipse/forum/ when the
// plugin supports CTL plugin theme templates. Keep this file PHP 5.6 safe.

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.php') !== false) {
    die('This file can not be used on its own!');
}

/**
 * Add the Eclipse-specific Forum presentation layers.
 *
 * @return array
 */
function forum_css_eclipse()
{
    global $_CONF;

    $layoutUrl = rtrim($_CONF['layout_url'], '/');

    return array(
        array(
            'name'       => 'eclipse-forum',
            'file'       => $layoutUrl . '/forum/forum.css',
            'attributes' => array('media' => 'all'),
            'priority'   => 275
        ),
        array(
            'name'       => 'eclipse-forum-blocks',
            'file'       => $layoutUrl . '/forum/blocks.css',
            'attributes' => array('media' => 'all'),
            'priority'   => 276
        )
    );
}

/**
 * Eclipse already provides the JavaScript libraries required by the Denim
 * compatible Forum templates.
 *
 * @return array
 */
function forum_js_libs_eclipse()
{
    return array();
}

/**
 * No Forum-specific JavaScript is required by the Eclipse skin.
 *
 * @return array
 */
function forum_js_files_eclipse()
{
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
