<?php

// Eclipse presentation bridge for the Geeklog Forum plugin.
// Forum/Geeklog will discover this file from layout/eclipse/forum/ when the
// plugin supports CTL plugin theme templates. Keep this file PHP 5.6 safe.

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.php') !== false) {
    die('This file can not be used on its own!');
}

/**
 * Forum's Denim-compatible templates rely on UIkit classes and FontAwesome
 * icons provided by Geeklog's bundled UIkit stylesheet. Eclipse does not load
 * Denim's theme_css_denim() callback, so register the core UIkit stylesheet
 * here while the Forum templates are migrated to Eclipse-native markup.
 *
 * Keep forum.css out of this list: Forum resolves it itself on modern Geeklog
 * and Eclipse also imports it from css/plugins.css for Geeklog 2.1.x fallback.
 * semantic.css owns the primary public Forum presentation; reports.css keeps
 * preferences/reports/moderation separate, and footer.css handles secondary
 * legend/rules/online information without UIkit layout dependencies.
 *
 * @return array
 */
function forum_css_eclipse()
{
    global $_CONF, $LANG_DIRECTION;

    $direction = ($LANG_DIRECTION === 'rtl') ? '_rtl' : '';

    return array(
        array(
            'name'       => 'uikit',
            'file'       => '/vendor/uikit/css' . $direction . '/uikit.min.css',
            'attributes' => array('media' => 'all'),
            'priority'   => 80
        ),
        array(
            'name'       => 'eclipse-forum-semantic',
            'file'       => '/layout/' . $_CONF['theme'] . '/forum/semantic.css',
            'attributes' => array('media' => 'all'),
            'priority'   => 320
        ),
        array(
            'name'       => 'eclipse-forum-reports',
            'file'       => '/layout/' . $_CONF['theme'] . '/forum/reports.css',
            'attributes' => array('media' => 'all'),
            'priority'   => 330
        ),
        array(
            'name'       => 'eclipse-forum-footer',
            'file'       => '/layout/' . $_CONF['theme'] . '/forum/footer.css',
            'attributes' => array('media' => 'all'),
            'priority'   => 340
        )
    );
}

/**
 * Forum's dropdowns and other Denim-compatible controls require the same
 * Geeklog JavaScript libraries loaded by the Denim theme. These can be reduced
 * later as Eclipse replaces the remaining UIkit-dependent Forum templates.
 *
 * @return array
 */
function forum_js_libs_eclipse()
{
    return array(
        array(
            'library' => 'jquery',
            'footer'  => false
        ),
        array(
            'library' => 'uikit',
            'footer'  => false
        ),
        array(
            'library' => 'uikit_modifier',
            'footer'  => false
        )
    );
}

/**
 * No additional Forum-specific JavaScript files are required by Eclipse.
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
