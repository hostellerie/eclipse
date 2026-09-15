<?php

if (!defined('VERSION')) {
    die('This file can not be used on its own!');
}

/**
 * Render the Menu plugin navigation using its resolved-tree API when available.
 * Unresolved legacy callback nodes are omitted from the structured rendering
 * instead of forcing the entire navigation back through MENU_getMenu().
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
        if (is_array($tree) && !empty($tree)) {
            $resolvedTree = eclipse_menu_filter_resolved_nodes($tree);
            if (!empty($resolvedTree)) {
                return '<div class="eclipse-menu">'
                    . eclipse_menu_render_tree($resolvedTree, true)
                    . '</div>';
            }
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
 * unresolved.
 *
 * Retained as a small public helper for compatibility/tests. The navigation
 * renderer now filters unresolved legacy nodes instead of abandoning the whole
 * structured tree.
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
 * Keep only nodes that Menu can represent as structured navigation data.
 *
 * A legacy PHP callback may legitimately return arbitrary HTML and therefore
 * be marked resolved=false by Menu. That one item must not force every other
 * resolved node (including semantic admin warnings) through the legacy HTML
 * renderer.
 *
 * @param array $nodes
 * @return array
 */
function eclipse_menu_filter_resolved_nodes($nodes)
{
    $filtered = array();

    if (!is_array($nodes)) {
        return $filtered;
    }

    foreach ($nodes as $node) {
        if (!is_array($node)) {
            continue;
        }
        if (isset($node['resolved']) && !$node['resolved']) {
            continue;
        }

        if (isset($node['children']) && is_array($node['children'])) {
            $node['children'] = eclipse_menu_filter_resolved_nodes($node['children']);
        } else {
            $node['children'] = array();
        }

        $filtered[] = $node;
    }

    return $filtered;
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
 * Return a presentation-safe semantic status supplied by Menu.
 *
 * Menu owns the meaning; Eclipse owns the visual representation. Unknown
 * values are deliberately ignored so arbitrary data can never become a CSS
 * class.
 *
 * @param array $node
 * @return string
 */
function eclipse_menu_node_status($node)
{
    if (!is_array($node) || !isset($node['status'])) {
        return '';
    }

    $status = strtolower(trim((string) $node['status']));
    return in_array($status, array('info', 'success', 'warning', 'danger'), true)
        ? $status : '';
}

/**
 * Normalize a Menu label to plain text before Eclipse escapes it for output.
 *
 * Newer Menu versions already provide presentation-neutral labels. This
 * defensive layer also handles older/current installations where a plugin
 * control arrived as raw or HTML-encoded markup, including double-encoded
 * wrappers. Semantic color remains driven exclusively by node['status'].
 *
 * @param mixed $label
 * @return string
 */
function eclipse_menu_plain_label($label)
{
    $plain = (string) $label;

    for ($i = 0; $i < 2; $i++) {
        $decoded = html_entity_decode($plain, ENT_QUOTES, 'UTF-8');
        if ($decoded === $plain) {
            break;
        }
        $plain = $decoded;
    }

    $plain = strip_tags($plain);
    $plain = preg_replace('/\s+/u', ' ', $plain);

    return trim((string) $plain);
}

/**
 * Render a resolved Menu tree using Eclipse-owned markup.
 *
 * Menu remains responsible for labels, hierarchy, permissions, ordering,
 * targets, resolved URLs and semantic status. Eclipse owns only the
 * HTML/CSS/JS presentation.
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
        $status = eclipse_menu_node_status($node);

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
        if ($status !== '') {
            $classes[] = 'eclipse-menu-status-' . $status;
        }
        if ($index === $count) {
            $classes[] = 'eclipse-menu-last';
        }

        $label = eclipse_menu_plain_label(isset($node['label']) ? $node['label'] : '');
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
