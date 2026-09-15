<?php

if (strpos(strtolower(isset($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : ''), 'forum-seo.php') !== false) {
    die('This file can not be used on its own!');
}

/**
 * Normalize Forum text for use in a meta description.
 *
 * @param string $text
 * @param int    $limit
 * @return string
 */
function eclipse_forum_meta_clean($text, $limit = 160)
{
    $text = (string) $text;
    if ($text === '') {
        return '';
    }

    // Remove markup that may be stored in Forum descriptions or posts.
    $text = preg_replace('~\[(?:/?[a-z][a-z0-9]*)(?:=[^\]]*)?\]~i', ' ', $text);
    $text = strip_tags($text);
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\s+/u', ' ', $text);
    $text = trim($text);

    if ($text === '') {
        return '';
    }

    $length = function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
    if ($length <= $limit) {
        return $text;
    }

    $candidate = function_exists('mb_substr')
        ? mb_substr($text, 0, $limit + 1, 'UTF-8')
        : substr($text, 0, $limit + 1);

    $candidate = preg_replace('/\s+\S*$/u', '', $candidate);
    $candidate = rtrim($candidate, " \t\n\r\0\x0B,;:-");

    return $candidate !== '' ? $candidate . '…' : '';
}

/**
 * Return a positive integer request value or 0.
 *
 * @param string $name
 * @return int
 */
function eclipse_forum_meta_request_id($name)
{
    if (!isset($_REQUEST[$name])) {
        return 0;
    }

    $value = (string) $_REQUEST[$name];
    return preg_match('/^[1-9][0-9]*$/', $value) ? (int) $value : 0;
}

/**
 * Fetch a single Forum value without assuming the plugin is installed.
 *
 * @param string $tableKey
 * @param string $field
 * @param string $where
 * @return string
 */
function eclipse_forum_meta_db_value($tableKey, $field, $where)
{
    global $_TABLES;

    if (!isset($_TABLES[$tableKey]) || !function_exists('DB_getItem')) {
        return '';
    }

    return (string) DB_getItem($_TABLES[$tableKey], $field, $where);
}

/**
 * Build a useful, unique meta description for public Forum pages.
 *
 * Priority:
 * - topic: first post -> forum description -> category description -> site slogan
 * - forum: forum description -> category description -> forum name + slogan
 * - category: category description -> category name + slogan
 * - forum index: site name + slogan
 *
 * @return string
 */
function eclipse_forum_meta_description()
{
    global $_CONF;

    if (function_exists('eclipse_is_admin_request') && eclipse_is_admin_request()) {
        return '';
    }

    $requestPath = '';
    if (!empty($_SERVER['REQUEST_URI'])) {
        $requestPath = parse_url((string) $_SERVER['REQUEST_URI'], PHP_URL_PATH);
        if (!is_string($requestPath)) {
            $requestPath = '';
        }
    }
    if ($requestPath === '' && !empty($_SERVER['PHP_SELF'])) {
        $requestPath = (string) $_SERVER['PHP_SELF'];
    }

    $requestPath = str_replace('\\', '/', strtolower($requestPath));
    if (preg_match('#(?:^|/)forum(?:/|$)#', $requestPath) !== 1) {
        return '';
    }

    $siteName = !empty($_CONF['site_name']) ? eclipse_forum_meta_clean($_CONF['site_name'], 80) : '';
    $siteSlogan = !empty($_CONF['site_slogan']) ? eclipse_forum_meta_clean($_CONF['site_slogan'], 130) : '';

    $topicId = eclipse_forum_meta_request_id('showtopic');
    if ($topicId > 0) {
        $comment = eclipse_forum_meta_db_value('forum_topic', 'comment', 'id = ' . $topicId);
        $description = eclipse_forum_meta_clean($comment);
        if ($description !== '') {
            return $description;
        }

        $forumId = (int) eclipse_forum_meta_db_value('forum_topic', 'forum', 'id = ' . $topicId);
        if ($forumId > 0) {
            $forumDescription = eclipse_forum_meta_db_value('forum_forums', 'forum_dscp', 'forum_id = ' . $forumId);
            $description = eclipse_forum_meta_clean($forumDescription);
            if ($description !== '') {
                return $description;
            }

            $categoryId = (int) eclipse_forum_meta_db_value('forum_forums', 'forum_cat', 'forum_id = ' . $forumId);
            if ($categoryId > 0) {
                $categoryDescription = eclipse_forum_meta_db_value('forum_categories', 'cat_dscp', 'id = ' . $categoryId);
                $description = eclipse_forum_meta_clean($categoryDescription);
                if ($description !== '') {
                    return $description;
                }
            }
        }
    }

    $forumId = eclipse_forum_meta_request_id('forum');
    if ($forumId > 0) {
        $forumDescription = eclipse_forum_meta_db_value('forum_forums', 'forum_dscp', 'forum_id = ' . $forumId);
        $description = eclipse_forum_meta_clean($forumDescription);
        if ($description !== '') {
            return $description;
        }

        $categoryId = (int) eclipse_forum_meta_db_value('forum_forums', 'forum_cat', 'forum_id = ' . $forumId);
        if ($categoryId > 0) {
            $categoryDescription = eclipse_forum_meta_db_value('forum_categories', 'cat_dscp', 'id = ' . $categoryId);
            $description = eclipse_forum_meta_clean($categoryDescription);
            if ($description !== '') {
                return $description;
            }
        }

        $forumName = eclipse_forum_meta_clean(
            eclipse_forum_meta_db_value('forum_forums', 'forum_name', 'forum_id = ' . $forumId),
            100
        );
        $fallback = trim($forumName . ($forumName !== '' && $siteSlogan !== '' ? '. ' : '') . $siteSlogan);
        if ($fallback !== '') {
            return eclipse_forum_meta_clean($fallback);
        }
    }

    $categoryId = eclipse_forum_meta_request_id('category');
    if ($categoryId > 0) {
        $categoryDescription = eclipse_forum_meta_db_value('forum_categories', 'cat_dscp', 'id = ' . $categoryId);
        $description = eclipse_forum_meta_clean($categoryDescription);
        if ($description !== '') {
            return $description;
        }

        $categoryName = eclipse_forum_meta_clean(
            eclipse_forum_meta_db_value('forum_categories', 'cat_name', 'id = ' . $categoryId),
            100
        );
        $fallback = trim($categoryName . ($categoryName !== '' && $siteSlogan !== '' ? '. ' : '') . $siteSlogan);
        if ($fallback !== '') {
            return eclipse_forum_meta_clean($fallback);
        }
    }

    $fallback = '';
    if ($siteName !== '') {
        $fallback = 'Forum - ' . $siteName;
    }
    if ($siteSlogan !== '') {
        $fallback .= ($fallback !== '' ? ' : ' : '') . $siteSlogan;
    }

    return eclipse_forum_meta_clean($fallback);
}

/**
 * Render the Forum meta description tag when a useful description is found.
 *
 * @return string
 */
function eclipse_forum_meta_description_tag()
{
    $description = eclipse_forum_meta_description();
    if ($description === '') {
        return '';
    }

    return '<meta name="description" content="'
        . htmlspecialchars($description, ENT_QUOTES, 'UTF-8')
        . '">' . "\n";
}
