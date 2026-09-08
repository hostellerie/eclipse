<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'admin-dashboard.php') !== false) die('This file can not be used on its own!');

require_once __DIR__ . '/language.php';
require_once __DIR__ . '/zip-compat.php';

if (!empty($_CONF['path_data']) && function_exists('CTL_clearCache')) {
    $eclipseManifest = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'MANIFEST.json';
    $eclipseBuildFingerprint = is_file($eclipseManifest) ? @hash_file('sha256', $eclipseManifest) : '';
    if (is_string($eclipseBuildFingerprint) && $eclipseBuildFingerprint !== '') {
        $eclipseBuildMarker = rtrim($_CONF['path_data'], '/\\') . DIRECTORY_SEPARATOR . '.eclipse-theme-build';
        $eclipsePreviousFingerprint = is_file($eclipseBuildMarker) ? trim((string) @file_get_contents($eclipseBuildMarker)) : '';
        if ($eclipsePreviousFingerprint !== $eclipseBuildFingerprint) {
            CTL_clearCache();
            @file_put_contents($eclipseBuildMarker, $eclipseBuildFingerprint . "\n", LOCK_EX);
            @chmod($eclipseBuildMarker, 0640);
        }
    }
}

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

function eclipse_admin_discover_plugin_stats()
{
    $rows = array();
    if (!function_exists('PLG_getPluginStats')) return $rows;
    $all = PLG_getPluginStats(3);
    if (!is_array($all)) return $rows;
    foreach ($all as $plugin => $summary) {
        if (!is_array($summary)) continue;
        $items = isset($summary[0]) && is_array($summary[0]) ? $summary : array($summary);
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item[0]) || !isset($item[1])) continue;
            $rows[] = array('plugin' => (string) $plugin, 'label' => (string) $item[0], 'value' => (string) $item[1]);
        }
    }
    return $rows;
}

function eclipse_admin_discover_plugin_whatsnew()
{
    $rows = array();
    if (!function_exists('PLG_getWhatsNew')) return $rows;
    $data = PLG_getWhatsNew();
    if (!is_array($data) || count($data) < 3 || !is_array($data[0]) || !is_array($data[2])) return $rows;
    $headlines = $data[0];
    $content = $data[2];
    foreach ($content as $index => $entries) {
        $headline = isset($headlines[$index]) ? trim(strip_tags((string) $headlines[$index])) : '';
        if ($headline === '') $headline = eclipse_lang('plugin_activity', 'Plugin activity');
        if (!is_array($entries)) $entries = array($entries);
        $count = 0;
        foreach ($entries as $entry) {
            if ($count >= 4) break;
            $html = trim((string) $entry);
            if ($html === '') continue;
            $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));
            if ($text === '') continue;
            if (strlen($text) > 120) $text = substr($text, 0, 117) . '...';
            $rows[] = array('headline' => $headline, 'html' => $html, 'text' => $text);
            $count++;
        }
    }
    return $rows;
}

function eclipse_admin_discover_feeds()
{
    global $_TABLES;
    $rows = array();
    if (!function_exists('SEC_hasRights') || !SEC_hasRights('syndication.edit') || empty($_TABLES['syndication'])) return $rows;
    $sql = "SELECT fid,title,type,format,filename,updated FROM {$_TABLES['syndication']} WHERE is_enabled=1 ORDER BY title ASC";
    return eclipse_admin_dashboard_rows($sql, 8);
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
    $pluginStats = eclipse_admin_discover_plugin_stats(); $pluginNews = eclipse_admin_discover_plugin_whatsnew(); $feeds = eclipse_admin_discover_feeds();
    $admin = rtrim($_CONF['site_admin_url'], '/'); $site = rtrim($_CONF['site_url'], '/');
    $articleScript = defined('VERSION') && version_compare(VERSION, '2.2.0', '>=') ? 'article.php' : 'story.php';
    $html = '<section class="eclipse-admin-dashboard-data" aria-label="' . eclipse_admin_dashboard_h(eclipse_lang('editorial_overview')) . '">';

    if ($pluginStats) {
        $html .= '<article class="eclipse-dashboard-widget eclipse-dashboard-widget-stats"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('plugin_statistics', 'Plugin statistics')) . '</h2><ul>';
        foreach ($pluginStats as $row) {
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['label']) . '</strong><small>' . eclipse_admin_dashboard_h($row['plugin']) . '</small></div><span class="eclipse-dashboard-value">' . eclipse_admin_dashboard_h($row['value']) . '</span></li>';
        }
        $html .= '</ul></article>';
    }

    if ($feeds) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('syndication_feeds', 'Syndication feeds')) . '</h2><ul>';
        foreach ($feeds as $row) {
            $meta = trim((string) $row['type']);
            if (!empty($row['format'])) $meta .= ($meta !== '' ? ' · ' : '') . $row['format'];
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h($meta) . '</small></div><a href="' . $admin . '/syndication.php?mode=edit&amp;fid=' . (int) $row['fid'] . '">' . eclipse_admin_dashboard_h(eclipse_lang('manage', 'Manage')) . '</a></li>';
        }
        $html .= '</ul></article>';
    }

    if ($pluginNews) {
        $html .= '<article class="eclipse-dashboard-widget eclipse-dashboard-widget-wide"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('plugin_activity', 'Plugin activity')) . '</h2><ul>';
        foreach ($pluginNews as $row) {
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['headline']) . '</strong><small>' . eclipse_admin_dashboard_h($row['text']) . '</small></div></li>';
        }
        $html .= '</ul></article>';
    }

    if ($stories) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('recent_stories')) . '</h2><ul>';
        foreach ($stories as $row) {
            $sid = rawurlencode($row['sid']); $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h(eclipse_admin_dashboard_date($row['date'])) . (!empty($row['username']) ? ' &middot; ' . eclipse_admin_dashboard_h($row['username']) : '') . '</small></div><span><a href="' . $site . '/article.php?story=' . $sid . '">' . eclipse_admin_dashboard_h(eclipse_lang('view')) . '</a><a href="' . $admin . '/' . $articleScript . '?mode=edit&amp;sid=' . $sid . '">' . eclipse_admin_dashboard_h(eclipse_lang('edit')) . '</a></span></li>';
        }
        $html .= '</ul></article>';
    }
    if ($comments) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('recent_comments')) . '</h2><ul>';
        foreach ($comments as $row) {
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags($row['comment']))); if (strlen($plain) > 100) $plain = substr($plain, 0, 97) . '...';
            $context = (!empty($row['type']) ? $row['type'] . ' ' : '') . (!empty($row['sid']) ? $row['sid'] . ' ' : '') . eclipse_admin_dashboard_date($row['date']);
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h(!empty($row['username']) ? $row['username'] : eclipse_lang('anonymous')) . '</strong><small>' . eclipse_admin_dashboard_h($plain) . '</small><small>' . eclipse_admin_dashboard_h(trim($context)) . '</small></div><a href="' . $site . '/comment.php?mode=view&amp;cid=' . (int) $row['cid'] . '">' . eclipse_admin_dashboard_h(eclipse_lang('view')) . '</a></li>';
        }
        $html .= '</ul></article>';
    }
    if ($drafts) {
        $html .= '<article class="eclipse-dashboard-widget eclipse-dashboard-widget-wide"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('drafts')) . '</h2><ul>';
        foreach ($drafts as $row) $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h(eclipse_admin_dashboard_date($row['date'])) . '</small></div><a href="' . $admin . '/' . $articleScript . '?mode=edit&amp;sid=' . rawurlencode($row['sid']) . '">' . eclipse_admin_dashboard_h(eclipse_lang('continue')) . '</a></li>';
        $html .= '</ul></article>';
    }
    if ($pages) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('recent_static_pages')) . '</h2><ul>';
        foreach ($pages as $row) $html .= '<li><strong>' . eclipse_admin_dashboard_h($row['sp_title']) . '</strong><a href="' . $admin . '/plugins/staticpages/index.php?mode=edit&amp;sp_id=' . rawurlencode($row['sp_id']) . '">' . eclipse_admin_dashboard_h(eclipse_lang('edit')) . '</a></li>';
        $html .= '</ul></article>';
    }
    return $html . '</section>';
}
