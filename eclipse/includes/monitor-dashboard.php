<?php

if (isset($_SERVER['PHP_SELF']) &&
        strpos(strtolower($_SERVER['PHP_SELF']), 'monitor-dashboard.php') !== false) {
    die('This file can not be used on its own!');
}

/**
 * Consume Monitor through Geeklog's public service contract.
 *
 * Eclipse owns presentation only. It deliberately does not inspect plugin
 * files, Monitor storage or GitHub itself.
 *
 * @return array|null
 */
function eclipse_monitor_dashboard_data()
{
    if (!function_exists('eclipse_is_admin_request') || !eclipse_is_admin_request()
            || !function_exists('eclipse_admin_page') || eclipse_admin_page() !== 'index'
            || !function_exists('PLG_invokeService')) {
        return null;
    }

    $args = array('include_remote' => true);
    $output = array();
    $svcMsg = array();
    $result = @PLG_invokeService('monitor', 'get_plugins', $args, $output, $svcMsg);
    $ok = defined('PLG_RET_OK') ? PLG_RET_OK : 0;

    if ($result != $ok || !is_array($output)
            || !isset($output['data']) || !is_array($output['data'])) {
        return null;
    }

    return $output['data'];
}

function eclipse_monitor_dashboard_labels()
{
    global $_CONF;

    $fr = isset($_CONF['language'])
        && strpos(strtolower((string) $_CONF['language']), 'french') === 0;

    if ($fr) {
        return array(
            'updates' => 'Mises à jour disponibles',
            'view_release' => 'Voir la version',
            'view_all' => 'Voir tous les plugins',
            'attention_one' => '1 upgrade de plugin à terminer',
            'attention_many' => '%d upgrades de plugins à terminer',
            'manage_upgrades' => 'Terminer les upgrades de plugins'
        );
    }

    return array(
        'updates' => 'Updates available',
        'view_release' => 'View release',
        'view_all' => 'View all plugins',
        'attention_one' => '1 plugin upgrade to finish',
        'attention_many' => '%d plugin upgrades to finish',
        'manage_upgrades' => 'Finish plugin upgrades'
    );
}

function eclipse_monitor_dashboard_safe_url($url)
{
    $url = trim((string) $url);
    if ($url === '' || !preg_match('#^https?://#i', $url)) {
        return '';
    }

    return $url;
}

/**
 * Add local upgrades to the existing Eclipse "attention" overview.
 *
 * The script is additive: the normal Eclipse overview remains authoritative
 * for drafts, submissions and comments. Monitor contributes only the local
 * plugin upgrade signal and links execution back to Geeklog's native manager.
 */
function eclipse_monitor_dashboard_attention_script($count, $pluginsUrl, $labels)
{
    $count = max(0, (int) $count);
    if ($count < 1) {
        return '';
    }

    $label = $count === 1
        ? $labels['attention_one']
        : sprintf($labels['attention_many'], $count);
    $payload = array(
        'count' => $count,
        'label' => $label,
        'action' => $labels['manage_upgrades'],
        'url' => $pluginsUrl
    );
    $json = json_encode($payload);
    if (!is_string($json)) {
        return '';
    }

    return '<script>(function(){var d=' . $json . ';'
        . 'function add(){var box=document.querySelector(".eclipse-overview-attention");if(!box)return false;'
        . 'var ul=box.querySelector("ul");if(!ul){ul=document.createElement("ul");var p=box.querySelector("p");if(p)p.remove();box.appendChild(ul);}'
        . 'if(!ul.querySelector("[data-eclipse-monitor-upgrades]")){var li=document.createElement("li");li.setAttribute("data-eclipse-monitor-upgrades","1");'
        . 'var a=document.createElement("a");a.href=d.url;a.appendChild(document.createTextNode(d.label));'
        . 'var b=document.createElement("span");b.className="eclipse-attention-count";b.textContent=String(d.count);a.appendChild(b);li.appendChild(a);ul.appendChild(li);}'
        . 'var actions=document.querySelector(".eclipse-overview-actions ul");if(actions&&!actions.querySelector("[data-eclipse-monitor-upgrade-action]")){'
        . 'var ali=document.createElement("li");ali.setAttribute("data-eclipse-monitor-upgrade-action","1");var aa=document.createElement("a");aa.href=d.url;aa.textContent=d.action;ali.appendChild(aa);actions.appendChild(ali);}return true;}'
        . 'function boot(){if(add())return;var o=new MutationObserver(function(){if(add())o.disconnect();});o.observe(document.documentElement,{childList:true,subtree:true});setTimeout(function(){o.disconnect();add();},3000);}'
        . 'if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",boot);else boot();}());</script>';
}

/**
 * Move the update card into Eclipse's existing dashboard grid. The card is
 * rendered late through content-bottom.php so this avoids duplicating or
 * modifying the main dashboard business logic.
 */
function eclipse_monitor_dashboard_attach_script()
{
    return '<script>(function(){function attach(){var card=document.getElementById("eclipse-dashboard-plugin-updates");var grid=document.querySelector(".eclipse-admin-dashboard-data");if(!card||!grid)return false;if(card.parentNode!==grid)grid.appendChild(card);return true;}if(document.readyState==="loading")document.addEventListener("DOMContentLoaded",attach);else attach();}());</script>';
}

/**
 * Render the remote-update card and local-upgrade attention contribution.
 *
 * @return string
 */
function eclipse_monitor_dashboard_render()
{
    global $_CONF;

    $data = eclipse_monitor_dashboard_data();
    if (!is_array($data)) {
        return '';
    }

    $summary = isset($data['summary']) && is_array($data['summary'])
        ? $data['summary'] : array();
    $plugins = isset($data['plugins']) && is_array($data['plugins'])
        ? $data['plugins'] : array();
    $upgradeCount = isset($summary['upgrades_required'])
        ? max(0, (int) $summary['upgrades_required']) : 0;
    $updates = array();

    foreach ($plugins as $plugin) {
        if (!is_array($plugin) || !isset($plugin['version_state'])
                || $plugin['version_state'] !== 'update') {
            continue;
        }
        $updates[] = $plugin;
    }

    $labels = eclipse_monitor_dashboard_labels();
    $admin = isset($_CONF['site_admin_url'])
        ? rtrim((string) $_CONF['site_admin_url'], '/') : '';
    $pluginsUrl = $admin . '/plugins.php';
    $monitorUrl = $admin . '/plugins/monitor/index.php';
    $html = eclipse_monitor_dashboard_attention_script(
        $upgradeCount,
        $pluginsUrl,
        $labels
    );

    if (empty($updates)) {
        return $html;
    }

    $title = eclipse_admin_dashboard_h($labels['updates'])
        . ' <span class="eclipse-dashboard-count">' . count($updates) . '</span>';

    if (function_exists('eclipse_admin_dashboard_module_start')) {
        $html .= eclipse_admin_dashboard_module_start(
            'eclipse-dashboard-plugin-updates',
            $title,
            false
        );
    } else {
        $html .= '<article class="eclipse-dashboard-widget" id="eclipse-dashboard-plugin-updates">'
            . '<header class="eclipse-dashboard-widget-header"><h2>' . $title . '</h2></header>'
            . '<div class="eclipse-dashboard-body">';
    }

    $html .= '<ul class="eclipse-monitor-updates">';
    $shown = 0;
    foreach ($updates as $plugin) {
        if ($shown >= 5) {
            break;
        }

        $name = isset($plugin['name']) ? (string) $plugin['name'] : '';
        $local = !empty($plugin['code_version'])
            ? (string) $plugin['code_version']
            : (isset($plugin['installed_version']) ? (string) $plugin['installed_version'] : '');
        $latest = isset($plugin['latest_version']) ? (string) $plugin['latest_version'] : '';
        $versionUrl = isset($plugin['version_url'])
            ? eclipse_monitor_dashboard_safe_url($plugin['version_url']) : '';
        $meta = trim($local . ($local !== '' && $latest !== '' ? ' → ' : '') . $latest);

        $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($name) . '</strong>';
        if ($meta !== '') {
            $html .= '<small>' . eclipse_admin_dashboard_h($meta) . '</small>';
        }
        $html .= '</div>';
        if ($versionUrl !== '') {
            $html .= '<a href="' . eclipse_admin_dashboard_h($versionUrl)
                . '" rel="noopener noreferrer">'
                . eclipse_admin_dashboard_h($labels['view_release']) . '</a>';
        }
        $html .= '</li>';
        $shown++;
    }
    $html .= '</ul><a class="eclipse-dashboard-more" href="'
        . eclipse_admin_dashboard_h($monitorUrl) . '">'
        . eclipse_admin_dashboard_h($labels['view_all']) . '</a>';

    if (function_exists('eclipse_admin_dashboard_module_end')) {
        $html .= eclipse_admin_dashboard_module_end();
    } else {
        $html .= '</div></article>';
    }

    return $html . eclipse_monitor_dashboard_attach_script();
}
