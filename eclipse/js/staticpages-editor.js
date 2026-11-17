(function () {
    'use strict';

    function tr(key) {
        var dictionary = window.ECLIPSE_LANG || {};
        return Object.prototype.hasOwnProperty.call(dictionary, key) ? dictionary[key] : key;
    }

    function setupTabs(form) {
        var tabs = Array.prototype.slice.call(form.querySelectorAll('.eclipse-sp-tabs [role="tab"]'));
        if (!tabs.length) return;

        function activate(tab, focus) {
            tabs.forEach(function (item) {
                var selected = item === tab;
                item.setAttribute('aria-selected', selected ? 'true' : 'false');
                item.tabIndex = selected ? 0 : -1;
                var panel = form.querySelector('#' + item.getAttribute('aria-controls'));
                if (panel) panel.hidden = !selected;
            });
            if (focus) tab.focus();
        }

        tabs.forEach(function (tab, index) {
            tab.addEventListener('click', function () { activate(tab, false); });
            tab.addEventListener('keydown', function (event) {
                var next = index;
                if (event.key === 'ArrowRight') next = (index + 1) % tabs.length;
                else if (event.key === 'ArrowLeft') next = (index - 1 + tabs.length) % tabs.length;
                else if (event.key === 'Home') next = 0;
                else if (event.key === 'End') next = tabs.length - 1;
                else return;
                event.preventDefault();
                activate(tabs[next], true);
            });
        });
    }

    function detachTitleToId(form) {
        var titleInput = form.querySelector('input[name="sp_title"]');
        if (!titleInput) return;
        titleInput.removeAttribute('onkeyup');
        titleInput.onkeyup = null;
    }

    function setupIdProtection(form) {
        var idInput = form.querySelector('input[name="sp_id"]');
        var oldId = form.querySelector('input[name="sp_old_id"]');
        var unlock = form.querySelector('[data-eclipse-sp-unlock]');
        var note = form.querySelector('[data-eclipse-sp-id-note]');
        if (!idInput || !oldId || !unlock || !note) return;

        var existingId = (oldId.value || '').trim();
        var titleToIdEnabled = document.body.classList.contains('eclipse-titletoid-enabled');
        if (!titleToIdEnabled || existingId === '') return;

        detachTitleToId(form);

        var originalId = idInput.value;
        unlock.hidden = false;
        unlock.textContent = tr('sp_unlock_id');
        unlock.setAttribute('aria-controls', idInput.id || '');
        note.hidden = false;
        note.textContent = tr('sp_id_protected');

        idInput.readOnly = true;
        idInput.classList.add('eclipse-staticpage-id-locked');
        idInput.setAttribute('aria-readonly', 'true');

        unlock.addEventListener('click', function () {
            if (idInput.readOnly) {
                var warning = tr('sp_id_change_warning');
                if (!window.confirm(warning)) return;
                detachTitleToId(form);
                idInput.readOnly = false;
                idInput.classList.remove('eclipse-staticpage-id-locked');
                idInput.removeAttribute('aria-readonly');
                unlock.textContent = tr('sp_lock_id');
                idInput.focus();
            } else {
                idInput.readOnly = true;
                idInput.classList.add('eclipse-staticpage-id-locked');
                idInput.setAttribute('aria-readonly', 'true');
                unlock.textContent = tr('sp_unlock_id');
            }
        });

        form.addEventListener('submit', function (event) {
            var current = (idInput.value || '').trim();
            if (current === originalId) return;
            var submitter = event.submitter;
            if (submitter && submitter.name === 'mode' && /cancel|annuler/i.test(submitter.value || '')) return;
            var warning = tr('sp_id_save_warning');
            if (!window.confirm(warning)) {
                event.preventDefault();
                idInput.focus();
            }
        });
    }

    function setupSeoTitleCounter(form) {
        var pageTitle = form.querySelector('input[name="sp_page_title"]');
        var title = form.querySelector('input[name="sp_title"]');
        var field = pageTitle || title;
        if (!field || !title) return;
        var note = document.createElement('p');
        note.className = 'description eclipse-seo-title-count';
        note.setAttribute('role', 'status');
        note.setAttribute('aria-live', 'polite');
        field.parentNode.appendChild(note);
        function refresh() {
            var value = (pageTitle && pageTitle.value.trim()) || title.value.trim();
            var siteName = document.body.getAttribute('data-eclipse-site-name') || '';
            var includeSite = document.body.getAttribute('data-eclipse-title-mode') === 'title_and_site' && value && siteName;
            var suffix = includeSite ? ' - ' + siteName : '';
            var length = (value + suffix).length;
            note.textContent = tr('seo_title_label') + length + tr('seo_title_recommended')
                + (includeSite ? tr('seo_title_including') + suffix.length
                    + tr('seo_title_site_suffix') : '.');
            note.classList.toggle('is-warning', length < 30 || length > 60);
        }
        title.addEventListener('input', refresh);
        if (pageTitle) pageTitle.addEventListener('input', refresh);
        refresh();
    }

    function setupSeoDescriptionCounter(form) {
        var description = form.querySelector('textarea[name="meta_description"]');
        if (!description || !description.parentNode) return;

        var note = document.createElement('p');
        note.className = 'description eclipse-seo-description-count';
        note.setAttribute('role', 'status');
        note.setAttribute('aria-live', 'polite');
        description.parentNode.appendChild(note);

        function refresh() {
            var length = Array.from(description.value.trim()).length;
            var guidance = length < 120
                ? tr('seo_description_short')
                : length > 160
                    ? tr('seo_description_long')
                    : tr('seo_description_good');
            note.textContent = tr('seo_description_label') + length
                + tr('seo_description_recommended')
                + guidance + '.';
            note.classList.toggle('is-warning', length < 120 || length > 160);
        }

        description.addEventListener('input', refresh);
        refresh();
    }

    function enhance(form) {
        if (!form || form.dataset.eclipseStaticpageEnhanced === '1') return;
        form.dataset.eclipseStaticpageEnhanced = '1';
        setupTabs(form);
        setupIdProtection(form);
        setupSeoTitleCounter(form);
        setupSeoDescriptionCounter(form);
    }

    function init() {
        enhance(document.getElementById('sp-editor'));
        enhance(document.getElementById('sp-advanced_editor'));
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
}());
