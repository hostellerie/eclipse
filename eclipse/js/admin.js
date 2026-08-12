(function () {
    'use strict';
    if (!document.body.classList.contains('admin-ui-mode-modern')) return;
    var main = document.getElementById('main-content');
    var wrapper = document.getElementById('wrapper');
    if (!main || !wrapper || document.querySelector('.eclipse-admin-bar')) return;

    var sourceMode = document.body.classList.contains('admin-navigation-source-left') ? 'left' : (document.body.classList.contains('admin-navigation-source-right') ? 'right' : 'both');
    var allCandidates = Array.prototype.slice.call(document.querySelectorAll('#leftblocks .block-left, #rightblocks .block-right, #admin-menu .block-left, #admin-menu .block-right'));
    var sources = allCandidates.filter(function (candidate) {
        if (!candidate.querySelector('.adminoption, .adminoption_off')) return false;
        if (candidate.closest('#admin-menu')) return true;
        return sourceMode === 'both' || (sourceMode === 'left' && candidate.closest('#leftblocks')) || (sourceMode === 'right' && candidate.closest('#rightblocks'));
    });
    if (!sources.length) sources = allCandidates.filter(function (candidate) { return candidate.querySelector('.adminoption, .adminoption_off'); });
    var bar = document.createElement('header');
    bar.className = 'eclipse-admin-bar';
    var identity = document.createElement('div');
    var eyebrow = document.createElement('span');
    var siteName = document.querySelector('.site_name');
    eyebrow.className = 'eclipse-admin-eyebrow'; eyebrow.textContent = (siteName && siteName.textContent.trim() ? siteName.textContent.trim() : 'Geeklog') + ' / Administration';
    var title = document.createElement('strong');
    var heading = main.querySelector('h1, h2, .block-title');
    title.textContent = heading && heading.textContent.trim() ? heading.textContent.trim() : 'Administration';
    identity.appendChild(eyebrow); identity.appendChild(title);

    var actions = document.createElement('div'); actions.className = 'eclipse-admin-bar-actions';
    var menuButton = document.createElement('button');
    menuButton.type = 'button'; menuButton.className = 'eclipse-admin-mobile-toggle';
    menuButton.setAttribute('aria-controls', 'eclipse-admin-sidebar'); menuButton.setAttribute('aria-expanded', 'false');
    menuButton.textContent = 'Navigation'; actions.appendChild(menuButton);
    if (window.geeklog && window.geeklog.site_url) {
        var viewSite = document.createElement('a');
        viewSite.className = 'eclipse-admin-view-site'; viewSite.href = window.geeklog.site_url; viewSite.textContent = 'View site';
        actions.appendChild(viewSite);
    }
    bar.appendChild(identity); bar.appendChild(actions); wrapper.insertBefore(bar, wrapper.firstChild);

    var sidebar = document.createElement('aside');
    sidebar.id = 'eclipse-admin-sidebar'; sidebar.className = 'eclipse-admin-sidebar-shell';
    var brand = document.createElement('a');
    brand.className = 'eclipse-admin-brand'; brand.href = window.geeklog && window.geeklog.site_url ? window.geeklog.site_url + '/admin/' : '#';
    brand.innerHTML = '<span aria-hidden="true">E</span><b>Eclipse</b><small>Administration</small>'; sidebar.appendChild(brand);
    var navigation = document.createElement('nav'); navigation.setAttribute('aria-label', 'Administration');
    var known = {}; var sectionIndex = 0;
    function blockIcon(label, links) {
        var labelProbe = label.toLowerCase(); var probe = (label + ' ' + links.map(function (link) { return (link.textContent || '') + ' ' + (link.getAttribute('href') || ''); }).join(' ')).toLowerCase();
        var categoryProbe = /user|users|plugin|extension|tool|tools|config|setting|content|core|studio/.test(labelProbe) ? labelProbe : probe;
        var path = '<rect x="4" y="4" width="6" height="6" rx="1"/><rect x="14" y="4" width="6" height="6" rx="1"/><rect x="4" y="14" width="6" height="6" rx="1"/><rect x="14" y="14" width="6" height="6" rx="1"/>';
        if (/tool|log|backup|clear|security|outil|cache/.test(categoryProbe)) path = '<path d="M14 6a4 4 0 0 0-5 5L3 17l4 4 6-6a4 4 0 0 0 5-5l-3 2-3-3Z"/>';
        else if (/user|account|member|group|utilisateur|compte|groupe/.test(categoryProbe)) path = '<circle cx="12" cy="8" r="3"/><path d="M5 20c.5-4 3-6 7-6s6.5 2 7 6"/>';
        else if (/plugin|extension|module/.test(categoryProbe)) path = '<path d="M9 4h3a2 2 0 1 1 4 0h4v4a2 2 0 1 0 0 4v8h-8a2 2 0 1 0-4 0H4v-4a2 2 0 1 0 0-4V8h5a2 2 0 1 1 0-4Z"/>';
        else if (/config|setting|site|block|param|reglage|studio/.test(categoryProbe)) path = '<circle cx="12" cy="12" r="3"/><path d="M19 13.5v-3l-2-.7-.7-1.7.9-1.9-2.1-2.1-1.9.9-1.7-.7-.7-2h-3l-.7 2-1.7.7-1.9-.9-2.1 2.1.9 1.9-.7 1.7-2 .7v3l2 .7.7 1.7-.9 1.9 2.1 2.1 1.9-.9 1.7.7.7 2h3l.7-2 1.7-.7 1.9.9 2.1-2.1-.9-1.9.7-1.7Z"/>';
        else if (/story|article|topic|comment|content|static|page|contenu|core/.test(categoryProbe)) path = '<path d="M6 3h9l4 4v14H6Z"/><path d="M15 3v5h4M9 12h7M9 16h7"/>';
        return '<svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' + path + '</svg>';
    }
    function addSection(sectionLabel, rawLinks) {
        var links = rawLinks.filter(function (link) {
            var href = link.getAttribute('href') || ''; var text = link.textContent.trim();
            if (!href || !text || (href.charAt(0) === '#' && href !== '#eclipse-theme-studio') || known[href + '|' + text]) return false;
            known[href + '|' + text] = true; return true;
        });
        if (!links.length) return;
        var section = document.createElement('section');
        var sectionButton = document.createElement('button'); sectionButton.type = 'button'; sectionButton.className = 'eclipse-admin-section-toggle';
        var sectionIcon = document.createElement('span'); sectionIcon.className = 'eclipse-admin-section-icon'; sectionIcon.setAttribute('aria-hidden', 'true'); sectionIcon.innerHTML = blockIcon(sectionLabel, links);
        var sectionText = document.createElement('b'); sectionText.textContent = sectionLabel; sectionButton.appendChild(sectionIcon); sectionButton.appendChild(sectionText);
        var list = document.createElement('ul'); var listId = 'eclipse-admin-section-' + sectionIndex++;
        list.id = listId; sectionButton.setAttribute('aria-controls', listId);
        links.forEach(function (original) {
            var item = document.createElement('li'); var link = document.createElement('a');
            link.href = original.href; link.textContent = /^studio$/i.test(sectionLabel) && /#eclipse-theme-studio$/.test(original.href) ? 'Theme Studio' : original.textContent.trim();
            if (original.parentNode && /adminoption_off|sideoption_off/.test(original.parentNode.className || '')) link.setAttribute('aria-current', 'page');
            item.appendChild(link); list.appendChild(item);
        });
        sectionButton.setAttribute('aria-expanded', 'false');
        sectionButton.addEventListener('click', function () {
            if (window.matchMedia('(min-width:52.01rem)').matches) { sectionButton.blur(); return; }
            section.classList.remove('is-dismissing');
            var expanded = sectionButton.getAttribute('aria-expanded') === 'true';
            sectionButton.setAttribute('aria-expanded', expanded ? 'false' : 'true'); section.classList.toggle('is-open', !expanded);
        });
        section.addEventListener('focusout', function (event) {
            if (event.relatedTarget && section.contains(event.relatedTarget)) return;
            section.classList.remove('is-open'); sectionButton.setAttribute('aria-expanded', 'false');
        });
        list.addEventListener('click', function () { section.classList.remove('is-open'); section.classList.add('is-dismissing'); sectionButton.setAttribute('aria-expanded', 'false'); });
        section.addEventListener('mouseleave', function () { section.classList.remove('is-dismissing'); });
        section.appendChild(sectionButton); section.appendChild(list); navigation.appendChild(section);
    }
    function addSource(source) {
        var sourceTitle = source.querySelector('.block-title, h2'); var defaultLabel = sourceTitle ? sourceTitle.textContent.trim() : 'Administration';
        var groupedLists = []; var groupHeadings = Array.prototype.slice.call(source.querySelectorAll('.sideoption_group_label'));
        groupHeadings.forEach(function (heading) {
            var list = heading.nextElementSibling;
            while (list && list.tagName !== 'UL') list = list.nextElementSibling;
            if (!list) return; groupedLists.push(list); addSection(heading.textContent.trim() || defaultLabel, Array.prototype.slice.call(list.querySelectorAll('.adminoption a[href]')));
        });
        var baseLinks = Array.prototype.slice.call(source.querySelectorAll('.adminoption a[href]')).filter(function (link) {
            return !groupedLists.some(function (list) { return list.contains(link); });
        });
        addSection(defaultLabel, baseLinks);
    }
    var dashboard = document.querySelector('.eclipse-command-page'); var nativeSource = document.getElementById('eclipse-admin-native-source'); var dashboardGroups = [];
    var groupSource = dashboard || nativeSource;
    if (groupSource) Array.prototype.slice.call(groupSource.querySelectorAll('h2, h3')).forEach(function (child) {
        if (!/^H[23]$/.test(child.tagName)) return;
        var list = child.nextElementSibling;
        if (!list || !list.classList.contains('admin-commandcontrol')) return;
        var links = Array.prototype.slice.call(list.querySelectorAll('a[href]'));
        if (!links.length) return; dashboardGroups.push({ label: child.textContent.trim() || 'Administration', links: links });
    });
    if (dashboardGroups.length) {
        dashboardGroups.forEach(function (group) { addSection(group.label, group.links); });
        var studioLink = dashboard ? dashboard.querySelector('.eclipse-studio-launch[href]') : null;
        if (!studioLink && nativeSource) studioLink = nativeSource.querySelector('.eclipse-native-studio-link[href]');
        if (studioLink) addSection('Studio', [studioLink]);
    } else if (nativeSource) {
        addSource(nativeSource);
        var nativeStudioLink = nativeSource.querySelector('.eclipse-native-studio-link[href]'); if (nativeStudioLink) addSection('Studio', [nativeStudioLink]);
    } else sources.forEach(addSource);
    if (dashboard && dashboardGroups.length) {
        var dashboardHost = dashboard.closest ? dashboard.closest('.block-center') : null;
        if (dashboardHost) dashboardHost.classList.add('eclipse-dashboard-host');
        var overview = document.createElement('section'); overview.className = 'eclipse-admin-overview';
        var overviewTitle = document.createElement('h2'); overviewTitle.textContent = 'CMS overview'; overview.appendChild(overviewTitle);
        var overviewGrid = document.createElement('div'); overviewGrid.className = 'eclipse-admin-overview-grid';
        var dashboardLinks = []; dashboardGroups.forEach(function (group) { dashboardLinks = dashboardLinks.concat(group.links); });
        function overviewCard(title, entries, emptyText, modifier) {
            var card = document.createElement('article'); card.className = 'eclipse-overview-card' + (modifier ? ' eclipse-overview-' + modifier : ''); var heading = document.createElement('h3'); heading.textContent = title; card.appendChild(heading);
            if (!entries.length) { var empty = document.createElement('p'); empty.textContent = emptyText; card.appendChild(empty); return card; }
            var list = document.createElement('ul'); entries.forEach(function (entry) { var item = document.createElement('li'); var link = document.createElement('a'); link.href = entry.href; link.textContent = entry.label; item.appendChild(link); list.appendChild(item); }); card.appendChild(list); return card;
        }
        var attention = dashboardLinks.map(function (link) { var text = link.textContent.trim(); var match = text.match(/\(([0-9]+)(?:\/([0-9]+))?\)/); var count = match ? parseInt(match[1],10) : 0; if (match && typeof match[2] !== 'undefined' && /comment/i.test(text + ' ' + link.href)) count = parseInt(match[2],10); return { href:link.href,label:text,count:count }; }).filter(function (entry) { return entry.count > 0 && /submi|moder|pending|await|comment/i.test(entry.label + ' ' + entry.href); });
        overviewGrid.appendChild(overviewCard('Needs attention', attention, 'Nothing currently requires your attention.', 'attention'));
        var actionRules = [
            { test:/\/admin\/(?:article|story)\.php/i, label:'+ Write article' }, { test:/\/admin\/plugins\/staticpages\/index\.php/i, label:'+ Create Static Page' },
            { test:/\/admin\/comment\.php/i, label:'Manage comments', keep:true }, { test:/\/admin\/moderation\.php/i, label:'Review submissions', keep:true },
            { test:/\/admin\/block\.php/i, label:'+ Add block' }, { test:/\/admin\/user\.php/i, label:'+ Add user' }
        ];
        var quick = []; actionRules.forEach(function (rule) { var original = dashboardLinks.find(function (link) { return rule.test.test(link.href); }); if (!original) return; var href = original.href; if (!rule.keep) { try { var url = new URL(href, window.location.href); url.searchParams.set('mode','edit'); href = url.href; } catch (ignore) {} } quick.push({href:href,label:rule.label}); });
        overviewGrid.appendChild(overviewCard('Quick actions', quick, 'No quick action is available for this account.', 'actions'));
        overview.appendChild(overviewGrid); dashboard.insertBefore(overview, dashboard.firstChild);
    }
    if (!sectionIndex) { bar.remove(); return; }
    sidebar.appendChild(navigation);
    var collapseButton = document.createElement('button'); collapseButton.type = 'button'; collapseButton.className = 'eclipse-admin-collapse-toggle';
    collapseButton.setAttribute('aria-controls', 'eclipse-admin-sidebar'); collapseButton.setAttribute('aria-label', 'Collapse navigation'); collapseButton.title = 'Collapse navigation'; collapseButton.innerHTML = '<span aria-hidden="true">&#x2039;</span>';
    sidebar.appendChild(collapseButton); wrapper.insertBefore(sidebar, main.parentNode);
    function updateSidebarFit() {
        sidebar.classList.remove('is-tall');
        if (sidebar.scrollHeight > Math.max(320, window.innerHeight - 58)) sidebar.classList.add('is-tall');
    }
    var storageKey = 'eclipse-admin-sidebar-collapsed'; var desktop = window.matchMedia('(min-width:52.01rem)');
    var storedCollapsed = false;
    try { storedCollapsed = window.localStorage.getItem(storageKey) === '1'; } catch (ignore) {}
    if (storedCollapsed && desktop.matches) document.body.classList.add('eclipse-admin-sidebar-collapsed');
    function updateNavigationButtons() {
        var collapsed = document.body.classList.contains('eclipse-admin-sidebar-collapsed');
        var mobileOpen = document.body.classList.contains('eclipse-admin-sidebar-open');
        collapseButton.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
        collapseButton.querySelector('span').innerHTML = collapsed ? '&#x203A;' : '&#x2039;';
        collapseButton.setAttribute('aria-label', collapsed ? 'Expand navigation' : 'Collapse navigation'); collapseButton.title = collapsed ? 'Expand navigation' : 'Collapse navigation';
        menuButton.setAttribute('aria-expanded', mobileOpen ? 'true' : 'false'); menuButton.textContent = mobileOpen ? 'Close menu' : 'Menu';
    }
    document.body.classList.add('eclipse-admin-enhanced');
    menuButton.addEventListener('click', function () {
        document.body.classList.toggle('eclipse-admin-sidebar-open'); updateNavigationButtons();
    });
    collapseButton.addEventListener('click', function () {
        var collapsed = document.body.classList.toggle('eclipse-admin-sidebar-collapsed');
        try { window.localStorage.setItem(storageKey, collapsed ? '1' : '0'); } catch (ignore) {}
        updateNavigationButtons();
    });
    if (desktop.addEventListener) desktop.addEventListener('change', updateNavigationButtons); else if (desktop.addListener) desktop.addListener(updateNavigationButtons);
    window.addEventListener('resize', updateSidebarFit); updateNavigationButtons(); updateSidebarFit();
}());
