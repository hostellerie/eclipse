(function () {
    'use strict';

    var lang = window.ECLIPSE_LANG || {};
    var labels = {
        'Nothing currently requires your attention.': lang.nothing_needs_attention,
        'No quick action is available for this account.': lang.no_quick_action,
        'Collapse navigation': lang.collapse_navigation,
        'Expand navigation': lang.expand_navigation,
        '+ Create Static Page': lang.create_static_page,
        'Review submissions': lang.review_submissions,
        'Manage comments': lang.manage_comments,
        '+ Write article': lang.write_article,
        'Administration commands': lang.administration_commands,
        'Search administration commands': lang.search_administration_commands,
        'Close command palette': lang.close_command_palette,
        'Search commands…': lang.search_commands,
        'Theme Studio': lang.theme_studio,
        'CMS overview': lang.cms_overview,
        'Needs attention': lang.needs_attention,
        'Quick actions': lang.quick_actions,
        'Administration': lang.administration,
        'Navigation': lang.navigation,
        'View site': lang.view_site,
        'Close menu': lang.close_menu,
        '+ Add block': lang.add_block,
        '+ Add user': lang.add_user,
        'Commands': lang.commands,
        'Menu': lang.menu
    };
    var sourceLabels = Object.keys(labels).sort(function (a, b) { return b.length - a.length; });

    function translated(value) {
        var result = value;
        sourceLabels.forEach(function (source) {
            if (!labels[source] || result.indexOf(source) === -1) return;
            result = result.split(source).join(labels[source]);
        });
        return result;
    }

    function translate(root) {
        var scope = root && root.querySelectorAll ? root : document;
        var selectors = [
            '.eclipse-admin-bar', '.eclipse-admin-sidebar-shell',
            '.eclipse-admin-overview', '.eclipse-admin-brand',
            '.eclipse-studio-launch', '.eclipse-admin-collapse-toggle',
            '.eclipse-command-trigger', '.eclipse-command-palette'
        ];
        var nodes = [];
        selectors.forEach(function (selector) {
            Array.prototype.push.apply(nodes, scope.querySelectorAll(selector + ' *'));
            Array.prototype.push.apply(nodes, scope.querySelectorAll(selector));
        });
        nodes.forEach(function (node) {
            if (node.children.length === 0) {
                var text = node.textContent || '';
                var value = translated(text);
                if (value !== text) node.textContent = value;
            }
            ['aria-label', 'title', 'placeholder'].forEach(function (attribute) {
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
        var target = document.body;
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
