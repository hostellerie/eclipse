(function () {
    'use strict';

    var lang = window.ECLIPSE_LANG || {};
    var labels = {
        'Administration': lang.administration,
        'Navigation': lang.navigation,
        'View site': lang.view_site,
        'Theme Studio': lang.theme_studio,
        'CMS overview': lang.cms_overview,
        'Needs attention': lang.needs_attention,
        'Nothing currently requires your attention.': lang.nothing_needs_attention,
        'Quick actions': lang.quick_actions,
        'No quick action is available for this account.': lang.no_quick_action,
        '+ Write article': lang.write_article,
        '+ Create Static Page': lang.create_static_page,
        'Manage comments': lang.manage_comments,
        'Review submissions': lang.review_submissions,
        '+ Add block': lang.add_block,
        '+ Add user': lang.add_user,
        'Collapse navigation': lang.collapse_navigation,
        'Expand navigation': lang.expand_navigation,
        'Close menu': lang.close_menu,
        'Menu': lang.menu
    };

    function translated(value) {
        return Object.prototype.hasOwnProperty.call(labels, value) && labels[value] ? labels[value] : value;
    }

    function translate(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var selectors = [
            '.eclipse-admin-bar', '.eclipse-admin-sidebar-shell',
            '.eclipse-admin-overview', '.eclipse-admin-brand',
            '.eclipse-studio-launch', '.eclipse-admin-collapse-toggle'
        ];
        var nodes = [];
        selectors.forEach(function (selector) {
            Array.prototype.push.apply(nodes, scope.querySelectorAll(selector + ' *'));
            Array.prototype.push.apply(nodes, scope.querySelectorAll(selector));
        });
        nodes.forEach(function (node) {
            if (node.children.length === 0) {
                var text = (node.textContent || '').trim();
                var value = translated(text);
                if (value !== text) node.textContent = value;
            }
            ['aria-label', 'title'].forEach(function (attribute) {
                if (!node.hasAttribute || !node.hasAttribute(attribute)) return;
                var current = node.getAttribute(attribute);
                var value = translated(current);
                if (value !== current) node.setAttribute(attribute, value);
            });
        });
    }

    function start() {
        translate(document);
        if (!window.MutationObserver) return;
        var target = document.getElementById('wrapper') || document.body;
        var scheduled = false;
        var observer = new MutationObserver(function () {
            if (scheduled) return;
            scheduled = true;
            window.requestAnimationFrame(function () {
                scheduled = false;
                translate(target);
            });
        });
        observer.observe(target, { childList: true, subtree: true });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', start);
    else start();
}());
