<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'functions.php') !== false) {
    die('This file can not be used on its own!');
}

require_once __DIR__ . '/includes/admin-dashboard.php';

function theme_config_eclipse()
{
    global $_CONF;
    $minimumThemeVersion = !empty($_CONF['min_theme_gl_version']) ? $_CONF['min_theme_gl_version'] : '2.0.0';
    return array(
        'theme_name'             => 'Eclipse',
        'theme_version'          => eclipse_theme_version(),
        'theme_gl_version'       => $minimumThemeVersion,
        'theme_description'      => 'Responsive Eclipse child theme with a dedicated administration presentation.',
        'theme_author'           => 'Eclipse theme contributors',
        'theme_homepage'         => 'https://github.com/hostellerie/eclipse',
        'theme_license'          => 'GPL-2.0+',
        'image_type'              => 'svg',
        'doctype'                 => 'html5',
        'etag'                    => false,
        'supported_version_theme' => $minimumThemeVersion,
        'theme_default'           => 'denim',
    );
}

function eclipse_menu_plugin_active()
{
    global $_PLUGINS;
    return isset($_PLUGINS) && is_array($_PLUGINS) && in_array('menu', $_PLUGINS, true);
}

function eclipse_menu_navigation()
{
    if (!eclipse_menu_plugin_active() || !function_exists('MENU_getMenu')) {
        return '';
    }
    return MENU_getMenu('navigation', 'eclipse-menu', 'eclipse-menu-root',
        'eclipse-menu-item', 'eclipse-menu-parent', 'eclipse-menu-last',
        'eclipse-menu-current', 1);
}

function eclipse_is_admin_request()
{
    $path = eclipse_request_path();
    return preg_match('#(?:^|/)admin(?:/|$)#', $path) === 1;
}

function eclipse_request_path()
{
    $path = isset($_SERVER['PHP_SELF']) ? (string) $_SERVER['PHP_SELF'] : '';
    return str_replace('\\', '/', strtolower($path));
}

function eclipse_admin_page()
{
    if (!eclipse_is_admin_request()) return '';
    $page = basename(eclipse_request_path());
    return preg_replace('/[^a-z0-9_-]/', '', preg_replace('/\.php$/', '', $page));
}

function eclipse_is_story_editor()
{
    $page = eclipse_admin_page();
    return $page === 'story' || $page === 'article';
}

function eclipse_is_configuration_page()
{
    return eclipse_admin_page() === 'configuration';
}

function eclipse_supports_admin_list()
{
    return defined('VERSION') && version_compare(VERSION, '2.2.0', '>=');
}

function eclipse_context_classes()
{
    if (!eclipse_is_admin_request()) return 'eclipse-public-page';
    $page = eclipse_admin_page();
    return 'eclipse-admin-page' . ($page !== '' ? ' eclipse-admin-' . $page : '');
}

function eclipse_html_language()
{
    global $_CONF, $LANG_ISO639_1;
    $options = eclipse_theme_options();
    $configured = isset($options['html_lang']) ? trim($options['html_lang']) : 'auto';
    if ($configured !== 'auto' && preg_match('/^[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*$/', $configured)) {
        return htmlspecialchars($configured, ENT_QUOTES, 'UTF-8');
    }
    if (!empty($LANG_ISO639_1) && preg_match('/^[a-zA-Z]{2,3}$/', $LANG_ISO639_1)) return strtolower($LANG_ISO639_1);
    $language = isset($_CONF['language']) ? strtolower((string) $_CONF['language']) : '';
    $names = array('french' => 'fr', 'english' => 'en', 'german' => 'de', 'spanish' => 'es', 'italian' => 'it', 'dutch' => 'nl', 'portuguese' => 'pt');
    foreach ($names as $name => $code) if (strpos($language, $name) === 0) return $code;
    return 'en';
}

function eclipse_sitemap_header_link()
{
    global $_CONF;
    if (eclipse_is_admin_request() || empty($_CONF['site_url'])) return '';
    $options = eclipse_theme_options();
    $configured = isset($options['sitemap_path']) ? trim($options['sitemap_path']) : '';
    $file = $configured !== '' ? $configured : (isset($_CONF['sitemap_file']) ? $_CONF['sitemap_file'] : '');
    $file = trim(str_replace('\\', '/', (string) $file));
    if ($file === '' || strpos($file, '..') !== false || preg_match('/[\x00-\x1f]/', $file)) return '';
    $url = preg_match('#^https?://#i', $file) ? $file : rtrim($_CONF['site_url'], '/') . '/' . ltrim($file, '/');
    return '<link rel="sitemap" type="application/xml" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">' . "\n";
}

function eclipse_adsense_header_script()
{
    $options = eclipse_theme_options();
    $client = isset($options['adsense_client']) ? trim($options['adsense_client']) : '';
    if (eclipse_is_admin_request() || empty($options['adsense_enabled']) || !preg_match('/^ca-pub-[0-9]{10,20}$/', $client)) return '';
    return '<!-- Google AdSense -->' . "\n" . '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=' . rawurlencode($client) . '" crossorigin="anonymous"></script>' . "\n";
}

function eclipse_topic_heading()
{
    global $_TABLES;
    $options = eclipse_theme_options();
    $script = isset($_SERVER['PHP_SELF']) ? basename($_SERVER['PHP_SELF']) : '';
    if (empty($options['topic_h1_enabled']) || $script !== 'index.php' || empty($_GET['topic'])) return '';
    $topicId = trim((string) $_GET['topic']);
    if ($topicId === '' || !preg_match('/^[a-zA-Z0-9_-]+$/', $topicId)) return '';
    $label = '';
    if (isset($_TABLES['topics']) && function_exists('DB_getItem')) {
        $safeId = function_exists('DB_escapeString') ? DB_escapeString($topicId) : addslashes($topicId);
        $label = (string) DB_getItem($_TABLES['topics'], 'topic', "tid = '" . $safeId . "'");
    }
    if ($label === '') $label = ucwords(str_replace(array('-', '_'), ' ', $topicId));
    return '<h1 class="eclipse-topic-heading">' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</h1>';
}

function theme_css_eclipse()
{
    global $_CONF, $LANG_DIRECTION;
    // Geeklog 2.2.x validates query strings as part of a local filesystem path.
    // A same-origin absolute URL follows its external-resource branch instead,
    // preserving a real browser cache key even when Resource cache is disabled.
    $modernResource = defined('VERSION') && version_compare(VERSION, '2.2.0', '>=');
    $resourceRoot = $modernResource && !empty($_CONF['site_url']) ? rtrim($_CONF['site_url'], '/') : '';
    $version = '?v=1.0.0';
    $requestPath = eclipse_request_path();
    $isAdmin = eclipse_is_admin_request();
    $isAdminDashboard = $isAdmin && eclipse_admin_page() === 'index';
    $isStoryEditor = eclipse_is_story_editor();
    $isCommentPage = !$isAdmin && (substr($requestPath, -12) === '/article.php' || substr($requestPath, -12) === '/comment.php');

    $cssFiles = array(
        array('name' => 'denim-base', 'file' => $resourceRoot . '/layout/' . $_CONF['theme_default'] . '/css_' . $LANG_DIRECTION . '/style.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 100),
        array('name' => 'eclipse-variables', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/variables.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 200),
        array('name' => 'eclipse-base', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/base.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 210),
        array('name' => 'eclipse-layout', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/layout.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 220),
        array('name' => 'eclipse-components', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/components.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 230),
        array('name' => 'eclipse-forms', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/forms.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 240),
        array('name' => 'eclipse-plugins', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/plugins.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 250),
        array('name' => 'eclipse-responsive', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/responsive.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 260),
        array('name' => 'eclipse-modern', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/modern.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 270),
        array('name' => 'eclipse-ui-fixes', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/ui-fixes.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 290),
        array('name' => 'eclipse-v3', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/v3.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 300),
        array('name' => 'eclipse-footer-links', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/footer-links.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 305),
    );
    if ($isAdmin) {
        $cssFiles[] = array('name' => 'eclipse-admin', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/admin/admin.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 307);
    }
    if ($isAdminDashboard) {
        $cssFiles[] = array('name' => 'eclipse-studio', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/studio.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 280);
    }
    if ($isStoryEditor) {
        $cssFiles[] = array('name' => 'eclipse-story-editor', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/story-editor.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 310);
    }
    if ($isCommentPage) {
        $cssFiles[] = array('name' => 'eclipse-comments', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/comments.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 310);
    }
    if (eclipse_menu_plugin_active()) {
        $cssFiles[] = array('name' => 'eclipse-menu', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/menu.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 315);
        $cssFiles[] = array('name' => 'eclipse-menu-refinements', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/menu-refinements.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 316);
    }
    return $cssFiles;
}

function theme_js_libs_eclipse()
{
    return array();
}

function theme_js_files_eclipse()
{
    global $_CONF;
    $modernResource = defined('VERSION') && version_compare(VERSION, '2.2.0', '>=');
    $resourceRoot = $modernResource && !empty($_CONF['site_url']) ? rtrim($_CONF['site_url'], '/') : '';
    $version = '?v=1.0.0';
    $files = array(array('file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/js/theme.js' . $version, 'footer' => true, 'priority' => 100));
    if (eclipse_is_admin_request()) {
        $files[] = array('file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/js/admin.js' . $version, 'footer' => true, 'priority' => 110);
    }
    return $files;
}

function theme_init_eclipse()
{
    global $_BLOCK_TEMPLATE, $_CONF, $TEMPLATE_OPTIONS;
    $_CONF['left_blocks_in_footer'] = 1;
    // Geeklog 2.2 resolves child-theme fallbacks through theme_default and
    // CTL_core_templatePath.  The legacy override would force Denim ahead of
    // Eclipse in template resolution, so retain it only for Geeklog 2.1.x.
    if (!defined('VERSION') || version_compare(VERSION, '2.2.0', '<')) {
        $TEMPLATE_OPTIONS['override'] = 'denim';
    }
    $_BLOCK_TEMPLATE['_msg_block'] = 'blockheader-message.thtml,blockfooter-message.thtml';
    $_BLOCK_TEMPLATE['configmanager_block'] = 'blockheader-config.thtml,blockfooter-config.thtml';
    $_BLOCK_TEMPLATE['configmanager_subblock'] = 'blockheader-config.thtml,blockfooter-config.thtml';
    $_BLOCK_TEMPLATE['whats_related_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';
    $_BLOCK_TEMPLATE['story_options_block'] = 'blockheader-related.thtml,blockfooter-related.thtml';
    $_BLOCK_TEMPLATE['admin_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
    $_BLOCK_TEMPLATE['section_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
    // Geeklog 2.2 introduced a separate wrapper for administration lists.
    // Do not register it on 2.1.1: its Denim fallback has no corresponding template.
    if (eclipse_supports_admin_list()) {
        $_BLOCK_TEMPLATE['_admin_list'] = 'blockheader-child.thtml,blockfooter.thtml';
    }
    if (!COM_isAnonUser()) {
        $_BLOCK_TEMPLATE['user_block'] = 'blockheader-list.thtml,blockfooter-list.thtml';
    }
}

function eclipse_theme_options()
{
    static $options;
    if (isset($options)) {
        return $options;
    }
    $defaults = array(
        'color_primary' => '#3157d5', 'color_secondary' => '#6750a4', 'color_link' => '#2448bd',
        'color_background' => '#f4f6fb', 'color_surface' => '#ffffff', 'color_text' => '#202431',
        'site_max_width' => '1200px', 'reading_width' => '72ch', 'font_family' => 'humanist',
        'font_size' => '16px', 'spacing' => 'normal', 'radius' => 'medium', 'sidebar_position' => 'right',
        'show_left_sidebar' => false, 'show_right_sidebar' => true, 'button_style' => 'solid', 'menu_style' => 'floating',
        'block_style' => 'card', 'header_style' => 'gradient', 'footer_style' => 'dark',
        'color_scheme' => 'light', 'admin_ui_mode' => 'modern', 'admin_navigation_source' => 'both', 'mobile_menu' => true, 'editor_hide_sidebars' => true, 'share_facebook' => false, 'share_linkedin' => false, 'share_x' => false,
        'adsense_enabled' => false, 'adsense_client' => '', 'topic_h1_enabled' => false, 'html_lang' => 'auto', 'sitemap_path' => '', 'logo' => 'images/logo-mark.svg', 'header_image' => '',
    );
    $file = __DIR__ . '/themeconfig.php';
    $custom = is_file($file) ? include $file : array();
    $options = is_array($custom) ? array_merge($defaults, $custom) : $defaults;
    $saved = eclipse_data_json('eclipse-settings.json', array());
    if (is_array($saved)) $options = array_merge($options, eclipse_sanitize_options($saved));
    return $options;
}

function eclipse_sanitize_options($input)
{
    $clean = array();
    foreach (array('color_primary', 'color_secondary', 'color_link', 'color_background', 'color_surface', 'color_text') as $key) {
        if (isset($input[$key]) && preg_match('/^#[0-9a-fA-F]{6}$/', $input[$key])) $clean[$key] = strtolower($input[$key]);
    }
    $choices = array(
        'font_family' => array('system', 'serif', 'humanist'), 'spacing' => array('compact', 'normal', 'relaxed'),
        'radius' => array('none', 'small', 'medium', 'large'), 'sidebar_position' => array('left', 'right'),
        'button_style' => array('solid', 'outline', 'soft'), 'menu_style' => array('floating', 'capsule', 'editorial', 'contrast'), 'block_style' => array('card', 'bordered', 'flat'),
        'header_style' => array('gradient', 'solid', 'minimal'), 'footer_style' => array('dark', 'light', 'minimal'),
        'color_scheme' => array('light', 'dark', 'auto'), 'admin_ui_mode' => array('modern', 'classic'), 'admin_navigation_source' => array('left', 'right', 'both'),
    );
    foreach ($choices as $key => $allowed) if (isset($input[$key]) && in_array($input[$key], $allowed, true)) $clean[$key] = $input[$key];
    foreach (array('site_max_width', 'reading_width') as $key) if (isset($input[$key]) && preg_match('/^(?:[1-9][0-9]{0,3}(?:px|rem)|[1-9][0-9]{0,2}ch)$/', $input[$key])) $clean[$key] = $input[$key];
    if (isset($input['font_size']) && preg_match('/^(?:1[4-9]|2[0-2])px$/', $input['font_size'])) $clean['font_size'] = $input['font_size'];
    foreach (array('show_left_sidebar', 'show_right_sidebar', 'mobile_menu', 'editor_hide_sidebars', 'share_facebook', 'share_linkedin', 'share_x', 'adsense_enabled', 'topic_h1_enabled') as $key) {
        if (array_key_exists($key, $input)) $clean[$key] = !empty($input[$key]);
    }
    foreach (array('logo', 'header_image') as $key) if (isset($input[$key]) && ($input[$key] === '' || preg_match('#^(?:images/)?[a-zA-Z0-9._/-]+$#', $input[$key]))) $clean[$key] = $input[$key];
    if (isset($input['adsense_client'])) {
        $client = trim((string) $input['adsense_client']);
        if ($client === '' || preg_match('/^ca-pub-[0-9]{10,20}$/', $client)) $clean['adsense_client'] = $client;
    }
    if (isset($input['html_lang'])) {
        $language = trim((string) $input['html_lang']);
        if ($language === 'auto' || preg_match('/^[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*$/', $language)) $clean['html_lang'] = $language;
    }
    if (isset($input['sitemap_path'])) {
        $sitemap = trim(str_replace('\\', '/', (string) $input['sitemap_path']));
        if ($sitemap === '' || (strpos($sitemap, '..') === false && !preg_match('/[\x00-\x20"<>]/', $sitemap) && preg_match('#^(?:https?://)?[a-zA-Z0-9_./:%?&=+~-]+$#', $sitemap))) $clean['sitemap_path'] = $sitemap;
    }
    return $clean;
}

function eclipse_storage_root()
{
    global $_CONF;
    if (empty($_CONF['path_data'])) return '';
    $data = rtrim((string) $_CONF['path_data'], '/\\');
    if ($data === '' || !eclipse_is_absolute_path($data)) return '';
    $root = !empty($_CONF['eclipse_data_path']) ? rtrim((string) $_CONF['eclipse_data_path'], '/\\') : $data . '-eclipse';
    if (!eclipse_is_absolute_path($root) || strpos(str_replace('\\', '/', $root), '../') !== false) return '';
    $html = !empty($_CONF['path_html']) ? rtrim((string) $_CONF['path_html'], '/\\') : '';
    $normalizedRoot = strtolower(str_replace('\\', '/', $root)) . '/';
    $normalizedData = strtolower(str_replace('\\', '/', $data)) . '/';
    $normalizedHtml = strtolower(str_replace('\\', '/', $html)) . '/';
    if (strpos($normalizedRoot, $normalizedData) === 0) return '';
    if ($html !== '' && strpos($normalizedRoot, $normalizedHtml) === 0) return '';
    return $root;
}

function eclipse_is_absolute_path($path)
{
    return $path !== '' && ($path[0] === '/' || $path[0] === '\\' || preg_match('/^[A-Za-z]:[\\\\\/]/', $path));
}

function eclipse_storage_prepare()
{
    $root = eclipse_storage_root();
    if ($root === '') return false;
    if (!is_dir($root) && !@mkdir($root, 0750, true)) return false;
    @chmod($root, 0750);
    $guards = array('.htaccess' => "<IfModule mod_authz_core.c>\nRequire all denied\n</IfModule>\n<IfModule !mod_authz_core.c>\nDeny from all\n</IfModule>\n", 'index.html' => '');
    foreach ($guards as $name => $contents) {
        $path = $root . DIRECTORY_SEPARATOR . $name;
        if (!is_file($path)) {
            $handle = @fopen($path, 'xb');
            if ($handle) { fwrite($handle, $contents); fclose($handle); @chmod($path, 0640); }
        }
    }
    return is_writable($root);
}

function eclipse_storage_name($name)
{
    $allowed = array('eclipse-settings.json', 'eclipse-footer.json', 'eclipse-palettes.json', 'eclipse-history.json', 'storage-migration.json');
    return in_array($name, $allowed, true) ? $name : '';
}

function eclipse_sanitize_palettes($input)
{
    if (!is_array($input)) return array();
    $clean = array();
    foreach (array_slice($input, 0, 50, true) as $name => $palette) {
        $name = trim(strip_tags((string) $name));
        if ($name === '' || !is_array($palette)) continue;
        $name = function_exists('mb_substr') ? mb_substr($name, 0, 50, 'UTF-8') : substr($name, 0, 50);
        $colors = eclipse_sanitize_options($palette);
        $entry = array();
        foreach (array('color_primary', 'color_secondary', 'color_link', 'color_background', 'color_surface', 'color_text') as $key) {
            if (!isset($colors[$key])) { $entry = array(); break; }
            $entry[$key] = $colors[$key];
        }
        if ($entry) $clean[$name] = $entry;
    }
    ksort($clean);
    return $clean;
}

function eclipse_storage_normalize($name, $value)
{
    if (!is_array($value)) return false;
    if ($name === 'eclipse-settings.json') return eclipse_sanitize_options($value);
    if ($name === 'eclipse-footer.json') return eclipse_sanitize_footer_data($value);
    if ($name === 'eclipse-palettes.json') return eclipse_sanitize_palettes($value);
    if ($name === 'eclipse-history.json') {
        $history = array();
        foreach (array_slice($value, 0, 20) as $entry) {
            if (!is_array($entry) || empty($entry['id']) || empty($entry['date'])) continue;
            $state = isset($entry['state']) && is_array($entry['state']) ? $entry['state'] : array('settings' => isset($entry['settings']) ? $entry['settings'] : array());
            if (!isset($state['settings']) || !is_array($state['settings'])) continue;
            $normalized = array('settings' => eclipse_sanitize_options($state['settings']));
            if (isset($state['footer']) && is_array($state['footer'])) $normalized['footer'] = eclipse_sanitize_footer_data($state['footer']);
            if (isset($state['palettes']) && is_array($state['palettes'])) $normalized['palettes'] = eclipse_sanitize_palettes($state['palettes']);
            $history[] = array(
                'id' => preg_replace('/[^0-9A-Za-z._-]/', '', (string) $entry['id']),
                'date' => substr((string) $entry['date'], 0, 40),
                'label' => isset($entry['label']) ? eclipse_footer_plain_text($entry['label'], 80) : 'Settings',
                'state' => $normalized,
            );
        }
        return $history;
    }
    if ($name === 'storage-migration.json') return $value;
    return false;
}

function eclipse_storage_read_path($path, $name)
{
    if (!is_file($path) || !is_readable($path) || filesize($path) > 5242880) return false;
    $lock = @fopen($path . '.lock', 'c');
    if ($lock) @chmod($path . '.lock', 0640);
    if ($lock) @flock($lock, LOCK_SH);
    $json = @file_get_contents($path, false, null, 0, 5242881);
    if ($lock) { @flock($lock, LOCK_UN); fclose($lock); }
    if (!is_string($json) || strlen($json) > 5242880) return false;
    return eclipse_storage_normalize($name, json_decode($json, true));
}

function eclipse_storage_write_raw($name, $value)
{
    if (eclipse_storage_name($name) === '' || !eclipse_storage_prepare()) return false;
    $value = eclipse_storage_normalize($name, $value);
    if ($value === false) return false;
    $json = json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false || strlen($json) > 5242880) return false;
    $root = eclipse_storage_root(); $path = $root . DIRECTORY_SEPARATOR . $name;
    $lock = @fopen($path . '.lock', 'c');
    if ($lock) @chmod($path . '.lock', 0640);
    if (!$lock || !@flock($lock, LOCK_EX)) { if ($lock) fclose($lock); return false; }
    $temp = $root . DIRECTORY_SEPARATOR . '.' . $name . '.' . getmypid() . '.' . str_replace('.', '', uniqid('', true)) . '.tmp';
    $ok = @file_put_contents($temp, $json . "\n", LOCK_EX) !== false;
    if ($ok) {
        @chmod($temp, 0640);
        $verify = @file_get_contents($temp, false, null, 0, 5242881);
        $ok = is_string($verify) && strlen($verify) <= 5242880
            && eclipse_storage_normalize($name, json_decode($verify, true)) !== false;
    }
    if ($ok && is_file($path)) {
        $currentJson = @file_get_contents($path, false, null, 0, 5242881);
        $currentValid = is_string($currentJson) && strlen($currentJson) <= 5242880
            && eclipse_storage_normalize($name, json_decode($currentJson, true)) !== false;
        if ($currentValid) {
            $ok = @copy($path, $path . '.bak');
            if ($ok) @chmod($path . '.bak', 0640);
        } else {
            @copy($path, $path . '.invalid-' . date('Ymd-His'));
        }
    }
    if ($ok) {
        if (!@rename($temp, $path)) { if (is_file($path)) @unlink($path); $ok = @rename($temp, $path); }
        if ($ok) @chmod($path, 0640);
    }
    if (is_file($temp)) @unlink($temp);
    @flock($lock, LOCK_UN); fclose($lock);
    return $ok;
}

function eclipse_read_legacy_vars($name)
{
    global $_TABLES;
    if (!isset($_TABLES['vars']) || !function_exists('DB_getItem')) return false;
    $key = 'ecl.' . substr(sha1($name), 0, 8);
    $safeKey = function_exists('DB_escapeString') ? DB_escapeString($key) : addslashes($key);
    $count = (int) DB_getItem($_TABLES['vars'], 'value', "name = '" . $safeKey . "'");
    if ($count < 1 || $count > 1000) return false;
    $stored = '';
    for ($i = 0; $i < $count; $i++) {
        $partKey = $key . '.' . $i;
        $safePart = function_exists('DB_escapeString') ? DB_escapeString($partKey) : addslashes($partKey);
        $stored .= (string) DB_getItem($_TABLES['vars'], 'value', "name = '" . $safePart . "'");
    }
    $decoded = base64_decode($stored, true);
    return $decoded === false ? false : eclipse_storage_normalize($name, json_decode($decoded, true));
}

function eclipse_migrate_storage_file($name)
{
    global $_CONF;
    if (!eclipse_storage_prepare()) return false;
    $root = eclipse_storage_root(); $destination = $root . DIRECTORY_SEPARATOR . $name;
    if (is_file($destination)) return true;
    $migrationLock = @fopen($root . DIRECTORY_SEPARATOR . '.migration.lock', 'c');
    if ($migrationLock) @chmod($root . DIRECTORY_SEPARATOR . '.migration.lock', 0640);
    if (!$migrationLock || !@flock($migrationLock, LOCK_EX)) { if ($migrationLock) fclose($migrationLock); return false; }
    $source = ''; $value = eclipse_read_legacy_vars($name);
    if ($value !== false) $source = 'geeklog-vars';
    if ($value === false && !empty($_CONF['path_data'])) {
        $legacy = rtrim($_CONF['path_data'], '/\\') . DIRECTORY_SEPARATOR . $name;
        $value = eclipse_storage_read_path($legacy, $name);
        if ($value !== false) $source = 'path_data';
    }
    $ok = $value !== false && eclipse_storage_write_raw($name, $value);
    if ($ok) {
        $reportPath = $root . DIRECTORY_SEPARATOR . 'storage-migration.json';
        $report = eclipse_storage_read_path($reportPath, 'storage-migration.json');
        if ($report === false) $report = array('schema' => 1, 'started_at' => date('c'), 'files' => array());
        $report['updated_at'] = date('c');
        $report['files'][$name] = array('source' => $source, 'status' => 'copied', 'date' => date('c'));
        eclipse_storage_write_raw('storage-migration.json', $report);
    }
    @flock($migrationLock, LOCK_UN); fclose($migrationLock);
    return $ok;
}

function eclipse_data_json($name, $fallback = array())
{
    if (eclipse_storage_name($name) === '') return $fallback;
    $root = eclipse_storage_root();
    if ($root === '') return $fallback;
    $path = $root . DIRECTORY_SEPARATOR . $name;
    if (!is_file($path)) eclipse_migrate_storage_file($name);
    $value = eclipse_storage_read_path($path, $name);
    if ($value === false) $value = eclipse_storage_read_path($path . '.bak', $name);
    return $value === false ? $fallback : $value;
}

function eclipse_write_data_json($name, $value)
{
    return eclipse_storage_write_raw($name, $value);
}

function eclipse_persistent_root()
{
    global $_CONF;
    if (empty($_CONF['path'])) return '';
    return rtrim($_CONF['path'], '/\\') . DIRECTORY_SEPARATOR . 'backups' . DIRECTORY_SEPARATOR . 'eclipse';
}

function eclipse_delete_data_json($name)
{
    if (eclipse_storage_name($name) === '') return false;
    $root = eclipse_storage_root(); if ($root === '') return false;
    $path = $root . DIRECTORY_SEPARATOR . $name;
    $lock = @fopen($path . '.lock', 'c');
    if (!$lock || !@flock($lock, LOCK_EX)) { if ($lock) fclose($lock); return false; }
    $ok = (!is_file($path) || @unlink($path)) && (!is_file($path . '.bak') || @unlink($path . '.bak'));
    @flock($lock, LOCK_UN); fclose($lock);
    return $ok;
}

function eclipse_clear_theme_cache()
{
    global $_CONF;
    if (!function_exists('CTL_clearCacheDirectories') || empty($_CONF['path_data'])) return false;
    $data = rtrim($_CONF['path_data'], '/\\') . DIRECTORY_SEPARATOR;
    CTL_clearCacheDirectories($data . 'layout_cache', 'eclipse');
    CTL_clearCacheDirectories($data . 'layout_css', 'eclipse');
    return true;
}

function eclipse_footer_plain_text($value, $maximum)
{
    $value = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)));
    return function_exists('mb_substr') ? mb_substr($value, 0, $maximum, 'UTF-8') : substr($value, 0, $maximum);
}

function eclipse_sanitize_footer_data($input)
{
    $clean = array('groups' => array(), 'copyright' => '', 'legal_notice' => '');
    if (!is_array($input)) return $clean;
    $clean['copyright'] = isset($input['copyright']) ? eclipse_footer_plain_text($input['copyright'], 240) : '';
    $clean['legal_notice'] = isset($input['legal_notice']) ? eclipse_footer_plain_text($input['legal_notice'], 320) : '';
    $groups = isset($input['groups']) && is_array($input['groups']) ? array_slice($input['groups'], 0, 8) : array();
    $total = 0;
    foreach ($groups as $group) {
        $links = array();
        $sourceLinks = isset($group['links']) && is_array($group['links']) ? array_slice($group['links'], 0, 12) : array();
        foreach ($sourceLinks as $link) {
            if ($total >= 48 || !is_array($link)) break;
            $label = isset($link['label']) ? eclipse_footer_plain_text($link['label'], 80) : '';
            $url = isset($link['url']) ? trim((string) $link['url']) : '';
            $safeUrl = $url !== '' && strpos($url, '..') === false && !preg_match('/[\x00-\x20"<>]/', $url)
                && (preg_match('#^https?://[a-zA-Z0-9.-]+(?::[0-9]+)?(?:/[^\s"<>]*)?$#', $url)
                    || preg_match('#^/(?:[^\s"<>]*)$#', $url) || preg_match('/^mailto:[^\s@]+@[^\s@]+$/', $url));
            if ($label === '' || !$safeUrl) continue;
            $links[] = array('label' => $label, 'url' => $url, 'enabled' => !empty($link['enabled']),
                'emphasis' => !empty($link['emphasis']), 'new_window' => !empty($link['new_window']), 'nofollow' => !empty($link['nofollow']));
            $total++;
        }
        if ($links) $clean['groups'][] = array('links' => $links);
    }
    return $clean;
}

function eclipse_footer_data()
{
    return eclipse_sanitize_footer_data(eclipse_data_json('eclipse-footer.json', array()));
}

function eclipse_render_footer_links()
{
    global $_CONF;
    $data = eclipse_footer_data();
    if (!$data['groups'] && $data['copyright'] === '' && $data['legal_notice'] === '') return '';
    $html = '<section class="eclipse-footer-extras">';
    if ($data['groups']) {
        $html .= '<nav class="eclipse-footer-links" aria-label="Footer links">';
        foreach ($data['groups'] as $group) {
            $rowHtml = '';
            foreach ($group['links'] as $link) {
                if (empty($link['enabled'])) continue;
                $external = preg_match('#^https?://#i', $link['url']);
                $relations = array();
                if (!empty($link['new_window'])) { $relations[] = 'noopener'; $relations[] = 'noreferrer'; }
                if (!empty($link['nofollow'])) $relations[] = 'nofollow';
                $rowHtml .= '<li' . (!empty($link['emphasis']) ? ' class="is-emphasized"' : '') . '><a href="' . htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8') . '"'
                    . (!empty($link['new_window']) ? ' target="_blank"' : '') . ($relations ? ' rel="' . implode(' ', array_unique($relations)) . '"' : '')
                    . ($external ? ' class="is-external"' : '') . '>' . htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') . '</a></li>';
            }
            if ($rowHtml !== '') $html .= '<ul>' . $rowHtml . '</ul>';
        }
        $html .= '</nav>';
    }
    $replacements = array('{year}' => date('Y'), '{site_name}' => isset($_CONF['site_name']) ? $_CONF['site_name'] : '');
    if ($data['copyright'] !== '' || $data['legal_notice'] !== '') {
        $html .= '<div class="eclipse-footer-legal">';
        if ($data['copyright'] !== '') $html .= '<p>' . htmlspecialchars(strtr($data['copyright'], $replacements), ENT_QUOTES, 'UTF-8') . '</p>';
        if ($data['legal_notice'] !== '') $html .= '<p>' . htmlspecialchars(strtr($data['legal_notice'], $replacements), ENT_QUOTES, 'UTF-8') . '</p>';
        $html .= '</div>';
    }
    return $html . '</section>';
}

function eclipse_footer_editor_link($groupIndex, $linkIndex, $link)
{
    $h = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
    $name = 'eclipse_footer[groups][' . $groupIndex . '][links][' . $linkIndex . ']';
    return '<div class="eclipse-footer-link-row"><label><span>Label</span><input name="' . $name . '[label]" value="' . $h(isset($link['label']) ? $link['label'] : '') . '" maxlength="80"></label><label><span>URL or path</span><input name="' . $name . '[url]" value="' . $h(isset($link['url']) ? $link['url'] : '') . '" placeholder="/contact" maxlength="500"></label><div class="eclipse-footer-link-options">'
        . '<label><input type="checkbox" name="' . $name . '[enabled]" value="1"' . (!isset($link['enabled']) || !empty($link['enabled']) ? ' checked' : '') . '> Enabled</label>'
        . '<label><input type="checkbox" name="' . $name . '[emphasis]" value="1"' . (!empty($link['emphasis']) ? ' checked' : '') . '> Emphasize</label>'
        . '<label><input type="checkbox" name="' . $name . '[new_window]" value="1"' . (!empty($link['new_window']) ? ' checked' : '') . '> New window</label>'
        . '<label><input type="checkbox" name="' . $name . '[nofollow]" value="1"' . (!empty($link['nofollow']) ? ' checked' : '') . '> Nofollow</label></div><button type="button" class="eclipse-footer-remove-link">Remove</button></div>';
}

function eclipse_footer_editor($data)
{
    $h = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
    $groups = $data['groups'] ? $data['groups'] : array(array('links' => array(array('label' => '', 'url' => '', 'enabled' => true))));
    $html = '<fieldset data-studio-section="footer-links" class="eclipse-footer-editor"><legend>Footer links</legend><p class="eclipse-section-intro">Create up to eight link rows. Data is stored as protected JSON outside Geeklog\'s cache directory and survives theme updates and cache cleaning.</p><div class="eclipse-footer-groups">';
    foreach ($groups as $groupIndex => $group) {
        $html .= '<section class="eclipse-footer-group" data-group-index="' . $groupIndex . '"><header><h3>Link row</h3><button type="button" class="eclipse-footer-remove-group">Remove row</button></header><div class="eclipse-footer-group-links">';
        foreach ($group['links'] as $linkIndex => $link) $html .= eclipse_footer_editor_link($groupIndex, $linkIndex, $link);
        $html .= '</div><button type="button" class="eclipse-footer-add-link">Add link</button></section>';
    }
    $html .= '</div><button type="button" class="eclipse-footer-add-group">Add link row</button><div class="eclipse-field-grid eclipse-footer-legal-fields"><label><span>Copyright line</span><input name="eclipse_footer[copyright]" value="' . $h($data['copyright']) . '" placeholder="All rights reserved &copy; {year} {site_name}."></label><label><span>Legal notice</span><input name="eclipse_footer[legal_notice]" value="' . $h($data['legal_notice']) . '" placeholder="All trademarks belong to their respective owners."></label></div><template id="eclipse-footer-link-template">' . eclipse_footer_editor_link('__G__', '__L__', array('enabled' => true)) . '</template><template id="eclipse-footer-group-template"><section class="eclipse-footer-group" data-group-index="__G__"><header><h3>Link row</h3><button type="button" class="eclipse-footer-remove-group">Remove row</button></header><div class="eclipse-footer-group-links">' . eclipse_footer_editor_link('__G__', '__L__', array('enabled' => true)) . '</div><button type="button" class="eclipse-footer-add-link">Add link</button></section></template></fieldset>';
    return $html;
}

function eclipse_record_settings_history($label)
{
    $settings = eclipse_data_json('eclipse-settings.json', array());
    $footer = eclipse_data_json('eclipse-footer.json', array());
    $palettes = eclipse_data_json('eclipse-palettes.json', array());
    if (!$settings && !$footer && !$palettes) return true;
    $history = eclipse_data_json('eclipse-history.json', array());
    array_unshift($history, array(
        'id' => date('YmdHis') . '-' . substr(sha1(uniqid('', true)), 0, 6),
        'date' => date('c'), 'label' => $label,
        'state' => array('settings' => $settings, 'footer' => $footer, 'palettes' => $palettes),
    ));
    $history = array_slice($history, 0, 20);
    return eclipse_write_data_json('eclipse-history.json', $history);
}

function eclipse_write_complete_state($state)
{
    if (!is_array($state) || !isset($state['settings'], $state['footer'], $state['palettes'])) return false;
    $documents = array(
        'eclipse-settings.json' => eclipse_sanitize_options($state['settings']),
        'eclipse-footer.json' => eclipse_sanitize_footer_data($state['footer']),
        'eclipse-palettes.json' => eclipse_sanitize_palettes($state['palettes']),
    );
    $before = array(); $written = array();
    foreach ($documents as $name => $value) $before[$name] = eclipse_data_json($name, null);
    foreach ($documents as $name => $value) {
        if (!eclipse_write_data_json($name, $value)) {
            foreach (array_reverse($written) as $writtenName) {
                if ($before[$writtenName] === null) eclipse_delete_data_json($writtenName);
                else eclipse_write_data_json($writtenName, $before[$writtenName]);
            }
            return false;
        }
        $written[] = $name;
    }
    return true;
}

function eclipse_render_customizer()
{
    global $_CONF, $_TABLES;
    if (!function_exists('SEC_inGroup') || !SEC_inGroup('Root')) return '';
    $message = '';
    $tokenName = defined('CSRF_TOKEN') ? CSRF_TOKEN : 'token';
    if (isset($_POST['eclipse_update'])) {
        if (!function_exists('SEC_checkToken') || !SEC_checkToken()) {
            $message = '<p class="eclipse-notice eclipse-error">Security token rejected. No update was installed.</p>';
        } else {
            $result = eclipse_install_uploaded_update(isset($_FILES['eclipse_archive']) ? $_FILES['eclipse_archive'] : array());
            $message = '<p class="eclipse-notice ' . ($result['success'] ? 'eclipse-success' : 'eclipse-error') . '">' . htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') . '</p>';
        }
    } elseif (isset($_POST['eclipse_backup_restore'])) {
        if (!function_exists('SEC_checkToken') || !SEC_checkToken()) {
            $message = '<p class="eclipse-notice eclipse-error">Security token rejected. No backup was restored.</p>';
        } else {
            $dataDir = !empty($_CONF['path_data']) ? rtrim($_CONF['path_data'], '/\\') : '';
            $backupRoot = eclipse_persistent_root();
            $backupName = isset($_POST['eclipse_backup_name']) ? basename((string) $_POST['eclipse_backup_name']) : '';
            $selectedBackup = $backupRoot . DIRECTORY_SEPARATOR . $backupName;
            $rootReal = realpath($backupRoot); $selectedReal = realpath($selectedBackup);
            $validName = preg_match('/^eclipse-[0-9A-Za-z._-]+$/', $backupName) && $rootReal !== false && $selectedReal !== false && strpos($selectedReal . DIRECTORY_SEPARATOR, $rootReal . DIRECTORY_SEPARATOR) === 0 && is_file($selectedReal . DIRECTORY_SEPARATOR . 'theme.ini');
            $safetyBackup = $backupRoot . DIRECTORY_SEPARATOR . 'eclipse-before-rollback-' . date('Ymd-His');
            if (!$validName) {
                $message = '<p class="eclipse-notice eclipse-error">Select a valid Eclipse backup.</p>';
            } elseif (!eclipse_copy_tree(__DIR__, $safetyBackup, 0750, 0640)) {
                $message = '<p class="eclipse-notice eclipse-error">Unable to create the pre-rollback safety backup.</p>';
            } elseif (!eclipse_copy_tree($selectedBackup, __DIR__, 0755, 0644)) {
                $message = '<p class="eclipse-notice eclipse-error">Backup restoration failed. The pre-rollback copy remains available.</p>';
            } else {
                $message = '<p class="eclipse-notice eclipse-success">Theme backup restored and Eclipse theme caches cleared. Reload the page.</p>';
                eclipse_clear_theme_cache();
            }
        }
    } elseif (isset($_POST['eclipse_history_restore'])) {
        if (!function_exists('SEC_checkToken') || !SEC_checkToken()) {
            $message = '<p class="eclipse-notice eclipse-error">Security token rejected. History was not restored.</p>';
        } else {
            $restoreId = isset($_POST['eclipse_history_id']) ? (string) $_POST['eclipse_history_id'] : '';
            $history = eclipse_data_json('eclipse-history.json', array());
            $restored = false;
            foreach ($history as $entry) {
                if (isset($entry['id']) && (string) $entry['id'] === $restoreId) {
                    $state = isset($entry['state']) && is_array($entry['state']) ? $entry['state'] : array('settings' => isset($entry['settings']) ? $entry['settings'] : array());
                    if (!isset($state['settings']) || !is_array($state['settings'])) break;
                    if (!eclipse_record_settings_history('Before history restore')) break;
                    $restored = eclipse_write_complete_state(array(
                        'settings' => $state['settings'],
                        'footer' => isset($state['footer']) ? $state['footer'] : eclipse_data_json('eclipse-footer.json', array()),
                        'palettes' => isset($state['palettes']) ? $state['palettes'] : eclipse_data_json('eclipse-palettes.json', array()),
                    ));
                    break;
                }
            }
            if ($restored) eclipse_clear_theme_cache();
            $message = $restored ? '<p class="eclipse-notice eclipse-success">Settings, footer links and palettes restored from history.</p>' : '<p class="eclipse-notice eclipse-error">The selected history entry could not be restored completely.</p>';
        }
    } elseif (isset($_POST['eclipse_palette_save']) || isset($_POST['eclipse_palette_delete'])) {
        if (!function_exists('SEC_checkToken') || !SEC_checkToken()) {
            $message = '<p class="eclipse-notice eclipse-error">Security token rejected. No palette was changed.</p>';
        } else {
            $name = isset($_POST['eclipse_palette_name']) ? trim(strip_tags((string) $_POST['eclipse_palette_name'])) : '';
            $name = function_exists('mb_substr') ? mb_substr($name, 0, 50, 'UTF-8') : substr($name, 0, 50);
            $palettes = eclipse_data_json('eclipse-palettes.json', array());
            if ($name === '') {
                $message = '<p class="eclipse-notice eclipse-error">Enter a palette name.</p>';
            } elseif (isset($_POST['eclipse_palette_delete'])) {
                unset($palettes[$name]);
                $paletteSaved = eclipse_write_data_json('eclipse-palettes.json', eclipse_sanitize_palettes($palettes));
                if ($paletteSaved) eclipse_clear_theme_cache();
                $message = $paletteSaved ? '<p class="eclipse-notice eclipse-success">Custom palette removed and Eclipse theme caches cleared.</p>' : '<p class="eclipse-notice eclipse-error">Unable to update persistent palette storage.</p>';
            } else {
                $submitted = isset($_POST['eclipse']) && is_array($_POST['eclipse']) ? $_POST['eclipse'] : array();
                $colors = eclipse_sanitize_options($submitted);
                $palette = array();
                foreach (array('color_primary', 'color_secondary', 'color_link', 'color_background', 'color_surface', 'color_text') as $colorKey) if (isset($colors[$colorKey])) $palette[$colorKey] = $colors[$colorKey];
                if (count($palette) !== 6) {
                    $message = '<p class="eclipse-notice eclipse-error">The custom palette contains invalid colors.</p>';
                } else {
                    $palettes[$name] = $palette;
                    ksort($palettes);
                    $paletteSaved = eclipse_write_data_json('eclipse-palettes.json', eclipse_sanitize_palettes($palettes));
                    if ($paletteSaved) eclipse_clear_theme_cache();
                    $message = $paletteSaved ? '<p class="eclipse-notice eclipse-success">Custom palette saved and Eclipse theme caches cleared.</p>' : '<p class="eclipse-notice eclipse-error">Unable to write persistent palette storage.</p>';
                }
            }
        }
    } elseif (isset($_POST['eclipse_save']) || isset($_POST['eclipse_reset'])) {
        if (!function_exists('SEC_checkToken') || !SEC_checkToken()) {
            $message = '<p class="eclipse-notice eclipse-error">Security token rejected. No setting was saved.</p>';
        } elseif (isset($_POST['eclipse_reset'])) {
            eclipse_record_settings_history('Before restoring defaults');
            $settingsRemoved = eclipse_delete_data_json('eclipse-settings.json');
            $footerRemoved = eclipse_delete_data_json('eclipse-footer.json');
            if ($settingsRemoved && $footerRemoved) {
                eclipse_clear_theme_cache();
                $message = '<p class="eclipse-notice eclipse-success">Saved settings and footer links removed from persistent JSON storage. Eclipse defaults will be used on the next page load.</p>';
            } else {
                $message = '<p class="eclipse-notice eclipse-error">Unable to remove one or more persistent Eclipse documents.</p>';
            }
        } else {
            $submitted = isset($_POST['eclipse']) && is_array($_POST['eclipse']) ? $_POST['eclipse'] : array();
            foreach (array('show_left_sidebar', 'show_right_sidebar', 'mobile_menu', 'editor_hide_sidebars', 'share_facebook', 'share_linkedin', 'share_x', 'adsense_enabled', 'topic_h1_enabled') as $booleanKey) {
                if (!isset($submitted[$booleanKey])) $submitted[$booleanKey] = false;
            }
            $settings = eclipse_sanitize_options($submitted);
            $footerSubmitted = isset($_POST['eclipse_footer']) && is_array($_POST['eclipse_footer']) ? $_POST['eclipse_footer'] : array();
            $footerSettings = eclipse_sanitize_footer_data($footerSubmitted);
            $historySaved = eclipse_record_settings_history('Before saving settings');
            $portablePalettes = null;
            if (!empty($_POST['eclipse_portable_palettes']) && strlen((string) $_POST['eclipse_portable_palettes']) <= 5242880) {
                $decodedPalettes = json_decode((string) $_POST['eclipse_portable_palettes'], true);
                if (is_array($decodedPalettes)) $portablePalettes = eclipse_sanitize_palettes($decodedPalettes);
            }
            if ($portablePalettes === null) $portablePalettes = eclipse_data_json('eclipse-palettes.json', array());
            $stateSaved = eclipse_write_complete_state(array('settings' => $settings, 'footer' => $footerSettings, 'palettes' => $portablePalettes));
            if ($historySaved && $stateSaved) {
                eclipse_clear_theme_cache();
                $message = '<p class="eclipse-notice eclipse-success">Complete Eclipse settings, footer links and palettes saved persistently.</p>';
            } else {
                $message = '<p class="eclipse-notice eclipse-error">Unable to store the complete Eclipse state safely.</p>';
            }
        }
    }
    $o = eclipse_theme_options();
    $footerData = eclipse_footer_data();
    $customPalettes = eclipse_data_json('eclipse-palettes.json', array());
    $settingsHistory = eclipse_data_json('eclipse-history.json', array());
    $themeBackups = array();
    $backupRoot = eclipse_persistent_root();
    if ($backupRoot !== '' && is_dir($backupRoot) && ($backupHandle = @opendir($backupRoot))) {
        while (($backupName = readdir($backupHandle)) !== false) if (preg_match('/^eclipse-[0-9A-Za-z._-]+$/', $backupName) && is_file($backupRoot . DIRECTORY_SEPARATOR . $backupName . DIRECTORY_SEPARATOR . 'theme.ini')) $themeBackups[] = $backupName;
        closedir($backupHandle); rsort($themeBackups);
    }
    $token = function_exists('SEC_createToken') ? SEC_createToken() : '';
    $h = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
    $environmentGeeklog = defined('VERSION') ? VERSION : '2.1.1 reference';
    $environmentDataWritable = eclipse_storage_prepare();
    $environmentHtml = '<dl class="eclipse-environment"><div><dt>Geeklog</dt><dd>' . $h($environmentGeeklog) . '</dd></div><div><dt>PHP</dt><dd>' . $h(PHP_VERSION) . '</dd></div><div><dt>ZIP updates</dt><dd class="' . (class_exists('ZipArchive') ? 'is-ok' : 'is-warning') . '">' . (class_exists('ZipArchive') ? 'Available' : 'ZipArchive missing') . '</dd></div><div><dt>Persistent JSON</dt><dd class="' . ($environmentDataWritable ? 'is-ok' : 'is-warning') . '">' . ($environmentDataWritable ? 'Writable sibling storage' : 'Not writable') . '</dd></div></dl>';
    $select = function ($name, $values) use ($o, $h) {
        $html = '<select name="eclipse[' . $h($name) . ']">';
        foreach ($values as $value => $label) $html .= '<option value="' . $h($value) . '"' . ($o[$name] === $value ? ' selected' : '') . '>' . $h($label) . '</option>';
        return $html . '</select>';
    };
    $html = '<section class="eclipse-customizer" id="eclipse-theme-studio" tabindex="-1"><header><div><span class="eclipse-eyebrow">Eclipse ' . htmlspecialchars(eclipse_theme_version(), ENT_QUOTES, 'UTF-8') . '</span><h2>Theme studio</h2><p>Changes are stored as protected JSON outside Geeklog\'s cache directory.</p></div><div class="eclipse-studio-header-actions"><span class="eclipse-preview-mark" aria-hidden="true">&#9680;</span><a href="#eclipse-dashboard-start">Back to dashboard</a></div></header>' . $message;
    $previewDocument = '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width"><style>:root{--p:#3157d5;--s:#6750a4;--l:#2448bd;--bg:#f4f6fb;--surface:#fff;--text:#202431}*{box-sizing:border-box}body{margin:0;color:var(--text);background:var(--bg);font:16px/1.5 system-ui,sans-serif}.hero{padding:2.2rem 7%;color:#fff;background:linear-gradient(125deg,var(--p),var(--s))}.hero h1{margin:0;font-size:clamp(1.8rem,5vw,3.2rem)}nav{display:flex;gap:.35rem;padding:.55rem 7%;background:var(--surface);border-bottom:1px solid #ccd2df}nav a{padding:.45rem .7rem;color:var(--l)}main{display:grid;grid-template-columns:minmax(0,1fr) 14rem;gap:1rem;padding:1.25rem 7%}.card{padding:1.2rem;background:var(--surface);border:1px solid #dce1eb;border-radius:.75rem;box-shadow:0 10px 28px rgba(28,38,66,.08)}h2{margin-top:0}.meta{color:#687083;font-size:.82rem}.button{display:inline-block;padding:.55rem .85rem;color:#fff;background:var(--p);border-radius:.45rem}@media(max-width:600px){main{grid-template-columns:1fr}.hero{padding-block:1.4rem}nav{overflow:auto}}</style></head><body><header class="hero"><h1>Geeklog France</h1><p>Free Content Management System GPL</p></header><nav><a>Home</a><a>Articles</a><a>Contact</a></nav><main><article class="card"><p class="meta">12 January 2026 &middot; Editorial preview</p><h2>Design preview</h2><p>This isolated page shows the selected palette without changing the administration interface.</p><span class="button">Primary action</span></article><aside class="card"><h2>Sidebar</h2><p><a style="color:var(--l)">Current section</a></p><p>Recent content</p></aside></main></body></html>';
    $previewDocument = str_replace(
        array('Geeklog France', 'Free Content Management System GPL'),
        array($h(!empty($_CONF['site_name']) ? $_CONF['site_name'] : 'Geeklog'), $h(!empty($_CONF['site_slogan']) ? $_CONF['site_slogan'] : 'Content management powered by Geeklog')),
        $previewDocument
    );
    $html .= '<nav class="eclipse-studio-tabs" role="tablist" aria-label="Theme Studio sections"><button type="button" role="tab" id="eclipse-tab-design" aria-controls="eclipse-panel-design" aria-selected="true">Design</button><button type="button" role="tab" id="eclipse-tab-preview" aria-controls="eclipse-panel-preview" aria-selected="false" tabindex="-1">Preview</button><button type="button" role="tab" id="eclipse-tab-updates" aria-controls="eclipse-panel-updates" aria-selected="false" tabindex="-1">Updates</button><button type="button" role="tab" id="eclipse-tab-documentation" aria-controls="eclipse-panel-documentation" aria-selected="false" tabindex="-1">Documentation</button></nav>';
    $html .= '<form method="post" enctype="multipart/form-data" class="eclipse-settings-form" data-eclipse-version="' . $h(eclipse_theme_version()) . '"><input type="hidden" name="' . $h($tokenName) . '" value="' . $h($token) . '"><input type="hidden" id="eclipse-portable-palettes" name="eclipse_portable_palettes" value=""><div id="eclipse-panel-design" class="eclipse-studio-panel" role="tabpanel" aria-labelledby="eclipse-tab-design">';
    $html .= '<fieldset data-studio-section="palette"><legend>Palette</legend><div class="eclipse-section-heading"><button type="button" class="eclipse-section-reset" data-reset-section="palette">Reset palette</button></div><div class="eclipse-palette-tools"><label><span>Preset palette</span><select id="eclipse-palette-preset"><option value="custom">Custom colors</option><option value="default">Eclipse default</option><option value="ocean">Ocean blue</option><option value="forest">Forest green</option><option value="sunset">Warm sunset</option><option value="graphite">Graphite</option>';
    foreach ($customPalettes as $paletteName => $paletteColors) {
        $orderedColors = array(); foreach (array('color_primary', 'color_secondary', 'color_link', 'color_background', 'color_surface', 'color_text') as $colorKey) $orderedColors[] = isset($paletteColors[$colorKey]) ? $paletteColors[$colorKey] : '';
        $html .= '<option value="saved-' . $h(sha1($paletteName)) . '" data-palette-name="' . $h($paletteName) . '" data-colors="' . $h(implode(',', $orderedColors)) . '">' . $h($paletteName) . '</option>';
    }
    $html .= '</select></label><figure class="eclipse-palette-figure"><figcaption>Live preview</figcaption><div class="eclipse-palette-preview" aria-label="Palette preview"><span class="eclipse-preview-header"></span><span class="eclipse-preview-card"><i></i><b></b><em></em></span><span class="eclipse-preview-button"></span></div></figure></div><div id="eclipse-contrast-report" class="eclipse-contrast-report" aria-live="polite"></div><div class="eclipse-field-grid eclipse-color-fields">';
    foreach (array('color_primary' => 'Primary', 'color_secondary' => 'Secondary', 'color_link' => 'Links', 'color_background' => 'Page', 'color_surface' => 'Cards', 'color_text' => 'Text') as $key => $label) $html .= '<label><span>' . $label . '</span><input type="color" name="eclipse[' . $key . ']" value="' . $h($o[$key]) . '"></label>';
    $html .= '</div><div class="eclipse-palette-save"><label><span>Palette name</span><input name="eclipse_palette_name" maxlength="50"></label><button type="submit" name="eclipse_palette_save" value="1">Save named palette</button><button type="submit" name="eclipse_palette_delete" value="1">Delete named palette</button></div></fieldset><fieldset data-studio-section="layout"><legend>Layout and type</legend><div class="eclipse-section-heading"><button type="button" class="eclipse-section-reset" data-reset-section="layout">Reset section</button></div><div class="eclipse-field-grid"><label><span>Site width</span><input name="eclipse[site_max_width]" value="' . $h($o['site_max_width']) . '"></label><label><span>Reading width</span><input name="eclipse[reading_width]" value="' . $h($o['reading_width']) . '"></label><label><span>Base size</span><input name="eclipse[font_size]" value="' . $h($o['font_size']) . '"></label>';
    $html .= '<label><span>Typography</span>' . $select('font_family', array('system' => 'Modern system', 'serif' => 'Editorial serif', 'humanist' => 'Humanist')) . '</label><label><span>Spacing</span>' . $select('spacing', array('compact' => 'Compact', 'normal' => 'Balanced', 'relaxed' => 'Airy')) . '</label><label><span>Corners</span>' . $select('radius', array('none' => 'Square', 'small' => 'Subtle', 'medium' => 'Rounded', 'large' => 'Very rounded')) . '</label></div></fieldset>';
    $html .= '<fieldset data-studio-section="appearance"><legend>Appearance</legend><div class="eclipse-section-heading"><button type="button" class="eclipse-section-reset" data-reset-section="appearance">Reset section</button></div><div class="eclipse-field-grid"><label><span>Color mode</span>' . $select('color_scheme', array('auto' => 'System', 'light' => 'Light', 'dark' => 'Dark')) . '</label><label><span>Administration interface</span>' . $select('admin_ui_mode', array('modern' => 'Modern workspace', 'classic' => 'Classic Eclipse')) . '</label><label><span>Admin navigation blocks</span>' . $select('admin_navigation_source', array('left' => 'Left blocks', 'right' => 'Right blocks', 'both' => 'Left and right blocks')) . '</label><label><span>Menu composition</span>' . $select('menu_style', array('floating' => 'Floating glass', 'capsule' => 'Gradient capsule', 'editorial' => 'Editorial line', 'contrast' => 'Contrast dock')) . '</label><label><span>Cards</span>' . $select('block_style', array('card' => 'Elevated', 'bordered' => 'Outlined', 'flat' => 'Flat')) . '</label><label><span>Buttons</span>' . $select('button_style', array('solid' => 'Solid', 'outline' => 'Outline', 'soft' => 'Soft')) . '</label><label><span>Header</span>' . $select('header_style', array('gradient' => 'Aurora gradient', 'solid' => 'Solid', 'minimal' => 'Minimal')) . '</label><label><span>Footer</span>' . $select('footer_style', array('dark' => 'Dark', 'light' => 'Light', 'minimal' => 'Minimal')) . '</label><label><span>Sidebar</span>' . $select('sidebar_position', array('right' => 'Right', 'left' => 'Left')) . '</label></div></fieldset>';
    $html .= '<fieldset data-studio-section="brand"><legend>Brand and regions</legend><div class="eclipse-section-heading"><button type="button" class="eclipse-section-reset" data-reset-section="brand">Reset section</button></div><div class="eclipse-field-grid"><label><span>Logo path</span><input name="eclipse[logo]" value="' . $h($o['logo']) . '" placeholder="images/logo.svg"></label><label><span>Header image</span><input name="eclipse[header_image]" value="' . $h($o['header_image']) . '" placeholder="images/header.jpg"></label></div><div class="eclipse-checks">';
    foreach (array('show_left_sidebar' => 'Left sidebar', 'show_right_sidebar' => 'Right sidebar', 'mobile_menu' => 'Mobile menu', 'editor_hide_sidebars' => 'Hide sidebars in story editor') as $key => $label) $html .= '<label><input type="checkbox" name="eclipse[' . $key . ']" value="1"' . (!empty($o[$key]) ? ' checked' : '') . '> ' . $label . '</label>';
    $html .= '</div></fieldset><fieldset data-studio-section="social" class="eclipse-social-sharing"><legend>Social sharing</legend><div class="eclipse-section-heading"><button type="button" class="eclipse-section-reset" data-reset-section="social">Reset section</button></div><p class="eclipse-section-intro">Choose the share links displayed on full article pages. No third-party script or request is loaded before a visitor clicks a link.</p><div class="eclipse-checks">';
    foreach (array('share_facebook' => 'Facebook', 'share_linkedin' => 'LinkedIn', 'share_x' => 'X') as $key => $label) $html .= '<label><input type="checkbox" name="eclipse[' . $key . ']" value="1"' . (!empty($o[$key]) ? ' checked' : '') . '> ' . $label . '</label>';
    $html .= '</div></fieldset><fieldset data-studio-section="integrations"><legend>SEO and integrations</legend><div class="eclipse-section-heading"><button type="button" class="eclipse-section-reset" data-reset-section="integrations">Reset section</button></div><p class="eclipse-section-intro">Leave Sitemap path empty to use Geeklog <code>sitemap_file</code>. Use <code>auto</code> for the active Geeklog language. Public SEO metadata and advertising are omitted from administration pages.</p><div class="eclipse-field-grid"><label><span>HTML language</span><input name="eclipse[html_lang]" value="' . $h($o['html_lang']) . '" placeholder="auto or fr-FR" pattern="auto|[a-zA-Z]{2,3}(?:-[a-zA-Z0-9]{2,8})*"></label><label><span>Sitemap URL or relative path</span><input name="eclipse[sitemap_path]" value="' . $h($o['sitemap_path']) . '" placeholder="sitemap.xml"></label><label><span>Google AdSense client</span><input name="eclipse[adsense_client]" value="' . $h($o['adsense_client']) . '" placeholder="ca-pub-0000000000000000" pattern="ca-pub-[0-9]{10,20}"></label></div><div class="eclipse-checks">';
    foreach (array('adsense_enabled' => 'Load the Google AdSense script', 'topic_h1_enabled' => 'Display the Geeklog topic name as an H1 on topic index pages') as $key => $label) $html .= '<label><input type="checkbox" name="eclipse[' . $key . ']" value="1"' . (!empty($o[$key]) ? ' checked' : '') . '> ' . $label . '</label>';
    $html .= '</div></fieldset>' . eclipse_footer_editor($footerData) . '<fieldset class="eclipse-portability"><legend>Portability and history</legend><div class="eclipse-portability-grid"><div><h3>Import or export</h3><p>Import a complete versioned draft containing settings, footer links and palettes. Review it, then save.</p><input type="file" id="eclipse-settings-import" accept="application/json,.json"><button type="button" id="eclipse-settings-export">Export complete Eclipse state</button></div><div><h3>Complete state history</h3><label><span>Saved snapshot</span><select id="eclipse-history-select" name="eclipse_history_id"><option value="">Select a snapshot</option>';
    foreach ($settingsHistory as $historyEntry) if (isset($historyEntry['id'], $historyEntry['date'])) $html .= '<option value="' . $h($historyEntry['id']) . '">' . $h(substr($historyEntry['date'], 0, 19) . ' -- ' . (isset($historyEntry['label']) ? $historyEntry['label'] : 'Settings')) . '</option>';
    $html .= '</select></label><button id="eclipse-history-restore" type="submit" name="eclipse_history_restore" value="1" disabled>Restore complete snapshot</button></div></div></fieldset></div><div id="eclipse-panel-preview" class="eclipse-studio-panel" role="tabpanel" aria-labelledby="eclipse-tab-preview" hidden><div class="eclipse-preview-toolbar" role="group" aria-label="Preview width"><button type="button" data-preview-width="desktop" aria-pressed="true">Desktop</button><button type="button" data-preview-width="tablet" aria-pressed="false">Tablet</button><button type="button" data-preview-width="mobile" aria-pressed="false">Mobile</button></div><div class="eclipse-isolated-preview" data-width="desktop"><iframe id="eclipse-preview-frame" title="Isolated Eclipse preview" sandbox srcdoc="' . $h($previewDocument) . '"></iframe></div></div><div class="eclipse-actions"><span id="eclipse-draft-status" class="eclipse-draft-status" role="status">No unsaved changes</span><button class="eclipse-cancel-preview" type="button">Cancel preview</button><button class="eclipse-reset" type="submit" name="eclipse_reset" value="1">Restore defaults</button><button type="submit" name="eclipse_save" value="1">Save complete Eclipse state</button></div></form>';
    $html .= '<section id="eclipse-panel-updates" class="eclipse-studio-panel" role="tabpanel" aria-labelledby="eclipse-tab-updates" hidden><div class="eclipse-updater"><div><span class="eclipse-eyebrow">Local update</span><h3>Install an Eclipse archive</h3><p>Select a versioned ZIP from your computer. A backup is created before files are replaced, then the Geeklog template and generated CSS caches are cleared automatically.</p></div><form method="post" enctype="multipart/form-data"><input type="hidden" name="' . $h($tokenName) . '" value="' . $h($token) . '"><label><span>Archive ZIP</span><input type="file" name="eclipse_archive" accept=".zip,application/zip" required></label><button type="submit" name="eclipse_update" value="1">Install update</button></form></div></section>';
    $html .= '<section id="eclipse-panel-documentation" class="eclipse-studio-panel eclipse-studio-documentation" role="tabpanel" aria-labelledby="eclipse-tab-documentation" hidden><header><span class="eclipse-eyebrow">Guide for Eclipse ' . $h(eclipse_theme_version()) . '</span><h3>Discover Theme Studio</h3><p>Use this short guide to configure the theme safely and understand where your choices are stored.</p></header><ol class="eclipse-onboarding"><li><b>Choose a palette</b><span>Start with a preset, then adjust individual colors. Check the contrast badges before saving.</span></li><li><b>Set layout and typography</b><span>Choose the reading width, spacing and type family that suit your content.</span></li><li><b>Review appearance and regions</b><span>Configure navigation, cards, buttons, header, footer and sidebars.</span></li><li><b>Test in Preview</b><span>Compare desktop, tablet and mobile without changing the live site.</span></li><li><b>Save and verify</b><span>Apply the settings, then check public and administration pages. Eclipse clears only its theme caches automatically.</span></li></ol><div class="eclipse-documentation-sections"><details open><summary>Preview, drafts and saving</summary><p>Color changes are previewed immediately. They are not applied site-wide until <b>Save Eclipse settings</b> is used. A successful save clears only Eclipse template and generated CSS cache entries. <b>Cancel preview</b> returns the form to its initial values.</p></details><details><summary>Palettes and accessibility</summary><p>Contrast badges report text and interface-color ratios. Prefer AA or AAA results. Warning and destructive-action colors remain protected from palette presets to avoid ambiguous buttons.</p></details><details><summary>Import, export and history</summary><p>Export settings before major changes. Imported JSON is treated as a draft for review. Successful saves create snapshots that can be restored from Settings history.</p></details><details><summary>Updates and rollback</summary><p>The Updates tab accepts a versioned Eclipse ZIP selected from your computer. The installer validates its contents, creates a backup and clears only Eclipse theme caches after replacement. Use Restore a theme backup if a deployment must be reversed.</p></details><details><summary>Storage and permissions</summary><p>Settings, footer links, palettes and history are protected JSON documents in the multisite-safe sibling directory <code>{path_data}-eclipse/</code>, outside Geeklog\'s cache-cleaning scope. Historical <code>vars</code> records and legacy JSON under <code>path_data</code> are migration sources only.</p></details><details><summary>Administration shortcuts</summary><p>Modern workspace provides a dark administration header and navigation groups that are folded by default. Expand a group heading to show its permission-filtered links. Press <kbd>Ctrl</kbd>+<kbd>K</kbd> on Windows/Linux or <kbd>Command</kbd>+<kbd>K</kbd> on macOS to open the command palette.</p></details><details><summary>Troubleshooting</summary><ul><li>If styling appears unchanged after a manual upload, clear Geeklog\'s resource and template caches once, then force-reload the browser.</li><li>If an archive is refused, verify that it contains a single <code>eclipse/</code> directory and only supported file types.</li><li>If settings cannot be saved, verify that PHP can write to the sibling <code>{path_data}-eclipse/</code> directory.</li><li>Use the backup browser to return to the previous theme files after a failed update.</li></ul></details></div></section></section>';
    $html = str_replace(
        '<details><summary>Storage and permissions</summary><p>Settings, footer links, palettes and history are stored persistently in Geeklog\'s <code>vars</code> table. Legacy JSON files in <code>path_data</code> are migrated automatically and are no longer required after migration.</p></details>',
        '<details><summary>Storage, migration and permissions</summary><p>Settings, footer links, palettes and history are protected JSON documents in a sibling directory derived from <code>path_data</code>, outside Geeklog\'s cache-cleaning scope. Existing RC56-RC58 records in <code>vars</code> and older JSON files under <code>path_data</code> are copied automatically without deleting their source. Writes use locks, a validated temporary file, atomic replacement and a <code>.bak</code> recovery copy. Theme ZIP backups remain under Geeklog\'s private <code>backups/eclipse</code> directory.</p></details>',
        $html
    );
    $html = str_replace(
        '<details><summary>Import, export and history</summary><p>Export settings before major changes. Imported JSON is treated as a draft for review. Successful saves create snapshots that can be restored from Settings history.</p></details>',
        '<details><summary>Import, export and history</summary><p>The versioned export contains settings, footer links and named palettes. Legacy flat settings exports remain accepted. An import is only a local draft until <b>Save complete Eclipse state</b> is pressed. Up to twenty complete snapshots are retained, and a safety snapshot is created before restoration.</p></details>',
        $html
    );
    $html = str_replace('If settings cannot be saved, verify that Geeklog can update its <code>vars</code> table.', 'If settings cannot be saved, verify that the protected sibling JSON storage is writable.', $html);
    $html = str_replace(
        '<details><summary>Administration shortcuts</summary><p>Press <kbd>Ctrl</kbd>+<kbd>K</kbd> on Windows/Linux or <kbd>Command</kbd>+<kbd>K</kbd> on macOS to open the command palette. The dashboard customizer can hide and reorder administration cards locally in the browser.</p></details>',
        '<details><summary>Eclipse Admin UI</summary><p>On Command &amp; Control, Modern workspace derives Core, Plugins, Tools and Users directly from Geeklog\'s permission-filtered dashboard groups and adds the existing Theme Studio link. The main area shows useful attention links, authorized quick actions and bounded recent-content panels; optional panels disappear when the feature or required right is unavailable. Other administration pages use native administrative blocks as a fallback.</p></details>',
        $html
    );
    $html .= '<section id="eclipse-backup-browser" class="eclipse-backup-browser"><div><span class="eclipse-eyebrow">Rollback</span><h3>Restore a theme backup</h3><p>The current theme is backed up again before restoration.</p></div><form method="post"><input type="hidden" name="' . $h($tokenName) . '" value="' . $h($token) . '"><label><span>Available backup</span><select name="eclipse_backup_name" required><option value="">Select a backup</option>';
    foreach ($themeBackups as $backupName) $html .= '<option value="' . $h($backupName) . '">' . $h($backupName) . '</option>';
    $html .= '</select></label><button type="submit" name="eclipse_backup_restore" value="1"' . (!$themeBackups ? ' disabled' : '') . '>Restore selected backup</button></form></section>';
    $html = str_replace('</p></header><ol class="eclipse-onboarding">', '</p>' . $environmentHtml . '</header><ol class="eclipse-onboarding">', $html);
    return $html;
}

function eclipse_admin_studio_source()
{
    global $_CONF;
    if (!eclipse_is_admin_request() || !function_exists('SEC_inGroup') || !SEC_inGroup('Root') || empty($_CONF['site_admin_url'])) return '';
    return '<a class="eclipse-native-studio-link" href="' . htmlspecialchars(rtrim($_CONF['site_admin_url'], '/') . '/index.php#eclipse-theme-studio', ENT_QUOTES, 'UTF-8') . '">Theme Studio</a>';
}

function eclipse_theme_version()
{
    static $version;
    if (isset($version)) return $version;
    $ini = @parse_ini_file(__DIR__ . '/theme.ini', true);
    $version = isset($ini['theme']['version']) ? $ini['theme']['version'] : 'unknown';
    return $version;
}

function eclipse_install_uploaded_update($upload)
{
    global $_CONF;
    $fail = function ($message) { return array('success' => false, 'message' => $message); };
    if (!class_exists('ZipArchive')) return $fail('The PHP ZipArchive extension is not available.');
    if (!is_array($upload) || !isset($upload['error']) || $upload['error'] !== UPLOAD_ERR_OK) return $fail('The ZIP upload failed.');
    if (empty($upload['tmp_name']) || !is_uploaded_file($upload['tmp_name'])) return $fail('The uploaded file could not be verified.');
    if (empty($upload['size']) || $upload['size'] > 20971520) return $fail('The archive must be smaller than 20 MB.');
    if (!preg_match('/\.zip$/i', isset($upload['name']) ? $upload['name'] : '')) return $fail('Only ZIP archives are accepted.');
    $dataDir = !empty($_CONF['path_data']) ? rtrim($_CONF['path_data'], '/\\') : '';
    if ($dataDir === '' || !is_dir($dataDir) || !is_writable($dataDir)) return $fail('The Geeklog data directory is missing or not writable.');
    $workRoot = $dataDir . DIRECTORY_SEPARATOR . 'eclipse-updates';
    if (!is_dir($workRoot) && !@mkdir($workRoot, 0750, true)) return $fail('Unable to create data/eclipse-updates.');
    $job = $workRoot . DIRECTORY_SEPARATOR . 'update-' . date('Ymd-His') . '-' . substr(sha1(uniqid('', true)), 0, 8);
    if (!@mkdir($job, 0750, true)) return $fail('Unable to create the temporary update directory.');
    $zip = new ZipArchive();
    if ($zip->open($upload['tmp_name']) !== true) return $fail('The uploaded archive is not a readable ZIP file.');
    $allowed = '/\.(?:php|ini|thtml|thtmlx|css|js|json|md|txt|html|svg|png|jpe?g|gif|ico)$/i';
    for ($i = 0; $i < $zip->numFiles; $i++) {
        $name = str_replace('\\', '/', $zip->getNameIndex($i));
        if ($name === '' || strpos($name, "\0") !== false || $name[0] === '/' || preg_match('#(^|/)\.\.(/|$)#', $name) || strpos($name, 'eclipse/') !== 0) {
            $zip->close(); eclipse_remove_tree($job); return $fail('Unsafe or unexpected path in the archive: ' . $name);
        }
        if (substr($name, -1) === '/') continue;
        if (!preg_match($allowed, $name)) {
            $zip->close(); eclipse_remove_tree($job); return $fail('File type not allowed in the archive: ' . $name);
        }
        if (method_exists($zip, 'getExternalAttributesIndex')) {
            $opsys = 0; $attr = 0;
            if ($zip->getExternalAttributesIndex($i, $opsys, $attr) && (($attr >> 16) & 0170000) === 0120000) {
                $zip->close(); eclipse_remove_tree($job); return $fail('Symbolic links are not allowed in updates.');
            }
        }
        $target = $job . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $name);
        $parent = dirname($target);
        if (!is_dir($parent) && !@mkdir($parent, 0750, true)) {
            $zip->close(); eclipse_remove_tree($job); return $fail('Unable to prepare the temporary update tree.');
        }
        $source = $zip->getStream($zip->getNameIndex($i));
        $destination = @fopen($target, 'wb');
        if (!$source || !$destination) {
            if (is_resource($source)) fclose($source); if (is_resource($destination)) fclose($destination);
            $zip->close(); eclipse_remove_tree($job); return $fail('Unable to extract ' . $name);
        }
        stream_copy_to_stream($source, $destination); fclose($source); fclose($destination);
    }
    $zip->close();
    $sourceTheme = $job . DIRECTORY_SEPARATOR . 'eclipse';
    $manifest = $sourceTheme . DIRECTORY_SEPARATOR . 'theme.ini';
    $functions = $sourceTheme . DIRECTORY_SEPARATOR . 'functions.php';
    if (!is_file($manifest) || !is_file($functions)) { eclipse_remove_tree($job); return $fail('The ZIP does not contain a complete eclipse/ theme.'); }
    $ini = @parse_ini_file($manifest, true);
    $newVersion = isset($ini['theme']['version']) ? $ini['theme']['version'] : '';
    if (!preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', $newVersion)) { eclipse_remove_tree($job); return $fail('The archive has no valid Eclipse version.'); }
    $integrityError = eclipse_verify_package_manifest($sourceTheme, $newVersion);
    if ($integrityError !== '') { eclipse_remove_tree($job); return $fail($integrityError); }
    $themeDir = __DIR__;
    if (!is_dir($themeDir) || !is_writable($themeDir)) { eclipse_remove_tree($job); return $fail('The eclipse theme directory is not writable by PHP.'); }
    $backupRoot = eclipse_persistent_root();
    if ($backupRoot === '' || (!is_dir($backupRoot) && !@mkdir($backupRoot, 0750, true))) { eclipse_remove_tree($job); return $fail('Unable to create the persistent Eclipse backup directory.'); }
    $backup = $backupRoot . DIRECTORY_SEPARATOR . 'eclipse-' . date('Ymd-His');
    if (!eclipse_copy_tree($themeDir, $backup, 0750, 0640)) { eclipse_remove_tree($job); return $fail('Unable to create the safety backup. No update was applied.'); }
    if (!eclipse_copy_tree($sourceTheme, $themeDir, 0755, 0644)) { eclipse_remove_tree($job); return $fail('The update copy failed. Restore the latest persistent Eclipse backup.'); }
    eclipse_remove_tree($job);
    $cacheMessage = eclipse_clear_theme_cache() ? ' Eclipse template and generated CSS cache entries were cleared.' : ' Eclipse cache entries could not be cleared automatically.';
    return array('success' => true, 'message' => 'Eclipse ' . $newVersion . ' installed successfully.' . $cacheMessage . ' Reload the page; refresh the browser only if an older asset remains visible.');
}

function eclipse_verify_package_manifest($themeRoot, $expectedVersion)
{
    $manifestPath = $themeRoot . DIRECTORY_SEPARATOR . 'MANIFEST.json';
    if (!is_file($manifestPath)) return 'The Eclipse package integrity manifest is missing.';
    $manifest = json_decode(@file_get_contents($manifestPath), true);
    if (!is_array($manifest) || !isset($manifest['version'], $manifest['algorithm'], $manifest['files']) || $manifest['version'] !== $expectedVersion || strtolower($manifest['algorithm']) !== 'sha256' || !is_array($manifest['files'])) return 'The Eclipse package integrity manifest is invalid.';
    $expected = array();
    foreach ($manifest['files'] as $relative => $hash) {
        $relative = str_replace('\\', '/', (string) $relative);
        if ($relative === '' || $relative === 'MANIFEST.json' || $relative[0] === '/' || strpos($relative, '../') !== false || !preg_match('/^[0-9A-Za-z._\/-]+$/', $relative) || !preg_match('/^[0-9a-f]{64}$/i', $hash)) return 'The Eclipse package integrity manifest contains an unsafe entry.';
        $path = $themeRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
        if (!is_file($path) || strtolower(hash_file('sha256', $path)) !== strtolower($hash)) return 'Package integrity check failed for ' . $relative . '.';
        $expected[$relative] = true;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($themeRoot, FilesystemIterator::SKIP_DOTS));
    foreach ($iterator as $file) {
        if (!$file->isFile() || $file->isLink()) continue;
        $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($themeRoot) + 1));
        if ($relative !== 'MANIFEST.json' && !isset($expected[$relative])) return 'Unexpected file missing from the integrity manifest: ' . $relative . '.';
    }
    return '';
}

function eclipse_copy_tree($source, $destination, $directoryMode = 0750, $fileMode = 0640)
{
    if (!is_dir($destination) && !@mkdir($destination, $directoryMode, true)) return false;
    @chmod($destination, $directoryMode);
    $handle = @opendir($source); if (!$handle) return false;
    $ok = true;
    while (($name = readdir($handle)) !== false) {
        if ($name === '.' || $name === '..') continue;
        $from = $source . DIRECTORY_SEPARATOR . $name; $to = $destination . DIRECTORY_SEPARATOR . $name;
        if (is_link($from)) { $ok = false; break; }
        if (is_dir($from)) { if (!eclipse_copy_tree($from, $to, $directoryMode, $fileMode)) { $ok = false; break; } }
        elseif (!@copy($from, $to)) { $ok = false; break; }
        else { @chmod($to, $fileMode); }
    }
    closedir($handle); return $ok;
}

function eclipse_remove_tree($path)
{
    if (!is_dir($path) || is_link($path)) return;
    $handle = @opendir($path); if (!$handle) return;
    while (($name = readdir($handle)) !== false) {
        if ($name === '.' || $name === '..') continue;
        $item = $path . DIRECTORY_SEPARATOR . $name;
        if (is_dir($item) && !is_link($item)) eclipse_remove_tree($item); else @unlink($item);
    }
    closedir($handle); @rmdir($path);
}

function eclipse_theme_classes()
{
    $o = eclipse_theme_options();
    $allowed = array(
        'color_scheme' => array('light', 'dark', 'auto'), 'spacing' => array('compact', 'normal', 'relaxed'),
        'radius' => array('none', 'small', 'medium', 'large'), 'sidebar_position' => array('left', 'right'),
        'button_style' => array('solid', 'outline', 'soft'), 'menu_style' => array('floating', 'capsule', 'editorial', 'contrast'), 'block_style' => array('card', 'bordered', 'flat'),
        'header_style' => array('gradient', 'solid', 'minimal'), 'footer_style' => array('dark', 'light', 'minimal'), 'admin_ui_mode' => array('modern', 'classic'), 'admin_navigation_source' => array('left', 'right', 'both'),
    );
    $classes = array('theme-eclipse');
    foreach ($allowed as $key => $values) {
        $value = in_array($o[$key], $values, true) ? $o[$key] : $values[0];
        $classes[] = str_replace('_', '-', $key) . '-' . $value;
    }
    $classes[] = !empty($o['show_left_sidebar']) ? 'show-left-sidebar' : 'hide-left-sidebar';
    $classes[] = !empty($o['show_right_sidebar']) ? 'show-right-sidebar' : 'hide-right-sidebar';
    $classes[] = !empty($o['editor_hide_sidebars']) ? 'editor-sidebars-hidden' : 'editor-sidebars-visible';
    $classes[] = !empty($o['share_facebook']) ? 'share-facebook-on' : 'share-facebook-off';
    $classes[] = !empty($o['share_linkedin']) ? 'share-linkedin-on' : 'share-linkedin-off';
    $classes[] = !empty($o['share_x']) ? 'share-x-on' : 'share-x-off';
    return implode(' ', $classes);
}

function eclipse_theme_css_variables()
{
    $o = eclipse_theme_options();
    $color = '/^#[0-9a-fA-F]{6}$/';
    $length = '/^(?:[1-9][0-9]{0,3}(?:px|rem)|[1-9][0-9]{0,2}ch)$/';
    $fonts = array('system' => 'system-ui, -apple-system, "Segoe UI", sans-serif', 'serif' => 'Georgia, "Times New Roman", serif', 'humanist' => 'Verdana, Geneva, sans-serif');
    $vars = array(
        '--eclipse-primary' => preg_match($color, $o['color_primary']) ? $o['color_primary'] : '#3157d5',
        '--eclipse-secondary' => preg_match($color, $o['color_secondary']) ? $o['color_secondary'] : '#6750a4',
        '--eclipse-link' => preg_match($color, $o['color_link']) ? $o['color_link'] : '#2448bd',
        '--eclipse-bg' => preg_match($color, $o['color_background']) ? $o['color_background'] : '#f4f6fb',
        '--eclipse-surface' => preg_match($color, $o['color_surface']) ? $o['color_surface'] : '#ffffff',
        '--eclipse-text' => preg_match($color, $o['color_text']) ? $o['color_text'] : '#202431',
        '--eclipse-max' => preg_match($length, $o['site_max_width']) ? $o['site_max_width'] : '1200px',
        '--eclipse-reading' => preg_match($length, $o['reading_width']) ? $o['reading_width'] : '72ch',
        '--eclipse-font' => isset($fonts[$o['font_family']]) ? $fonts[$o['font_family']] : $fonts['system'],
        '--eclipse-font-size' => preg_match('/^(?:1[4-9]|2[0-2])px$/', $o['font_size']) ? $o['font_size'] : '16px',
    );
    $css = ':root{';
    foreach ($vars as $name => $value) {
        $css .= $name . ':' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . ';';
    }
    return $css . '}';
}

function eclipse_theme_asset($key)
{
    $o = eclipse_theme_options();
    $value = isset($o[$key]) ? str_replace('\\', '/', $o[$key]) : '';
    return preg_match('#^(?:images/)?[a-zA-Z0-9._/-]+$#', $value) ? htmlspecialchars($value, ENT_QUOTES, 'UTF-8') : '';
}

?>
