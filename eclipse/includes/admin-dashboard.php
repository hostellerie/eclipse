<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'admin-dashboard.php') !== false) {
    die('This file can not be used on its own!');
}

require_once __DIR__ . '/language.php';
require_once __DIR__ . '/zip-compat.php';

if (!empty($_CONF['path_data']) && function_exists('CTL_clearCache')) {
    $manifest = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'MANIFEST.json';
    $fingerprint = is_file($manifest) ? @hash_file('sha256', $manifest) : '';
    if (is_string($fingerprint) && $fingerprint !== '') {
        $marker = rtrim($_CONF['path_data'], '/\\') . DIRECTORY_SEPARATOR . '.eclipse-theme-build';
        $previous = is_file($marker) ? trim((string) @file_get_contents($marker)) : '';
        if ($previous !== $fingerprint) {
            CTL_clearCache();
            @file_put_contents($marker, $fingerprint . "\n", LOCK_EX);
            @chmod($marker, 0640);
        }
    }
}

if (defined('VERSION') && version_compare(VERSION, '2.1.1', '>=') && version_compare(VERSION, '2.2.0', '<')) {
    global $_CONF;
    $_CONF['min_theme_gl_version'] = '2.0.0';
}

function eclipse_admin_dashboard_h($value)
{
    // Some legacy Geeklog content is already stored with HTML entities
    // (for example &#039; in story titles). Normalize it first, then escape
    // exactly once for safe HTML output. This keeps the helper XSS-safe while
    // avoiding literal entity strings such as "d&#039;impôt" in the dashboard.
    $value = html_entity_decode((string) $value, ENT_QUOTES, 'UTF-8');
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function eclipse_admin_dashboard_rows($sql, $limit)
{
    $rows = array();
    if (!function_exists('DB_query') || !function_exists('DB_fetchArray')) return $rows;
    if ((int) $limit > 0) $sql .= ' LIMIT ' . (int) $limit;
    $result = @DB_query($sql, 1);
    if (!$result) return $rows;
    while ($row = DB_fetchArray($result)) $rows[] = $row;
    return $rows;
}

function eclipse_admin_dashboard_count($table, $where)
{
    if (!$table || !function_exists('DB_query') || !function_exists('DB_fetchArray')) return 0;
    $sql = 'SELECT COUNT(*) AS eclipse_count FROM ' . $table . ($where !== '' ? ' WHERE ' . $where : '');
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
    return eclipse_admin_dashboard_rows($sql . ' ORDER BY s.date DESC', $drafts ? 100 : 5);
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

function eclipse_admin_get_actual_comment_count()
{
    global $_TABLES;
    return !empty($_TABLES['comments']) ? eclipse_admin_dashboard_count($_TABLES['comments'], '') : 0;
}

function eclipse_admin_get_recent_comments()
{
    global $_TABLES;
    if (!function_exists('SEC_hasRights') || !SEC_hasRights('comment.moderate') || empty($_TABLES['comments'])) return array();
    if (eclipse_admin_get_actual_comment_count() < 1) return array();
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
    return eclipse_admin_dashboard_rows($sql . ' ORDER BY modified DESC', 5);
}

function eclipse_admin_get_top_viewed($limit)
{
    global $_TABLES;
    if (empty($_TABLES['stories'])) return array();
    $sql = "SELECT s.sid,s.title,s.hits AS metric FROM {$_TABLES['stories']} s";
    if (!empty($_TABLES['topic_assignments'])) $sql .= ", {$_TABLES['topic_assignments']} ta WHERE ta.type='article' AND ta.id=s.sid";
    else $sql .= ' WHERE 1=1';
    $sql .= ' AND s.draft_flag=0 AND s.date<=NOW() AND s.hits>0';
    if (function_exists('COM_getPermSQL')) $sql .= COM_getPermSQL('AND', 0, 3, 's');
    if (!empty($_TABLES['topic_assignments']) && function_exists('COM_getTopicSql')) $sql .= COM_getTopicSql('AND', 0, 'ta');
    $sql .= ' GROUP BY s.sid,s.title,s.hits ORDER BY s.hits DESC';
    return eclipse_admin_dashboard_rows($sql, $limit);
}

function eclipse_admin_get_top_commented($limit)
{
    global $_TABLES;
    if (empty($_TABLES['stories']) || empty($_TABLES['comments']) || eclipse_admin_get_actual_comment_count() < 1) return array();
    $sql = "SELECT s.sid,s.title,COUNT(DISTINCT c.cid) AS metric FROM {$_TABLES['stories']} s INNER JOIN {$_TABLES['comments']} c ON c.sid=s.sid AND c.type='article'";
    if (!empty($_TABLES['topic_assignments'])) $sql .= ", {$_TABLES['topic_assignments']} ta WHERE ta.type='article' AND ta.id=s.sid";
    else $sql .= ' WHERE 1=1';
    $sql .= ' AND s.draft_flag=0 AND s.date<=NOW()';
    if (function_exists('COM_getPermSQL')) $sql .= COM_getPermSQL('AND', 0, 3, 's');
    if (!empty($_TABLES['topic_assignments']) && function_exists('COM_getTopicSql')) $sql .= COM_getTopicSql('AND', 0, 'ta');
    $sql .= ' GROUP BY s.sid,s.title HAVING COUNT(DISTINCT c.cid)>0 ORDER BY metric DESC';
    return eclipse_admin_dashboard_rows($sql, $limit);
}

function eclipse_admin_get_site_stats()
{
    global $_TABLES;
    $stats = array();
    if (!empty($_TABLES['vars']) && function_exists('DB_getItem')) $stats['hits'] = (int) DB_getItem($_TABLES['vars'], 'value', "name='totalhits'");
    if (!empty($_TABLES['stories'])) $stats['stories'] = eclipse_admin_dashboard_count($_TABLES['stories'], 'draft_flag=0 AND date<=NOW()');
    $commentCount = eclipse_admin_get_actual_comment_count();
    if ($commentCount > 0) $stats['comments'] = $commentCount;
    if (!empty($_TABLES['users'])) $stats['users'] = max(0, eclipse_admin_dashboard_count($_TABLES['users'], 'status=3') - 1);
    return $stats;
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
            if ($label !== '' && $value !== '') $rows[] = array('plugin' => (string) $plugin, 'label' => $label, 'value' => $value);
        }
    }
    return $rows;
}

function eclipse_admin_whatsnew_entries($headline, $entry)
{
    $items = array();
    $html = trim((string) $entry);
    if ($html === '') return $items;
    if (preg_match_all('/<a\b[^>]*\bhref\s*=\s*(["\'])(.*?)\1[^>]*>(.*?)<\/a>/is', $html, $matches, PREG_SET_ORDER)) {
        foreach ($matches as $match) {
            if (count($items) >= 4) break;
            $url = html_entity_decode(trim($match[2]), ENT_QUOTES, 'UTF-8');
            if (!preg_match('#^(?:https?://|/)#i', $url) || strpos($url, '..') !== false) continue;
            $label = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($match[3]), ENT_QUOTES, 'UTF-8')));
            if ($label === '') continue;
            if (strlen($label) > 120) $label = substr($label, 0, 117) . '...';
            $items[] = array('headline' => $headline, 'label' => $label, 'url' => $url, 'date' => '');
        }
        if ($items) return $items;
    }
    $text = trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html), ENT_QUOTES, 'UTF-8')));
    if ($text === '' || strcasecmp($text, $headline) === 0) return $items;
    $date = '';
    if (preg_match('/\b(20[0-9]{2}[-\/.][0-9]{1,2}[-\/.][0-9]{1,2})\b/', $text, $m)) $date = str_replace(array('/', '.'), '-', $m[1]);
    elseif (preg_match('/\b([0-9]{1,2}[-\/.][0-9]{1,2}[-\/.]20[0-9]{2})\b/', $text, $m)) $date = str_replace(array('/', '.'), '-', $m[1]);
    if (strlen($text) > 160) $text = substr($text, 0, 157) . '...';
    $items[] = array('headline' => $headline, 'label' => $text, 'url' => '', 'date' => $date);
    return $items;
}

function eclipse_admin_discover_plugin_whatsnew()
{
    $groups = array();
    if (!function_exists('PLG_getWhatsNew')) return $groups;
    $data = PLG_getWhatsNew();
    if (!is_array($data) || count($data) < 3 || !is_array($data[0]) || !is_array($data[2])) return $groups;
    foreach ($data[2] as $index => $entries) {
        $headline = isset($data[0][$index]) ? trim(strip_tags((string) $data[0][$index])) : '';
        if ($headline === '') $headline = eclipse_lang('plugin_activity', 'Plugin activity');
        if (!is_array($entries)) $entries = array($entries);
        $items = array();
        foreach ($entries as $entry) {
            foreach (eclipse_admin_whatsnew_entries($headline, $entry) as $item) {
                if (count($items) >= 4) break 2;
                $items[] = $item;
            }
        }
        if ($items) $groups[] = array('headline' => $headline, 'items' => $items);
    }
    return $groups;
}

function eclipse_admin_discover_feeds()
{
    global $_TABLES;
    if (!function_exists('SEC_hasRights') || !SEC_hasRights('syndication.edit') || empty($_TABLES['syndication'])) return array();
    return eclipse_admin_dashboard_rows("SELECT fid,title,type,format,filename,updated FROM {$_TABLES['syndication']} WHERE is_enabled=1 ORDER BY title ASC,fid ASC", 12);
}

function eclipse_admin_attention_data($draftCount)
{
    global $_CONF, $_TABLES;
    $data = array('drafts' => max(0, (int) $draftCount), 'comments' => 0, 'submissions' => 0);
    if (function_exists('SEC_hasRights') && SEC_hasRights('comment.moderate') && !empty($_CONF['commentsubmission']) && !empty($_TABLES['commentsubmissions'])) {
        $data['comments'] = eclipse_admin_dashboard_count($_TABLES['commentsubmissions'], '1=1');
    }
    if (function_exists('SEC_hasRights') && SEC_hasRights('story.moderate') && !empty($_TABLES['storysubmission'])) {
        $data['submissions'] = eclipse_admin_dashboard_count($_TABLES['storysubmission'], '1=1');
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
    $fr = isset($_CONF['language']) && strpos(strtolower((string) $_CONF['language']), 'french') === 0;
    if ($fr) return array(
        'comments' => 'Commentaires en attente', 'submissions' => 'Soumissions à examiner', 'drafts' => 'Brouillons à terminer',
        'manage_comments' => 'Gérer les commentaires', 'review_submissions' => 'Examiner les soumissions',
        'empty' => 'Aucun élément ne nécessite votre attention.', 'statistics' => 'Statistiques et tendances',
        'plugin_stats' => 'Contenus des plugins', 'site_stats' => 'Vue générale',
        'most_viewed' => 'Contenus les plus consultés', 'most_commented' => 'Contenus les plus commentés',
        'full_stats' => 'Toutes les statistiques', 'feeds' => 'Flux de syndication', 'activity' => 'Activité des plugins',
        'stories' => 'Articles publiés', 'site_comments' => 'Commentaires', 'users' => 'Utilisateurs actifs', 'hits' => 'Visites totales',
        'collapse' => 'Réduire', 'expand' => 'Développer'
    );
    return array(
        'comments' => 'Comments awaiting moderation', 'submissions' => 'Submissions awaiting review', 'drafts' => 'Draft stories to finish',
        'manage_comments' => 'Manage comments', 'review_submissions' => 'Review submissions',
        'empty' => 'Nothing currently requires your attention.', 'statistics' => 'Statistics and trends',
        'plugin_stats' => 'Plugin content', 'site_stats' => 'Overview',
        'most_viewed' => 'Most viewed content', 'most_commented' => 'Most commented content',
        'full_stats' => 'Full statistics', 'feeds' => 'Syndication feeds', 'activity' => 'Plugin activity',
        'stories' => 'Published stories', 'site_comments' => 'Comments', 'users' => 'Active users', 'hits' => 'Total visits',
        'collapse' => 'Collapse', 'expand' => 'Expand'
    );
}

function eclipse_admin_dashboard_module_start($id, $title, $wide)
{
    $class = 'eclipse-dashboard-widget' . ($wide ? ' eclipse-dashboard-widget-wide' : '');
    return '<article id="' . eclipse_admin_dashboard_h($id) . '" class="' . $class . '" data-eclipse-module="' . eclipse_admin_dashboard_h($id) . '">'
        . '<header class="eclipse-dashboard-widget-header"><h2>' . $title . '</h2>'
        . '<button type="button" class="eclipse-dashboard-toggle" aria-expanded="true" aria-controls="' . eclipse_admin_dashboard_h($id) . '-body"><span aria-hidden="true">−</span></button></header>'
        . '<div id="' . eclipse_admin_dashboard_h($id) . '-body" class="eclipse-dashboard-body">';
}

function eclipse_admin_dashboard_module_end()
{
    return '</div></article>';
}

function eclipse_admin_dashboard_overview_enhancer($attention, $hasComments)
{
    global $_CONF;
    $admin = rtrim($_CONF['site_admin_url'], '/');
    $payload = array(
        'drafts' => (int) $attention['drafts'],
        'comments' => (int) $attention['comments'],
        'submissions' => (int) $attention['submissions'],
        'hasComments' => (bool) $hasComments,
        'commentsUrl' => $admin . '/comment.php',
        'moderationUrl' => $admin . '/moderation.php',
        'draftsUrl' => '#eclipse-dashboard-drafts',
        'labels' => eclipse_admin_dashboard_labels()
    );
    $json = json_encode($payload);
    if (!is_string($json)) return '';

    $style = '<style>'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget-stats .eclipse-dashboard-value{font-size:.68rem!important;font-weight:650!important;line-height:1.2!important;color:#64748b!important;font-variant-numeric:tabular-nums}'
        . 'body.eclipse-admin-page .eclipse-dashboard-count,body.eclipse-admin-page .eclipse-attention-count{display:inline-flex;align-items:center;justify-content:center;vertical-align:middle;min-width:1.15rem;height:1.15rem;margin-left:.28rem;padding:0 .28rem;border-radius:999px;background:#b42318;color:#fff;font-size:.62rem;font-weight:800;line-height:1;font-variant-numeric:tabular-nums}'
        . 'body.eclipse-admin-page .eclipse-overview-attention li a,body.eclipse-admin-page .eclipse-overview-actions li a{display:inline-flex;align-items:center;justify-content:flex-start;gap:.25rem}'
        . 'body.eclipse-admin-page .eclipse-overview-attention li.is-draft .eclipse-attention-count,body.eclipse-admin-page .eclipse-dashboard-count{background:#9a6700}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget-header{display:flex;align-items:center;justify-content:space-between;gap:.55rem;margin:0 0 .6rem}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget-header h2{margin:0!important}'
        . 'body.eclipse-admin-page .eclipse-dashboard-toggle{display:inline-grid;place-items:center;flex:0 0 auto;width:1.35rem;height:1.35rem;margin:0;padding:0;border:1px solid #e6eaf0;border-radius:.32rem;background:#fff;color:#7a8699;font-size:.72rem;font-weight:750;line-height:1;cursor:pointer;box-shadow:none}'
        . 'body.eclipse-admin-page .eclipse-dashboard-toggle:hover{color:#172033;background:#f8fafc;border-color:#d9dfe7}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget.is-collapsed{padding-top:.72rem!important;padding-bottom:.72rem!important}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget.is-collapsed .eclipse-dashboard-widget-header{margin-bottom:0}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget.is-collapsed .eclipse-dashboard-body{display:none}'
        . 'body.eclipse-admin-page .eclipse-dashboard-stats-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));align-items:start;gap:.65rem .8rem}'
        . 'body.eclipse-admin-page .eclipse-dashboard-stats-section{min-width:0;align-self:start}'
        . 'body.eclipse-admin-page .eclipse-dashboard-stats-section h3{margin:0 0 .35rem;color:#64748b;font-size:.66rem;font-weight:850;letter-spacing:.08em;text-transform:uppercase}'
        . 'body.eclipse-admin-page .eclipse-dashboard-stat-overview{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:.35rem!important;margin:0!important;padding:0!important;list-style:none!important}'
        . 'body.eclipse-admin-page .eclipse-dashboard-stat-overview li{display:block!important;grid-column:auto!important;min-width:0!important;margin:0!important;padding:.42rem .5rem!important;border:1px solid #edf0f4;border-radius:.42rem}'
        . 'body.eclipse-admin-page .eclipse-dashboard-stat-overview strong{font-size:.7rem}'
        . 'body.eclipse-admin-page .eclipse-dashboard-stat-overview span{display:block;margin-top:.12rem;color:#334155;font-size:.8rem;font-weight:720;font-variant-numeric:tabular-nums}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget-stats ul{margin:0!important;padding:0!important;list-style:none!important}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget-stats li{min-height:0!important;margin:0!important;padding:.38rem 0!important}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget-stats li+li{border-top:1px solid #edf0f4}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget-stats li strong{font-size:.72rem}'
        . 'body.eclipse-admin-page .eclipse-dashboard-widget-stats li small{font-size:.66rem}'
        . 'body.eclipse-admin-page .eclipse-dashboard-ranking{margin:0;padding:0;list-style:none}'
        . 'body.eclipse-admin-page .eclipse-dashboard-ranking li{padding:.36rem 0}'
        . 'body.eclipse-admin-page .eclipse-dashboard-ranking li a{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}'
        . 'body.eclipse-admin-page .eclipse-dashboard-ranking li span{font-size:.66rem;font-weight:650;color:#64748b;font-variant-numeric:tabular-nums}'
        . 'body.eclipse-admin-page .eclipse-dashboard-more{display:inline-block;margin-top:.45rem;font-size:.68rem;font-weight:750}'
        . '@media(max-width:52rem){body.eclipse-admin-page .eclipse-dashboard-stats-grid{grid-template-columns:1fr}body.eclipse-admin-page .eclipse-dashboard-stat-overview{grid-template-columns:repeat(2,minmax(0,1fr))!important}}'
        . '@media(max-width:32rem){body.eclipse-admin-page .eclipse-dashboard-stat-overview{grid-template-columns:1fr!important}}'
        . '</style>';

    $script = '<script>(function(){var data=' . $json . ';'
        . 'function badge(n){var s=document.createElement("span");s.className="eclipse-attention-count";s.textContent=String(n);return s;}'
        . 'function enhanceOverview(){var attention=document.querySelector(".eclipse-overview-attention");var actions=document.querySelector(".eclipse-overview-actions ul");if(!attention||!actions)return false;var old=attention.querySelector("ul");if(old)old.remove();var empty=attention.querySelector("p");if(empty)empty.remove();var entries=[];if(data.comments>0)entries.push({label:data.labels.comments,count:data.comments,url:data.commentsUrl});if(data.submissions>0)entries.push({label:data.labels.submissions,count:data.submissions,url:data.moderationUrl});if(data.drafts>0)entries.push({label:data.labels.drafts,count:data.drafts,url:data.draftsUrl,draft:true});if(!entries.length){var p=document.createElement("p");p.textContent=data.labels.empty;attention.appendChild(p);}else{var ul=document.createElement("ul");entries.forEach(function(e){var li=document.createElement("li");if(e.draft)li.className="is-draft";var a=document.createElement("a");a.href=e.url;a.appendChild(document.createTextNode(e.label));a.appendChild(badge(e.count));li.appendChild(a);ul.appendChild(li);});attention.appendChild(ul);}function ensure(re,label,url,count,allowed){var links=Array.prototype.slice.call(actions.querySelectorAll("a"));var link=null;links.some(function(a){if(re.test(a.href)){link=a;return true;}return false;});if(!allowed){if(link&&link.parentNode)link.parentNode.remove();return;}if(!link){var li=document.createElement("li");link=document.createElement("a");link.href=url;link.textContent=label;li.appendChild(link);actions.appendChild(li);}if(count>0&&!link.querySelector(".eclipse-attention-count"))link.appendChild(badge(count));}ensure(/\/admin\/comment\.php/i,data.labels.manage_comments,data.commentsUrl,data.comments,data.hasComments||data.comments>0);ensure(/\/admin\/moderation\.php/i,data.labels.review_submissions,data.moderationUrl,data.submissions,true);return true;}'
        . 'function setupModules(){var key="eclipse-dashboard-collapsed-v1",saved=[];try{saved=JSON.parse(localStorage.getItem(key)||"[]");if(!Array.isArray(saved))saved=[];}catch(e){saved=[];}function save(){try{localStorage.setItem(key,JSON.stringify(saved));}catch(e){}}Array.prototype.forEach.call(document.querySelectorAll("[data-eclipse-module]"),function(module){var id=module.getAttribute("data-eclipse-module");var button=module.querySelector(".eclipse-dashboard-toggle");if(!button)return;function apply(collapsed){module.classList.toggle("is-collapsed",collapsed);button.setAttribute("aria-expanded",collapsed?"false":"true");button.setAttribute("title",collapsed?data.labels.expand:data.labels.collapse);var icon=button.querySelector("span");if(icon)icon.textContent=collapsed?"+":"−";}apply(saved.indexOf(id)!==-1);button.addEventListener("click",function(){var collapsed=!module.classList.contains("is-collapsed");apply(collapsed);var index=saved.indexOf(id);if(collapsed&&index===-1)saved.push(id);if(!collapsed&&index!==-1)saved.splice(index,1);save();});});function revealHash(){if(!location.hash)return;var target=document.querySelector(location.hash);if(target&&target.hasAttribute("data-eclipse-module")&&target.classList.contains("is-collapsed")){var b=target.querySelector(".eclipse-dashboard-toggle");if(b)b.click();}}window.addEventListener("hashchange",revealHash);revealHash();}'
        . 'function boot(){setupModules();if(!enhanceOverview()){var observer=new MutationObserver(function(){if(enhanceOverview())observer.disconnect();});observer.observe(document.documentElement,{childList:true,subtree:true});setTimeout(function(){observer.disconnect();enhanceOverview();},3000);}}if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",boot);else boot();}());</script>';

    return $style . $script;
}

function eclipse_admin_dashboard_render()
{
    global $_CONF;
    if (!eclipse_is_admin_request() || eclipse_admin_page() !== 'index') return '';

    $stories = eclipse_admin_get_recent_stories(false);
    $drafts = eclipse_admin_get_recent_stories(true);
    $draftCount = eclipse_admin_get_draft_count();
    $actualCommentCount = eclipse_admin_get_actual_comment_count();
    $comments = $actualCommentCount > 0 ? eclipse_admin_get_recent_comments() : array();
    $pages = eclipse_admin_get_recent_staticpages();
    $topStories = eclipse_admin_get_top_viewed(5);
    $topCommented = $actualCommentCount > 0 ? eclipse_admin_get_top_commented(5) : array();
    $siteStats = eclipse_admin_get_site_stats();
    $pluginStats = eclipse_admin_discover_plugin_stats();
    $pluginNews = eclipse_admin_discover_plugin_whatsnew();
    $feeds = eclipse_admin_discover_feeds();
    $attention = eclipse_admin_attention_data($draftCount);
    $labels = eclipse_admin_dashboard_labels();
    $admin = rtrim($_CONF['site_admin_url'], '/');
    $site = rtrim($_CONF['site_url'], '/');
    $articleScript = defined('VERSION') && version_compare(VERSION, '2.2.0', '>=') ? 'article.php' : 'story.php';
    $html = '<section class="eclipse-admin-dashboard-data" aria-label="' . eclipse_admin_dashboard_h(eclipse_lang('editorial_overview')) . '">';

    if ($drafts) {
        $html .= eclipse_admin_dashboard_module_start('eclipse-dashboard-drafts', eclipse_admin_dashboard_h(eclipse_lang('drafts')) . ' <span class="eclipse-dashboard-count">' . (int) $draftCount . '</span>', true) . '<ul>';
        foreach ($drafts as $row) {
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h(eclipse_admin_dashboard_date($row['date'])) . '</small></div><a href="' . $admin . '/' . $articleScript . '?mode=edit&amp;sid=' . rawurlencode($row['sid']) . '">' . eclipse_admin_dashboard_h(eclipse_lang('continue')) . '</a></li>';
        }
        $html .= '</ul>' . eclipse_admin_dashboard_module_end();
    }

    if ($stories) {
        $html .= eclipse_admin_dashboard_module_start('eclipse-dashboard-stories', eclipse_admin_dashboard_h(eclipse_lang('recent_stories')), false) . '<ul>';
        foreach ($stories as $row) {
            $sid = rawurlencode($row['sid']);
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['title']) . '</strong><small>' . eclipse_admin_dashboard_h(eclipse_admin_dashboard_date($row['date'])) . (!empty($row['username']) ? ' &middot; ' . eclipse_admin_dashboard_h($row['username']) : '') . '</small></div><span><a href="' . $site . '/article.php?story=' . $sid . '">' . eclipse_admin_dashboard_h(eclipse_lang('view')) . '</a><a href="' . $admin . '/' . $articleScript . '?mode=edit&amp;sid=' . $sid . '">' . eclipse_admin_dashboard_h(eclipse_lang('edit')) . '</a></span></li>';
        }
        $html .= '</ul>' . eclipse_admin_dashboard_module_end();
    }

    if ($comments) {
        $html .= eclipse_admin_dashboard_module_start('eclipse-dashboard-comments', eclipse_admin_dashboard_h(eclipse_lang('recent_comments')), false) . '<ul>';
        foreach ($comments as $row) {
            $plain = trim(preg_replace('/\s+/', ' ', strip_tags($row['comment'])));
            if (strlen($plain) > 100) $plain = substr($plain, 0, 97) . '...';
            $context = (!empty($row['type']) ? $row['type'] . ' ' : '') . (!empty($row['sid']) ? $row['sid'] . ' ' : '') . eclipse_admin_dashboard_date($row['date']);
            $html .= '<li><div><strong>' . eclipse_admin_dashboard_h(!empty($row['username']) ? $row['username'] : eclipse_lang('anonymous')) . '</strong><small>' . eclipse_admin_dashboard_h($plain) . '</small><small>' . eclipse_admin_dashboard_h(trim($context)) . '</small></div><a href="' . $site . '/comment.php?mode=view&amp;cid=' . (int) $row['cid'] . '">' . eclipse_admin_dashboard_h(eclipse_lang('view')) . '</a></li>';
        }
        $html .= '</ul>' . eclipse_admin_dashboard_module_end();
    }

    if ($pages) {
        $html .= eclipse_admin_dashboard_module_start('eclipse-dashboard-pages', eclipse_admin_dashboard_h(eclipse_lang('recent_static_pages')), false) . '<ul>';
        foreach ($pages as $row) {
            $html .= '<li><strong>' . eclipse_admin_dashboard_h($row['sp_title']) . '</strong><a href="' . $admin . '/plugins/staticpages/index.php?mode=edit&amp;sp_id=' . rawurlencode($row['sp_id']) . '">' . eclipse_admin_dashboard_h(eclipse_lang('edit')) . '</a></li>';
        }
        $html .= '</ul>' . eclipse_admin_dashboard_module_end();
    }

    if ($pluginNews) {
        $html .= eclipse_admin_dashboard_module_start('eclipse-dashboard-activity', eclipse_admin_dashboard_h($labels['activity']), true);
        foreach ($pluginNews as $group) {
            $html .= '<section class="eclipse-plugin-activity-group"><h3>' . eclipse_admin_dashboard_h($group['headline']) . '</h3><ul>';
            foreach ($group['items'] as $row) {
                $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['label']) . '</strong>' . ($row['date'] !== '' ? '<small>' . eclipse_admin_dashboard_h($row['date']) . '</small>' : '') . '</div>';
                if ($row['url'] !== '') $html .= '<a href="' . eclipse_admin_dashboard_h($row['url']) . '">' . eclipse_admin_dashboard_h(eclipse_lang('view')) . '</a>';
                $html .= '</li>';
            }
            $html .= '</ul></section>';
        }
        $html .= eclipse_admin_dashboard_module_end();
    }

    if ($feeds) {
        $html .= eclipse_admin_dashboard_module_start('eclipse-dashboard-feeds', eclipse_admin_dashboard_h($labels['feeds']), false) . '<ul>';
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
        $html .= '</ul>' . eclipse_admin_dashboard_module_end();
    }

    if ($siteStats || $pluginStats || $topStories || $topCommented) {
        $html .= eclipse_admin_dashboard_module_start('eclipse-dashboard-statistics', eclipse_admin_dashboard_h($labels['statistics']), true) . '<div class="eclipse-dashboard-stats-grid">';

        if ($siteStats) {
            $html .= '<section class="eclipse-dashboard-stats-section"><h3>' . eclipse_admin_dashboard_h($labels['site_stats']) . '</h3><ul class="eclipse-dashboard-stat-overview">';
            foreach (array('stories' => 'stories', 'comments' => 'site_comments', 'users' => 'users', 'hits' => 'hits') as $key => $labelKey) {
                if (!isset($siteStats[$key])) continue;
                $value = function_exists('COM_NumberFormat') ? COM_NumberFormat($siteStats[$key]) : $siteStats[$key];
                $html .= '<li><strong>' . eclipse_admin_dashboard_h($labels[$labelKey]) . '</strong><span>' . eclipse_admin_dashboard_h($value) . '</span></li>';
            }
            $html .= '</ul></section>';
        }

        if ($pluginStats) {
            $html .= '<section class="eclipse-dashboard-stats-section eclipse-dashboard-widget-stats"><h3>' . eclipse_admin_dashboard_h($labels['plugin_stats']) . '</h3><ul>';
            foreach ($pluginStats as $row) {
                $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($row['label']) . '</strong><small>' . eclipse_admin_dashboard_h($row['plugin']) . '</small></div><span class="eclipse-dashboard-value">' . eclipse_admin_dashboard_h($row['value']) . '</span></li>';
            }
            $html .= '</ul></section>';
        }

        if ($topStories) {
            $html .= '<section class="eclipse-dashboard-stats-section"><h3>' . eclipse_admin_dashboard_h($labels['most_viewed']) . '</h3><ol class="eclipse-dashboard-ranking">';
            foreach ($topStories as $row) {
                $metric = function_exists('COM_NumberFormat') ? COM_NumberFormat($row['metric']) : $row['metric'];
                $html .= '<li><a href="' . $site . '/article.php?story=' . rawurlencode($row['sid']) . '">' . eclipse_admin_dashboard_h($row['title']) . '</a><span>' . eclipse_admin_dashboard_h($metric) . '</span></li>';
            }
            $html .= '</ol></section>';
        }

        if ($topCommented) {
            $html .= '<section class="eclipse-dashboard-stats-section"><h3>' . eclipse_admin_dashboard_h($labels['most_commented']) . '</h3><ol class="eclipse-dashboard-ranking">';
            foreach ($topCommented as $row) {
                $metric = function_exists('COM_NumberFormat') ? COM_NumberFormat($row['metric']) : $row['metric'];
                $html .= '<li><a href="' . $site . '/article.php?story=' . rawurlencode($row['sid']) . '">' . eclipse_admin_dashboard_h($row['title']) . '</a><span>' . eclipse_admin_dashboard_h($metric) . '</span></li>';
            }
            $html .= '</ol></section>';
        }

        $html .= '</div><a class="eclipse-dashboard-more" href="' . $site . '/stats.php">' . eclipse_admin_dashboard_h($labels['full_stats']) . '</a>' . eclipse_admin_dashboard_module_end();
    }

    return $html . '</section>' . eclipse_admin_dashboard_overview_enhancer($attention, $actualCommentCount > 0);
}
