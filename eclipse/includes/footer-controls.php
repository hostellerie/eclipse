<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'footer-controls.php') !== false) {
    die('This file can not be used on its own!');
}

function eclipse_footer_controls_defaults()
{
    return array(
        'show_native' => true,
        'above_line' => '',
        'third_column' => '',
        'use_default_copyright' => true,
        'use_default_legal' => true,
        'use_default_third' => true,
    );
}

function eclipse_footer_controls_text($value, $maximum, $preserveLines = false)
{
    $value = strip_tags((string) $value);
    if ($preserveLines) {
        $value = preg_replace("/\r\n?|\n/u", "\n", $value);
        $lines = array();
        foreach (explode("\n", $value) as $line) {
            $line = trim(preg_replace('/[\t ]+/u', ' ', $line));
            if ($line !== '') $lines[] = $line;
        }
        $value = implode("\n", $lines);
    } else {
        $value = trim(preg_replace('/\s+/u', ' ', $value));
    }
    return function_exists('mb_substr') ? mb_substr($value, 0, $maximum, 'UTF-8') : substr($value, 0, $maximum);
}

function eclipse_footer_controls_sanitize($input)
{
    $clean = eclipse_footer_controls_defaults();
    if (!is_array($input)) return $clean;
    $clean['show_native'] = !empty($input['show_native']);
    $clean['above_line'] = isset($input['above_line']) ? eclipse_footer_controls_text($input['above_line'], 500) : '';
    $clean['third_column'] = isset($input['third_column']) ? eclipse_footer_controls_text($input['third_column'], 500, true) : '';
    foreach (array('use_default_copyright', 'use_default_legal', 'use_default_third') as $key) {
        if (array_key_exists($key, $input)) $clean[$key] = !empty($input[$key]);
    }

    // Migrate the short-lived 1.1.0 development fields without losing tester values.
    if ($clean['third_column'] === '') {
        $legacy = array();
        if (!empty($input['powered_line'])) $legacy[] = eclipse_footer_controls_text($input['powered_line'], 240);
        if (!empty($input['execution_line'])) $legacy[] = eclipse_footer_controls_text($input['execution_line'], 240);
        if ($legacy) $clean['third_column'] = implode("\n", $legacy);
    }
    return $clean;
}

function eclipse_footer_controls_path()
{
    if (!function_exists('eclipse_storage_root') || !function_exists('eclipse_storage_prepare')) return '';
    if (!eclipse_storage_prepare()) return '';
    $root = eclipse_storage_root();
    return $root === '' ? '' : $root . DIRECTORY_SEPARATOR . 'eclipse-footer-controls.json';
}

function eclipse_footer_controls_read()
{
    $path = eclipse_footer_controls_path();
    if ($path === '' || !is_file($path) || !is_readable($path) || filesize($path) > 65536) return eclipse_footer_controls_defaults();

    $lock = @fopen($path . '.lock', 'c');
    if ($lock) {
        @chmod($path . '.lock', 0640);
        @flock($lock, LOCK_SH);
    }
    $json = @file_get_contents($path, false, null, 0, 65537);
    if ($lock) {
        @flock($lock, LOCK_UN);
        fclose($lock);
    }
    if (!is_string($json) || strlen($json) > 65536) return eclipse_footer_controls_defaults();
    $decoded = json_decode($json, true);
    if (!is_array($decoded)) return eclipse_footer_controls_defaults();
    $clean = eclipse_footer_controls_sanitize($decoded);

    // Preserve pre-toggle 1.1.0 behaviour: an existing custom value remains custom,
    // while an empty legacy value continues to use the Geeklog default.
    $footer = function_exists('eclipse_footer_data') ? eclipse_footer_data() : array();
    if (!array_key_exists('use_default_copyright', $decoded)) {
        $clean['use_default_copyright'] = empty($footer['copyright']);
    }
    if (!array_key_exists('use_default_legal', $decoded)) {
        $clean['use_default_legal'] = empty($footer['legal_notice']);
    }
    if (!array_key_exists('use_default_third', $decoded)) {
        $clean['use_default_third'] = $clean['third_column'] === '';
    }
    return $clean;
}

function eclipse_footer_controls_write($value)
{
    $path = eclipse_footer_controls_path();
    if ($path === '') return false;

    $clean = eclipse_footer_controls_sanitize($value);
    $json = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false || strlen($json) > 65536) return false;

    $lock = @fopen($path . '.lock', 'c');
    if ($lock) @chmod($path . '.lock', 0640);
    if (!$lock || !@flock($lock, LOCK_EX)) {
        if ($lock) fclose($lock);
        return false;
    }

    $temp = dirname($path) . DIRECTORY_SEPARATOR . '.eclipse-footer-controls.' . getmypid() . '.' . str_replace('.', '', uniqid('', true)) . '.tmp';
    $ok = @file_put_contents($temp, $json . "\n", LOCK_EX) !== false;
    if ($ok) {
        @chmod($temp, 0640);
        $verify = @file_get_contents($temp, false, null, 0, 65537);
        $decoded = is_string($verify) ? json_decode($verify, true) : null;
        $ok = is_array($decoded) && eclipse_footer_controls_sanitize($decoded) === $clean;
    }
    if ($ok && is_file($path)) {
        @copy($path, $path . '.bak');
        if (is_file($path . '.bak')) @chmod($path . '.bak', 0640);
    }
    if ($ok) {
        if (!@rename($temp, $path)) {
            if (is_file($path)) @unlink($path);
            $ok = @rename($temp, $path);
        }
        if ($ok) @chmod($path, 0640);
    }
    if (is_file($temp)) @unlink($temp);
    @flock($lock, LOCK_UN);
    fclose($lock);
    return $ok;
}

function eclipse_footer_controls_delete()
{
    $path = eclipse_footer_controls_path();
    if ($path === '') return false;
    $ok = true;
    foreach (array($path, $path . '.bak') as $file) {
        if (is_file($file) && !@unlink($file)) $ok = false;
    }
    return $ok;
}

/** Clear the complete Geeklog template/resource cache when Eclipse changes. */
function eclipse_footer_clear_template_cache()
{
    global $_CONF;
    if (function_exists('CTL_clearCache')) {
        CTL_clearCache();
        return true;
    }
    if (!function_exists('CTL_clearCacheDirectories') || empty($_CONF['path_data'])) return false;
    $data = rtrim($_CONF['path_data'], '/\\') . DIRECTORY_SEPARATOR;
    CTL_clearCacheDirectories($data . 'layout_cache');
    CTL_clearCacheDirectories($data . 'layout_css');
    return true;
}

function eclipse_footer_refresh_cache_for_build()
{
    static $checked = false;
    if ($checked) return false;
    $checked = true;

    if (!function_exists('eclipse_storage_root') || !function_exists('eclipse_storage_prepare') || !eclipse_storage_prepare()) return false;
    $manifest = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'MANIFEST.json';
    $fingerprintSource = is_file($manifest) ? $manifest : __FILE__;
    $fingerprint = @hash_file('sha256', $fingerprintSource);
    if (!is_string($fingerprint) || $fingerprint === '') return false;

    $marker = eclipse_storage_root() . DIRECTORY_SEPARATOR . '.eclipse-build-cache';
    $previous = is_file($marker) ? trim((string) @file_get_contents($marker)) : '';
    if ($previous === $fingerprint) return false;

    eclipse_footer_clear_template_cache();
    @file_put_contents($marker, $fingerprint . "\n", LOCK_EX);
    @chmod($marker, 0640);
    return true;
}

function eclipse_footer_controls()
{
    return eclipse_footer_controls_read();
}

function eclipse_footer_uses_default($column)
{
    $controls = eclipse_footer_controls();
    $keys = array(1 => 'use_default_copyright', 2 => 'use_default_legal', 3 => 'use_default_third');
    return isset($keys[$column]) && !empty($controls[$keys[$column]]);
}

/**
 * Persist footer controls only after eclipse_render_customizer() has completed
 * the single Geeklog SEC_checkToken() used by Theme Studio.
 */
function eclipse_footer_controls_handle_post($studioHtml)
{
    if (empty($_POST['eclipse_save']) && empty($_POST['eclipse_reset'])) return;
    if (!is_string($studioHtml) || $studioHtml === '') return;

    $saveOk = strpos($studioHtml, 'Complete Eclipse settings, footer links and palettes saved persistently.') !== false;
    $resetOk = strpos($studioHtml, 'Saved settings and footer links removed from persistent JSON storage.') !== false;
    if (!$saveOk && !$resetOk) return;

    if (!empty($_POST['eclipse_reset'])) {
        if (eclipse_footer_controls_delete()) eclipse_footer_clear_template_cache();
        return;
    }

    $submitted = isset($_POST['eclipse_footer_controls']) && is_array($_POST['eclipse_footer_controls']) ? $_POST['eclipse_footer_controls'] : array();
    if (!isset($submitted['show_native'])) $submitted['show_native'] = false;
    foreach (array('use_default_copyright', 'use_default_legal', 'use_default_third') as $key) {
        if (!isset($submitted[$key])) $submitted[$key] = false;
    }
    if (eclipse_footer_controls_write($submitted)) eclipse_footer_clear_template_cache();
}

function eclipse_footer_expand_line($value, $preserveLines = false)
{
    global $_CONF;
    $value = strtr((string) $value, array(
        '{year}' => date('Y'),
        '{site_name}' => isset($_CONF['site_name']) ? $_CONF['site_name'] : '',
    ));

    $lines = $preserveLines ? explode("\n", $value) : array($value);
    $rendered = array();
    foreach ($lines as $line) {
        $line = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
        if (function_exists('PLG_replaceTags')) $line = PLG_replaceTags($line);
        if ($line !== '') $rendered[] = $line;
    }
    return implode('<br>', $rendered);
}

function eclipse_footer_render_above_line()
{
    $controls = eclipse_footer_controls();
    if ($controls['above_line'] === '') return '';
    return '<div class="eclipse-footer-above">' . eclipse_footer_expand_line($controls['above_line']) . '</div>';
}

function eclipse_footer_native_enabled()
{
    $controls = eclipse_footer_controls();
    return !empty($controls['show_native']);
}

function eclipse_footer_render_links_only()
{
    if (!function_exists('eclipse_footer_data')) return '';
    $data = eclipse_footer_data();
    if (empty($data['groups'])) return '';

    $html = '<section class="eclipse-footer-extras"><nav class="eclipse-footer-links" aria-label="Footer links">';
    $hasLinks = false;
    foreach ($data['groups'] as $group) {
        $rowHtml = '';
        if (empty($group['links']) || !is_array($group['links'])) continue;
        foreach ($group['links'] as $link) {
            if (empty($link['enabled'])) continue;
            $external = preg_match('#^https?://#i', $link['url']);
            $relations = array();
            if (!empty($link['new_window'])) {
                $relations[] = 'noopener';
                $relations[] = 'noreferrer';
            }
            if (!empty($link['nofollow'])) $relations[] = 'nofollow';
            $rowHtml .= '<li' . (!empty($link['emphasis']) ? ' class="is-emphasized"' : '') . '><a href="' . htmlspecialchars($link['url'], ENT_QUOTES, 'UTF-8') . '"'
                . (!empty($link['new_window']) ? ' target="_blank"' : '')
                . ($relations ? ' rel="' . implode(' ', array_unique($relations)) . '"' : '')
                . ($external ? ' class="is-external"' : '') . '>' . htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') . '</a></li>';
        }
        if ($rowHtml !== '') {
            $hasLinks = true;
            $html .= '<ul>' . $rowHtml . '</ul>';
        }
    }
    $html .= '</nav></section>';
    return $hasLinks ? $html : '';
}

function eclipse_footer_column_one()
{
    global $_CONF;
    if (!function_exists('eclipse_footer_data')) return '';
    $data = eclipse_footer_data();
    $value = isset($data['copyright']) ? $data['copyright'] : '';
    $value = strtr($value, array('{year}' => date('Y'), '{site_name}' => isset($_CONF['site_name']) ? $_CONF['site_name'] : ''));
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function eclipse_footer_column_two()
{
    global $_CONF;
    if (!function_exists('eclipse_footer_data')) return '';
    $data = eclipse_footer_data();
    $value = isset($data['legal_notice']) ? $data['legal_notice'] : '';
    $value = strtr($value, array('{year}' => date('Y'), '{site_name}' => isset($_CONF['site_name']) ? $_CONF['site_name'] : ''));
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function eclipse_footer_column_three()
{
    $controls = eclipse_footer_controls();
    return eclipse_footer_expand_line(isset($controls['third_column']) ? $controls['third_column'] : '', true);
}

function eclipse_footer_default_placeholder($column)
{
    global $_CONF, $LANG01;
    if ($column === 1) {
        $label = isset($LANG01[93]) ? $LANG01[93] : 'Copyright';
        $site = isset($_CONF['site_name']) ? $_CONF['site_name'] : 'Geeklog';
        return $label . ' © ' . date('Y') . ' ' . $site;
    }
    if ($column === 2) {
        return isset($LANG01[94]) ? $LANG01[94] : 'All trademarks and copyrights on this page are owned by their respective owners.';
    }
    $powered = isset($LANG01[95]) ? $LANG01[95] : 'Powered by';
    $created = isset($LANG01[91]) ? $LANG01[91] : 'Created this page in';
    $seconds = isset($LANG01[92]) ? $LANG01[92] : 'seconds';
    return $powered . ' Geeklog' . "\n" . $created . ' … ' . $seconds;
}

function eclipse_footer_controls_studio($html)
{
    eclipse_footer_refresh_cache_for_build();
    if ($html === '') return $html;
    $controls = eclipse_footer_controls();
    $h = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
    $defaultLabel = $h(eclipse_lang('footer_use_geeklog_default', 'Use Geeklog default content'));

    $controlsHtml = '<div class="eclipse-footer-native-controls">'
        . '<div class="eclipse-checks"><label><input type="checkbox" name="eclipse_footer_controls[show_native]" value="1"' . (!empty($controls['show_native']) ? ' checked' : '') . '> ' . $h(eclipse_lang('show_native_footer', 'Display the standard Geeklog footer block')) . '</label></div>'
        . '<label class="eclipse-footer-above-field"><span>' . $h(eclipse_lang('footer_above_line', 'Line above the footer block')) . '</span><input name="eclipse_footer_controls[above_line]" value="' . $h($controls['above_line']) . '" placeholder="[menu:footer]" maxlength="500"><small>' . $h(eclipse_lang('footer_above_line_help', 'Accepts Geeklog autotags, for example [menu:footer].')) . '</small></label>'
        . '</div>';

    $legalGrid = '<div class="eclipse-field-grid eclipse-footer-legal-fields">';
    if (strpos($html, $legalGrid) !== false) {
        $html = str_replace($legalGrid, $controlsHtml . $legalGrid, $html);
    }

    $copyrightToggle = '<span class="eclipse-footer-default-toggle"><input type="checkbox" name="eclipse_footer_controls[use_default_copyright]" value="1"' . (!empty($controls['use_default_copyright']) ? ' checked' : '') . '> ' . $defaultLabel . '</span>';
    $legalToggle = '<span class="eclipse-footer-default-toggle"><input type="checkbox" name="eclipse_footer_controls[use_default_legal]" value="1"' . (!empty($controls['use_default_legal']) ? ' checked' : '') . '> ' . $defaultLabel . '</span>';
    $thirdToggle = '<span class="eclipse-footer-default-toggle"><input type="checkbox" name="eclipse_footer_controls[use_default_third]" value="1"' . (!empty($controls['use_default_third']) ? ' checked' : '') . '> ' . $defaultLabel . '</span>';

    $copyrightPlaceholder = $h(eclipse_footer_default_placeholder(1));
    $legalPlaceholder = $h(eclipse_footer_default_placeholder(2));
    $thirdPlaceholder = $h(eclipse_footer_default_placeholder(3));

    $html = preg_replace_callback(
        '#<input name="eclipse_footer\[copyright\]" value="([^"]*)" placeholder="[^"]*">#',
        function ($match) use ($copyrightPlaceholder, $copyrightToggle) {
            return '<textarea name="eclipse_footer[copyright]" rows="3" maxlength="240" placeholder="' . $copyrightPlaceholder . '">' . $match[1] . '</textarea>' . $copyrightToggle;
        },
        $html
    );
    $html = preg_replace_callback(
        '#<input name="eclipse_footer\[legal_notice\]" value="([^"]*)" placeholder="[^"]*">#',
        function ($match) use ($legalPlaceholder, $legalToggle) {
            return '<textarea name="eclipse_footer[legal_notice]" rows="3" maxlength="320" placeholder="' . $legalPlaceholder . '">' . $match[1] . '</textarea>' . $legalToggle;
        },
        $html
    );

    $thirdField = '<label><span>' . $h(eclipse_lang('footer_powered_by_geeklog', 'Powered by Geeklog')) . '</span><textarea name="eclipse_footer_controls[third_column]" rows="3" maxlength="500" placeholder="' . $thirdPlaceholder . '">' . $h($controls['third_column']) . '</textarea>' . $thirdToggle . '<small>' . $h(eclipse_lang('footer_custom_empty_help', 'Uncheck the default option and leave this field empty to display an empty column.')) . '</small></label>';
    $needle = '</div><template id="eclipse-footer-link-template">';
    if (strpos($html, $needle) !== false) {
        $html = str_replace($needle, $thirdField . '</div><template id="eclipse-footer-link-template">', $html);
    }

    return $html;
}
