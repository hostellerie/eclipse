<?php

if (!defined('VERSION')) {
    die('This file can not be used on its own!');
}

require_once __DIR__ . '/menu-navigation.php';

/**
 * Render a complete isolated preview document for plugin presentation owned by
 * Eclipse. The generic callback name is consumed by plugins such as Menu; the
 * plugin never needs to know the active theme name.
 *
 * @param string $plugin
 * @param string $resource
 * @param array  $context
 * @return string
 */
function theme_plugin_presentation_preview($plugin, $resource, $context = array())
{
    global $_CONF;

    if ((string) $plugin !== 'menu' || strcasecmp((string) $resource, 'navigation') !== 0) {
        return '';
    }

    $layoutUrl = isset($_CONF['layout_url']) ? rtrim($_CONF['layout_url'], '/') : '';
    $styles = array(
        'css/variables.css',
        'css/base.css',
        'css/layout.css',
        'css/components.css',
        'css/menu.css',
        'css/menu-refinements.css',
    );

    $head = '';
    foreach ($styles as $style) {
        $head .= '<link rel="stylesheet" href="'
            . htmlspecialchars($layoutUrl . '/' . $style, ENT_QUOTES, 'UTF-8')
            . '">' . "\n";
    }

    $html = eclipse_menu_navigation_resolved();
    if ($html === '') {
        $html = '<p class="eclipse-menu-preview-empty">Menu preview is empty for the current administrator.</p>';
    }

    return '<!DOCTYPE html>' . "\n"
        . '<html><head><meta charset="utf-8">' . "\n"
        . '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n"
        . $head
        . '<style>html,body{margin:0;padding:0;background:transparent}body{padding:12px;box-sizing:border-box;min-height:80px}</style>' . "\n"
        . '</head><body class="theme-eclipse eclipse-public-page">' . "\n"
        . '<div id="navigation" class="navigation_bg eclipse-plugin-menu" role="navigation" aria-label="Main navigation">'
        . '<div class="navigation_content"><div id="eclipse-menu-panel">'
        . $html
        . '</div></div></div>' . "\n"
        . '</body></html>';
}
