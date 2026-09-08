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

function eclipse_admin_dashboard_count_table($table, $where)
{
    if (!$table || !function_exists('DB_query') || !function_exists('DB_fetchArray')) return 0;
    $sql = 'SELECT COUNT(*) AS eclipse_count FROM ' . $table;
    if ($where !== '') $sql .= ' WHERE ' . $where;
    $result = @DB_query($sql, 1);
    if (!$result) return 0;
    $row = DB_fetchArray($result);
    return isset($row['eclipse_count']) ? max(0, (int) $row['eclipse_count']) : 0;
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

function eclipse_admin_get_draft_count()
{
    global $_TABLES;
    if (!function_exists('SEC_hasRights') || !SEC_hasRights('story.edit') || empty($_TABLES['stories'])) return 0;
    $sql = "SELECT COUNT(*) AS eclipse_count FROM {$_TABLES['stories']} s WHERE s.draft_flag=1";
    if (function_exists('COM_getPermSQL')) $sql .= COM_getPermSQL('AND', 0, 3, 's');
    $result = @DB_query($sql, 1);
    if (!$result) return 0;
    $row = DB_fetchArray($result);
    return isset($row['eclipse_count']) ? max(0, (int) $row['eclipse_count']) : 0;
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

function eclipse_admin_get_top_stories()
{
    global $_TABLES;
    if (empty($_TABLES['stories'])) return array();
    $sql = "SELECT s.sid,s.title,s.hits FROM {$_TABLES['stories']} s";
    if (!empty($_TABLES['topic_assignments'])) {
        $sql .= ", {$_TABLES['topic_assignments']} ta WHERE ta.type='article' AND ta.id=s.sid";
    } else {
        $sql .= ' WHERE 1=1';
    }
    $sql .= ' AND s.draft_flag=0 AND s.date<=NOW() AND s.hits>0';
    if (function_exists('COM_getPermSQL')) $sql .= COM_getPermSQL('AND', 0, 3, 's');
    if (!empty($_TABLES['topic_assignments']) && function_exists('COM_getTopicSql')) $sql .= COM_getTopicSql('AND', 0, 'ta');
    $sql .= ' GROUP BY s.sid,s.title,s.hits ORDER BY s.hits DESC';
    return eclipse_admin_dashboard_rows($sql, 3);
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
            $label = trim(strip_tags((string) $item[0]));
            $value = trim(strip_tags((string) $item[1]));
            if ($label === '' || $value === '') continue;
            $rows[] = array('plugin' => (string) $plugin, 'label' => $label, 'value' => $value);
        }
    }
    return $rows;
}

function eclipse_admin_whatsnew_entry($headline, $entry)
{
    $html = trim((string) $entry);
    if ($html === '') return null;
    $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
    if ($text === '') return null;

    $url = '';
    $label = $text;
    if (preg_match('/<a\b[^>]*\bhref\s*=\s*(["\'])(.*?)\1[^>]*>(.*?)<\/a>/is', $html, $match)) {
        $candidate = html_entity_decode(trim($match[2]), ENT_QUOTES, 'UTF-8');
        if (preg_match('#^(?:https?://|/)#i', $candidate) && strpos($candidate, '..') === false) $url = $candidate;
        $anchorText = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($match[3]), ENT_QUOTES, 'UTF-8')));
        if ($anchorText !== '') $label = $anchorText;
    }

    $date = '';
    if (preg_match('/\b(20[0-9]{2}[-\/.][0-9]{1,2}[-\/.][0-9]{1,2})\b/', $text, $dateMatch)) $date = str_replace(array('/', '.'), '-', $dateMatch[1]);
    elseif (preg_match('/\b([0-9]{1,2}[-\/.][0-9]{1,2}[-\/.]20[0-9]{2})\b/', $text, $dateMatch)) $date = str_replace(array('/', '.'), '-', $dateMatch[1]);

    if (strlen($text) > 160) $text = substr($text, 0, 157) . '...';
    if (strlen($label) > 120) $label = substr($label, 0, 117) . '...';
    return array('headline' => $headline, 'label' => $label, 'text' => $text, 'url' => $url, 'date' => $date);
}

function eclipse_admin_discover_plugin_whatsnew()
{
    $groups = array();
    if (!function_exists('PLG_getWhatsNew')) return $groups;
    $data = PLG_getWhatsNew();
    if (!is_array($data) || count($data) < 3 || !is_array($data[0]) || !is_array($data[2])) return $groups;
    $headlines = $data[0];
    $content = $data[2];
    foreach ($content as $index => $entries) {
        $headline = isset($headlines[$index]) ? trim(strip_tags((string) $headlines[$index])) : '';
        if ($headline === '') $headline = eclipse_lang('plugin_activity', 'Plugin activity');
        if (!is_array($entries)) $entries = array($entries);
        $items = array();
        foreach ($entries as $entry) {
            if (count($items) >= 4) break;
            $item = eclipse_admin_whatsnew_entry($headline, $entry);
            if ($item === null) continue;
            if ($item['url'] === '' && strcasecmp($item['text'], $headline) === 0) continue;
            $items[] = $item;
        }
        if ($items) $groups[] = array('headline' => $headline, 'items' => $items);
    }
    return $groups;
}

function eclipse_admin_discover_feeds()
{
    global $_TABLES;
    $rows = array();
    if (!function_exists('SEC_hasRights') || !SEC_hasRights('syndication.edit') || empty($_TABLES['syndication'])) return $rows;
    $sql = "SELECT fid,title,type,format,filename,updated FROM {$_TABLES['syndication']} WHERE is_enabled=1 ORDER BY title ASC,fid ASC";
    return eclipse_admin_dashboard_rows($sql, 12);
}

function eclipse_admin_attention_data($draftCount)
{
    global $_CONF, $_TABLES;
    $data = array('drafts' => max(0, (int) $draftCount), 'comments' => 0, 'submissions' => 0, 'plugin_submissions' => 0);
    if (function_exists('SEC_hasRights') && SEC_hasRights('comment.moderate') && !empty($_CONF['commentsubmission']) && !empty($_TABLES['commentsubmissions'])) {
        $data['comments'] = eclipse_admin_dashboard_count_table($_TABLES['commentsubmissions'], '1=1');
    }
    if (function_exists('SEC_hasRights') && SEC_hasRights('story.moderate') && !empty($_TABLES['storysubmission'])) {
        $data['submissions'] = eclipse_admin_dashboard_count_table($_TABLES['storysubmission'], '1=1');
    }
    if (function_exists('PLG_getSubmissionCount')) {
        $pluginCount = PLG_getSubmissionCount();
        if (is_numeric($pluginCount)) $data['plugin_submissions'] = max(0, (int) $pluginCount);
    }
    return $data;
}

function eclipse_admin_dashboard_date($value)
{
    $time = strtotime((string) $value);
    return $time ? date('Y-m-d', $time) : '';
}

function eclipse_admin_dashboard_labels()
{
    global $_CONF;
    $language = isset($_CONF['language']) ? strtolower((string) $_CONF['language']) : '';
    $fr = strpos($language, 'french') === 0;
    if ($fr) {
        return array(
            'comments' => 'Commentaires en attente',
            'submissions' => 'Soumissions à examiner',
            'drafts' => 'Brouillons à terminer',
            'manage_comments' => 'Gérer les commentaires',
            'review_submissions' => 'Examiner les soumissions',
            'empty' => 'Aucun élément ne nécessite votre attention.'
        );
    }
    return array(
        'comments' => 'Comments awaiting moderation',
        'submissions' => 'Submissions awaiting review',
        'drafts' => 'Draft stories to finish',
        'manage_comments' => 'Manage comments',
        'review_submissions' => 'Review submissions',
        'empty' => 'Nothing currently requires your attention.'
    );
}

function eclipse_admin_dashboard_overview_enhancer($attention)
{
    global $_CONF;
    $admin = rtrim($_CONF['site_admin_url'], '/');
    $articleScript = defined('VERSION') && version_compare(VERSION, '2.2.0', '>=') ? 'article.php' : 'story.php';
    $labels = eclipse_admin_dashboard_labels();
    $payload = array(
        'drafts' => (int) $attention['drafts'],
        'comments' => (int) $attention['comments'],
        'submissions' => (int) $attention['submissions'] + (int) $attention['plugin_submissions'],
        'commentsUrl' => $admin . '/comment.php',
        'moderationUrl' => $admin . '/moderation.php',
        'draftsUrl' => $admin . '/' . $articleScript,
        'labels' => $labels
    );
    $json = json_encode($payload);
    if (!is_string($json)) return '';
    return '<script>(function(){var data=' . $json . ';var done=false;function badge(n){var s=document.createElement("span");s.className="eclipse-attention-count";s.textContent=String(n);return s;}function run(){if(done)return true;var attention=document.querySelector(".eclipse-overview-attention");var actions=document.querySelector(".eclipse-overview-actions ul");if(!attention||!actions)return false;done=true;var old=attention.querySelector("ul");if(old)old.remove();var empty=attention.querySelector("p");if(empty)empty.remove();var entries=[];if(data.comments>0)entries.push({label:data.labels.comments,count:data.comments,url:data.commentsUrl});if(data.submissions>0)entries.push({label:data.labels.submissions,count:data.submissions,url:data.moderationUrl});if(data.drafts>0)entries.push({label:data.labels.drafts,count:data.drafts,url:data.draftsUrl,draft:true});if(!entries.length){var p=document.createElement("p");p.textContent=data.labels.empty;attention.appendChild(p);}else{var ul=document.createElement("ul");entries.forEach(function(e){var li=document.createElement("li");if(e.draft)li.className="is-draft";var a=document.createElement("a");a.href=e.url;a.appendChild(document.createTextNode(e.label));a.appendChild(badge(e.count));li.appendChild(a);ul.appendChild(li);});attention.appendChild(ul);}function ensure(re,label,url,count){var links=Array.prototype.slice.call(actions.querySelectorAll("a"));var link=links.find(function(a){return re.test(a.href);});if(!link){var li=document.createElement("li");link=document.createElement("a");link.href=url;link.textContent=label;li.appendChild(link);actions.appendChild(li);}if(count>0&&!link.querySelector(".eclipse-attention-count"))link.appendChild(badge(count));}ensure(/\/admin\/comment\.php/i,data.labels.manage_comments,data.commentsUrl,data.comments);ensure(/\/admin\/moderation\.php/i,data.labels.review_submissions,data.moderationUrl,data.submissions);return true;}if(!run()){var observer=new MutationObserver(function(){if(run())observer.disconnect();});observer.observe(document.documentElement,{childList:true,subtree:true});setTimeout(function(){observer.disconnect();run();},3000);}}());</script>';
}

function eclipse_admin_dashboard_render()
{
    global $_CONF;
    if (!eclipse_is_admin_request() || eclipse_admin_page() !== 'index') return '';
    $stories = eclipse_admin_get_recent_stories(false);
    $drafts = eclipse_admin_get_recent_stories(true);
    $draftCount = eclipse_admin_get_draft_count();
    $comments = eclipse_admin_get_recent_comments();
    $pages = eclipse_admin_get_recent_staticpages();
    $topStories = eclipse_admin_get_top_stories();
    $pluginStats = eclipse_admin_discover_plugin_stats();
    $pluginNews = eclipse_admin_discover_plugin_whatsnew();
    $feeds = eclipse_admin_discover_feeds();
    $attention = eclipse_admin_attention_data($draftCount);
    $admin = rtrim($_CONF['site_admin_url'], '/');
    $site = rtrim($_CONF['site_url'], '/');
    $articleScript = defined('VERSION') && version_compare(VERSION, '2.2.0', '>=') ? 'article.php' : 'story.php';
    $html = '<section class="eclipse-admin-dashboard-data" aria-label="' . eclipse_admin_dashboard_h(eclipse_lang('editorial_overview')) . '">';

    if ($pluginStats) {
        $html .= '<article class="eclipse-dashboard-widget eclipse-dashboard-widget-stats"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('plugin_statistics', 'Plugin statistics')) . '</h2><ul>';
        foreach ($pluginStats as $row) {
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['label']) . '</strong><small>' . eclipse_admin_dashboard_h($row['plugin']) . '</small></div><span class="eclipse-dashboard-value">' . eclipse_admin_dashboard_h($row['value']) . '</span></li>';
        }
        $html .= '</ul>';
        if ($topStories) {
            $html .= '<div class="eclipse-dashboard-top-content"><h3>' . eclipse_admin_dashboard_h(eclipse_lang('most_viewed', 'Most viewed content')) . '</h3><ol>';
            foreach ($topStories as $row) {
                $html .= '<li><a href="' . $site . '/article.php?story=' . rawurlencode($row['sid']) . '">' . eclipse_admin_dashboard_h($row['title']) . '</a><span>' . eclipse_admin_dashboard_h(function_exists('COM_NumberFormat') ? COM_NumberFormat($row['hits']) : $row['hits']) . '</span></li>';
            }
            $html .= '</ol><a class="eclipse-dashboard-more" href="' . $site . '/stats.php">' . eclipse_admin_dashboard_h(eclipse_lang('full_statistics', 'Full statistics')) . '</a></div>';
        }
        $html .= '</article>';
    }

    if ($feeds) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('syndication_feeds', 'Syndication feeds')) . '</h2><ul>';
        foreach ($feeds as $row) {
            $meta = trim((string) $row['type']);
            if (!empty($row['format'])) $meta .= ($meta !== '' ? ' · ' : '') . $row['format'];
            if (!empty($row['filename'])) $meta .= ($meta !== '' ? ' · ' : '') . basename((string) $row['filename']);
            if (!empty($row['updated'])) {
                $updated = eclipse_admin_dashboard_date($row['updated']);
                if ($updated !== '') $meta .= ($meta !== '' ? ' · ' : '') . $updated;
            }
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h($meta) . '</small></div><a href="' . $admin . '/syndication.php?mode=edit&amp;fid=' . (int) $row['fid'] . '">' . eclipse_admin_dashboard_h(eclipse_lang('manage', 'Manage')) . '</a></li>';
        }
        $html .= '</ul></article>';
    }

    if ($pluginNews) {
        $html .= '<article class="eclipse-dashboard-widget eclipse-dashboard-widget-wide"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('plugin_activity', 'Plugin activity')) . '</h2>';
        foreach ($pluginNews as $group) {
            $html .= '<section class="eclipse-plugin-activity-group"><h3>' . eclipse_admin_dashboard_h($group['headline']) . '</h3><ul>';
            foreach ($group['items'] as $row) {
                $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['label']) . '</strong>';
                if ($row['text'] !== $row['label']) $html .= '<small>' . eclipse_admin_dashboard_h($row['text']) . '</small>';
                if ($row['date'] !== '') $html .= '<small>' . eclipse_admin_dashboard_h($row['date']) . '</small>';
                $html .= '</div>';
                if ($row['url'] !== '') $html .= '<a href="' . eclipse_admin_dashboard_h($row['url']) . '">' . eclipse_admin_dashboard_h(eclipse_lang('view')) . '</a>';
                $html .= '</li>';
            }
            $html .= '</ul></section>';
        }
        $html .= '</article>';
    }

    if ($stories) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('recent_stories')) . '</h2><ul>';
        foreach ($stories as $row) {
            $sid = rawurlencode($row['sid']);
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h(eclipse_admin_dashboard_date($row['date'])) . (!empty($row['username']) ? ' &middot; ' . eclipse_admin_dashboard_h($row['username']) : '') . '</small></div><span><a href="' . $site . '/article.php?story=' . $sid . '">' . eclipse_admin_dashboard_h(eclipse_lang('view')) . '</a><a href="' . $admin . '/' . $articleScript . '?mode=edit&amp;sid=' . $sid . '">' . eclipse_admin_dashboard_h(eclipse_lang('edit')) . '</a></span></li>';
        }
        $html .= '</ul></article>';
    }

    if ($comments) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('recent_comments')) . '</h2><ul>';
        foreach ($comments as $row) {
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags($row['comment'])));
            if (strlen($plain) > 100) $plain = substr($plain, 0, 97) . '...';
            $context = (!empty($row['type']) ? $row['type'] . ' ' : '') . (!empty($row['sid']) ? $row['sid'] . ' ' : '') . eclipse_admin_dashboard_date($row['date']);
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h(!empty($row['username']) ? $row['username'] : eclipse_lang('anonymous')) . '</strong><small>' . eclipse_admin_dashboard_h($plain) . '</small><small>' . eclipse_admin_dashboard_h(trim($context)) . '</small></div><a href="' . $site . '/comment.php?mode=view&amp;cid=' . (int) $row['cid'] . '">' . eclipse_admin_dashboard_h(eclipse_lang('view')) . '</a></li>';
        }
        $html .= '</ul></article>';
    }

    if ($drafts) {
        $html .= '<article class="eclipse-dashboard-widget eclipse-dashboard-widget-wide"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('drafts')) . ' <span class="eclipse-dashboard-count">' . (int) $draftCount . '</span></h2><ul>';
        foreach ($drafts as $row) $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h(eclipse_admin_dashboard_date($row['date'])) . '</small></div><a href="' . $admin . '/' . $articleScript . '?mode=edit&amp;sid=' . rawurlencode($row['sid']) . '">' . eclipse_admin_dashboard_h(eclipse_lang('continue')) . '</a></li>';
        $html .= '</ul></article>';
    }

    if ($pages) {
        $html .= '<article class="eclipse-dashboard-widget"><h2>' . eclipse_admin_dashboard_h(eclipse_lang('recent_static_pages')) . '</h2><ul>';
        foreach ($pages as $row) $html .= '<li><strong>' . eclipse_admin_dashboard_h($row['sp_title']) . '</strong><a href="' . $admin . '/plugins/staticpages/index.php?mode=edit&amp;sp_id=' . rawurlencode($row['sp_id']) . '">' . eclipse_admin_dashboard_h(eclipse_lang('edit')) . '</a></li>';
        $html .= '</ul></article>';
    }

    return $html . '</section>' . eclipse_admin_dashboard_overview_enhancer($attention);
}
