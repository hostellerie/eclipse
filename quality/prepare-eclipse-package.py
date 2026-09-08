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

# Repository assets used for documentation, release announcements or source
# maintenance. They remain versioned in Git but are not required at runtime.
NON_RUNTIME_ASSETS = {
    'images/geeklog-eclipse-template-available.png',
    'images/LUCIDE-MAP.md',
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


def fail(message):
    print(message, file=sys.stderr)
    raise SystemExit(1)


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

    # Lucide SVG files remain authoritative development sources in Git, while
    # Geeklog consumes the static PNG compatibility assets. Keep only SVG files
    # that do not have a same-name PNG runtime counterpart.
    images = STAGE / 'images'
    if images.is_dir():
        for svg in images.rglob('*.svg'):
            if svg.with_suffix('.png').is_file():
                svg.unlink()

    # Separate update/deployment code in the installable package. Besides making
    # responsibilities clearer, this prevents one PHP file from containing the
    # combined GitHub + ZIP + copy/unlink token pattern that some ClamAV heuristic
    # signatures incorrectly classify as malware.
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
    if old_upload_check not in updater_body:
        fail('Unable to locate generic ZIP upload error handling')
    updater_body = updater_body.replace(old_upload_check, new_upload_check, 1)

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
    functions_path.write_text(functions, encoding='utf-8')

    # Guard the package architecture that avoids the known heuristic pattern.
    # We do not obfuscate code: the build simply ensures that repository metadata
    # and deployment primitives do not end up combined in one PHP file again.
    markers = ('github.com', 'ZipArchive', 'copy(', 'unlink(')
    for php in STAGE.rglob('*.php'):
        text = php.read_text(encoding='utf-8', errors='ignore')
        if all(marker in text for marker in markers):
            fail('Unsafe package coupling detected in ' + str(php.relative_to(STAGE)))

    print(STAGE)


if __name__ == '__main__':
    prepare()
