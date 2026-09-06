<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'content-bottom.php') !== false) {
    die('This file can not be used on its own!');
}

function eclipse_content_bottom_defaults()
{
    return array('content' => '');
}

function eclipse_content_bottom_path()
{
    if (!function_exists('eclipse_storage_root') || !function_exists('eclipse_storage_prepare')) return '';
    if (!eclipse_storage_prepare()) return '';
    $root = eclipse_storage_root();
    return $root === '' ? '' : $root . DIRECTORY_SEPARATOR . 'eclipse-content-bottom.json';
}

function eclipse_content_bottom_sanitize($input)
{
    $clean = eclipse_content_bottom_defaults();
    if (!is_array($input)) return $clean;
    $value = isset($input['content']) ? strip_tags((string) $input['content']) : '';
    $value = preg_replace("/\r\n?|\r/u", "\n", $value);
    $lines = array();
    foreach (explode("\n", $value) as $line) {
        $lines[] = rtrim(preg_replace('/[\t ]+/u', ' ', $line));
    }
    $value = trim(implode("\n", $lines));
    $clean['content'] = function_exists('mb_substr') ? mb_substr($value, 0, 4000, 'UTF-8') : substr($value, 0, 4000);
    return $clean;
}

function eclipse_content_bottom_read()
{
    $path = eclipse_content_bottom_path();
    if ($path === '' || !is_file($path) || !is_readable($path) || filesize($path) > 65536) return eclipse_content_bottom_defaults();
    $json = @file_get_contents($path, false, null, 0, 65537);
    if (!is_string($json) || strlen($json) > 65536) return eclipse_content_bottom_defaults();
    $decoded = json_decode($json, true);
    return eclipse_content_bottom_sanitize(is_array($decoded) ? $decoded : array());
}

function eclipse_content_bottom_write($input)
{
    $path = eclipse_content_bottom_path();
    if ($path === '') return false;
    $clean = eclipse_content_bottom_sanitize($input);
    $json = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    if ($json === false || strlen($json) > 65536) return false;

    $lock = @fopen($path . '.lock', 'c');
    if ($lock) @chmod($path . '.lock', 0640);
    if (!$lock || !@flock($lock, LOCK_EX)) {
        if ($lock) fclose($lock);
        return false;
    }

    $temp = dirname($path) . DIRECTORY_SEPARATOR . '.eclipse-content-bottom.' . getmypid() . '.' . str_replace('.', '', uniqid('', true)) . '.tmp';
    $ok = @file_put_contents($temp, $json . "\n", LOCK_EX) !== false;
    if ($ok) {
        @chmod($temp, 0640);
        if (is_file($path)) {
            @copy($path, $path . '.bak');
            if (is_file($path . '.bak')) @chmod($path . '.bak', 0640);
        }
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

function eclipse_content_bottom_delete()
{
    $path = eclipse_content_bottom_path();
    if ($path === '') return false;
    $ok = true;
    foreach (array($path, $path . '.bak') as $file) {
        if (is_file($file) && !@unlink($file)) $ok = false;
    }
    return $ok;
}

function eclipse_content_bottom_handle_post($studioHtml)
{
    if (empty($_POST['eclipse_save']) && empty($_POST['eclipse_reset'])) return;
    if (!is_string($studioHtml) || $studioHtml === '') return;

    $saveOk = strpos($studioHtml, 'Complete Eclipse settings, footer links and palettes saved persistently.') !== false;
    $resetOk = strpos($studioHtml, 'Saved settings and footer links removed from persistent JSON storage.') !== false;
    if (!$saveOk && !$resetOk) return;

    if (!empty($_POST['eclipse_reset'])) {
        eclipse_content_bottom_delete();
        return;
    }

    $submitted = isset($_POST['eclipse_content_bottom']) && is_array($_POST['eclipse_content_bottom']) ? $_POST['eclipse_content_bottom'] : array();
    eclipse_content_bottom_write($submitted);
}

function eclipse_content_bottom_render()
{
    $data = eclipse_content_bottom_read();
    if ($data['content'] === '') return '';

    $rendered = array();
    foreach (explode("\n", $data['content']) as $line) {
        $line = htmlspecialchars($line, ENT_QUOTES, 'UTF-8');
        if (function_exists('PLG_replaceTags')) $line = PLG_replaceTags($line);
        $rendered[] = $line;
    }
    return '<div class="eclipse-content-bottom">' . implode('<br>', $rendered) . '</div>';
}

function eclipse_content_bottom_studio($html)
{
    if (!is_string($html) || $html === '') return $html;
    $data = eclipse_content_bottom_read();
    $h = function ($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); };

    $block = '<fieldset data-studio-section="content-bottom" class="eclipse-content-bottom-editor">'
        . '<legend>' . $h(eclipse_lang('content_bottom_title', 'Content below the site content')) . '</legend>'
        . '<p class="eclipse-section-intro">' . $h(eclipse_lang('content_bottom_intro', 'Add text or Geeklog autotags displayed below the main site content and before the right column.')) . '</p>'
        . '<label><span>' . $h(eclipse_lang('content_bottom_content', 'Text or autotag')) . '</span>'
        . '<textarea name="eclipse_content_bottom[content]" rows="5" maxlength="4000" placeholder="[menu:footer]">' . $h($data['content']) . '</textarea>'
        . '<small>' . $h(eclipse_lang('content_bottom_help', 'Plain text and Geeklog autotags are accepted. Leave empty to display nothing.')) . '</small></label>'
        . '</fieldset>';

    $needle = '<fieldset data-studio-section="footer-links" class="eclipse-footer-editor">';
    if (strpos($html, $needle) !== false) {
        return str_replace($needle, $block . $needle, $html);
    }
    return $html;
}
