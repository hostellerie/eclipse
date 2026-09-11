(function () {
    'use strict';

    function isFrench() {
        return (document.documentElement.lang || '').toLowerCase().indexOf('fr') === 0;
    }

    function enhance(form) {
        if (!form || form.dataset.eclipseStaticpageEnhanced === '1') return;
        form.dataset.eclipseStaticpageEnhanced = '1';
        form.classList.add('eclipse-staticpage-editor');

        var idInput = form.querySelector('input[name="sp_id"]');
        var oldId = form.querySelector('input[name="sp_old_id"]');
        if (!idInput || !oldId) return;

        var existingId = (oldId.value || '').trim();
        var titleToIdEnabled = document.body.classList.contains('eclipse-titletoid-enabled');
        if (!titleToIdEnabled || existingId === '') return;

        var fr = isFrench();
        var originalId = idInput.value;
        var parent = idInput.parentNode;
        if (!parent) return;

        var wrap = document.createElement('div');
        wrap.className = 'eclipse-staticpage-id-wrap';
        parent.insertBefore(wrap, idInput);
        wrap.appendChild(idInput);

        var unlock = document.createElement('button');
        unlock.type = 'button';
        unlock.className = 'eclipse-staticpage-id-unlock';
        unlock.textContent = fr ? 'Déverrouiller l’ID' : 'Unlock ID';
        unlock.setAttribute('aria-controls', idInput.id || '');
        wrap.appendChild(unlock);

        var note = document.createElement('p');
        note.className = 'eclipse-staticpage-id-note';
        note.innerHTML = fr
            ? '<strong>ID protégé.</strong> Cet identifiant fait partie de l’URL publique. Le modifier peut casser des liens existants.'
            : '<strong>Protected ID.</strong> This identifier is part of the public URL. Changing it can break existing links.';
        wrap.parentNode.insertBefore(note, wrap.nextSibling);

        idInput.readOnly = true;
        idInput.classList.add('eclipse-staticpage-id-locked');
        idInput.setAttribute('aria-readonly', 'true');

        unlock.addEventListener('click', function () {
            if (idInput.readOnly) {
                var warning = fr
                    ? 'Modifier cet ID changera l’URL de la page statique et peut casser des liens existants. Continuer ?'
                    : 'Changing this ID changes the Static Page URL and may break existing links. Continue?';
                if (!window.confirm(warning)) return;
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

    function init() {
        enhance(document.getElementById('sp-editor'));
        enhance(document.getElementById('sp-advanced_editor'));
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
    else init();
}());
