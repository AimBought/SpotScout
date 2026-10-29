'use strict';

(function () {
    var STORAGE_KEY = 'theme';
    var DARK = 'dark';
    var LIGHT = 'light';

    var toggle = document.querySelector('.theme-toggle');

    function getPreferred() {
        var stored = localStorage.getItem(STORAGE_KEY);
        if (stored === DARK || stored === LIGHT) {
            return stored;
        }
        return window.matchMedia('(prefers-color-scheme: dark)').matches ? DARK : LIGHT;
    }

    function apply(theme) {
        document.documentElement.setAttribute('data-theme', theme);
        localStorage.setItem(STORAGE_KEY, theme);

        var isDark = theme === DARK;

        toggle?.setAttribute('aria-pressed', String(isDark));
        toggle?.setAttribute(
            'aria-label',
            isDark ? 'Włącz jasny motyw' : 'Włącz ciemny motyw'
        );
    }

    // Apply stored / preferred theme on DOM-ready (belt-and-suspenders;
    // the inline <script> in <head> already prevents FOUC).
    apply(getPreferred());

    toggle?.addEventListener('click', function () {
        var current = document.documentElement.getAttribute('data-theme');
        apply(current === DARK ? LIGHT : DARK);
    });

    // React when the OS-level preference changes while the page is open.
    window.matchMedia('(prefers-color-scheme: dark)')
        .addEventListener('change', function (e) {
            if (!localStorage.getItem(STORAGE_KEY)) {
                apply(e.matches ? DARK : LIGHT);
            }
        });
})();