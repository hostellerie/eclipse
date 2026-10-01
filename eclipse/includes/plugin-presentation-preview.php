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

    if ((string) $plugin !== 'menu') {
        return '';
    }

    $resource = trim((string) $resource);
    if ($resource === '') {
        return '';
    }

    $menuType = isset($context['menu_type']) ? (int) $context['menu_type'] : 0;
    switch ($menuType) {
        case 1:
            $menuContext = 'primary';
            break;
        case 2:
            $menuContext = 'footer';
            break;
        case 3:
        case 4:
            $menuContext = 'sidebar';
            break;
        default:
            $menuContext = 'inline';
            break;
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

    $html = eclipse_menu_render($resource, $menuContext);
    if ($html === '') {
        $html = '<p class="eclipse-menu-preview-empty">Menu preview is empty for the current administrator.</p>';
    }

    if ($menuContext === 'primary') {
        $content = '<div id="navigation" class="navigation_bg eclipse-plugin-menu" role="navigation" aria-label="Main navigation">'
            . '<div class="navigation_content"><div id="eclipse-menu-panel">'
            . $html
            . '</div></div></div>';
    } elseif ($menuContext === 'footer') {
        $content = '<footer id="footer" role="contentinfo"><nav class="eclipse-menu-footer-region" aria-label="Footer navigation">'
            . $html
            . '</nav></footer>';
    } elseif ($menuContext === 'sidebar') {
        $content = '<aside class="eclipse-menu-preview-sidebar" aria-label="Sidebar navigation">'
            . $html
            . '</aside>';
    } else {
        $content = '<div class="eclipse-menu-preview-inline">' . $html . '</div>';
    }

    return '<!DOCTYPE html>' . "\n"
        . '<html><head><meta charset="utf-8">' . "\n"
        . '<meta name="viewport" content="width=device-width, initial-scale=1">' . "\n"
        . $head
        . '<style>html,body{margin:0;padding:0;background:transparent}body{padding:12px;box-sizing:border-box;min-height:80px}.eclipse-menu-preview-sidebar{max-width:20rem}.eclipse-menu-preview-sidebar .eclipse-menu-root{display:block;margin:0;padding:0;list-style:none}.eclipse-menu-preview-sidebar .eclipse-menu-root ul{margin:.25rem 0 .25rem .9rem;padding-left:.7rem;border-left:1px solid var(--eclipse-border)}.eclipse-menu-preview-sidebar a,.eclipse-menu-preview-sidebar .eclipse-menu-label{display:block;padding:.35rem .45rem;color:var(--eclipse-text);text-decoration:none;border-radius:.35rem}.eclipse-menu-preview-sidebar a:hover,.eclipse-menu-preview-sidebar a:focus-visible,.eclipse-menu-preview-sidebar a[aria-current=page]{color:var(--eclipse-primary);background:color-mix(in srgb,var(--eclipse-primary) 7%,transparent)}</style>' . "\n"
        . '</head><body class="theme-eclipse eclipse-public-page footer-style-dark">' . "\n"
        . $content . "\n"
        . '</body></html>';
}
