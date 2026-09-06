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
    return json_encode($output, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
}

function eclipse_translate_theme_studio_html($html)
{
    if (eclipse_language_file_name() === 'english' || $html === '') {
        return $html;
    }

    $map = array(
        'Theme studio' => 'theme_studio_title',
        'Back to dashboard' => 'back_to_dashboard',
        'Theme Studio sections' => 'theme_studio',
        'Design' => 'design',
        'Preview' => 'preview',
        'Updates' => 'updates',
        'Documentation' => 'documentation',
        'Palette' => 'palette',
        'Layout and type' => 'layout_and_type',
        'Appearance' => 'appearance',
        'Restore defaults' => 'restore_defaults',
        'Save complete Eclipse state' => 'save_complete_state',
        'Install an Eclipse archive' => 'install_archive',
        'Install update' => 'install_update',
        'Archive ZIP' => 'archive_zip',
        'Local update' => 'local_update',
        'Cancel preview' => 'cancel_preview',
        'No unsaved changes' => 'no_unsaved_changes',
        'Preview width' => 'preview_width',
        'Desktop' => 'desktop',
        'Tablet' => 'tablet',
        'Mobile' => 'mobile',
        'Discover Theme Studio' => 'discover_theme_studio'
    );

    $search = array();
    $replace = array();
    foreach ($map as $english => $key) {
        $search[] = $english;
        $replace[] = htmlspecialchars(eclipse_lang($key, $english), ENT_QUOTES, 'UTF-8');
    }

    return str_replace($search, $replace, $html);
}
