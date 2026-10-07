/* After Class – live username search.
   Works without JavaScript too: the form just submits and the page reloads. */
(function () {
    'use strict';

    var form = document.querySelector('[data-search-form]');
    var box  = document.getElementById('search-results');
    if (!form || !box || !window.fetch) { return; }

    var input = form.querySelector('input[name="q"]');
    var timer = null;
    var controller = null;
    var lastQuery = input.value.trim();

    function search(force) {
        var q = input.value.trim();
        if (!force && q === lastQuery) { return; }
        lastQuery = q;

        if (controller) { controller.abort(); }
        controller = new AbortController();
        box.setAttribute('aria-busy', 'true');

        fetch(form.action + '?q=' + encodeURIComponent(q), {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            credentials: 'same-origin',
            signal: controller.signal
        })
            .then(function (res) {
                // Session ended: we were redirected to the login page, so reload to show it.
                if (res.redirected || !res.ok) { throw new Error('reload'); }
                return res.text();
            })
            .then(function (html) {
                box.innerHTML = html;
                box.removeAttribute('aria-busy');
                history.replaceState(null, '', q ? '?q=' + encodeURIComponent(q) : form.getAttribute('action'));
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') { return; } // a newer search replaced this one
                box.removeAttribute('aria-busy');
                window.location.href = form.action + (q ? '?q=' + encodeURIComponent(q) : '');
            });
    }

    input.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(function () { search(false); }, 220);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        clearTimeout(timer);
        search(true);
    });
})();
