from pathlib import Path

functions = Path('eclipse/functions.php')
text = functions.read_text()
old = "function theme_init_eclipse()\n{\n    global $_BLOCK_TEMPLATE, $_CONF, $TEMPLATE_OPTIONS;\n    $_CONF['left_blocks_in_footer'] = 1;"
new = "function theme_init_eclipse()\n{\n    global $_BLOCK_TEMPLATE, $_CONF, $TEMPLATE_OPTIONS, $_IMAGE_TYPE;\n    // Geeklog admin helpers build their icon URLs from this global.\n    // Eclipse is SVG-native, so set the image type at theme initialization.\n    $_IMAGE_TYPE = 'svg';\n    $_CONF['left_blocks_in_footer'] = 1;"
if old not in text:
    raise SystemExit('theme_init_eclipse signature not found')
functions.write_text(text.replace(old, new, 1))

theme_js = Path('eclipse/js/theme.js')
text = theme_js.read_text()
start_marker = "    document.querySelectorAll('img[src*=\\\"/layout/eclipse/images/\\\"]').forEach(function (img) {"
end_marker = "    function setupSeoAssistant"
start = text.find(start_marker)
if start < 0:
    raise SystemExit('legacy PNG-to-SVG fallback start not found')
end = text.find(end_marker, start)
if end < 0:
    raise SystemExit('legacy PNG-to-SVG fallback end not found')
theme_js.write_text(text[:start] + text[end:])
