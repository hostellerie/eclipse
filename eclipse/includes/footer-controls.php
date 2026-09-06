<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'footer-controls.php') !== false) {
    die('This file can not be used on its own!');
}

function eclipse_footer_controls_defaults()
{
    return array(
        'show_native' => true,
        'above_line' => '',
        'powered_line' => '',
        'execution_line' => '',
    );
}

function eclipse_footer_controls_text($value, $maximum)
{
    $value = trim(preg_replace('/\s+/u', ' ', strip_tags((string) $value)));
    return function_exists('mb_substr') ? mb_substr($value, 0, $maximum, 'UTF-8') : substr($value, 0, $maximum);
}

function eclipse_footer_controls_sanitize($input)
{
    $clean = eclipse_footer_controls_defaults();
    if (!is_array($input)) return $clean;
    $clean['show_native'] = !empty($input['show_native']);
    $clean['above_line'] = isset($input['above_line']) ? eclipse_footer_controls_text($input['above_line'], 500) : '';
    $clean['powered_line'] = isset($input['powered_line']) ? eclipse_footer_controls_text($input['powered_line'], 240) : '';
    $clean['execution_line'] = isset($input['execution_line']) ? eclipse_footer_controls_text($input['execution_line'], 240) : '';
    return $clean;
}

function eclipse_footer_controls()
{
    $saved = function_exists('eclipse_data_json') ? eclipse_data_json('eclipse-footer-controls.json', array()) : array();
    return eclipse_footer_controls_sanitize(is_array($saved) ? $saved : array());
}

function eclipse_footer_controls_handle_post()
{
    if (empty($_POST['eclipse_save']) && empty($_POST['eclipse_reset'])) return;
    if (function_exists('SEC_checkToken') && !SEC_checkToken()) return;
    if (!empty($_POST['eclipse_reset'])) {
        if (function_exists('eclipse_delete_data_json')) eclipse_delete_data_json('eclipse-footer-controls.json');
        return;
    }
    $submitted = isset($_POST['eclipse_footer_controls']) && is_array($_POST['eclipse_footer_controls']) ? $_POST['eclipse_footer_controls'] : array();
    if (!isset($submitted['show_native'])) $submitted['show_native'] = false;
    if (function_exists('eclipse_write_data_json')) eclipse_write_data_json('eclipse-footer-controls.json', eclipse_footer_controls_sanitize($submitted));
}

function eclipse_footer_expand_line($value)
{
    global $_CONF;
    $value = strtr((string) $value, array(
        '{year}' => date('Y'),
        '{site_name}' => isset($_CONF['site_name']) ? $_CONF['site_name'] : '',
    ));
    $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    if (function_exists('PLG_replaceTags')) {
        $value = PLG_replaceTags($value);
    }
    return $value;
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

function eclipse_footer_has_custom_legal()
{
    if (!function_exists('eclipse_footer_data')) return false;
    $data = eclipse_footer_data();
    return !empty($data['copyright']) || !empty($data['legal_notice']);
}

function eclipse_footer_render_custom_legal()
{
    global $_CONF;
    if (!function_exists('eclipse_footer_data')) return '';
    $data = eclipse_footer_data();
    $replace = array('{year}' => date('Y'), '{site_name}' => isset($_CONF['site_name']) ? $_CONF['site_name'] : '');
    $html = '<p class="copyright eclipse-footer-custom-legal">';
    if ($data['copyright'] !== '') $html .= htmlspecialchars(strtr($data['copyright'], $replace), ENT_QUOTES, 'UTF-8');
    if ($data['copyright'] !== '' && $data['legal_notice'] !== '') $html .= '<br>';
    if ($data['legal_notice'] !== '') $html .= htmlspecialchars(strtr($data['legal_notice'], $replace), ENT_QUOTES, 'UTF-8');
    return $html . '</p>';
}

function eclipse_footer_render_links_only()
{
    if (!function_exists('eclipse_render_footer_links')) return '';
    $html = eclipse_render_footer_links();
    if ($html === '') return '';
    $html = preg_replace('#<div class="eclipse-footer-legal">.*?</div>#s', '', $html);
    return $html;
}

function eclipse_footer_custom_powered_line()
{
    $controls = eclipse_footer_controls();
    return $controls['powered_line'] !== '' ? eclipse_footer_expand_line($controls['powered_line']) : '';
}

function eclipse_footer_custom_execution_line()
{
    $controls = eclipse_footer_controls();
    return $controls['execution_line'] !== '' ? eclipse_footer_expand_line($controls['execution_line']) : '';
}

function eclipse_footer_controls_studio($html)
{
    if ($html === '') return $html;
    $controls = eclipse_footer_controls();
    $h = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };
    $fieldset = '<fieldset data-studio-section="native-footer" class="eclipse-native-footer-editor">'
        . '<legend>' . $h(eclipse_lang('native_footer_block', 'Geeklog footer block')) . '</legend>'
        . '<p class="eclipse-section-intro">' . $h(eclipse_lang('native_footer_help', 'Control the standard Geeklog footer block. Leave replacement fields empty to keep Geeklog defaults.')) . '</p>'
        . '<div class="eclipse-checks"><label><input type="checkbox" name="eclipse_footer_controls[show_native]" value="1"' . (!empty($controls['show_native']) ? ' checked' : '') . '> ' . $h(eclipse_lang('show_native_footer', 'Display the standard Geeklog footer block')) . '</label></div>'
        . '<div class="eclipse-field-grid">'
        . '<label><span>' . $h(eclipse_lang('footer_above_line', 'Line above the footer block')) . '</span><input name="eclipse_footer_controls[above_line]" value="' . $h($controls['above_line']) . '" placeholder="[menu:footer]" maxlength="500"><small>' . $h(eclipse_lang('footer_above_line_help', 'Accepts Geeklog autotags, for example [menu:footer].')) . '</small></label>'
        . '<label><span>' . $h(eclipse_lang('footer_powered_line', 'Powered by line')) . '</span><input name="eclipse_footer_controls[powered_line]" value="' . $h($controls['powered_line']) . '" maxlength="240"><small>' . $h(eclipse_lang('footer_blank_keeps_default', 'Leave empty to keep the Geeklog default.')) . '</small></label>'
        . '<label><span>' . $h(eclipse_lang('footer_execution_line', 'Execution line')) . '</span><input name="eclipse_footer_controls[execution_line]" value="' . $h($controls['execution_line']) . '" maxlength="240"><small>' . $h(eclipse_lang('footer_blank_keeps_default', 'Leave empty to keep the Geeklog default.')) . '</small></label>'
        . '</div><p class="eclipse-section-intro">' . $h(eclipse_lang('footer_legal_repurpose_help', 'Copyright line and Legal notice below now replace the first column of the standard footer instead of adding duplicate lines above it.')) . '</p>'
        . '</fieldset>';
    $needle = '<fieldset class="eclipse-portability">';
    if (strpos($html, $needle) !== false) return str_replace($needle, $fieldset . $needle, $html);
    return $html;
}
