<?php

if (isset($_SERVER['PHP_SELF']) &&
        strpos(strtolower($_SERVER['PHP_SELF']), 'monitor-dashboard.php') !== false) {
    die('This file can not be used on its own!');
}

function eclipse_monitor_dashboard_data()
{
    if (!function_exists('PLG_invokeService')) {
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
            'manage_upgrades' => 'Terminer les upgrades de plugins',
            'compatible' => 'Compatible avec ce site',
            'incompatible' => 'Non compatible avec ce site',
            'compatibility_unknown' => 'Compatibilité non déterminée',
            'requires_geeklog' => 'Geeklog %s+',
            'requires_php' => 'PHP %s+'
        );
    }

    return array(
        'updates' => 'Updates available',
        'view_release' => 'View release',
        'view_all' => 'View all plugins',
        'attention_one' => '1 plugin upgrade to finish',
        'attention_many' => '%d plugin upgrades to finish',
        'manage_upgrades' => 'Finish plugin upgrades',
        'compatible' => 'Compatible with this site',
        'incompatible' => 'Not compatible with this site',
        'compatibility_unknown' => 'Compatibility not determined',
        'requires_geeklog' => 'Geeklog %s+',
        'requires_php' => 'PHP %s+'
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
 * Count only upgrades that Geeklog can actually present as actionable in the
 * native plugin administration screen. Disabled plugins are intentionally
 * excluded: Geeklog does not offer their upgrade action until they are enabled,
 * so Eclipse must not advertise an upgrade "to finish" for them.
 */
function eclipse_monitor_dashboard_upgrade_count($data)
{
    if (!is_array($data) || !isset($data['plugins']) || !is_array($data['plugins'])) {
        return 0;
    }

    $count = 0;
    foreach ($data['plugins'] as $plugin) {
        if (!is_array($plugin)) {
            continue;
        }

        $enabled = isset($plugin['enabled']) ? (bool) $plugin['enabled'] : false;
        $upgradeRequired = !empty($plugin['upgrade_required']);

        if ($enabled && $upgradeRequired) {
            $count++;
        }
    }

    return $count;
}

function eclipse_monitor_dashboard_render($data)
{
    global $_CONF;

    if (!is_array($data)
            || !function_exists('eclipse_admin_dashboard_module_start')
            || !function_exists('eclipse_admin_dashboard_module_end')
            || !function_exists('eclipse_admin_dashboard_h')) {
        return '';
    }

    $plugins = isset($data['plugins']) && is_array($data['plugins'])
        ? $data['plugins'] : array();
    $updates = array();

    foreach ($plugins as $plugin) {
        if (!is_array($plugin) || !isset($plugin['version_state'])
                || $plugin['version_state'] !== 'update') {
            continue;
        }
        $updates[] = $plugin;
    }

    if (empty($updates)) {
        return '';
    }

    $labels = eclipse_monitor_dashboard_labels();
    $admin = isset($_CONF['site_admin_url'])
        ? rtrim((string) $_CONF['site_admin_url'], '/') : '';
    $monitorUrl = $admin . '/plugins/monitor/index.php';
    $title = eclipse_admin_dashboard_h($labels['updates'])
        . ' <span class="eclipse-dashboard-count">' . count($updates) . '</span>';
    $html = eclipse_admin_dashboard_module_start(
        'eclipse-dashboard-plugin-updates',
        $title,
        false
    );

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
        $requirements = isset($plugin['remote_requirements']) && is_array($plugin['remote_requirements'])
            ? $plugin['remote_requirements'] : array();
        $compatibility = isset($plugin['compatibility']) && is_array($plugin['compatibility'])
            ? $plugin['compatibility'] : array();
        $compatibilityState = isset($compatibility['state'])
            ? (string) $compatibility['state'] : 'unknown';

        $html .= '<li><div><strong>' . eclipse_admin_dashboard_h($name) . '</strong>';
        if ($meta !== '') {
            $html .= '<small>' . eclipse_admin_dashboard_h($meta) . '</small>';
        }

        $requirementLabels = array();
        if (!empty($requirements['geeklog_min'])) {
            $requirementLabels[] = sprintf($labels['requires_geeklog'], $requirements['geeklog_min']);
        }
        if (!empty($requirements['php_min'])) {
            $requirementLabels[] = sprintf($labels['requires_php'], $requirements['php_min']);
        }
        if (!empty($requirementLabels)) {
            $html .= '<small>' . eclipse_admin_dashboard_h(implode(' · ', $requirementLabels)) . '</small>';
        }

        if ($compatibilityState === 'compatible') {
            $compatibilityLabel = '✓ ' . $labels['compatible'];
        } elseif ($compatibilityState === 'incompatible') {
            $compatibilityLabel = '✕ ' . $labels['incompatible'];
        } else {
            $compatibilityLabel = '? ' . $labels['compatibility_unknown'];
        }

        $html .= '<small class="eclipse-monitor-compatibility eclipse-monitor-compatibility-'
            . eclipse_admin_dashboard_h($compatibilityState) . '">'
            . eclipse_admin_dashboard_h($compatibilityLabel) . '</small></div>';

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

    return $html . eclipse_admin_dashboard_module_end();
}
