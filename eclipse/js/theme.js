(function () {
    'use strict';
    var translations = {
        en: {
            deleteConfirm: 'Delete this?', sendConfirm: 'Send this?', articleDetails: 'Article details', articleTitle: 'Article title', articleContent: 'Article content',
            identity: 'Title and classification', publish: 'Publishing', seo: 'Search and identifiers', seoOverview: 'SEO overview', more: 'Additional options', access: 'Access and permissions',
            draftFound: 'A newer local draft is available in this browser.', restoreDraft: 'Restore draft', discardDraft: 'Discard', autosaved: 'Local draft saved', focusMode: 'Focus mode', exitFocus: 'Exit focus mode', shareArticle: 'Share this article', shareOn: 'Share on'
        },
        fr: {
            deleteConfirm: 'Supprimer cet élément ?', sendConfirm: 'Envoyer cet élément ?', articleDetails: 'Détails de l’article', articleTitle: 'Titre de l’article', articleContent: 'Contenu de l’article',
            identity: 'Titre et classement', publish: 'Publication', seo: 'Référencement et identifiants', seoOverview: 'Vue d’ensemble SEO', more: 'Options complémentaires', access: 'Accès et permissions',
            draftFound: 'Un brouillon local plus recent est disponible dans ce navigateur.', restoreDraft: 'Restaurer', discardDraft: 'Ignorer', autosaved: 'Brouillon local enregistre', focusMode: 'Mode redaction', exitFocus: 'Quitter le mode redaction', shareArticle: 'Partager cet article', shareOn: 'Partager sur'
        }
    };
    function interfaceLanguage(context) {
        if (window.geeklog && geeklog.lang && /^fr\b/i.test(geeklog.lang.iso639Code || '')) return 'fr';
        if (window.geeklog && geeklog.lang && /^en\b/i.test(geeklog.lang.iso639Code || '')) return 'en';
        var text = context ? context.textContent || '' : '';
        if (/\b(titre|auteur|contenu|rubrique|enregistrer|aperçu)\b/i.test(text)) return 'fr';
        if (/\b(title|author|content|topic|save|preview)\b/i.test(text)) return 'en';
        return /^fr\b/i.test(document.documentElement.lang || '') ? 'fr' : 'en';
    }
    function strings(context) { return translations[interfaceLanguage(context)]; }
    if (/(?:^|\/)admin(?:\/|$)/.test(window.location.pathname)) {
        document.body.classList.add('eclipse-admin-page');
    }
    if (/(?:^|\/)admin\/configuration\.php$/.test(window.location.pathname)) {
        document.body.classList.add('eclipse-configuration-page');
    }
    var toggle = document.querySelector('.menu-toggle');
    var menu = document.getElementById('navigation_ul') || document.getElementById('eclipse-menu-panel');
    if (menu && menu.id === 'eclipse-menu-panel') {
        menu.querySelectorAll('li').forEach(function (item) {
            var submenu = Array.prototype.find.call(item.children, function (child) { return child.tagName === 'UL'; });
            var link = Array.prototype.find.call(item.children, function (child) { return child.tagName === 'A'; });
            if (!submenu || !link) return;
            item.classList.add('eclipse-has-submenu');
            link.setAttribute('aria-haspopup', 'true');
            link.setAttribute('aria-expanded', 'false');
            link.addEventListener('click', function (event) {
                if (!window.matchMedia('(max-width: 47.99rem)').matches) return;
                event.preventDefault();
                event.stopPropagation();
                var open = item.classList.toggle('is-submenu-open');
                link.setAttribute('aria-expanded', String(open));
            });
        });
    }
    if (toggle && menu) {
        toggle.addEventListener('click', function () {
            var open = toggle.getAttribute('aria-expanded') === 'true';
            toggle.setAttribute('aria-expanded', String(!open));
            menu.classList.toggle('is-open', !open);
        });
        menu.addEventListener('click', function (event) {
            var clickedLink = event.target.closest('a');
            var clickedItem = clickedLink && clickedLink.parentElement;
            if (clickedLink && (!clickedItem || !clickedItem.classList.contains('eclipse-has-submenu')) && window.matchMedia('(max-width: 47.99rem)').matches) {
                toggle.setAttribute('aria-expanded', 'false');
                menu.classList.remove('is-open');
            }
        });
    }
    if (typeof window.delconfirm !== 'function') window.delconfirm = function () { return window.confirm(strings(document.body).deleteConfirm); };
    if (typeof window.postconfirm !== 'function') window.postconfirm = function () { return window.confirm(strings(document.body).sendConfirm); };
    function setupAccessibility() {
        document.querySelectorAll('.story_editor .navi img[onclick]').forEach(function (control) {
            if (!control.hasAttribute('tabindex')) control.tabIndex = 0;
            control.setAttribute('role','button');
            if (!control.getAttribute('aria-label')) control.setAttribute('aria-label',control.getAttribute('title') || control.getAttribute('alt') || 'Resize editor');
            control.addEventListener('keydown',function(event){if(event.key==='Enter'||event.key===' '){event.preventDefault();control.click();}});
        });
        var currentPath=(window.location.pathname.replace(/\/+$/,'')||'/');
        document.querySelectorAll('#navigation_ul a[href],#eclipse-menu-panel a[href]').forEach(function(link){
            var rawHref=(link.getAttribute('href')||'').trim();
            if (!rawHref || rawHref==='#' || /^javascript:/i.test(rawHref)) return;
            try { var target=new URL(link.href,window.location.href);var targetPath=(target.pathname.replace(/\/+$/,'')||'/');if(target.origin===window.location.origin&&targetPath===currentPath)link.setAttribute('aria-current','page'); } catch(error) {}
        });
    }
    function setupArticleSharing() {
        if (!/(?:^|\/)article\.php(?:\/|$)/.test(window.location.pathname) || document.querySelector('.eclipse-share-links')) return;
        var networks=[];
        if(document.body.classList.contains('share-facebook-on'))networks.push({name:'Facebook',url:'https://www.facebook.com/sharer/sharer.php?u='});
        if(document.body.classList.contains('share-linkedin-on'))networks.push({name:'LinkedIn',url:'https://www.linkedin.com/sharing/share-offsite/?url='});
        if(document.body.classList.contains('share-x-on'))networks.push({name:'X',url:'https://twitter.com/intent/tweet?url='});
        if(!networks.length)return;
        var article=document.querySelector('.storytext');if(!article)return;
        var labels=strings(article);var canonical=document.querySelector('link[rel="canonical"]');var articleUrl=canonical?canonical.href:window.location.href.split('#')[0];
        var nav=document.createElement('nav');nav.className='eclipse-share-links';nav.setAttribute('aria-label',labels.shareArticle);
        var heading=document.createElement('span');heading.className='eclipse-share-label';heading.textContent=labels.shareArticle;nav.appendChild(heading);
        networks.forEach(function(network){var link=document.createElement('a');link.href=network.url+encodeURIComponent(articleUrl);link.target='_blank';link.rel='noopener noreferrer';link.className='eclipse-share-'+network.name.toLowerCase();link.setAttribute('aria-label',labels.shareOn+' '+network.name);link.textContent=network.name;nav.appendChild(link);});
        var footer=article.querySelector('.story-footer');if(footer)article.insertBefore(nav,footer);else article.appendChild(nav);
    }
    document.querySelectorAll('input[type="submit"],button,input[type="image"][name="delbutton"]').forEach(function (control) {
        var action = ((control.value || '') + ' ' + (control.textContent || '') + ' ' + (control.name || '')).toLowerCase();
        if (/(delete|remove|supprimer|effacer|delbutton)/.test(action)) control.classList.add('eclipse-danger-action');
    });

    function setupStudioTabs() {
        var tabs = Array.prototype.slice.call(document.querySelectorAll('.eclipse-studio-tabs [role="tab"]'));
        if (!tabs.length) return;
        var backupBrowser = document.getElementById('eclipse-backup-browser');
        var updatesPanel = document.getElementById('eclipse-panel-updates');
        var studioActions = document.querySelector('.eclipse-customizer .eclipse-actions');
        if (backupBrowser && updatesPanel) updatesPanel.appendChild(backupBrowser);
        function activate(tab, focus) {
            tabs.forEach(function (item) {
                var selected = item === tab;
                item.setAttribute('aria-selected', selected ? 'true' : 'false');
                item.tabIndex = selected ? 0 : -1;
                var panel = document.getElementById(item.getAttribute('aria-controls'));
                if (panel) panel.hidden = !selected;
            });
            if (studioActions) {
                var showActions = ['eclipse-panel-design','eclipse-panel-preview'].indexOf(tab.getAttribute('aria-controls')) !== -1;
                studioActions.hidden = !showActions;
                studioActions.style.display = showActions ? '' : 'none';
            }
            if (focus) tab.focus();
        }
        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () { activate(tab, false); });
            tab.addEventListener('keydown', function (event) {
                var next = event.key === 'ArrowRight' ? index + 1 : event.key === 'ArrowLeft' ? index - 1 : event.key === 'Home' ? 0 : event.key === 'End' ? tabs.length - 1 : -1;
                if (next < 0 && (event.key === 'ArrowLeft' || event.key === 'ArrowRight')) next = event.key === 'ArrowLeft' ? tabs.length - 1 : 0;
                if (next >= tabs.length) next = 0;
                if (next >= 0) { event.preventDefault(); activate(tabs[next], true); }
            });
        });
        var preview = document.querySelector('.eclipse-isolated-preview');
        document.querySelectorAll('[data-preview-width]').forEach(function (button) {
            button.addEventListener('click', function () {
                if (preview) preview.setAttribute('data-width', button.getAttribute('data-preview-width'));
                document.querySelectorAll('[data-preview-width]').forEach(function (item) { item.setAttribute('aria-pressed', item === button ? 'true' : 'false'); });
            });
        });
    }

    function setupPalettePreview() {
        var selector = document.getElementById('eclipse-palette-preset');
        var form = selector ? selector.closest('form') : null;
        if (!selector || !form) return;
        var palettes = {
            default: ['#3157d5', '#6750a4', '#2448bd', '#f4f6fb', '#ffffff', '#202431'],
            ocean: ['#087e8b', '#155eaa', '#075985', '#eef8fa', '#ffffff', '#16313a'],
            forest: ['#287a52', '#7a8f3a', '#17633f', '#f2f7f1', '#ffffff', '#203229'],
            sunset: ['#6d3f8c', '#e07a5f', '#58316f', '#fff5ee', '#ffffff', '#382522'],
            graphite: ['#3f4b5b', '#687386', '#334155', '#f1f3f5', '#ffffff', '#1f2933']
        };
        selector.querySelectorAll('option[data-colors]').forEach(function (option) {
            var colors = (option.getAttribute('data-colors') || '').split(',');
            if (colors.length === 6) palettes[option.value] = colors;
        });
        var names = ['color_primary', 'color_secondary', 'color_link', 'color_background', 'color_surface', 'color_text'];
        var variables = ['--eclipse-primary', '--eclipse-secondary', '--eclipse-link', '--eclipse-bg', '--eclipse-surface', '--eclipse-text'];
        var inputs = names.map(function (name) { return form.querySelector('[name="eclipse[' + name + ']"]'); });
        var initialColors = inputs.map(function (input) { return input ? input.value.toLowerCase() : ''; });
        var status = document.getElementById('eclipse-draft-status');
        var cancel = form.querySelector('.eclipse-cancel-preview');
        var contrastReport = document.getElementById('eclipse-contrast-report');
        var dirty = false;
        function currentColors() { return inputs.map(function (input) { return input ? input.value.toLowerCase() : ''; }); }
        function identifyPalette() {
            var current = currentColors().join('|');
            var match = Object.keys(palettes).find(function (key) { return palettes[key].join('|') === current; });
            selector.value = match || 'custom';
        }
        function channel(value) { value /= 255; return value <= 0.03928 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4); }
        function luminance(hex) { return 0.2126 * channel(parseInt(hex.substr(1, 2), 16)) + 0.7152 * channel(parseInt(hex.substr(3, 2), 16)) + 0.0722 * channel(parseInt(hex.substr(5, 2), 16)); }
        function contrast(first, second) { var a = luminance(first); var b = luminance(second); return (Math.max(a, b) + 0.05) / (Math.min(a, b) + 0.05); }
        function contrastBadge(label, first, second) {
            var ratio = contrast(first, second);
            var level = ratio >= 7 ? 'AAA' : ratio >= 4.5 ? 'AA' : 'Fail';
            return '<span class="eclipse-contrast-badge eclipse-contrast-' + level.toLowerCase() + '"><b>' + label + '</b><em>' + ratio.toFixed(2) + ':1 · ' + level + '</em></span>';
        }
        function updateContrast(colors) {
            if (!contrastReport) return;
            contrastReport.innerHTML = contrastBadge('Text / cards', colors[5], colors[4]) + contrastBadge('Links / cards', colors[2], colors[4]) + contrastBadge('White / buttons', '#ffffff', colors[0]);
        }
        function previewColors(colors) {
            colors.forEach(function (color, index) { document.documentElement.style.setProperty(variables[index], color); });
            var frame = document.getElementById('eclipse-preview-frame');
            if (frame && frame.contentDocument) {
                var previewVariables = ['--p', '--s', '--l', '--bg', '--surface', '--text'];
                colors.forEach(function (color, index) { frame.contentDocument.documentElement.style.setProperty(previewVariables[index], color); });
            }
            updateContrast(colors);
        }
        function setDirty(value) {
            dirty = value;
            if (status) { status.textContent = value ? 'Unsaved preview changes' : 'No unsaved changes'; status.classList.toggle('is-dirty', value); }
        }
        selector.addEventListener('change', function () {
            if (!palettes[selector.value]) return;
            palettes[selector.value].forEach(function (color, index) { if (inputs[index]) inputs[index].value = color; });
            var selectedOption = selector.options[selector.selectedIndex];
            var paletteName = form.querySelector('[name="eclipse_palette_name"]');
            if (paletteName) paletteName.value = selectedOption.getAttribute('data-palette-name') || '';
            previewColors(palettes[selector.value]);
            setDirty(true);
        });
        inputs.forEach(function (input) {
            if (input) input.addEventListener('input', function () { previewColors(currentColors()); identifyPalette(); setDirty(true); });
        });
        form.querySelectorAll('input:not([type="hidden"]),select').forEach(function (control) {
            if (control !== selector && inputs.indexOf(control) === -1) control.addEventListener('change', function () { setDirty(true); });
        });
        var footerEditor = form.querySelector('.eclipse-footer-editor');
        var initialFooterHtml = footerEditor ? footerEditor.innerHTML : '';
        function footerTemplate(id) { var node = document.getElementById(id); return node ? node.innerHTML : ''; }
        if (footerEditor) {
            var footerSequence = Date.now();
            footerEditor.addEventListener('click', function (event) {
                var button = event.target.closest('button');
                if (!button || !footerEditor.contains(button)) return;
                var groups = footerEditor.querySelector('.eclipse-footer-groups');
                if (button.classList.contains('eclipse-footer-add-group')) {
                    if (!groups || groups.querySelectorAll('.eclipse-footer-group').length >= 8) return;
                    footerSequence += 1;
                    var groupHtml = footerTemplate('eclipse-footer-group-template').replace(/__G__/g, footerSequence).replace(/__L__/g, footerSequence);
                    groups.insertAdjacentHTML('beforeend', groupHtml); setDirty(true);
                } else if (button.classList.contains('eclipse-footer-add-link')) {
                    var group = button.closest('.eclipse-footer-group'); var list = group && group.querySelector('.eclipse-footer-group-links');
                    if (!group || !list || list.querySelectorAll('.eclipse-footer-link-row').length >= 12) return;
                    footerSequence += 1;
                    var linkHtml = footerTemplate('eclipse-footer-link-template').replace(/__G__/g, group.getAttribute('data-group-index')).replace(/__L__/g, footerSequence);
                    list.insertAdjacentHTML('beforeend', linkHtml); setDirty(true);
                } else if (button.classList.contains('eclipse-footer-remove-link')) {
                    var row = button.closest('.eclipse-footer-link-row'); if (row) { row.remove(); setDirty(true); }
                } else if (button.classList.contains('eclipse-footer-remove-group')) {
                    var removedGroup = button.closest('.eclipse-footer-group'); if (removedGroup) { removedGroup.remove(); setDirty(true); }
                }
            });
            footerEditor.addEventListener('input', function () { setDirty(true); });
            footerEditor.addEventListener('change', function () { setDirty(true); });
        }
        var defaults = {
            palette: { color_primary:'#3157d5',color_secondary:'#6750a4',color_link:'#2448bd',color_background:'#f4f6fb',color_surface:'#ffffff',color_text:'#202431' },
            layout: { site_max_width:'1200px',reading_width:'72ch',font_size:'16px',font_family:'humanist',spacing:'normal',radius:'medium' },
            appearance: { color_scheme:'light',admin_ui_mode:'modern',admin_navigation_source:'both',menu_style:'floating',block_style:'card',button_style:'solid',header_style:'gradient',footer_style:'dark',sidebar_position:'right' },
            brand: { logo:'images/logo-mark.svg',header_image:'',show_left_sidebar:false,show_right_sidebar:true,mobile_menu:true,editor_hide_sidebars:true },
            social: { share_facebook:false,share_linkedin:false,share_x:false },
            integrations: { adsense_enabled:false,adsense_client:'',topic_h1_enabled:false,html_lang:'auto',sitemap_path:'' }
        };
        form.querySelectorAll('[data-reset-section]').forEach(function (button) {
            button.addEventListener('click', function () {
                var values = defaults[button.getAttribute('data-reset-section')] || {};
                Object.keys(values).forEach(function (name) {
                    var control = form.querySelector('[name="eclipse[' + name + ']"]');
                    if (!control) return;
                    if (control.type === 'checkbox') control.checked = values[name]; else control.value = values[name];
                });
                previewColors(currentColors()); identifyPalette(); setDirty(true);
            });
        });
        function collectSettings() {
            var settings = {};
            form.querySelectorAll('[name^="eclipse["]').forEach(function (control) {
                var match = control.name.match(/^eclipse\[([^\]]+)\]$/); if (!match) return;
                settings[match[1]] = control.type === 'checkbox' ? control.checked : control.value;
            });
            return settings;
        }
        function collectFooter() {
            var footer = { groups: [], copyright: '', legal_notice: '' };
            if (!footerEditor) return footer;
            footerEditor.querySelectorAll('.eclipse-footer-group').forEach(function (group) {
                var links = [];
                group.querySelectorAll('.eclipse-footer-link-row').forEach(function (row) {
                    function field(suffix) { return row.querySelector('[name$="[' + suffix + ']"]'); }
                    links.push({
                        label: field('label') ? field('label').value : '', url: field('url') ? field('url').value : '',
                        enabled: !!(field('enabled') && field('enabled').checked), emphasis: !!(field('emphasis') && field('emphasis').checked),
                        new_window: !!(field('new_window') && field('new_window').checked), nofollow: !!(field('nofollow') && field('nofollow').checked)
                    });
                });
                footer.groups.push({ links: links });
            });
            var copyright = footerEditor.querySelector('[name="eclipse_footer[copyright]"]');
            var legal = footerEditor.querySelector('[name="eclipse_footer[legal_notice]"]');
            footer.copyright = copyright ? copyright.value : ''; footer.legal_notice = legal ? legal.value : '';
            return footer;
        }
        function collectPalettes() {
            var result = {};
            selector.querySelectorAll('option[data-palette-name][data-colors]').forEach(function (option) {
                var colors = option.getAttribute('data-colors').split(',');
                if (colors.length !== 6) return;
                result[option.getAttribute('data-palette-name')] = {
                    color_primary:colors[0],color_secondary:colors[1],color_link:colors[2],
                    color_background:colors[3],color_surface:colors[4],color_text:colors[5]
                };
            });
            return result;
        }
        function applyFooter(footer) {
            if (!footerEditor || !footer || typeof footer !== 'object' || !Array.isArray(footer.groups)) return false;
            var groupsNode = footerEditor.querySelector('.eclipse-footer-groups'); if (!groupsNode) return false;
            groupsNode.innerHTML = '';
            footer.groups.slice(0, 8).forEach(function (groupData, groupIndex) {
                var groupId = 'import-' + groupIndex;
                var groupHtml = footerTemplate('eclipse-footer-group-template').replace(/__G__/g, groupId).replace(/__L__/g, groupId + '-0');
                groupsNode.insertAdjacentHTML('beforeend', groupHtml);
                var groupNode = groupsNode.lastElementChild; var list = groupNode.querySelector('.eclipse-footer-group-links'); list.innerHTML = '';
                var links = groupData && Array.isArray(groupData.links) ? groupData.links.slice(0, 12) : [];
                links.forEach(function (linkData, linkIndex) {
                    list.insertAdjacentHTML('beforeend', footerTemplate('eclipse-footer-link-template').replace(/__G__/g, groupId).replace(/__L__/g, groupId + '-' + linkIndex));
                    var row = list.lastElementChild;
                    Object.keys(linkData || {}).forEach(function (key) {
                        var control = row.querySelector('[name$="[' + key + ']"]'); if (!control) return;
                        if (control.type === 'checkbox') control.checked = !!linkData[key]; else control.value = String(linkData[key]);
                    });
                });
            });
            var copyright = footerEditor.querySelector('[name="eclipse_footer[copyright]"]');
            var legal = footerEditor.querySelector('[name="eclipse_footer[legal_notice]"]');
            if (copyright) copyright.value = typeof footer.copyright === 'string' ? footer.copyright : '';
            if (legal) legal.value = typeof footer.legal_notice === 'string' ? footer.legal_notice : '';
            return true;
        }
        function applyPalettes(imported) {
            if (!imported || typeof imported !== 'object' || Array.isArray(imported)) return false;
            var hidden = document.getElementById('eclipse-portable-palettes');
            if (hidden) hidden.value = JSON.stringify(imported);
            selector.querySelectorAll('option[data-imported="true"]').forEach(function (option) { option.remove(); });
            Object.keys(imported).slice(0, 50).forEach(function (name) {
                var palette = imported[name] || {}; var colors = names.map(function (key) { return palette[key] || ''; });
                if (!colors.every(function (color) { return /^#[0-9a-fA-F]{6}$/.test(color); })) return;
                var option = document.createElement('option'); option.value = 'imported-' + name; option.textContent = name;
                option.setAttribute('data-imported', 'true'); option.setAttribute('data-palette-name', name); option.setAttribute('data-colors', colors.join(','));
                selector.appendChild(option); palettes[option.value] = colors.map(function (color) { return color.toLowerCase(); });
            });
            return true;
        }
        function applySettings(settings) {
            if (!settings || typeof settings !== 'object' || Array.isArray(settings)) return false;
            var changed = false;
            Object.keys(settings).forEach(function (name) {
                var control = form.querySelector('[name="eclipse[' + name + ']"]');
                if (!control || (typeof settings[name] !== 'string' && typeof settings[name] !== 'boolean')) return;
                if (control.type === 'checkbox') { if (typeof settings[name] !== 'boolean') return; control.checked = settings[name]; } else control.value = settings[name];
                changed = true;
            });
            if (changed) { previewColors(currentColors()); identifyPalette(); setDirty(true); }
            return changed;
        }
        var exportButton = document.getElementById('eclipse-settings-export');
        if (exportButton) exportButton.addEventListener('click', function () {
            var portable = { schema:'eclipse-studio-state', schema_version:1, eclipse_version:form.getAttribute('data-eclipse-version') || '', exported_at:new Date().toISOString(), settings:collectSettings(), footer:collectFooter(), palettes:collectPalettes() };
            var blob = new Blob([JSON.stringify(portable, null, 2) + '\n'], { type:'application/json' });
            var url = URL.createObjectURL(blob); var link = document.createElement('a');
            link.href = url; link.download = 'eclipse-studio-state.json'; document.body.appendChild(link); link.click(); link.remove(); window.setTimeout(function () { URL.revokeObjectURL(url); }, 1000);
        });
        var importInput = document.getElementById('eclipse-settings-import');
        if (importInput) importInput.addEventListener('change', function () {
            var file = importInput.files && importInput.files[0];
            if (!file) return; if (file.size > 5242880) { window.alert('The Eclipse JSON file exceeds 5 MiB.'); importInput.value = ''; return; }
            var reader = new FileReader(); reader.onload = function () { try {
                var data = JSON.parse(reader.result); var legacy = !data.schema && !data.settings;
                if (!legacy && (data.schema !== 'eclipse-studio-state' || data.schema_version !== 1 || !data.settings)) throw new Error('schema');
                var changed = applySettings(legacy ? data : data.settings);
                if (!legacy && data.footer) changed = applyFooter(data.footer) || changed;
                if (!legacy && data.palettes) changed = applyPalettes(data.palettes) || changed;
                if (!changed) throw new Error('empty'); setDirty(true);
            } catch (error) { window.alert('Invalid or unsupported Eclipse state JSON.'); importInput.value = ''; } }; reader.readAsText(file);
        });
        var historySelect = document.getElementById('eclipse-history-select'); var historyRestore = document.getElementById('eclipse-history-restore');
        if (historySelect && historyRestore) historySelect.addEventListener('change', function () { historyRestore.disabled = !historySelect.value; });
        if (cancel) cancel.addEventListener('click', function () {
            form.reset(); if (footerEditor) footerEditor.innerHTML = initialFooterHtml;
            var portablePalettes = document.getElementById('eclipse-portable-palettes'); if (portablePalettes) portablePalettes.value = '';
            selector.querySelectorAll('option[data-imported="true"]').forEach(function (option) { option.remove(); });
            inputs.forEach(function (input, index) { if (input) input.value = initialColors[index]; }); previewColors(initialColors); identifyPalette(); setDirty(false);
        });
        form.addEventListener('submit', function () { setDirty(false); });
        window.addEventListener('beforeunload', function (event) { if (!dirty) return; event.preventDefault(); event.returnValue = ''; });
        var frame = document.getElementById('eclipse-preview-frame');
        if (frame) {
            frame.addEventListener('load', function () { previewColors(currentColors()); });
            if (!frame.getAttribute('sandbox')) {
                var previewSource = frame.getAttribute('srcdoc');
                frame.setAttribute('sandbox', 'allow-same-origin');
                frame.setAttribute('srcdoc', previewSource);
            }
        }
        identifyPalette();
        previewColors(initialColors);
    }
    function setupStudio() { setupStudioTabs(); setupPalettePreview(); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setupStudio, { once: true });
    else setupStudio();

    var iconPaths = {
        block: '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        story: '<path d="M5 3h11l3 3v15H5z"/><path d="M8 10h8M8 14h8M8 18h5"/>',
        user: '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0 1 16 0"/>',
        config: '<circle cx="12" cy="12" r="3"/><path d="M12 2v3m0 14v3M2 12h3m14 0h3M5 5l2 2m10 10 2 2M19 5l-2 2M7 17l-2 2"/>',
        topic: '<path d="M3 6h7l2 2h9v11H3z"/><path d="M7 12h10M7 16h6"/>',
        plugin: '<path d="M8 3h5v5h5v5h-5v5H8v-5H3V8h5Z"/>',
        database: '<ellipse cx="12" cy="5" rx="8" ry="3"/><path d="M4 5v6c0 1.7 3.6 3 8 3s8-1.3 8-3V5M4 11v6c0 1.7 3.6 3 8 3s8-1.3 8-3v-6"/>',
        document: '<path d="M5 3h10l4 4v14H5z"/><path d="M14 3v5h5M8 12h8M8 16h6"/>',
        security: '<path d="M12 3 4 6v5c0 5 3.4 8.5 8 10 4.6-1.5 8-5 8-10V6z"/><path d="m9 12 2 2 4-5"/>',
        tool: '<path d="m14 6 4-3 3 3-3 4-4-4ZM13 7 4 16v4h4l9-9"/>',
        default: '<circle cx="12" cy="12" r="8"/><path d="M12 8v8M8 12h8"/>'
    };
    function commandIcon(href, text) {
        var value = (href + ' ' + text).toLowerCase();
        var key = /block/.test(value) ? 'block' : /stor|content|submission/.test(value) ? 'story' : /user|group|mail/.test(value) ? 'user' : /config|environment/.test(value) ? 'config' : /topic|syndicat|feed/.test(value) ? 'topic' : /plugin|spam/.test(value) ? 'plugin' : /database|backup/.test(value) ? 'database' : /doc|log|file/.test(value) ? 'document' : /security|clear|version/.test(value) ? 'security' : /track|tool/.test(value) ? 'tool' : 'default';
        return '<svg class="eclipse-command-icon" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' + iconPaths[key] + '</svg>';
    }
    document.querySelectorAll('body.admin-ui-mode-modern .admin-commandcontrol li a').forEach(function (link) {
        if (!link.querySelector('.eclipse-command-icon')) link.insertAdjacentHTML('afterbegin', commandIcon(link.getAttribute('href') || '', link.textContent || ''));
    });

    document.querySelectorAll('img[src*="/layout/eclipse/images/"]').forEach(function (img) {
        function fallbackToSvg() {
            var src = img.getAttribute('src') || '';
            img.removeEventListener('error', fallbackToSvg);
            if (/\.png(?:\?.*)?$/i.test(src)) {
                img.addEventListener('error', function () { img.hidden = true; }, { once: true });
                img.setAttribute('src', src.replace(/\.png(?=\?|$)/i, '.svg'));
            } else {
                img.hidden = true;
            }
        }
        img.addEventListener('error', fallbackToSvg);
        if (img.complete && img.naturalWidth === 0) window.setTimeout(fallbackToSvg, 0);
    });

    function setupSeoAssistant(form) {
        var assistant = form.querySelector('[data-eclipse-seo-assistant]'); if (!assistant) return;
        var title = form.querySelector('input[name="page_title"],input[name="title"]');
        var storyTitle = form.querySelector('input[name="title"]');
        var slug = form.querySelector('input[name="sid"]');
        var description = form.querySelector('textarea[name="meta_description"]');
        var content = Array.prototype.slice.call(form.querySelectorAll('textarea[name="introtext"],textarea[name="bodytext"],textarea[name="introhtml"],textarea[name="bodyhtml"]'));
        function text(value) { var element = document.createElement('div'); element.innerHTML = value || ''; return (element.textContent || '').replace(/\s+/g, ' ').trim(); }
        function check(name, okay, message) { var item = assistant.querySelector('[data-seo-check="' + name + '"]'); if (!item) return; item.className = okay ? 'is-good' : 'is-warning'; item.textContent = message; }
        function refresh() {
            var titleValue = (title && title.value.trim()) || (storyTitle && storyTitle.value.trim()) || '';
            var descriptionValue = description ? description.value.trim() : '';
            var slugValue = slug ? slug.value.trim() : '';
            var words = text(content.map(function (field) { return field.value; }).join(' ')).split(/\s+/).filter(Boolean).length;
            var base = window.geeklog && geeklog.site_url ? geeklog.site_url.replace(/\/$/, '') : window.location.origin;
            var previewTitle = assistant.querySelector('[data-seo-preview-title]');
            var previewUrl = assistant.querySelector('[data-seo-preview-url]');
            var previewDescription = assistant.querySelector('[data-seo-preview-description]');
            if (previewTitle) previewTitle.textContent = titleValue || 'Article title';
            if (previewUrl) previewUrl.textContent = base + '/article.php/' + (slugValue || 'article-slug');
            if (previewDescription) previewDescription.textContent = descriptionValue || text(content.map(function (field) { return field.value; }).join(' ')).slice(0, 160) || 'The search description will appear here.';
            check('title', titleValue.length >= 30 && titleValue.length <= 60, 'Title: ' + titleValue.length + ' characters (recommended 30–60).');
            check('description', descriptionValue.length >= 120 && descriptionValue.length <= 160, descriptionValue ? 'Meta description: ' + descriptionValue.length + ' characters (recommended 120–160).' : 'Meta description: missing; the preview currently uses an automatic content excerpt.');
            check('slug', /^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slugValue), 'Slug: ' + (slugValue ? 'format ' + (/^[a-z0-9]+(?:-[a-z0-9]+)*$/.test(slugValue) ? 'ready; uniqueness is verified when saving.' : 'should use lowercase words and hyphens.') : 'missing.'));
            check('content', words >= 300, 'Content: approximately ' + words + ' words' + (words >= 300 ? '.' : ' (300 or more recommended for substantial articles).'));
        }
        [title, storyTitle, slug, description].concat(content).forEach(function (field) { if (field) field.addEventListener('input', refresh); });
        refresh();
    }

    function setupEditorialSafety(form) {
        if (!window.localStorage || form.getAttribute('data-eclipse-autosave') === 'true') return;
        form.setAttribute('data-eclipse-autosave', 'true');
        var labels = strings(form), dirty = false, lastSaved = '', lastObserved = '';
        var oldSid = form.querySelector('[name="old_sid"],[name="oldsid"]');
        var sid = form.querySelector('[name="sid"]');
        var identity = oldSid && oldSid.value.trim() ? oldSid.value.trim() : sid && sid.defaultValue.trim() ? sid.defaultValue.trim() : 'new-story';
        var storageKey = 'eclipse-story-draft:' + window.location.pathname + ':' + identity;
        var allowedNames = ['title','page_title','sid','meta_description','meta_keywords','introtext','bodytext','introhtml','bodyhtml','postmode'];
        function editorValue(control) {
            if (window.CKEDITOR && control.id && CKEDITOR.instances && CKEDITOR.instances[control.id]) return CKEDITOR.instances[control.id].getData();
            return control.value;
        }
        function snapshot() {
            var values = {};
            allowedNames.forEach(function (name) { var control = form.querySelector('[name="' + name + '"]'); if (control) values[name] = editorValue(control); });
            return values;
        }
        function signature(values) { return JSON.stringify(values); }
        function apply(values) {
            allowedNames.forEach(function (name) {
                var control = form.querySelector('[name="' + name + '"]'); if (!control || typeof values[name] !== 'string') return;
                control.value = values[name];
                if (window.CKEDITOR && control.id && CKEDITOR.instances && CKEDITOR.instances[control.id]) CKEDITOR.instances[control.id].setData(values[name]);
                control.dispatchEvent(new Event('input', { bubbles:true }));
            });
            dirty = true;
        }
        var tools = document.createElement('div'); tools.className = 'eclipse-editor-safety';
        var status = document.createElement('span'); status.className = 'eclipse-autosave-status'; status.setAttribute('role','status'); status.textContent = labels.autosaved + ': —';
        var focus = document.createElement('button'); focus.type = 'button'; focus.className = 'eclipse-focus-toggle'; focus.textContent = labels.focusMode; focus.setAttribute('aria-pressed','false');
        focus.addEventListener('click', function () { var active = document.body.classList.toggle('eclipse-focus-writing'); focus.textContent = active ? labels.exitFocus : labels.focusMode; focus.setAttribute('aria-pressed', active ? 'true' : 'false'); });
        tools.appendChild(status); tools.appendChild(focus); form.insertBefore(tools, form.firstChild);
        function saveDraft() {
            if (!dirty) return;
            var values = snapshot(), serialized = signature(values); if (serialized === lastSaved) return;
            try { localStorage.setItem(storageKey, JSON.stringify({ savedAt:Date.now(), values:values })); lastSaved = serialized; status.textContent = labels.autosaved + ' · ' + new Date().toLocaleTimeString([], { hour:'2-digit', minute:'2-digit' }); }
            catch (error) { status.textContent = 'Local autosave unavailable'; }
        }
        var initial = signature(snapshot()); lastObserved = initial;
        try {
            var stored = JSON.parse(localStorage.getItem(storageKey) || 'null');
            if (stored && stored.values && signature(stored.values) === initial) localStorage.removeItem(storageKey);
            else if (stored && stored.values) {
                var notice = document.createElement('div'); notice.className = 'eclipse-draft-recovery';
                var message = document.createElement('strong'); message.textContent = labels.draftFound;
                var restore = document.createElement('button'); restore.type = 'button'; restore.textContent = labels.restoreDraft;
                var discard = document.createElement('button'); discard.type = 'button'; discard.textContent = labels.discardDraft;
                restore.addEventListener('click', function () { apply(stored.values); notice.remove(); saveDraft(); });
                discard.addEventListener('click', function () { localStorage.removeItem(storageKey); notice.remove(); });
                notice.appendChild(message); notice.appendChild(restore); notice.appendChild(discard); tools.insertAdjacentElement('afterend', notice);
            }
        } catch (error) {}
        form.addEventListener('input', function (event) { if (event.target.name && allowedNames.indexOf(event.target.name) !== -1) dirty = true; });
        form.addEventListener('change', function (event) { if (event.target.name && allowedNames.indexOf(event.target.name) !== -1) dirty = true; });
        form.addEventListener('submit', function () { saveDraft(); dirty = false; });
        window.setInterval(function () { var current = signature(snapshot()); if (current !== lastObserved) { dirty = true; lastObserved = current; } saveDraft(); }, 15000);
        window.addEventListener('beforeunload', function (event) { if (!dirty) return; saveDraft(); event.preventDefault(); event.returnValue = ''; });
    }

    function setupAdminLists() {
        if (!document.body.classList.contains('eclipse-admin-page') || !window.localStorage) return;
        var pathKey = window.location.pathname + window.location.search.replace(/([?&])page=\d+/g, '$1page=');
        document.querySelectorAll('.admin-list-search').forEach(function (search, searchIndex) {
            var form = search.closest('form'); if (!form || form.getAttribute('data-eclipse-filters') === 'true') return;
            form.setAttribute('data-eclipse-filters','true');
            var key = 'eclipse-admin-filters:' + pathKey + ':' + searchIndex;
            var visibilityKey = key + ':visible';
            var saved = {}; try { saved = JSON.parse(localStorage.getItem(key) || '{}') || {}; } catch (error) {}
            var tools = document.createElement('div'); tools.className = 'eclipse-filter-tools';
            var toggleFilters = document.createElement('button'); toggleFilters.type = 'button'; toggleFilters.className = 'eclipse-filter-toggle';
            function setFiltersVisible(visible) { document.body.classList.toggle('eclipse-admin-tools-visible',visible); toggleFilters.setAttribute('aria-expanded',visible?'true':'false'); toggleFilters.textContent = visible ? '\u25b4 Hide Eclipse tools' : '\u25be Show Eclipse tools'; localStorage.setItem(visibilityKey,visible?'1':'0'); }
            toggleFilters.addEventListener('click',function(){setFiltersVisible(!document.body.classList.contains('eclipse-admin-tools-visible'));});
            var select = document.createElement('select'); select.setAttribute('aria-label','Saved filters'); select.innerHTML = '<option value="">Saved filters</option>';
            function render() { select.length = 1; Object.keys(saved).sort().forEach(function (name) { var option = document.createElement('option'); option.value = name; option.textContent = name; select.appendChild(option); }); }
            function values() { var result = {}; form.querySelectorAll('input[name],select[name]').forEach(function (control) { if (!control.name || /token/i.test(control.name) || /submit/i.test(control.type)) return; result[control.name] = control.type === 'checkbox' ? control.checked : control.value; }); return result; }
            var save = document.createElement('button'); save.type = 'button'; save.textContent = 'Save filter';
            var remove = document.createElement('button'); remove.type = 'button'; remove.textContent = 'Delete filter';
            save.addEventListener('click', function () { var name = window.prompt('Filter name'); if (!name) return; name = name.trim().slice(0,40); if (!name) return; saved[name] = values(); localStorage.setItem(key,JSON.stringify(saved)); render(); select.value = name; });
            remove.addEventListener('click', function () { if (!select.value) return; delete saved[select.value]; localStorage.setItem(key,JSON.stringify(saved)); render(); });
            select.addEventListener('change', function () { var data = saved[select.value]; if (!data) return; Object.keys(data).forEach(function (name) { var control = form.querySelector('[name="' + CSS.escape(name) + '"]'); if (!control) return; if (control.type === 'checkbox') control.checked = Boolean(data[name]); else control.value = data[name]; }); });
            tools.appendChild(toggleFilters); tools.appendChild(select); tools.appendChild(save); tools.appendChild(remove); search.insertAdjacentElement('afterend',tools); render();
            setFiltersVisible(localStorage.getItem(visibilityKey) === '1');
        });
        document.querySelectorAll('table.admin-list-table').forEach(function (table, tableIndex) {
            if (table.getAttribute('data-eclipse-table') === 'true') return; table.setAttribute('data-eclipse-table','true');
            var key = 'eclipse-admin-table:' + pathKey + ':' + tableIndex;
            var state = { density:'normal', hidden:[] }; try { state = Object.assign(state,JSON.parse(localStorage.getItem(key) || '{}')); } catch (error) {}
            var rows = Array.prototype.slice.call(table.rows); if (!rows.length) return; var headers = Array.prototype.slice.call(rows[0].cells);
            var toolbar = document.createElement('div'); toolbar.className = 'eclipse-table-tools';
            var density = document.createElement('div'); density.className = 'eclipse-density-tools'; density.setAttribute('role','group'); density.setAttribute('aria-label','Table density');
            ['compact','normal','comfortable'].forEach(function (value) { var button = document.createElement('button'); button.type='button'; button.textContent=value.charAt(0).toUpperCase()+value.slice(1); button.setAttribute('aria-pressed',state.density===value?'true':'false'); button.addEventListener('click',function(){state.density=value;table.setAttribute('data-density',value);density.querySelectorAll('button').forEach(function(item){item.setAttribute('aria-pressed',item===button?'true':'false');});localStorage.setItem(key,JSON.stringify(state));}); density.appendChild(button); });
            var columns = document.createElement('details'); columns.className = 'eclipse-column-tools'; var summary = document.createElement('summary'); summary.textContent = 'Columns'; columns.appendChild(summary); var columnList = document.createElement('div'); columns.appendChild(columnList);
            function setColumn(index, visible) { rows.forEach(function (row) { if (row.cells[index]) row.cells[index].hidden = !visible; }); var position=state.hidden.indexOf(index); if(visible&&position!==-1)state.hidden.splice(position,1);if(!visible&&position===-1)state.hidden.push(index);localStorage.setItem(key,JSON.stringify(state)); }
            headers.forEach(function (cell,index) { var text=(cell.textContent||'').trim()||('Column '+(index+1)); var label=document.createElement('label');var input=document.createElement('input');input.type='checkbox';input.checked=state.hidden.indexOf(index)===-1;input.addEventListener('change',function(){setColumn(index,input.checked);});label.appendChild(input);label.appendChild(document.createTextNode(text));columnList.appendChild(label);if(!input.checked)setColumn(index,false); });
            var groupCounts={}; table.querySelectorAll('input[type="checkbox"][name]').forEach(function(input){groupCounts[input.name]=(groupCounts[input.name]||0)+1;}); var groupName=Object.keys(groupCounts).sort(function(a,b){return groupCounts[b]-groupCounts[a];})[0];
            if(groupName&&groupCounts[groupName]>1){var boxes=Array.prototype.slice.call(table.querySelectorAll('input[type="checkbox"][name="'+CSS.escape(groupName)+'"]'));var selection=document.createElement('label');selection.className='eclipse-selection-tool';var all=document.createElement('input');all.type='checkbox';var counter=document.createElement('span');function update(){var count=boxes.filter(function(box){return box.checked;}).length;counter.textContent=count+' selected';all.checked=count===boxes.length;all.indeterminate=count>0&&count<boxes.length;}all.addEventListener('change',function(){boxes.forEach(function(box){box.checked=all.checked;box.dispatchEvent(new Event('change',{bubbles:true}));});update();});boxes.forEach(function(box){box.addEventListener('change',update);});selection.appendChild(all);selection.appendChild(counter);toolbar.appendChild(selection);update();}
            toolbar.appendChild(density); toolbar.appendChild(columns); table.setAttribute('data-density',state.density);
            var tableForm = table.closest('form');
            var filterToolbar = tableForm ? tableForm.querySelector('.eclipse-filter-tools') : null;
            if (!filterToolbar) {
                Array.prototype.forEach.call(document.querySelectorAll('.eclipse-filter-tools'), function (candidate) {
                    if (candidate.compareDocumentPosition(table) & Node.DOCUMENT_POSITION_FOLLOWING) filterToolbar = candidate;
                });
            }
            if (filterToolbar) {
                while (toolbar.firstChild) filterToolbar.appendChild(toolbar.firstChild);
            } else {
                var wrapper=table.closest('.table-wrapper')||table; wrapper.parentNode.insertBefore(toolbar,wrapper);
            }
        });
    }

    function enhanceStoryEditor() {
        var advancedRoot = document.getElementById('advanced_editor');
        var advancedVisible = advancedRoot && window.getComputedStyle(advancedRoot).display !== 'none';
        var form = advancedVisible ? advancedRoot.querySelector('form[name="frmstory"]') : Array.prototype.find.call(document.querySelectorAll('form[name="frmstory"]'), function (candidate) { return !candidate.closest('#advanced_editor'); });
        if (!form) form = document.querySelector('form[name="frmstory"]');
        if (!form) return;
        if (form.getAttribute('data-eclipse-enhanced') === 'true') return;
        form.setAttribute('data-eclipse-enhanced', 'true');
        document.body.classList.add('eclipse-story-editor-page');
        form.classList.add('eclipse-story-form');
        var titleInput = form.querySelector('input[name="title"]');
        var slugInput = form.querySelector('input[name="sid"]');
        var labels = strings(form);
        if (titleInput) titleInput.removeAttribute('onkeyup');
        if (titleInput && slugInput) {
            var params = new URLSearchParams(window.location.search);
            var oldSid = form.querySelector('[name="old_sid"],[name="oldsid"]');
            var isNewStory = !params.get('sid') && !(oldSid && oldSid.value.trim());
            if (isNewStory) {
                var slugWasEdited = false;
                slugInput.addEventListener('input', function (event) { if (event.isTrusted) slugWasEdited = true; });
                titleInput.addEventListener('input', function () {
                    if (slugWasEdited) return;
                    var value = titleInput.value;
                    if (value.normalize) value = value.normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                    slugInput.value = value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '').substring(0, 128);
                    slugInput.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }
        }
        setupSeoAssistant(form);
        setupEditorialSafety(form);
        ['#admin-storyeditor_advanced-show_topic_icon','#admin-storyeditor_advanced-draft_flag'].forEach(function (selector) {
            var checkbox = form.querySelector(selector); if (!checkbox) return;
            var valueCell = checkbox.closest('dd'); var labelCell = valueCell ? valueCell.previousElementSibling : null;
            if (valueCell) valueCell.classList.add('eclipse-checkbox-cell');
            if (labelCell && labelCell.tagName === 'DT') labelCell.classList.add('eclipse-checkbox-cell');
        });
        var advanced = form.closest('#advanced_editor');
        if (advanced) {
            advanced.classList.add('eclipse-advanced-editor');
            var advancedBasic = form.querySelector(':scope > .admin_basic');
            if (advancedBasic) {
                advancedBasic.classList.add('eclipse-editor-overview');
                advancedBasic.setAttribute('data-eclipse-heading', labels.articleDetails);
            }
            var nativeIdentity = form.querySelector(':scope > .eclipse-editor-identity');
            if (!nativeIdentity && advancedBasic && titleInput) {
                var titleDefinition = titleInput.closest('dd');
                var titleTerm = titleDefinition ? titleDefinition.previousElementSibling : null;
                while (titleTerm && titleTerm.tagName !== 'DT') titleTerm = titleTerm.previousElementSibling;
                if (titleDefinition && titleTerm) {
                    var advancedIdentity = document.createElement('section');
                    advancedIdentity.className = 'eclipse-editor-identity';
                    var identityHeading = document.createElement('h2');
                    identityHeading.textContent = labels.articleTitle;
                    advancedIdentity.appendChild(identityHeading);
                    var identityList = document.createElement('dl');
                    identityList.className = 'form_block';
                    identityList.appendChild(titleTerm);
                    identityList.appendChild(titleDefinition);
                    advancedIdentity.appendChild(identityList);
                    form.insertBefore(advancedIdentity, form.firstChild);
                }
            }
            if (advancedBasic && !form.querySelector('.eclipse-advanced-seo-overview')) {
                var advancedSource = advancedBasic.querySelector(':scope > dl.form_block');
                var advancedSeo = document.createElement('section'); advancedSeo.className = 'eclipse-editor-panel eclipse-panel-seo eclipse-advanced-seo-overview';
                var advancedSeoHeading = document.createElement('h2'); advancedSeoHeading.textContent = labels.seo; advancedSeo.appendChild(advancedSeoHeading);
                var advancedSeoList = document.createElement('dl'); advancedSeoList.className = 'form_block'; advancedSeo.appendChild(advancedSeoList);
                var movedSeoFields = 0;
                function moveAdvancedSeoField(selector) {
                    if (!advancedSource) return; var control = advancedSource.querySelector(selector); if (!control) return;
                    var definition = control.closest('dd'); if (!definition) return; var term = definition.previousElementSibling;
                    while (term && term.tagName !== 'DT') term = term.previousElementSibling;
                    if (term) advancedSeoList.appendChild(term); advancedSeoList.appendChild(definition); movedSeoFields++;
                }
                ['#admin-storyeditor_advanced-sid','#admin-storyeditor-metadescription','#admin-storyeditor-metakeywords'].forEach(moveAdvancedSeoField);
                if (movedSeoFields) {
                    var seoAssistant = document.createElement('div'); seoAssistant.className = 'eclipse-seo-assistant'; seoAssistant.setAttribute('data-eclipse-seo-assistant','true');
                    seoAssistant.innerHTML = '<h3></h3><div class="eclipse-search-preview"><strong data-seo-preview-title></strong><span data-seo-preview-url></span><p data-seo-preview-description></p></div><ul><li data-seo-check="title"></li><li data-seo-check="description"></li><li data-seo-check="slug"></li><li data-seo-check="content"></li></ul>';
                    seoAssistant.querySelector('h3').textContent = labels.seoOverview; advancedSeo.appendChild(seoAssistant);
                    var identityPanel = form.querySelector(':scope > .eclipse-editor-identity');
                    if (identityPanel) identityPanel.insertAdjacentElement('afterend', advancedSeo); else form.insertBefore(advancedSeo, advancedBasic);
                    setupSeoAssistant(form);
                }
            }
            var editorCanvases = Array.prototype.slice.call(form.querySelectorAll('.story_editor'));
            editorCanvases.forEach(function (editor) {
                editor.classList.add('eclipse-editor-canvas');
                editor.setAttribute('data-eclipse-heading', labels.articleContent);
                if (advancedBasic && !form.classList.contains('eclipse-native-story-editor')) form.insertBefore(editor, advancedBasic);
            });
            function syncAdvancedOverview() {
                if (!advancedBasic) return;
                var editorActive = editorCanvases.some(function (editor) {
                    return editor.style.display !== 'none';
                });
                advancedBasic.hidden = !editorActive;
            }
            editorCanvases.forEach(function (editor) {
                new MutationObserver(syncAdvancedOverview).observe(editor, { attributes: true, attributeFilter: ['style', 'class'] });
            });
            var optionPanels = form.querySelectorAll('#se_publish,#se_images,#se_archive,#se_perms,#se_options');
            optionPanels.forEach(function (panel) {
                new MutationObserver(syncAdvancedOverview).observe(panel, { attributes: true, attributeFilter: ['style', 'class'] });
            });
            syncAdvancedOverview();
            var advancedActions = advanced.querySelector('#se_bottom');
            if (advancedActions) advancedActions.classList.add('eclipse-editor-actions');
            return;
        }
        var basic = form.querySelector(':scope > .admin_basic');
        var source = basic ? basic.querySelector(':scope > dl.form_block') : null;
        if (!basic || !source || !document.getElementById('admin-storyeditor-title')) return;
        var workspace = document.createElement('div'); workspace.className = 'eclipse-story-workspace';
        var main = document.createElement('div'); main.className = 'eclipse-story-main';
        var side = document.createElement('aside'); side.className = 'eclipse-story-sidebar';
        workspace.appendChild(main); workspace.appendChild(side); basic.insertBefore(workspace, source);
        function panel(title, className, parent) {
            var section = document.createElement('section'); section.className = 'eclipse-editor-panel ' + className;
            var heading = document.createElement('h2'); heading.textContent = title; section.appendChild(heading);
            var dl = document.createElement('dl'); dl.className = 'form_block'; section.appendChild(dl); parent.appendChild(section); return dl;
        }
        var identity = panel(labels.identity, 'eclipse-panel-identity', main);
        var content = panel(labels.articleContent, 'eclipse-panel-content', main);
        var publish = panel(labels.publish, 'eclipse-panel-publish', side);
        var seo = panel(labels.seo, 'eclipse-panel-seo', side);
        function moveField(selector, target) {
            var control = source.querySelector(selector); if (!control) return;
            var dd = control.closest('dd'); if (!dd) return;
            var dt = dd.previousElementSibling;
            while (dt && dt.tagName !== 'DT') dt = dt.previousElementSibling;
            if (dt) target.appendChild(dt);
            target.appendChild(dd);
            var next = dd.nextElementSibling;
            while (next && next.tagName === 'DD') { var current = next; next = next.nextElementSibling; target.appendChild(current); }
        }
        ['#admin-storyeditor-title','#admin-storyeditor-page_title','[name="topic[]"]','#admin-storyeditor-show_topic_icon'].forEach(function (s) { moveField(s, identity); });
        ['#admin-storyeditor-introtext','#admin-storyeditor-bodytext','#admin-storyeditor-postmode'].forEach(function (s) { moveField(s, content); });
        ['#admin-storyeditor-publish_month','#admin-storyeditor-draft_flag','[name="frontpage"]','#cmt_close_flag','#admin-storyeditor-archiveflag','#admin-storyeditor-expire_month','#admin-storyeditor-cachetime'].forEach(function (s) { moveField(s, publish); });
        ['#admin-storyeditor-sid','#admin-storyeditor-metadescription','#admin-storyeditor-metakeywords'].forEach(function (s) { moveField(s, seo); });
        var remaining = panel(labels.more, 'eclipse-panel-more', side);
        while (source.firstChild) remaining.appendChild(source.firstChild);
        source.remove();
        var access = basic.querySelector(':scope > fieldset');
        if (access) { access.classList.add('eclipse-editor-access'); var legend = access.querySelector('legend'); if (legend && !legend.textContent.trim()) legend.textContent = labels.access; side.appendChild(access); }
        var actions = basic.querySelector(':scope > .submit');
        if (actions) { actions.classList.add('eclipse-editor-actions'); basic.appendChild(actions); }
    }
    function setupAdminDashboard() {
        var root = document.querySelector('.eclipse-command-page');
        if (!root || !window.localStorage || root.getAttribute('data-eclipse-dashboard') === 'true') return;
        root.setAttribute('data-eclipse-dashboard','true');
        var lists = Array.prototype.slice.call(root.querySelectorAll('.admin-commandcontrol'));
        if (!lists.length) return;
        var storageKey = 'eclipse-admin-dashboard:v1';
        var state = { groups:{}, hidden:[] };
        try { state = Object.assign(state,JSON.parse(localStorage.getItem(storageKey) || '{}')); } catch (error) {}
        if (!state.groups || typeof state.groups !== 'object') state.groups = {};
        if (!Array.isArray(state.hidden)) state.hidden = [];
        function itemKey(item) { var link=item.querySelector('a'); return link ? (link.getAttribute('href') || link.textContent.trim()) : item.textContent.trim(); }
        function groupItems(list) { return Array.prototype.slice.call(list.children).filter(function(item){return item.tagName==='LI';}); }
        var defaultGroups={}; lists.forEach(function(list,index){defaultGroups[index]=groupItems(list).map(itemKey);});
        function saveState() { localStorage.setItem(storageKey,JSON.stringify(state)); }
        function applyState() {
            lists.forEach(function(list,index){
                var items=groupItems(list); var order=state.groups[index] || [];
                items.sort(function(a,b){var ai=order.indexOf(itemKey(a));var bi=order.indexOf(itemKey(b));if(ai<0)ai=9999;if(bi<0)bi=9999;return ai-bi;});
                items.forEach(function(item){item.hidden=state.hidden.indexOf(itemKey(item))!==-1;list.appendChild(item);});
                state.groups[index]=items.map(itemKey);
            });
        }
        var customizer=document.createElement('details'); customizer.className='eclipse-dashboard-customizer';
        var summary=document.createElement('summary'); summary.textContent='Customize dashboard'; customizer.appendChild(summary);
        var panel=document.createElement('div'); panel.className='eclipse-dashboard-panel'; customizer.appendChild(panel);
        function renderPanel(){
            panel.textContent='';
            lists.forEach(function(list,index){
                var section=document.createElement('section'); var heading=document.createElement('h3');
                var previous=list.previousElementSibling; heading.textContent=previous&&/^H[2-4]$/.test(previous.tagName)?previous.textContent.trim():'Dashboard group '+(index+1); section.appendChild(heading);
                groupItems(list).forEach(function(item,itemIndex){
                    var key=itemKey(item); var link=item.querySelector('a'); var row=document.createElement('div'); row.className='eclipse-dashboard-option';
                    var label=document.createElement('label'); var checkbox=document.createElement('input'); checkbox.type='checkbox'; checkbox.checked=state.hidden.indexOf(key)===-1;
                    checkbox.addEventListener('change',function(){var position=state.hidden.indexOf(key);if(checkbox.checked&&position!==-1)state.hidden.splice(position,1);if(!checkbox.checked&&position===-1)state.hidden.push(key);saveState();applyState();});
                    label.appendChild(checkbox); label.appendChild(document.createTextNode(link?link.textContent.trim():item.textContent.trim())); row.appendChild(label);
                    var actions=document.createElement('span');
                    [['\u2191','Move earlier',-1],['\u2193','Move later',1]].forEach(function(definition){var button=document.createElement('button');button.type='button';button.textContent=definition[0];button.setAttribute('aria-label',definition[1]+' '+label.textContent);button.disabled=(definition[2]<0&&itemIndex===0)||(definition[2]>0&&itemIndex===groupItems(list).length-1);button.addEventListener('click',function(){var order=state.groups[index];var position=order.indexOf(key);var target=position+definition[2];if(position<0||target<0||target>=order.length)return;var swap=order[target];order[target]=key;order[position]=swap;saveState();applyState();renderPanel();});actions.appendChild(button);});
                    row.appendChild(actions); section.appendChild(row);
                }); panel.appendChild(section);
            });
            var reset=document.createElement('button');reset.type='button';reset.className='eclipse-dashboard-reset';reset.textContent='Reset dashboard';reset.addEventListener('click',function(){localStorage.removeItem(storageKey);state={groups:{},hidden:[]};Object.keys(defaultGroups).forEach(function(index){state.groups[index]=defaultGroups[index].slice();});lists.forEach(function(list){groupItems(list).forEach(function(item){item.hidden=false;});});applyState();renderPanel();});panel.appendChild(reset);
        }
        applyState(); renderPanel();
        var studioLaunch=root.querySelector('.eclipse-studio-launch');
        if(studioLaunch)studioLaunch.insertAdjacentElement('afterend',customizer);else root.insertBefore(customizer,root.firstChild);
    }
    function setupAdminCommandPalette() {
        if (!document.body.classList.contains('eclipse-admin-page') || document.querySelector('.eclipse-command-palette')) return;
        var commands=[]; var known={};
        document.querySelectorAll('.admin-commandcontrol a[href],.eclipse-studio-launch[href],#rightblocks a[href],#leftblocks a[href],#admin-menu a[href]').forEach(function(link){
            var href=link.getAttribute('href')||''; var label=(link.textContent||'').replace(/\s+/g,' ').trim();
            if (!href || !label || href.charAt(0)==='#' || /^javascript:/i.test(href)) return;
            try { var absolute=new URL(href,window.location.href); if(absolute.origin!==window.location.origin)return; href=absolute.href; } catch(error){return;}
            var key=href+'|'+label.toLowerCase(); if(known[key])return; known[key]=true; commands.push({label:label,href:href});
        });
        if (!commands.length) return;
        commands.sort(function(a,b){return a.label.localeCompare(b.label);});
        var trigger=document.createElement('button');trigger.type='button';trigger.className='eclipse-command-trigger';trigger.setAttribute('aria-haspopup','dialog');trigger.innerHTML='<span aria-hidden="true">\u2318K</span> Commands';document.body.appendChild(trigger);
        var palette=document.createElement('div');palette.className='eclipse-command-palette';palette.hidden=true;
        var backdrop=document.createElement('button');backdrop.type='button';backdrop.className='eclipse-command-backdrop';backdrop.setAttribute('aria-label','Close command palette');
        var dialog=document.createElement('section');dialog.className='eclipse-command-dialog';dialog.setAttribute('role','dialog');dialog.setAttribute('aria-modal','true');dialog.setAttribute('aria-labelledby','eclipse-command-title');
        var title=document.createElement('h2');title.id='eclipse-command-title';title.textContent='Administration commands';
        var input=document.createElement('input');input.type='search';input.className='eclipse-command-search';input.placeholder='Search commands…';input.setAttribute('aria-label','Search administration commands');input.setAttribute('autocomplete','off');
        var results=document.createElement('ul');results.className='eclipse-command-results';results.setAttribute('role','listbox');
        var help=document.createElement('p');help.className='eclipse-command-help';help.textContent='↑ ↓ Navigate · Enter Open · Esc Close';
        dialog.appendChild(title);dialog.appendChild(input);dialog.appendChild(results);dialog.appendChild(help);palette.appendChild(backdrop);palette.appendChild(dialog);document.body.appendChild(palette);
        var visible=[];var active=0;var previousFocus=null;
        function selectResult(index){active=index;Array.prototype.forEach.call(results.querySelectorAll('[role="option"]'),function(item,itemIndex){item.setAttribute('aria-selected',itemIndex===active?'true':'false');});}
        function draw(){var query=input.value.trim().toLowerCase();visible=commands.filter(function(command){return !query||command.label.toLowerCase().indexOf(query)!==-1;}).slice(0,12);active=Math.min(active,Math.max(0,visible.length-1));results.textContent='';visible.forEach(function(command,index){var item=document.createElement('li');item.setAttribute('role','option');item.setAttribute('aria-selected',index===active?'true':'false');var link=document.createElement('a');link.href=command.href;link.textContent=command.label;link.addEventListener('mouseenter',function(){selectResult(index);});item.appendChild(link);results.appendChild(item);});if(!visible.length){var empty=document.createElement('li');empty.className='eclipse-command-empty';empty.textContent='No matching command';results.appendChild(empty);}}
        function openPalette(){previousFocus=document.activeElement;palette.hidden=false;document.body.classList.add('eclipse-command-open');input.value='';active=0;draw();window.setTimeout(function(){input.focus();},0);}
        function closePalette(){palette.hidden=true;document.body.classList.remove('eclipse-command-open');if(previousFocus&&previousFocus.focus)previousFocus.focus();}
        trigger.addEventListener('click',openPalette);backdrop.addEventListener('click',closePalette);input.addEventListener('input',function(){active=0;draw();});
        input.addEventListener('keydown',function(event){if(event.key==='ArrowDown'){event.preventDefault();selectResult(Math.min(active+1,visible.length-1));}else if(event.key==='ArrowUp'){event.preventDefault();selectResult(Math.max(active-1,0));}else if(event.key==='Enter'&&visible[active]){event.preventDefault();window.location.href=visible[active].href;}else if(event.key==='Escape'){event.preventDefault();closePalette();}});
        palette.addEventListener('keydown',function(event){if(event.key!=='Tab')return;var focusable=Array.prototype.slice.call(dialog.querySelectorAll('input,a[href],button:not([disabled])'));if(!focusable.length)return;var first=focusable[0];var last=focusable[focusable.length-1];if(event.shiftKey&&document.activeElement===first){event.preventDefault();last.focus();}else if(!event.shiftKey&&document.activeElement===last){event.preventDefault();first.focus();}});
        document.addEventListener('keydown',function(event){if((event.ctrlKey||event.metaKey)&&event.key.toLowerCase()==='k'){event.preventDefault();if(palette.hidden)openPalette();else closePalette();}else if(event.key==='Escape'&&!palette.hidden){closePalette();}});
    }
    function setupEditorialAdministration() {
        setupAccessibility(); setupArticleSharing();
        document.querySelectorAll('.story_status>li').forEach(function (item) {
            if (!item.textContent.trim() && !item.querySelector('img,svg,[aria-label]')) item.hidden = true;
        });
        if (document.body.classList.contains('eclipse-admin-page') && !document.body.classList.contains('admin-ui-mode-modern')) return;
        setupAdminDashboard(); setupAdminCommandPalette(); setupAdminLists(); enhanceStoryEditor();
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', setupEditorialAdministration, { once: true });
    else setupEditorialAdministration();
}());
