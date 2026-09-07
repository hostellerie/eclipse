(function () {
    'use strict';

    function setupVividRedPreset() {
        var selector = document.getElementById('eclipse-palette-preset');
        var form = selector ? selector.closest('form') : null;
        if (!selector || !form) return;

        var presetValue = 'vivid-red';
        var colors = ['#0067ff', '#004ec2', '#005bbb', '#f4f6fb', '#ffffff', '#202431'];
        var names = ['color_primary', 'color_secondary', 'color_link', 'color_background', 'color_surface', 'color_text'];
        var inputs = names.map(function (name) {
            return form.querySelector('[name="eclipse[' + name + ']"]');
        });

        if (!selector.querySelector('option[value="' + presetValue + '"]')) {
            var option = document.createElement('option');
            option.value = presetValue;
            option.textContent = 'Vivid red';
            option.setAttribute('data-colors', colors.join(','));
            var defaultOption = selector.querySelector('option[value="default"]');
            if (defaultOption && defaultOption.nextSibling) {
                selector.insertBefore(option, defaultOption.nextSibling);
            } else {
                selector.appendChild(option);
            }
        }

        function matchesPreset() {
            return inputs.every(function (input, index) {
                return input && input.value.toLowerCase() === colors[index];
            });
        }

        function applyHeaderForCurrentPreset() {
            if (selector.value === presetValue || matchesPreset()) {
                selector.value = presetValue;
                document.documentElement.style.setProperty('--eclipse-header-primary', '#ef1b23');
                document.documentElement.style.setProperty('--eclipse-header-secondary', '#c90012');
            } else {
                document.documentElement.style.removeProperty('--eclipse-header-primary');
                document.documentElement.style.removeProperty('--eclipse-header-secondary');
            }
        }

        if (matchesPreset()) selector.value = presetValue;
        applyHeaderForCurrentPreset();

        selector.addEventListener('change', function () {
            if (selector.value === presetValue) {
                inputs.forEach(function (input, index) {
                    if (!input) return;
                    input.value = colors[index];
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                });

                // The main Theme Studio script only knows its built-in palettes.
                // Restore the selected preset after its generic palette detector runs.
                selector.value = presetValue;
            }

            // Run after the main Theme Studio change handler so every non-vivid
            // preset falls back to its own primary/secondary header colors.
            window.setTimeout(applyHeaderForCurrentPreset, 0);
        });

        inputs.forEach(function (input) {
            if (!input) return;
            input.addEventListener('input', function () {
                window.setTimeout(applyHeaderForCurrentPreset, 0);
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupVividRedPreset);
    } else {
        setupVividRedPreset();
    }
}());
