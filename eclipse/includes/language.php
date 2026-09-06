<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'language.php') !== false) {
    die('This file can not be used on its own!');
}

function eclipse_language_file_name()
{
    global $_CONF, $LANG_ISO639_1;

    $language = isset($_CONF['language']) ? strtolower((string) $_CONF['language']) : '';
    if (strpos($language, 'french') === 0 || (!empty($LANG_ISO639_1) && strtolower($LANG_ISO639_1) === 'fr')) {
        return 'french';
    }

    return 'english';
}

function eclipse_load_language()
{
    global $LANG_ECLIPSE;

    static $loaded = false;
    if ($loaded && isset($LANG_ECLIPSE) && is_array($LANG_ECLIPSE)) {
        return $LANG_ECLIPSE;
    }

    $languageDir = dirname(__DIR__) . '/language/';
    $LANG_ECLIPSE = array();
    if (is_file($languageDir . 'english.php')) {
        include $languageDir . 'english.php';
    }
    $english = is_array($LANG_ECLIPSE) ? $LANG_ECLIPSE : array();

    $selected = eclipse_language_file_name();
    if ($selected !== 'english' && is_file($languageDir . $selected . '.php')) {
        $LANG_ECLIPSE = array();
        include $languageDir . $selected . '.php';
        $translated = is_array($LANG_ECLIPSE) ? $LANG_ECLIPSE : array();
        $LANG_ECLIPSE = array_merge($english, $translated);
    } else {
        $LANG_ECLIPSE = $english;
    }

    $loaded = true;
    return $LANG_ECLIPSE;
}

function eclipse_lang($key, $fallback = '')
{
    $strings = eclipse_load_language();
    if (isset($strings[$key])) {
        return $strings[$key];
    }
    return $fallback !== '' ? $fallback : $key;
}

function eclipse_lang_js()
{
    $strings = eclipse_load_language();
    $keys = array(
        'administration', 'navigation', 'view_site', 'menu', 'close_menu',
        'collapse_navigation', 'expand_navigation', 'theme_studio', 'studio',
        'cms_overview', 'needs_attention', 'nothing_needs_attention',
        'quick_actions', 'no_quick_action', 'write_article', 'create_static_page',
        'manage_comments', 'review_submissions', 'add_block', 'add_user'
    );
    $output = array();
    foreach ($keys as $key) {
        if (isset($strings[$key])) {
            $output[$key] = $strings[$key];
        }
    }
    return json_encode($output);
}
