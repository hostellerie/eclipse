<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'admin-dashboard.php') !== false) die('This file can not be used on its own!');

/*
 * Geeklog 2.1.x decides between its legacy table-based configuration UI and
 * the Geeklog 2.x div-based UI from min_theme_gl_version via
 * supported_version_theme. Eclipse uses the modern markup, so make that
 * capability explicit only on the supported 2.1.x branch. Geeklog 2.2.x is
 * left untouched.
 */
if (defined('VERSION') && version_compare(VERSION, '2.1.1', '>=') && version_compare(VERSION, '2.2.0', '<')) {
    global $_CONF;
    $_CONF['min_theme_gl_version'] = '2.0.0';
}

function eclipse_admin_dashboard_h($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function eclipse_admin_dashboard_rows($sql, $limit)
{
    $rows = array();
    if (!function_exists('DB_query') || !function_exists('DB_fetchArray')) return $rows;
    $result = @DB_query($sql . ' LIMIT ' . (int) $limit, 1);
    if (!$result) return $rows;
    while ($row = DB_fetchArray($result)) $rows[] = $row;
    return $rows;
}

function eclipse_admin_get_recent_stories($drafts)
{
    global $_TABLES;
    if (!function_exists('SEC_hasRights') || !SEC_hasRights('story.edit') || empty($_TABLES['stories'])) return array();
    $users = !empty($_TABLES['users']) ? $_TABLES['users'] : '';
    $join = $users !== '' ? " LEFT JOIN {$users} u ON u.uid=s.uid" : '';
    $username = $users !== '' ? ',u.username' : ",'' AS username";
    $sql = "SELECT s.sid,s.title,s.date{$username} FROM {$_TABLES['stories']} s{$join} WHERE s.draft_flag=" . ($drafts ? '1' : '0');
    if (!$drafts) $sql .= ' AND s.date<=NOW()';
    if (function_exists('COM_getPermSQL')) $sql .= COM_getPermSQL('AND', 0, 3, 's');
    return eclipse_admin_dashboard_rows($sql . ' ORDER BY s.date DESC', $drafts ? 4 : 5);
}

function eclipse_admin_get_recent_comments()
{
    global $_TABLES;
    if (!function_exists('SEC_hasRights') || !SEC_hasRights('comment.moderate') || empty($_TABLES['comments'])) return array();
    $users = !empty($_TABLES['users']) ? $_TABLES['users'] : '';
    $join = $users !== '' ? " LEFT JOIN {$users} u ON u.uid=c.uid" : '';
    $username = $users !== '' ? ',u.username' : ",'' AS username";
    return eclipse_admin_dashboard_rows("SELECT c.cid,c.sid,c.type,c.comment,c.date{$username} FROM {$_TABLES['comments']} c{$join} ORDER BY c.date DESC", 5);
}

function eclipse_admin_get_recent_staticpages()
{
    global $_TABLES, $_PLUGINS;
    if (!is_array($_PLUGINS) || !in_array('staticpages', $_PLUGINS) || !function_exists('SEC_hasRights') || !SEC_hasRights('staticpages.edit') || empty($_TABLES['staticpage'])) return array();
    $sql = "SELECT sp_id,sp_title,modified FROM {$_TABLES['staticpage']} WHERE draft_flag=0 AND template_flag=0";
    if (function_exists('COM_getPermSQL')) $sql .= COM_getPermSQL('AND', 0, 3);
    return eclipse_admin_dashboard_rows($sql . ' ORDER BY modified DESC', 4);
}

function eclipse_admin_dashboard_date($value)
{
    $time = strtotime((string) $value);
    return $time ? date('Y-m-d', $time) : '';
}

function eclipse_admin_dashboard_render()
{
    global $_CONF;
    if (!eclipse_is_admin_request() || eclipse_admin_page() !== 'index') return '';
    $stories = eclipse_admin_get_recent_stories(false); $drafts = eclipse_admin_get_recent_stories(true);
    $comments = eclipse_admin_get_recent_comments(); $pages = eclipse_admin_get_recent_staticpages();
    $admin = rtrim($_CONF['site_admin_url'], '/'); $site = rtrim($_CONF['site_url'], '/');
    $articleScript = defined('VERSION') && version_compare(VERSION, '2.2.0', '>=') ? 'article.php' : 'story.php';
    $html = '<section class="eclipse-admin-dashboard-data" aria-label="Editorial overview">';
    if ($stories) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>Recent stories</h2><ul>';
        foreach ($stories as $row) {
            $sid = rawurlencode($row['sid']); $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h(eclipse_admin_dashboard_date($row['date'])) . (!empty($row['username']) ? ' &middot; ' . eclipse_admin_dashboard_h($row['username']) : '') . '</small></div><span><a href="' . $site . '/article.php?story=' . $sid . '">View</a><a href="' . $admin . '/' . $articleScript . '?mode=edit&amp;sid=' . $sid . '">Edit</a></span></li>';
        }
        $html .= '</ul></article>';
    }
    if ($comments) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>Recent comments</h2><ul>';
        foreach ($comments as $row) {
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags($row['comment']))); if (strlen($plain) > 100) $plain = substr($plain, 0, 97) . '...';
            $context = (!empty($row['type']) ? $row['type'] . ' ' : '') . (!empty($row['sid']) ? $row['sid'] . ' ' : '') . eclipse_admin_dashboard_date($row['date']);
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h(!empty($row['username']) ? $row['username'] : 'Anonymous') . '</strong><small>' . eclipse_admin_dashboard_h($plain) . '</small><small>' . eclipse_admin_dashboard_h(trim($context)) . '</small></div><a href="' . $site . '/comment.php?mode=view&amp;cid=' . (int) $row['cid'] . '">View</a></li>';
        }
        $html .= '</ul></article>';
    }
    if ($drafts) {
        $html .= '<article class="eclipse-dashboard-widget eclipse-dashboard-widget-wide"><h2>Drafts</h2><ul>';
        foreach ($drafts as $row) $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h(eclipse_admin_dashboard_date($row['date'])) . '</small></div><a href="' . $admin . '/' . $articleScript . '?mode=edit&amp;sid=' . rawurlencode($row['sid']) . '">Continue</a></li>';
        $html .= '</ul></article>';
    }
    if ($pages) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>Recent Static Pages</h2><ul>';
        foreach ($pages as $row) $html .= '<li><strong>' . eclipse_admin_dashboard_h($row['sp_title']) . '</strong><a href="' . $admin . '/plugins/staticpages/index.php?mode=edit&amp;sp_id=' . rawurlencode($row['sp_id']) . '">Edit</a></li>';
        $html .= '</ul></article>';
    }
    return $html . '</section>';
}
