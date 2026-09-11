from pathlib import Path

p = Path('eclipse/functions.php')
text = p.read_text()

old = """function eclipse_is_configuration_page()
{
    return eclipse_admin_page() === 'configuration';
}

function eclipse_supports_admin_list()
{
    return defined('VERSION') && version_compare(VERSION, '2.2.0', '>=');
}

function eclipse_context_classes()
{
    if (!eclipse_is_admin_request()) return 'eclipse-public-page';
    $page = eclipse_admin_page();
    return 'eclipse-admin-page' . ($page !== '' ? ' eclipse-admin-' . $page : '');
}
"""
new = """function eclipse_is_configuration_page()
{
    return eclipse_admin_page() === 'configuration';
}

function eclipse_is_staticpages_admin()
{
    if (!eclipse_is_admin_request()) return false;
    return preg_match('#(?:^|/)admin/plugins/staticpages/index\\.php$#', eclipse_request_path()) === 1;
}

function eclipse_supports_admin_list()
{
    return defined('VERSION') && version_compare(VERSION, '2.2.0', '>=');
}

function eclipse_context_classes()
{
    global $_CONF;
    if (!eclipse_is_admin_request()) return 'eclipse-public-page';
    $page = eclipse_admin_page();
    $classes = 'eclipse-admin-page' . ($page !== '' ? ' eclipse-admin-' . $page : '');
    if (eclipse_is_staticpages_admin()) {
        $classes .= ' eclipse-staticpages-admin';
        if (!empty($_CONF['titletoid'])) $classes .= ' eclipse-titletoid-enabled';
    }
    return $classes;
}
"""
if old not in text:
    raise SystemExit('Context block not found')
text = text.replace(old, new, 1)

old = """    $isAdminDashboard = $isAdmin && eclipse_admin_page() === 'index';
    $isStoryEditor = eclipse_is_story_editor();
    $isCommentPage = !$isAdmin && (substr($requestPath, -12) === '/article.php' || substr($requestPath, -12) === '/comment.php');
"""
new = """    $isAdminDashboard = $isAdmin && eclipse_admin_page() === 'index';
    $isStoryEditor = eclipse_is_story_editor();
    $isStaticpagesAdmin = eclipse_is_staticpages_admin();
    $isCommentPage = !$isAdmin && (substr($requestPath, -12) === '/article.php' || substr($requestPath, -12) === '/comment.php');
"""
if old not in text:
    raise SystemExit('CSS context vars not found')
text = text.replace(old, new, 1)

old = """    if ($isStoryEditor) {
        $cssFiles[] = array('name' => 'eclipse-story-editor', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/story-editor.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 310);
    }
    if ($isCommentPage) {
"""
new = """    if ($isStoryEditor) {
        $cssFiles[] = array('name' => 'eclipse-story-editor', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/story-editor.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 310);
    }
    if ($isStaticpagesAdmin) {
        $cssFiles[] = array('name' => 'eclipse-staticpages-editor', 'file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/css/staticpages-editor.css' . $version, 'attributes' => array('media' => 'all'), 'priority' => 312);
    }
    if ($isCommentPage) {
"""
if old not in text:
    raise SystemExit('CSS insertion point not found')
text = text.replace(old, new, 1)

old = """    if (eclipse_is_admin_request()) {
        $files[] = array('file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/js/admin.js' . $version, 'footer' => true, 'priority' => 110);
    }
    return $files;
"""
new = """    if (eclipse_is_admin_request()) {
        $files[] = array('file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/js/admin.js' . $version, 'footer' => true, 'priority' => 110);
    }
    if (eclipse_is_staticpages_admin()) {
        $files[] = array('file' => $resourceRoot . '/layout/' . $_CONF['theme'] . '/js/staticpages-editor.js' . $version, 'footer' => true, 'priority' => 120);
    }
    return $files;
"""
if old not in text:
    raise SystemExit('JS insertion point not found')
text = text.replace(old, new, 1)

p.write_text(text)
