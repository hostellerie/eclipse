(function () {
    'use strict';

    function isFrench() {
        return (document.documentElement.lang || '').toLowerCase().indexOf('fr') === 0;
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

        var fr = isFrench();
        var originalId = idInput.value;
        unlock.hidden = false;
        unlock.textContent = fr ? 'Déverrouiller l’ID' : 'Unlock ID';
        unlock.setAttribute('aria-controls', idInput.id || '');
        note.hidden = false;
        note.innerHTML = fr
            ? '<strong>ID protégé.</strong> Cet identifiant fait partie de l’URL publique. Déverrouillez-le uniquement si vous souhaitez modifier directement l’URL.'
            : '<strong>Protected ID.</strong> This identifier is part of the public URL. Unlock it only when you intend to edit the URL directly.';

        idInput.readOnly = true;
        idInput.classList.add('eclipse-staticpage-id-locked');
        idInput.setAttribute('aria-readonly', 'true');

        unlock.addEventListener('click', function () {
            if (idInput.readOnly) {
                var warning = fr
                    ? 'Modifier cet ID changera l’URL de la page statique et peut casser des liens existants. Continuer ?'
                    : 'Changing this ID changes the Static Page URL and may break existing links. Continue?';
                if (!window.confirm(warning)) return;
                detachTitleToId(form);
                idInput.readOnly = false;
                idInput.classList.remove('eclipse-staticpage-id-locked');
                idInput.removeAttribute('aria-readonly');
                unlock.textContent = fr ? 'Reverrouiller' : 'Lock again';
                idInput.focus();
            } else {
                idInput.readOnly = true;
                idInput.classList.add('eclipse-staticpage-id-locked');
                idInput.setAttribute('aria-readonly', 'true');
                unlock.textContent = fr ? 'Déverrouiller l’ID' : 'Unlock ID';
            }
        });

        form.addEventListener('submit', function (event) {
            var current = (idInput.value || '').trim();
            if (current === originalId) return;
            var submitter = event.submitter;
            if (submitter && submitter.name === 'mode' && /cancel|annuler/i.test(submitter.value || '')) return;
            var warning = fr
                ? 'L’ID a été modifié. L’URL publique de cette page changera après enregistrement. Confirmer la modification ?'
                : 'The ID was changed. This page’s public URL will change after saving. Confirm the change?';
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
            var fr = isFrench();
            note.textContent = (fr ? 'Titre SEO : ' : 'SEO title: ') + length
                + (fr ? ' caractères (30 à 60 conseillés)' : ' characters (30–60 recommended)')
                + (includeSite ? (fr ? ', dont ' : ', including ') + suffix.length
                    + (fr ? ' pour « - » et le nom du site.' : ' for the separator and site name.') : '.');
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
            var fr = isFrench();
            var guidance = length < 120
                ? (fr ? 'description courte' : 'short description')
                : length > 160
                    ? (fr ? 'risque de troncature' : 'may be truncated')
                    : (fr ? 'longueur conseillée' : 'recommended length');
            note.textContent = (fr ? 'Meta description : ' : 'Meta description: ') + length
                + (fr ? ' caractères (120 à 160 conseillés) — ' : ' characters (120–160 recommended) — ')
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
