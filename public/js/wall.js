/* After Class – Freedom Wall interactions.
   Everything degrades gracefully: forms still submit normally without JS. */
(function () {
    'use strict';

    var MAX_BYTES = 4 * 1024 * 1024;

    function byId(id) { return document.getElementById(id); }

    /* ---------- Clicks (delegated) ---------- */
    document.addEventListener('click', function (e) {
        var t;

        // Show / hide the comments + repost panels
        if ((t = e.target.closest('[data-toggle-panel]'))) {
            var panel = byId(t.getAttribute('data-toggle-panel'));
            if (!panel) { return; }
            var opening = panel.hidden;
            var parentMenu = t.closest('details');
            if (parentMenu) { parentMenu.removeAttribute('open'); }
            panel.hidden = !opening;
            t.setAttribute('aria-expanded', opening ? 'true' : 'false');
            if (opening) {
                var field = panel.querySelector('textarea, input[type="text"]');
                if (field) { field.focus(); }
            }
            return;
        }

        // Edit a post in place
        if ((t = e.target.closest('[data-edit-open]'))) {
            var id = t.getAttribute('data-edit-open');
            var form = byId('edit-form-' + id);
            var content = byId('post-content-' + id);
            var menu = t.closest('details');
            if (menu) { menu.removeAttribute('open'); }
            if (form && content) {
                content.hidden = true;
                form.hidden = false;
                var ta = form.querySelector('textarea');
                if (ta) { autosize(ta); ta.focus(); }
            }
            return;
        }

        if ((t = e.target.closest('[data-edit-cancel]'))) {
            var pid = t.getAttribute('data-edit-cancel');
            var f = byId('edit-form-' + pid);
            var c = byId('post-content-' + pid);
            if (f && c) { f.reset(); f.hidden = true; c.hidden = false; }
            return;
        }

        // Copy the link to a single post
        if ((t = e.target.closest('[data-copy-link]'))) {
            var label = t.querySelector('.share-label');
            copyText(t.getAttribute('data-copy-link')).then(function () {
                flash(t, label, 'Link copied');
            }, function () {
                window.prompt('Copy this link:', t.getAttribute('data-copy-link'));
            });
            return;
        }

        // Remove the chosen composer photo
        if (e.target.closest('.remove-preview')) {
            clearComposerImage();
            return;
        }

        // Click outside closes any open dropdown
        var open = document.querySelectorAll('details.post-menu[open], details.profile-menu[open]');
        for (var i = 0; i < open.length; i++) {
            if (!open[i].contains(e.target)) { open[i].removeAttribute('open'); }
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') { return; }
        var open = document.querySelectorAll('details[open]');
        for (var i = 0; i < open.length; i++) { open[i].removeAttribute('open'); }
    });

    /* ---------- Submits (delegated) ---------- */
    document.addEventListener('submit', function (e) {
        var form = e.target;

        // Confirm destructive actions
        var message = form.getAttribute && form.getAttribute('data-confirm');
        if (message && !window.confirm(message)) {
            e.preventDefault();
            return;
        }

        // Like without reloading the page
        if (form.classList && form.classList.contains('js-like-form') && window.fetch) {
            e.preventDefault();
            var btn = form.querySelector('button');
            btn.disabled = true;

            fetch(form.action, {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form),
                credentials: 'same-origin'
            }).then(function (res) {
                if (!res.ok) { throw new Error('Request failed'); }
                return res.json();
            }).then(function (data) {
                var icon = btn.querySelector('i');
                btn.classList.toggle('is-liked', data.liked);
                btn.setAttribute('aria-pressed', data.liked ? 'true' : 'false');
                icon.classList.toggle('fa-solid', data.liked);
                icon.classList.toggle('fa-regular', !data.liked);
                btn.querySelector('.like-count').textContent = data.count;
                btn.disabled = false;
            }).catch(function () {
                form.submit(); // fall back to a normal page reload
            });
        }
    });

    /* ---------- Composer ---------- */
    var fileInput = byId('composer-image');
    var preview = document.querySelector('.image-preview');
    var note = document.querySelector('.composer-note');

    function clearComposerImage() {
        if (!fileInput) { return; }
        fileInput.value = '';
        if (preview) {
            preview.hidden = true;
            var img = preview.querySelector('img');
            if (img && img.src) { URL.revokeObjectURL(img.src); img.removeAttribute('src'); }
        }
    }

    if (fileInput && preview) {
        fileInput.addEventListener('change', function () {
            var file = fileInput.files && fileInput.files[0];
            if (note) { note.hidden = true; }
            if (!file) { clearComposerImage(); return; }

            if (file.type.indexOf('image/') !== 0) {
                clearComposerImage();
                showNote('Please choose an image file.');
                return;
            }
            if (file.size > MAX_BYTES) {
                clearComposerImage();
                showNote('That photo is over 4 MB. Please pick a smaller one.');
                return;
            }
            preview.querySelector('img').src = URL.createObjectURL(file);
            preview.hidden = false;
        });
    }

    function showNote(text) {
        if (!note) { return; }
        note.textContent = text;
        note.hidden = false;
    }

    // Character counter
    var body = byId('composer-body');
    var counter = document.querySelector('.char-count');
    if (body && counter) {
        var update = function () { counter.textContent = body.value.length + '/' + body.maxLength; };
        body.addEventListener('input', update);
        update();
    }

    /* ---------- Auto-growing textareas ---------- */
    function autosize(el) {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight + 2, 420) + 'px';
    }

    document.addEventListener('input', function (e) {
        if (e.target.classList && e.target.classList.contains('js-autosize')) { autosize(e.target); }
    });

    var autos = document.querySelectorAll('.composer .js-autosize');
    for (var i = 0; i < autos.length; i++) { autosize(autos[i]); }

    /* ---------- Helpers ---------- */
    function copyText(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }
        return new Promise(function (resolve, reject) {
            var area = document.createElement('textarea');
            area.value = text;
            area.setAttribute('readonly', '');
            area.style.cssText = 'position:fixed;opacity:0;top:0;left:0';
            document.body.appendChild(area);
            area.select();
            var ok = false;
            try { ok = document.execCommand('copy'); } catch (err) { ok = false; }
            document.body.removeChild(area);
            ok ? resolve() : reject();
        });
    }

    function flash(button, label, text) {
        if (!label) { return; }
        var original = label.textContent;
        label.textContent = text;
        button.classList.add('is-done');
        setTimeout(function () {
            label.textContent = original;
            button.classList.remove('is-done');
        }, 1600);
    }

    /* ---------- Announcements: dismiss and remember ---------- */
    var seenKey = 'afterclass.dismissedAnnouncements';

    function readDismissed() {
        try { return JSON.parse(window.localStorage.getItem(seenKey)) || []; } catch (err) { return []; }
    }

    var dismissed = readDismissed();
    var banners = document.querySelectorAll('[data-announcement]');
    for (var b = 0; b < banners.length; b++) {
        if (dismissed.indexOf(banners[b].getAttribute('data-announcement')) !== -1) { banners[b].hidden = true; }
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-dismiss-announcement]');
        if (!btn) { return; }
        var id = btn.getAttribute('data-dismiss-announcement');
        var list = readDismissed();
        if (list.indexOf(id) === -1) { list.push(id); }
        try { window.localStorage.setItem(seenKey, JSON.stringify(list)); } catch (err) { /* private mode */ }
        var box = btn.closest('[data-announcement]');
        if (box) { box.hidden = true; }
    });

    /* ---------- Fade out the "saved" toast ---------- */
    var toast = document.querySelector('.toast');
    if (toast) {
        setTimeout(function () {
            toast.classList.add('is-fading');
            setTimeout(function () { toast.hidden = true; }, 450);
        }, 4500);
    }
})();
