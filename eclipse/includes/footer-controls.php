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

    // Migrate the short-lived 1.1.0 development fields without losing a tester's values.
    if ($clean['third_column'] === '') {
        $legacy = array();
        if (!empty($input['powered_line'])) $legacy[] = eclipse_footer_controls_text($input['powered_line'], 240);
        if (!empty($input['execution_line'])) $legacy[] = eclipse_footer_controls_text($input['execution_line'], 240);
        if ($legacy) $clean['third_column'] = implode("\n", $legacy);
    }
    return $clean;
}

function eclipse_footer_controls()
{
    $saved = function_exists('eclipse_data_json') ? eclipse_data_json('eclipse-footer-controls.json', array()) : array();
    return eclipse_footer_controls_sanitize(is_array($saved) ? $saved : array());
}

/**
 * Persist footer controls only after eclipse_render_customizer() has completed
 * its own SEC_checkToken() and reported a successful save/reset. This avoids
 * validating the same one-time Geeklog token twice.
 */
function eclipse_footer_controls_handle_post($studioHtml)
{
    if (empty($_POST['eclipse_save']) && empty($_POST['eclipse_reset'])) return;
    if (!is_string($studioHtml) || $studioHtml === '') return;

    $saveOk = strpos($studioHtml, 'Complete Eclipse settings, footer links and palettes saved persistently.') !== false;
    $resetOk = strpos($studioHtml, 'Saved settings and footer links removed from persistent JSON storage.') !== false;
    if (!$saveOk && !$resetOk) return;

    if (!empty($_POST['eclipse_reset'])) {
        if (function_exists('eclipse_delete_data_json')) eclipse_delete_data_json('eclipse-footer-controls.json');
        return;
    }

    $submitted = isset($_POST['eclipse_footer_controls']) && is_array($_POST['eclipse_footer_controls']) ? $_POST['eclipse_footer_controls'] : array();
    if (!isset($submitted['show_native'])) $submitted['show_native'] = false;
    if (function_exists('eclipse_write_data_json')) {
        eclipse_write_data_json('eclipse-footer-controls.json', eclipse_footer_controls_sanitize($submitted));
    }
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
    if (!function_exists('eclipse_render_footer_links')) return '';
    $html = eclipse_render_footer_links();
    if ($html === '') return '';
    return preg_replace('#<div class="eclipse-footer-legal">.*?</div>#s', '', $html);
}

function eclipse_footer_column_one()
{
    global $_CONF;
    if (!function_exists('eclipse_footer_data')) return '';
    $data = eclipse_footer_data();
    if (empty($data['copyright'])) return '';
    $value = strtr($data['copyright'], array('{year}' => date('Y'), '{site_name}' => isset($_CONF['site_name']) ? $_CONF['site_name'] : ''));
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function eclipse_footer_column_two()
{
    global $_CONF;
    if (!function_exists('eclipse_footer_data')) return '';
    $data = eclipse_footer_data();
    if (empty($data['legal_notice'])) return '';
    $value = strtr($data['legal_notice'], array('{year}' => date('Y'), '{site_name}' => isset($_CONF['site_name']) ? $_CONF['site_name'] : ''));
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function eclipse_footer_column_three()
{
    $controls = eclipse_footer_controls();
    return $controls['third_column'] !== '' ? eclipse_footer_expand_line($controls['third_column'], true) : '';
}

function eclipse_footer_controls_studio($html)
{
    if ($html === '') return $html;
    $controls = eclipse_footer_controls();
    $h = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };

    $controlsHtml = '<div class="eclipse-footer-native-controls">'
        . '<div class="eclipse-checks"><label><input type="checkbox" name="eclipse_footer_controls[show_native]" value="1"' . (!empty($controls['show_native']) ? ' checked' : '') . '> ' . $h(eclipse_lang('show_native_footer', 'Display the standard Geeklog footer block')) . '</label></div>'
        . '<label class="eclipse-footer-above-field"><span>' . $h(eclipse_lang('footer_above_line', 'Line above the footer block')) . '</span><input name="eclipse_footer_controls[above_line]" value="' . $h($controls['above_line']) . '" placeholder="[menu:footer]" maxlength="500"><small>' . $h(eclipse_lang('footer_above_line_help', 'Accepts Geeklog autotags, for example [menu:footer].')) . '</small></label>'
        . '</div>';

    $legalGrid = '<div class="eclipse-field-grid eclipse-footer-legal-fields">';
    if (strpos($html, $legalGrid) !== false) {
        $html = str_replace($legalGrid, $controlsHtml . $legalGrid, $html);
    }

    $thirdField = '<label><span>' . $h(eclipse_lang('footer_third_column', 'Third footer column')) . '</span><textarea name="eclipse_footer_controls[third_column]" rows="2" maxlength="500">' . $h($controls['third_column']) . '</textarea><small>' . $h(eclipse_lang('footer_blank_keeps_default', 'Leave empty to keep the Geeklog default.')) . '</small></label>';
    $needle = '</div><template id="eclipse-footer-link-template">';
    if (strpos($html, $needle) !== false) {
        $html = str_replace($needle, $thirdField . '</div><template id="eclipse-footer-link-template">', $html);
    }

    return $html;
}
