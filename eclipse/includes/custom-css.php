<?php

if (strpos(strtolower(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : ''), 'custom-css.php') !== false) {
    die('This file can not be used on its own!');
}

function eclipse_custom_css_path()
{
    if (!function_exists('eclipse_storage_root')) return '';
    $root = eclipse_storage_root();
    return $root === '' ? '' : $root . DIRECTORY_SEPARATOR . 'eclipse-custom.css';
}

function eclipse_custom_css_validate($css, &$error = '')
{
    $error = '';
    if (!is_string($css)) {
        $error = 'invalid';
        return false;
    }
    $css = str_replace(array("\r\n", "\r"), "\n", $css);
    if (strlen($css) > 65536) {
        $error = 'too_large';
        return false;
    }
    if (strpos($css, "\0") !== false || preg_match('/[\x01-\x08\x0B\x0C\x0E-\x1F]/', $css)) {
        $error = 'control_chars';
        return false;
    }
    if (preg_match('#</style\b#i', $css)) {
        $error = 'style_close';
        return false;
    }
    return rtrim($css) . ($css === '' ? '' : "\n");
}

function eclipse_custom_css_read()
{
    $path = eclipse_custom_css_path();
    if ($path === '' || !is_file($path) || !is_readable($path) || filesize($path) > 65536) return '';
    $css = @file_get_contents($path, false, null, 0, 65537);
    if (!is_string($css) || strlen($css) > 65536) return '';
    $error = '';
    $validated = eclipse_custom_css_validate($css, $error);
    return $validated === false ? '' : $validated;
}

function eclipse_custom_css_write($css, &$error = '')
{
    $validated = eclipse_custom_css_validate($css, $error);
    if ($validated === false) return false;
    if (!function_exists('eclipse_storage_prepare') || !eclipse_storage_prepare()) {
        $error = 'storage';
        return false;
    }
    $path = eclipse_custom_css_path();
    if ($path === '') {
        $error = 'storage';
        return false;
    }
    $lock = @fopen($path . '.lock', 'c');
    if (!$lock || !@flock($lock, LOCK_EX)) {
        if ($lock) fclose($lock);
        $error = 'lock';
        return false;
    }
    $ok = true;
    if (is_file($path)) {
        $ok = @copy($path, $path . '.bak');
        if ($ok) @chmod($path . '.bak', 0640);
    }
    $temp = dirname($path) . DIRECTORY_SEPARATOR . '.eclipse-custom.css.' . getmypid() . '.' . str_replace('.', '', uniqid('', true)) . '.tmp';
    if ($ok) {
        $ok = @file_put_contents($temp, $validated, LOCK_EX) !== false;
        if ($ok) @chmod($temp, 0640);
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
    if (!$ok) $error = 'write';
    return $ok;
}

function eclipse_custom_css_notice($notice = null)
{
    static $current = array();
    if (is_array($notice)) $current = $notice;
    return $current;
}

function eclipse_custom_css_message($code)
{
    $lang = function_exists('eclipse_html_language') ? strtolower((string) eclipse_html_language()) : 'en';
    $fr = strpos($lang, 'fr') === 0;
    $messages = array(
        'saved' => $fr ? 'CSS personnalisé enregistré.' : 'Custom CSS saved.',
        'cleared' => $fr ? 'CSS personnalisé vidé.' : 'Custom CSS cleared.',
        'token' => $fr ? 'Jeton de sécurité refusé. Aucun CSS n’a été modifié.' : 'Security token rejected. Custom CSS was not changed.',
        'too_large' => $fr ? 'Le CSS personnalisé ne peut pas dépasser 64 Kio.' : 'Custom CSS cannot exceed 64 KiB.',
        'style_close' => $fr ? 'La séquence </style> n’est pas autorisée dans le CSS personnalisé.' : 'The </style> sequence is not allowed in Custom CSS.',
        'control_chars' => $fr ? 'Le CSS contient des caractères de contrôle non autorisés.' : 'The CSS contains unsupported control characters.',
        'storage' => $fr ? 'Le stockage protégé Eclipse n’est pas disponible en écriture.' : 'Eclipse protected storage is not writable.',
        'lock' => $fr ? 'Impossible de verrouiller le fichier CSS personnalisé.' : 'Unable to lock the Custom CSS file.',
        'write' => $fr ? 'Impossible d’enregistrer le CSS personnalisé.' : 'Unable to save Custom CSS.',
        'invalid' => $fr ? 'Le CSS personnalisé reçu est invalide.' : 'The submitted Custom CSS is invalid.',
    );
    return isset($messages[$code]) ? $messages[$code] : ($fr ? 'Impossible d’enregistrer le CSS personnalisé.' : 'Unable to save Custom CSS.');
}

function eclipse_custom_css_handle_request()
{
    if (!function_exists('eclipse_is_admin_request') || !eclipse_is_admin_request()) return;
    if (!function_exists('SEC_inGroup') || !SEC_inGroup('Root')) return;
    if (!isset($_POST['eclipse_custom_css_save']) && !isset($_POST['eclipse_custom_css_clear'])) return;
    if (!function_exists('SEC_checkToken') || !SEC_checkToken()) {
        eclipse_custom_css_notice(array('type' => 'error', 'code' => 'token', 'message' => eclipse_custom_css_message('token')));
        return;
    }
    $css = isset($_POST['eclipse_custom_css_clear']) ? '' : (isset($_POST['eclipse_custom_css']) ? (string) $_POST['eclipse_custom_css'] : '');
    $error = '';
    if (eclipse_custom_css_write($css, $error)) {
        $code = isset($_POST['eclipse_custom_css_clear']) ? 'cleared' : 'saved';
        if (function_exists('eclipse_clear_theme_cache')) eclipse_clear_theme_cache();
        eclipse_custom_css_notice(array('type' => 'success', 'code' => $code, 'message' => eclipse_custom_css_message($code)));
    } else {
        eclipse_custom_css_notice(array('type' => 'error', 'code' => $error, 'message' => eclipse_custom_css_message($error)));
    }
}

function eclipse_custom_css_tag()
{
    if (function_exists('eclipse_is_admin_request') && eclipse_is_admin_request()) return '';
    $css = eclipse_custom_css_read();
    if ($css === '') return '';
    return "<style id=\"eclipse-custom-css\">\n" . $css . "</style>\n";
}
