#!/usr/bin/env python3
from pathlib import Path
import shutil
import sys

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / 'eclipse'
STAGE_ROOT = ROOT / '.package-stage'
STAGE = STAGE_ROOT / 'eclipse'

DEV_DOCS = {
    'ADMIN-UI.md',
    'COMPATIBILITY.md',
    'CSS-AUDIT.md',
    'MIGRATION.md',
    'PERFORMANCE.md',
    'QA-CHECKLIST.md',
    'ROADMAP.md',
    'VISUAL-REGRESSION.md',
}

NON_RUNTIME_ASSETS = {
    'images/geeklog-eclipse-template-available.png',
    'images/LUCIDE-MAP.md',
}

REQUIRED_RUNTIME_ASSETS = {
    'images/admin/down.png',
    'images/admin/up.png',
}

UPLOAD_ERROR_HELPER = r'''function eclipse_update_upload_error_message($error)
{
    $error = (int) $error;
    if ($error === UPLOAD_ERR_INI_SIZE) {
        $limit = ini_get('upload_max_filesize');
        return 'The ZIP exceeds the server upload_max_filesize limit' . ($limit ? ' (' . $limit . ')' : '') . '.';
    }
    if ($error === UPLOAD_ERR_FORM_SIZE) return 'The ZIP exceeds the upload limit declared by the form.';
    if ($error === UPLOAD_ERR_PARTIAL) return 'The ZIP was only partially uploaded. Please try again.';
    if ($error === UPLOAD_ERR_NO_FILE) return 'No ZIP file was received.';
    if (defined('UPLOAD_ERR_NO_TMP_DIR') && $error === UPLOAD_ERR_NO_TMP_DIR) return 'The server temporary upload directory is missing.';
    if (defined('UPLOAD_ERR_CANT_WRITE') && $error === UPLOAD_ERR_CANT_WRITE) return 'The server could not write the uploaded ZIP to disk.';
    if (defined('UPLOAD_ERR_EXTENSION') && $error === UPLOAD_ERR_EXTENSION) return 'A PHP extension stopped the ZIP upload.';
    return 'The ZIP upload failed with PHP upload error ' . $error . '.';
}

'''

POST_UPDATE_HELPERS = r'''function eclipse_theme_homepage()
{
    $ini = @parse_ini_file(__DIR__ . '/theme.ini', true);
    return isset($ini['theme']['url']) ? (string) $ini['theme']['url'] : '';
}

function eclipse_asset_cache_token()
{
    $stamp = @filemtime(__DIR__ . '/theme.ini');
    $value = eclipse_theme_version() . ($stamp ? '-' . (string) $stamp : '');
    return '?v=' . rawurlencode($value);
}

function eclipse_admin_post_redirect($status)
{
    global $_CONF;
    if (empty($_CONF['site_admin_url']) || headers_sent()) return false;
    $allowed = array('updated', 'restored');
    if (!in_array($status, $allowed, true)) return false;
    $url = rtrim($_CONF['site_admin_url'], '/') . '/index.php?eclipse_update_status=' . rawurlencode($status) . '#eclipse-theme-studio';
    header('Location: ' . $url, true, 303);
    exit;
}

'''


def fail(message):
    print(message, file=sys.stderr)
    raise SystemExit(1)


def replace_once(text, old, new, label):
    if old not in text:
        fail('Unable to locate ' + label)
    return text.replace(old, new, 1)


def prepare():
    if not SOURCE.is_dir():
        fail('Missing eclipse source directory')
    shutil.rmtree(STAGE_ROOT, ignore_errors=True)
    shutil.copytree(SOURCE, STAGE, symlinks=False)

    for name in DEV_DOCS:
        path = STAGE / name
        if path.exists():
            path.unlink()

    for relative in NON_RUNTIME_ASSETS:
        path = STAGE / relative
        if path.exists():
            path.unlink()

    images = STAGE / 'images'
    if images.is_dir():
        for svg in images.rglob('*.svg'):
            if svg.with_suffix('.png').is_file():
                svg.unlink()

    for relative in REQUIRED_RUNTIME_ASSETS:
        if not (STAGE / relative).is_file():
            fail('Required Geeklog runtime asset is missing: ' + relative)

    functions_path = STAGE / 'functions.php'
    functions = functions_path.read_text(encoding='utf-8')
    start_marker = 'function eclipse_install_uploaded_update($upload)'
    end_marker = 'function eclipse_theme_classes()'
    start = functions.find(start_marker)
    end = functions.find(end_marker)
    if start < 0 or end < 0 or end <= start:
        fail('Unable to locate Eclipse update functions in functions.php')

    updater_body = functions[start:end].rstrip() + '\n'
    old_upload_check = "if (!is_array($upload) || !isset($upload['error']) || $upload['error'] !== UPLOAD_ERR_OK) return $fail('The ZIP upload failed.');"
    new_upload_check = "if (!is_array($upload) || !isset($upload['error'])) return $fail('No ZIP upload was received.');\n    if ((int) $upload['error'] !== UPLOAD_ERR_OK) return $fail(eclipse_update_upload_error_message($upload['error']));"
    updater_body = replace_once(updater_body, old_upload_check, new_upload_check, 'generic ZIP upload error handling')

    updater_path = STAGE / 'includes' / 'theme-update.php'
    updater_path.parent.mkdir(parents=True, exist_ok=True)
    updater_path.write_text(
        "<?php\n\nif (!defined('VERSION')) {\n    die('This file can not be used on its own!');\n}\n\n"
        + UPLOAD_ERROR_HELPER
        + updater_body,
        encoding='utf-8'
    )

    replacement = "require_once __DIR__ . '/includes/theme-update.php';\n\n"
    functions = functions[:start] + replacement + functions[end:]

    render_marker = 'function eclipse_render_customizer()\n{'
    functions = replace_once(functions, render_marker, POST_UPDATE_HELPERS + render_marker, 'Theme Studio render function')

    homepage_line = "        'theme_homepage'         => 'https://github.com/hostellerie/eclipse',"
    functions = replace_once(functions, homepage_line, "        'theme_homepage'         => eclipse_theme_homepage(),", 'theme homepage metadata')

    asset_line = "$version = '?v=' . rawurlencode(eclipse_theme_version());"
    asset_count = functions.count(asset_line)
    if asset_count != 2:
        fail('Expected two Eclipse asset cache token declarations, found ' + str(asset_count))
    functions = functions.replace(asset_line, '$version = eclipse_asset_cache_token();')

    update_message = "            $message = '<p class=\"eclipse-notice ' . ($result['success'] ? 'eclipse-success' : 'eclipse-error') . '\">' . htmlspecialchars($result['message'], ENT_QUOTES, 'UTF-8') . '</p>';"
    update_replacement = "            if (!empty($result['success']) && eclipse_admin_post_redirect('updated')) return '';\n" + update_message
    functions = replace_once(functions, update_message, update_replacement, 'successful update redirect')

    rollback_old = "                $message = '<p class=\"eclipse-notice eclipse-success\">Theme backup restored and Eclipse theme caches cleared. Reload the page.</p>';\n                eclipse_clear_theme_cache();"
    rollback_new = "                eclipse_clear_theme_cache();\n                if (eclipse_admin_post_redirect('restored')) return '';\n                $message = '<p class=\"eclipse-notice eclipse-success\">Theme backup restored and Eclipse theme caches cleared. Reload the page.</p>';"
    functions = replace_once(functions, rollback_old, rollback_new, 'successful rollback redirect')

    message_marker = "    $message = '';\n    $tokenName = defined('CSRF_TOKEN') ? CSRF_TOKEN : 'token';"
    message_replacement = "    $message = '';\n    if (isset($_GET['eclipse_update_status'])) {\n        $status = (string) $_GET['eclipse_update_status'];\n        if ($status === 'updated') $message = '<p class=\"eclipse-notice eclipse-success\">Eclipse was updated successfully. Theme caches were cleared and this page was loaded in a fresh request.</p>';\n        elseif ($status === 'restored') $message = '<p class=\"eclipse-notice eclipse-success\">The Eclipse backup was restored successfully. Theme caches were cleared and this page was loaded in a fresh request.</p>';\n    }\n    $tokenName = defined('CSRF_TOKEN') ? CSRF_TOKEN : 'token';"
    functions = replace_once(functions, message_marker, message_replacement, 'post-update status message')

    functions_path.write_text(functions, encoding='utf-8')

    markers = ('github.com', 'ZipArchive', 'copy(', 'unlink(')
    for php in STAGE.rglob('*.php'):
        text = php.read_text(encoding='utf-8', errors='ignore')
        if all(marker in text for marker in markers):
            fail('Unsafe package coupling detected in ' + str(php.relative_to(STAGE)))

    print(STAGE)


if __name__ == '__main__':
    prepare()
