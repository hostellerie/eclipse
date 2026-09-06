<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'language.php') !== false) {
    die('This file can not be used on its own!');
}

function eclipse_language_file_name()
{
    global $_CONF, $LANG_ISO639_1;

    $language = isset($_CONF['language']) ? strtolower((string) $_CONF['language']) : '';
    $language = preg_replace('/\.php$/', '', $language);

    if (strpos($language, 'french') === 0 || (!empty($LANG_ISO639_1) && strtolower($LANG_ISO639_1) === 'fr')) {
        return 'french';
    }

    return 'english';
}

function eclipse_language_read($name)
{
    global $LANG_ECLIPSE, $ECLIPSE_LANG_EXTRA;

    $directory = dirname(__DIR__) . '/language/';
    $LANG_ECLIPSE = array();
    $ECLIPSE_LANG_EXTRA = array();

    $file = $directory . $name . '.php';
    if (is_file($file)) {
        include $file;
    }
    $strings = is_array($LANG_ECLIPSE) ? $LANG_ECLIPSE : array();

    $extra = $directory . $name . '-extra.php';
    if (is_file($extra)) {
        include $extra;
        if (is_array($ECLIPSE_LANG_EXTRA)) {
            $strings = array_merge($strings, $ECLIPSE_LANG_EXTRA);
        }
    }

    return $strings;
}

function eclipse_load_language()
{
    global $LANG_ECLIPSE;

    static $loaded = false;
    if ($loaded && isset($LANG_ECLIPSE) && is_array($LANG_ECLIPSE)) {
        return $LANG_ECLIPSE;
    }

    $english = eclipse_language_read('english');
    $selected = eclipse_language_file_name();

    if ($selected !== 'english') {
        $LANG_ECLIPSE = array_merge($english, eclipse_language_read($selected));
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
        'manage_comments', 'review_submissions', 'add_block', 'add_user',
        'commands', 'close_command_palette', 'administration_commands',
        'search_commands', 'search_administration_commands'
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
        'Create up to eight link rows. Data is stored as protected JSON outside Geeklog\'s cache directory and survives theme updates and cache cleaning.' => 'footer_links_intro',
        'Import a complete versioned draft containing settings, footer links and palettes. Review it, then save.' => 'import_export_intro',
        'The current theme is backed up again before restoration.' => 'restore_theme_backup_intro',
        'Contrast badges report text and interface-color ratios. Prefer AA or AAA results. Warning and destructive-action colors remain protected from palette presets to avoid ambiguous buttons.' => 'palettes_accessibility_text',
        'The Updates tab accepts a versioned Eclipse ZIP selected from your computer. The installer validates its contents, creates a backup and clears only Eclipse theme caches after replacement. Use Restore a theme backup if a deployment must be reversed.' => 'updates_rollback_text',
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
        'Footer links' => 'footer_links',
        'Link row' => 'link_row',
        'Remove row' => 'remove_row',
        'Add link' => 'add_link',
        'Add link row' => 'add_link_row',
        'Rollback' => 'rollback',
        'Restore a theme backup' => 'restore_theme_backup',
        'Available backup' => 'available_backup',
        'Select a backup' => 'select_backup',
        'Restore selected backup' => 'restore_selected_backup',
        'Preview, drafts and saving' => 'preview_drafts_saving',
        'Palettes and accessibility' => 'palettes_accessibility',
        'Import, export and history' => 'import_export_history',
        'Updates and rollback' => 'updates_rollback',
        'Storage and permissions' => 'storage_permissions',
        'Administration shortcuts' => 'administration_shortcuts',
        'Troubleshooting' => 'troubleshooting',
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

    $fragmentMap = array(
        'Leave Sitemap path empty to use Geeklog <code>sitemap_file</code>. Use <code>auto</code> for the active Geeklog language. Public SEO metadata and advertising are omitted from administration pages.' => 'seo_integrations_intro',
        'Color changes are previewed immediately. They are not applied site-wide until <b>Save Eclipse settings</b> is used. A successful save clears only Eclipse template and generated CSS cache entries. <b>Cancel preview</b> returns the form to its initial values.' => 'preview_drafts_saving_text',
        'The versioned export contains settings, footer links and named palettes. Legacy flat settings exports remain accepted. An import is only a local draft until <b>Save complete Eclipse state</b> is pressed. Up to twenty complete snapshots are retained, and a safety snapshot is created before restoration.' => 'import_export_history_text',
        'Settings, footer links, palettes and history are protected JSON documents in the multisite-safe sibling directory <code>{path_data}-eclipse/</code>, outside Geeklog\'s cache-cleaning scope. Historical <code>vars</code> records and legacy JSON under <code>path_data</code> are migration sources only.' => 'storage_permissions_text',
        'Modern workspace provides a dark administration header and navigation groups that are folded by default. Expand a group heading to show its permission-filtered links. Press <kbd>Ctrl</kbd>+<kbd>K</kbd> on Windows/Linux or <kbd>Command</kbd>+<kbd>K</kbd> on macOS to open the command palette.' => 'administration_shortcuts_text',
        'If styling appears unchanged after a manual upload, clear Geeklog\'s resource and template caches once, then force-reload the browser.' => 'troubleshooting_1',
        'If an archive is refused, verify that it contains a single <code>eclipse/</code> directory and only supported file types.' => 'troubleshooting_2',
        'If settings cannot be saved, verify that PHP can write to the sibling <code>{path_data}-eclipse/</code> directory.' => 'troubleshooting_3',
        'Use the backup browser to return to the previous theme files after a failed update.' => 'troubleshooting_4'
    );

    foreach ($fragmentMap as $source => $key) {
        $html = str_replace($source, htmlspecialchars(eclipse_lang($key, strip_tags($source)), ENT_QUOTES, 'UTF-8'), $html);
    }

    $guidePrefix = htmlspecialchars(eclipse_lang('guide_for_eclipse', 'Guide for Eclipse'), ENT_QUOTES, 'UTF-8');
    $html = str_replace('>Guide for Eclipse ', '>' . $guidePrefix . ' ', $html);

    return $html;
}
