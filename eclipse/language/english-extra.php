<?php

$ECLIPSE_LANG_EXTRA = array(
    'footer_links' => 'Footer links',
    'footer_links_intro' => 'Create up to eight link rows. Data is stored as protected JSON outside Geeklog\'s cache directory and survives theme updates and cache cleaning.',
    'link_row' => 'Link row',
    'remove_row' => 'Remove row',
    'add_link' => 'Add link',
    'add_link_row' => 'Add link row',
    'label' => 'Label',
    'url_or_path' => 'URL or path',
    'remove' => 'Remove',
    'enabled' => 'Enabled',
    'emphasize' => 'Emphasize',
    'new_window' => 'New window',
    'nofollow' => 'Nofollow',
    'copyright_line' => 'Copyright line',
    'legal_notice' => 'Legal notice',
    'load_adsense_script' => 'Load the Google AdSense script',
    'display_topic_h1' => 'Display the Geeklog topic name as an H1 on topic index pages',
    'rollback' => 'Rollback',
    'restore_theme_backup' => 'Restore a theme backup',
    'restore_theme_backup_intro' => 'The current theme is backed up again before restoration.',
    'available_backup' => 'Available backup',
    'select_backup' => 'Select a backup',
    'restore_selected_backup' => 'Restore selected backup',
    'preview_drafts_saving' => 'Preview, drafts and saving',
    'preview_drafts_saving_text' => 'Color changes are previewed immediately. They are not applied site-wide until Save Eclipse settings is used. A successful save clears only Eclipse template and generated CSS cache entries. Cancel preview returns the form to its initial values.',
    'palettes_accessibility' => 'Palettes and accessibility',
    'palettes_accessibility_text' => 'Contrast badges report text and interface-color ratios. Prefer AA or AAA results. Warning and destructive-action colors remain protected from palette presets to avoid ambiguous buttons.',
    'import_export_history' => 'Import, export and history',
    'import_export_history_text' => 'The versioned export contains settings, footer links and named palettes. Legacy flat settings exports remain accepted. An import is only a local draft until Save complete Eclipse state is pressed. Up to twenty complete snapshots are retained, and a safety snapshot is created before restoration.',
    'updates_rollback' => 'Updates and rollback',
    'updates_rollback_text' => 'The Updates tab accepts a versioned Eclipse ZIP selected from your computer. The installer validates its contents, creates a backup and clears only Eclipse theme caches after replacement. Use Restore a theme backup if a deployment must be reversed.',
    'storage_permissions' => 'Storage and permissions',
    'storage_permissions_text' => 'Settings, footer links, palettes and history are protected JSON documents in the multisite-safe sibling directory {path_data}-eclipse/, outside Geeklog\'s cache-cleaning scope. Historical vars records and legacy JSON under path_data are migration sources only.',
    'administration_shortcuts' => 'Administration shortcuts',
    'administration_shortcuts_text' => 'Modern workspace provides a dark administration header and navigation groups that are folded by default. Expand a group heading to show its permission-filtered links. Press Ctrl+K on Windows/Linux or Command+K on macOS to open the command palette.',
    'troubleshooting' => 'Troubleshooting',
    'troubleshooting_1' => 'If styling appears unchanged after a manual upload, clear Geeklog\'s resource and template caches once, then force-reload the browser.',
    'troubleshooting_2' => 'If an archive is refused, verify that it contains a single eclipse/ directory and only supported file types.',
    'troubleshooting_3' => 'If settings cannot be saved, verify that PHP can write to the sibling {path_data}-eclipse/ directory.',
    'troubleshooting_4' => 'Use the backup browser to return to the previous theme files after a failed update.',
    'zip_updates' => 'ZIP updates',
    'available' => 'Available',
    'ziparchive_missing' => 'ZipArchive missing',
    'persistent_json' => 'Persistent JSON',
    'writable_sibling_storage' => 'Writable sibling storage',
    'not_writable' => 'Not writable',
    'native_footer_block' => 'Geeklog footer block',
    'native_footer_help' => 'Control the standard three-column Geeklog footer block. Each column can use the Geeklog default or a custom value, including an intentionally empty value.',
    'show_native_footer' => 'Display the standard Geeklog footer block',
    'footer_above_line' => 'Line above the footer block',
    'footer_above_line_help' => 'Accepts Geeklog autotags, for example [menu:footer].',
    'footer_powered_by_geeklog' => 'Powered by Geeklog',
    'footer_powered_by_help' => 'Use the default option to keep the native Powered by Geeklog and page generation time.',
    'footer_use_geeklog_default' => 'Use Geeklog default content',
    'footer_custom_empty_help' => 'Uncheck the default option and leave this field empty to display an empty column.',
    'footer_powered_line' => 'Powered by line',
    'footer_execution_line' => 'Execution line',
    'footer_blank_keeps_default' => 'Leave empty to keep the Geeklog default.',
    'footer_legal_repurpose_help' => 'Copyright line and Legal notice replace columns 1 and 2 of the standard footer instead of adding duplicate lines above it.',
    'commands' => 'Commands',
    'close_command_palette' => 'Close command palette',
    'administration_commands' => 'Administration commands',
    'search_commands' => 'Search commands…',
    'search_administration_commands' => 'Search administration commands'
);

if (!function_exists('eclipse_translate_theme_studio_remaining_html')) {
    function eclipse_translate_theme_studio_remaining_html($html)
    {
        if (!function_exists('eclipse_language_file_name') || eclipse_language_file_name() === 'english' || $html === '') {
            return $html;
        }

        $map = array(
            'Load the Google AdSense script' => 'load_adsense_script',
            'Display the Geeklog topic name as an H1 on topic index pages' => 'display_topic_h1',
            'Hide sidebars in story editor' => 'hide_sidebars_story_editor',
            'Left sidebar' => 'left_sidebar',
            'Right sidebar' => 'right_sidebar',
            'Mobile menu' => 'mobile_menu',
            'Copyright line' => 'copyright_line',
            'Legal notice' => 'legal_notice',
            'URL or path' => 'url_or_path',
            'New window' => 'new_window',
            'Emphasize' => 'emphasize',
            'Enabled' => 'enabled',
            'Nofollow' => 'nofollow',
            'Label' => 'label',
            'Remove' => 'remove',
            'ZIP updates' => 'zip_updates',
            'Available' => 'available',
            'ZipArchive missing' => 'ziparchive_missing',
            'Persistent JSON' => 'persistent_json',
            'Writable sibling storage' => 'writable_sibling_storage',
            'Not writable' => 'not_writable'
        );

        foreach ($map as $english => $key) {
            $translation = htmlspecialchars(eclipse_lang($key, $english), ENT_QUOTES, 'UTF-8');
            $html = str_replace('>' . $english . '<', '>' . $translation . '<', $html);
            $html = str_replace('> ' . $english . '<', '> ' . $translation . '<', $html);
        }

        return $html;
    }
}
