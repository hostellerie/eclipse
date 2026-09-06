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
        'Changes are stored as protected JSON outside Geeklog\'s cache directory.' => 'theme_studio_storage_note',
        'Choose the share links displayed on full article pages. No third-party script or request is loaded before a visitor clicks a link.' => 'social_sharing_intro',
        'Select a versioned ZIP from your computer. A backup is created before files are replaced, then the Geeklog template and generated CSS caches are cleared automatically.' => 'update_archive_intro',
        'Use this short guide to configure the theme safely and understand where your choices are stored.' => 'theme_studio_guide_intro',
        'Start with a preset, then adjust individual colors. Check the contrast badges before saving.' => 'choose_palette_help',
        'Choose the reading width, spacing and type family that suit your content.' => 'set_layout_typography_help',
        'Configure navigation, cards, buttons, header, footer and sidebars.' => 'review_appearance_regions_help',
        'Compare desktop, tablet and mobile without changing the live site.' => 'test_in_preview_help',
        'Apply the settings, then check public and administration pages. Eclipse clears only its theme caches automatically.' => 'save_and_verify_help',
        'Display the Geeklog topic name as an H1 on topic index pages' => 'display_topic_h1',
        'Hide sidebars in story editor' => 'hide_sidebars_story_editor',
        'Left and right blocks' => 'left_and_right_blocks',
        'Save complete Eclipse state' => 'save_complete_state',
        'Export complete Eclipse state' => 'export_complete_state',
        'Restore complete snapshot' => 'restore_complete_snapshot',
        'Complete state history' => 'complete_state_history',
        'Install an Eclipse archive' => 'install_archive',
        'Load the Google AdSense script' => 'load_adsense_script',
        'Sitemap URL or relative path' => 'sitemap_url_path',
        'Administration interface' => 'administration_interface',
        'Admin navigation blocks' => 'admin_navigation_blocks',
        'Set layout and typography' => 'set_layout_typography',
        'Review appearance and regions' => 'review_appearance_regions',
        'Discover Theme Studio' => 'discover_theme_studio',
        'Theme Studio sections' => 'theme_studio_sections',
        'Portability and history' => 'portability_and_history',
        'Delete named palette' => 'delete_named_palette',
        'Save named palette' => 'save_named_palette',
        'Brand and regions' => 'brand_and_regions',
        'SEO and integrations' => 'seo_and_integrations',
        'Google AdSense client' => 'google_adsense_client',
        'Modern workspace' => 'modern_workspace',
        'Gradient capsule' => 'gradient_capsule',
        'Editorial serif' => 'editorial_serif',
        'Floating glass' => 'floating_glass',
        'Editorial line' => 'editorial_line',
        'Contrast dock' => 'contrast_dock',
        'Aurora gradient' => 'aurora_gradient',
        'Classic Eclipse' => 'classic_eclipse',
        'Preset palette' => 'preset_palette',
        'Custom colors' => 'custom_colors',
        'Eclipse default' => 'eclipse_default',
        'Live preview' => 'live_preview',
        'Palette preview' => 'palette_preview',
        'Palette name' => 'palette_name',
        'Layout and type' => 'layout_and_type',
        'Reset palette' => 'reset_palette',
        'Reset section' => 'reset_section',
        'Reading width' => 'reading_width',
        'Administration' => 'administration',
        'Menu composition' => 'menu_composition',
        'Back to dashboard' => 'back_to_dashboard',
        'Social sharing' => 'social_sharing',
        'HTML language' => 'html_language',
        'Import or export' => 'import_or_export',
        'Saved snapshot' => 'saved_snapshot',
        'Select a snapshot' => 'select_snapshot',
        'Preview width' => 'preview_width',
        'No unsaved changes' => 'no_unsaved_changes',
        'Cancel preview' => 'cancel_preview',
        'Restore defaults' => 'restore_defaults',
        'Local update' => 'local_update',
        'Archive ZIP' => 'archive_zip',
        'Install update' => 'install_update',
        'Choose a palette' => 'choose_palette',
        'Test in Preview' => 'test_in_preview',
        'Save and verify' => 'save_and_verify',
        'Theme studio' => 'theme_studio_title',
        'Ocean blue' => 'ocean_blue',
        'Forest green' => 'forest_green',
        'Warm sunset' => 'warm_sunset',
        'Site width' => 'site_width',
        'Base size' => 'base_size',
        'Modern system' => 'modern_system',
        'Very rounded' => 'very_rounded',
        'Color mode' => 'color_mode',
        'Right blocks' => 'right_blocks',
        'Left blocks' => 'left_blocks',
        'Header image' => 'header_image',
        'Logo path' => 'logo_path',
        'Left sidebar' => 'left_sidebar',
        'Right sidebar' => 'right_sidebar',
        'Mobile menu' => 'mobile_menu',
        'Design' => 'design',
        'Preview' => 'preview',
        'Updates' => 'updates',
        'Documentation' => 'documentation',
        'Palette' => 'palette',
        'Primary' => 'primary',
        'Secondary' => 'secondary',
        'Links' => 'links',
        'Page' => 'page',
        'Cards' => 'cards',
        'Text' => 'text',
        'Typography' => 'typography',
        'Humanist' => 'humanist',
        'Spacing' => 'spacing',
        'Compact' => 'compact',
        'Balanced' => 'balanced',
        'Airy' => 'airy',
        'Corners' => 'corners',
        'Square' => 'square',
        'Subtle' => 'subtle',
        'Rounded' => 'rounded',
        'Appearance' => 'appearance',
        'System' => 'system',
        'Light' => 'light',
        'Dark' => 'dark',
        'Elevated' => 'elevated',
        'Outlined' => 'outlined',
        'Flat' => 'flat',
        'Buttons' => 'buttons',
        'Solid' => 'solid',
        'Outline' => 'outline',
        'Soft' => 'soft',
        'Header' => 'header',
        'Minimal' => 'minimal',
        'Footer' => 'footer',
        'Sidebar' => 'sidebar',
        'Right' => 'right',
        'Left' => 'left',
        'Desktop' => 'desktop',
        'Tablet' => 'tablet',
        'Mobile' => 'mobile',
        'Settings' => 'settings'
    );

    foreach ($map as $english => $key) {
        $translation = htmlspecialchars(eclipse_lang($key, $english), ENT_QUOTES, 'UTF-8');
        $html = str_replace('>' . $english . '<', '>' . $translation . '<', $html);
        $html = str_replace('="' . $english . '"', '="' . $translation . '"', $html);
    }

    $guidePrefix = htmlspecialchars(eclipse_lang('guide_for_eclipse', 'Guide for Eclipse'), ENT_QUOTES, 'UTF-8');
    $html = str_replace('>Guide for Eclipse ', '>' . $guidePrefix . ' ', $html);

    return $html;
}
