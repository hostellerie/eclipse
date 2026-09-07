<?php

if (strpos(strtolower($_SERVER['PHP_SELF']), 'palette-controls.php') !== false) {
    die('This file can not be used on its own!');
}

/**
 * Additional Eclipse built-in palettes that extend Theme Studio without
 * modifying Geeklog core or relying on client-side option injection.
 *
 * Each palette exposes the six colors already understood by theme.js. Visual
 * accents that intentionally differ from those six colors (such as the Vivid
 * red header) are applied through the palette body class returned below.
 */
function eclipse_palette_builtin_extensions()
{
    return array(
        'vivid-red' => array(
            'label' => 'Vivid red',
            // Red is the visual identity; blue remains reserved for links.
            // #d71920 keeps white primary-button text above WCAG AA contrast.
            'colors' => array('#d71920', '#a90d15', '#005bbb', '#f4f6fb', '#ffffff', '#202431'),
            'class' => 'palette-vivid-red',
        ),
    );
}

function eclipse_palette_current_colors()
{
    $options = eclipse_theme_options();
    $keys = array('color_primary', 'color_secondary', 'color_link', 'color_background', 'color_surface', 'color_text');
    $colors = array();
    foreach ($keys as $key) {
        $colors[] = isset($options[$key]) ? strtolower((string) $options[$key]) : '';
    }
    return $colors;
}

function eclipse_palette_current_key()
{
    $current = eclipse_palette_current_colors();
    foreach (eclipse_palette_builtin_extensions() as $key => $palette) {
        if ($current === array_map('strtolower', $palette['colors'])) {
            return $key;
        }
    }
    return '';
}

function eclipse_palette_body_class()
{
    $key = eclipse_palette_current_key();
    $palettes = eclipse_palette_builtin_extensions();
    return $key !== '' && isset($palettes[$key]['class']) ? $palettes[$key]['class'] : '';
}

/**
 * Extend the server-rendered Theme Studio preset select.
 *
 * Theme Studio already has a server-side extension pipeline in
 * admin/commandcontrol.thtml. Keeping palette additions here makes them work
 * identically on Geeklog 2.1.x and 2.2.x and lets theme.js consume data-colors
 * through its existing generic preset support.
 */
function eclipse_palette_studio($html)
{
    if (!is_string($html) || strpos($html, 'id="eclipse-palette-preset"') === false) {
        return $html;
    }

    $needle = '<option value="default">Eclipse default</option>';
    if (strpos($html, $needle) === false) {
        return $html;
    }

    $options = '';
    foreach (eclipse_palette_builtin_extensions() as $key => $palette) {
        if (strpos($html, 'value="' . $key . '"') !== false) {
            continue;
        }
        $options .= '<option value="' . htmlspecialchars($key, ENT_QUOTES, 'UTF-8') . '" data-colors="'
            . htmlspecialchars(implode(',', $palette['colors']), ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars($palette['label'], ENT_QUOTES, 'UTF-8') . '</option>';
    }

    return $options === '' ? $html : str_replace($needle, $needle . $options, $html);
}
