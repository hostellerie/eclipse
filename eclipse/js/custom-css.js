(function () {
    'use strict';

    var initialized = false;
    var observer = null;

    function activateCustomCssTab(tab, panel, tablist) {
        Array.prototype.forEach.call(tablist.querySelectorAll('[role="tab"]'), function (item) {
            var selected = item === tab;
            item.setAttribute('aria-selected', selected ? 'true' : 'false');
            item.tabIndex = selected ? 0 : -1;
            var controlled = document.getElementById(item.getAttribute('aria-controls'));
            if (controlled) controlled.hidden = !selected;
        });
        panel.hidden = false;
        var studioActions = document.querySelector('.eclipse-customizer .eclipse-actions');
        if (studioActions) {
            studioActions.hidden = true;
            studioActions.style.display = 'none';
        }
    }

    function setupCustomCssStudio() {
        if (initialized || document.getElementById('eclipse-tab-css')) {
            initialized = true;
            if (observer) observer.disconnect();
            return true;
        }

        var tablist = document.querySelector('.eclipse-studio-tabs');
        var studio = document.getElementById('eclipse-theme-studio');
        if (!tablist || !studio) return false;

        var updatesTab = document.getElementById('eclipse-tab-updates');
        var updatesPanel = document.getElementById('eclipse-panel-updates');
        var sourceForm = studio.querySelector('.eclipse-settings-form');
        if (!updatesTab || !updatesPanel || !sourceForm) return false;

        var isFrench = /^fr\b/i.test(document.documentElement.lang || '');
        var tab = document.createElement('button');
        tab.type = 'button';
        tab.id = 'eclipse-tab-css';
        tab.setAttribute('role', 'tab');
        tab.setAttribute('aria-controls', 'eclipse-panel-css');
        tab.setAttribute('aria-selected', 'false');
        tab.tabIndex = -1;
        tab.textContent = 'CSS';
        tablist.insertBefore(tab, updatesTab);

        var panel = document.createElement('section');
        panel.id = 'eclipse-panel-css';
        panel.className = 'eclipse-studio-panel eclipse-custom-css-panel';
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', 'eclipse-tab-css');
        panel.hidden = true;

        var form = document.createElement('form');
        form.method = 'post';
        form.className = 'eclipse-custom-css-form';
        form.action = window.location.href.split('#')[0] + '#eclipse-theme-studio';

        var sourceToken = Array.prototype.find.call(sourceForm.querySelectorAll('input[type="hidden"]'), function (input) {
            return input.name && input.name !== 'eclipse_portable_palettes';
        });
        if (sourceToken) {
            var token = document.createElement('input');
            token.type = 'hidden';
            token.name = sourceToken.name;
            token.value = sourceToken.value;
            form.appendChild(token);
        }

        var heading = document.createElement('header');
        var eyebrow = document.createElement('span');
        eyebrow.className = 'eclipse-eyebrow';
        eyebrow.textContent = isFrench ? 'Personnalisation avancée' : 'Advanced customization';
        var title = document.createElement('h3');
        title.textContent = isFrench ? 'CSS personnalisé' : 'Custom CSS';
        var intro = document.createElement('p');
        intro.textContent = isFrench
            ? 'Ajoutez ici des règles propres à ce site. Elles sont stockées hors du thème, survivent aux mises à jour d’Eclipse et sont chargées après les styles publics du thème.'
            : 'Add site-specific rules here. They are stored outside the theme, survive Eclipse updates and load after the public theme styles.';
        heading.appendChild(eyebrow);
        heading.appendChild(title);
        heading.appendChild(intro);
        form.appendChild(heading);

        var noticeData = window.ECLIPSE_CUSTOM_CSS_NOTICE || null;
        if (noticeData && noticeData.message) {
            var notice = document.createElement('p');
            notice.className = 'eclipse-notice ' + (noticeData.type === 'success' ? 'eclipse-success' : 'eclipse-error');
            notice.setAttribute('role', 'status');
            notice.textContent = noticeData.message;
            form.appendChild(notice);
        }

        var label = document.createElement('label');
        label.className = 'eclipse-custom-css-editor';
        var labelText = document.createElement('span');
        labelText.textContent = isFrench ? 'Feuille CSS du site' : 'Site CSS';
        var textarea = document.createElement('textarea');
        textarea.name = 'eclipse_custom_css';
        textarea.id = 'eclipse-custom-css-editor';
        textarea.rows = 22;
        textarea.maxLength = 65536;
        textarea.spellcheck = false;
        textarea.setAttribute('aria-describedby', 'eclipse-custom-css-help');
        textarea.value = typeof window.ECLIPSE_CUSTOM_CSS === 'string' ? window.ECLIPSE_CUSTOM_CSS : '';
        label.appendChild(labelText);
        label.appendChild(textarea);
        form.appendChild(label);

        var help = document.createElement('p');
        help.id = 'eclipse-custom-css-help';
        help.className = 'eclipse-section-intro';
        help.innerHTML = isFrench
            ? 'Limite : 64 Kio. Le CSS est appliqué aux pages publiques uniquement. La séquence <code>&lt;/style&gt;</code> est refusée pour éviter de sortir du bloc CSS.'
            : 'Limit: 64 KiB. CSS is applied to public pages only. The <code>&lt;/style&gt;</code> sequence is rejected to prevent leaving the CSS block.';
        form.appendChild(help);

        var actions = document.createElement('div');
        actions.className = 'eclipse-custom-css-actions';
        var save = document.createElement('button');
        save.type = 'submit';
        save.name = 'eclipse_custom_css_save';
        save.value = '1';
        save.textContent = isFrench ? 'Enregistrer le CSS' : 'Save CSS';
        var clear = document.createElement('button');
        clear.type = 'submit';
        clear.name = 'eclipse_custom_css_clear';
        clear.value = '1';
        clear.className = 'eclipse-reset';
        clear.textContent = isFrench ? 'Vider le CSS' : 'Clear CSS';
        clear.addEventListener('click', function (event) {
            if (!textarea.value.trim()) return;
            var message = isFrench ? 'Vider tout le CSS personnalisé ?' : 'Clear all Custom CSS?';
            if (!window.confirm(message)) event.preventDefault();
        });
        if (!sourceToken) {
            save.disabled = true;
            clear.disabled = true;
            help.textContent = isFrench
                ? 'Le jeton de sécurité Geeklog est indisponible. Rechargez la page avant d’enregistrer.'
                : 'The Geeklog security token is unavailable. Reload the page before saving.';
        }
        actions.appendChild(clear);
        actions.appendChild(save);
        form.appendChild(actions);

        panel.appendChild(form);
        studio.insertBefore(panel, updatesPanel);

        /* The Theme Studio can be moved into the modern administration DOM after
         * DOMContentLoaded. Keep this tab functional even when its native tab
         * controller was initialized before the CSS tab existed. */
        tab.addEventListener('click', function () {
            activateCustomCssTab(tab, panel, tablist);
        });
        tablist.addEventListener('click', function (event) {
            var clicked = event.target.closest('[role="tab"]');
            if (!clicked || clicked === tab) return;
            panel.hidden = true;
            tab.setAttribute('aria-selected', 'false');
            tab.tabIndex = -1;
        });

        initialized = true;
        if (observer) observer.disconnect();
        if (noticeData && noticeData.message) {
            window.setTimeout(function () { tab.click(); }, 0);
        }
        return true;
    }

    function start() {
        if (setupCustomCssStudio()) return;
        observer = new MutationObserver(function () {
            setupCustomCssStudio();
        });
        observer.observe(document.documentElement, { childList: true, subtree: true });
        window.setTimeout(function () {
            if (observer) observer.disconnect();
        }, 15000);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start, { once: true });
    } else {
        start();
    }
}());
