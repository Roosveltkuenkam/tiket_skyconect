(function () {
    var storageKey = 'skyconnect-theme';

    function preferredTheme() {
        var saved = localStorage.getItem(storageKey);

        if (saved === 'light' || saved === 'dark') {
            return saved;
        }

        return window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches
            ? 'dark'
            : 'light';
    }

    function applyTheme(theme) {
        document.documentElement.setAttribute('data-theme', theme);

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.setAttribute('aria-label', theme === 'dark' ? 'Activer le theme clair' : 'Activer le theme sombre');
            button.setAttribute('title', theme === 'dark' ? 'Theme clair' : 'Theme sombre');
        });
    }

    applyTheme(preferredTheme());

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(preferredTheme());

        document.querySelectorAll('[data-theme-toggle]').forEach(function (button) {
            button.addEventListener('click', function () {
                var current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
                var next = current === 'dark' ? 'light' : 'dark';

                localStorage.setItem(storageKey, next);
                applyTheme(next);
            });
        });
    });
})();
