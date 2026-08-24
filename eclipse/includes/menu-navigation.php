<?php

if (!defined('VERSION')) {
    die('This file can not be used on its own!');
}

/**
 * Render the Menu plugin navigation using its resolved-tree API when available.
 * Falls back to the historical MENU_getMenu() HTML renderer for older Menu
 * versions or trees containing an unresolved legacy callback.
 *
 * @return string
 */
function eclipse_menu_navigation_resolved()
{
    if (!eclipse_menu_plugin_active()) {
        return '';
    }

    if (function_exists('MENU_getResolvedTree')) {
        $tree = MENU_getResolvedTree('navigation');
        if (is_array($tree) && !empty($tree) && eclipse_menu_tree_is_resolved($tree)) {
            return '<div class="eclipse-menu">'
                . eclipse_menu_render_tree($tree, true)
                . '</div>';
        }
    }

    if (function_exists('MENU_getMenu')) {
        return MENU_getMenu('navigation', 'eclipse-menu', 'eclipse-menu-root',
            'eclipse-menu-item', 'eclipse-menu-parent', 'eclipse-menu-last',
            'eclipse-menu-current', 1);
    }

    return '';
}

/**
 * Return false when a tree contains a node which Menu explicitly reports as
 * unresolved. This keeps legacy PHP callback elements working through the old
 * renderer until they have a structured provider.
 *
 * @param array $nodes
 * @return bool
 */
function eclipse_menu_tree_is_resolved($nodes)
{
    foreach ($nodes as $node) {
        if (!is_array($node)) {
            return false;
        }
        if (isset($node['resolved']) && !$node['resolved']) {
            return false;
        }
        if (!empty($node['children']) && !eclipse_menu_tree_is_resolved($node['children'])) {
            return false;
        }
    }

    return true;
}

/**
 * Tell whether a node contains the currently selected page below it.
 *
 * This is deliberately distinct from node['selected']: only the actual link
 * receives aria-current="page", while ancestors receive an active-trail CSS
 * class for visual context.
 *
 * @param array $node
 * @return bool
 */
function eclipse_menu_node_has_selected_descendant($node)
{
    if (empty($node['children']) || !is_array($node['children'])) {
        return false;
    }

    foreach ($node['children'] as $child) {
        if (!is_array($child)) {
            continue;
        }
        if (!empty($child['selected']) || eclipse_menu_node_has_selected_descendant($child)) {
            return true;
        }
    }

    return false;
}

/**
 * Render a resolved Menu tree using Eclipse-owned markup.
 *
 * Menu remains responsible for labels, hierarchy, permissions, ordering,
 * targets and resolved URLs. Eclipse owns only the HTML/CSS/JS presentation.
 *
 * @param array $nodes
 * @param bool  $root
 * @return string
 */
function eclipse_menu_render_tree($nodes, $root = false)
{
    if (!is_array($nodes) || empty($nodes)) {
        return '';
    }

    $html = $root ? '<ul class="eclipse-menu-root">' : '<ul>';
    $count = count($nodes);
    $index = 0;

    foreach ($nodes as $node) {
        $index++;
        if (!is_array($node)) {
            continue;
        }

        $children = isset($node['children']) && is_array($node['children'])
            ? $node['children'] : array();
        $hasChildren = !empty($children);
        $selected = !empty($node['selected']);
        $activeTrail = !$selected && eclipse_menu_node_has_selected_descendant($node);
        $type = isset($node['type']) ? (int) $node['type'] : 0;

        $classes = array('eclipse-menu-item');
        if ($hasChildren) {
            $classes[] = 'eclipse-has-submenu';
        }
        if ($selected) {
            $classes[] = 'eclipse-menu-current';
        }
        if ($activeTrail) {
            $classes[] = 'eclipse-menu-active-trail';
        }
        if ($index === $count) {
            $classes[] = 'eclipse-menu-last';
        }

        $label = isset($node['label']) ? (string) $node['label'] : '';
        $url = isset($node['url']) ? (string) $node['url'] : '';
        $target = isset($node['target']) ? (string) $node['target'] : '';

        $html .= '<li class="' . htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8') . '">';

        // Type 8 is intentionally a non-link/menu-heading item.
        if ($type === 8) {
            $html .= '<span class="eclipse-menu-label">'
                . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</span>';
        } else {
            if ($url === '') {
                $url = '#';
            }
            $linkClass = $hasChildren ? ' class="eclipse-menu-parent"' : '';
            $html .= '<a' . $linkClass
                . ' href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '"';
            if ($target !== '') {
                $html .= ' target="' . htmlspecialchars($target, ENT_QUOTES, 'UTF-8') . '"';
                if ($target === '_blank') {
                    $html .= ' rel="noopener noreferrer"';
                }
            }
            if ($selected) {
                $html .= ' aria-current="page"';
            }
            if ($hasChildren) {
                $html .= ' aria-haspopup="true"';
            }
            $html .= '>' . htmlspecialchars($label, ENT_QUOTES, 'UTF-8') . '</a>';
        }

        if ($hasChildren) {
            $html .= eclipse_menu_render_tree($children, false);
        }

        $html .= '</li>';
    }

    $html .= '</ul>';
    return $html;
}
