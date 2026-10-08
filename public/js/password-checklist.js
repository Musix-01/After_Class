/* After Class – live password checklist for the register page.
   Looks for <input name="psw"> (and optionally name="psw_confirmation"), and shows the
   requirements the moment the password box is focused, ticking each one as it is met.
   The server-side validation in RegisterController stays as the real check. */
(function () {
    'use strict';

    var input = document.querySelector('input[name="psw"], input#psw');
    if (!input) { return; }

    var confirmInput = document.querySelector('input[name="psw_confirmation"], input#psw_confirmation');

    // Keep these in step with the 'psw' rules in RegisterController.
    var rules = [
        { id: 'length',  label: 'At least 8 characters',                 test: function (v) { return v.length >= 8; } },
        { id: 'capital', label: 'One capital letter (A to Z)',           test: function (v) { return /[A-Z]/.test(v); } },
        { id: 'special', label: 'One special character (! @ # $ % & *)', test: function (v) { return /[^A-Za-z0-9\s]/.test(v); } }
    ];

    /* If the input shares a wrapper with another element (like the eye button), place things under the wrapper. */
    function anchorFor(el) {
        var parent = el.parentElement;
        if (parent && parent !== el.form && parent.children.length > 1 &&
            (parent.querySelector('.toggle-pw, button') || /(group|wrap|field|input)/i.test(parent.className))) {
            return parent;
        }
        return el;
    }

    /* ---------- Styles (self-contained, own dark panel so it reads on any background) ---------- */
    var style = document.createElement('style');
    style.textContent =
        '.pw-checklist{list-style:none;margin:10px 0 4px;padding:12px 14px;text-align:left;' +
        'background:rgba(13,27,42,.6);border:1px solid rgba(255,255,255,.16);border-radius:12px;' +
        'font:600 .88rem/1.4 "Baloo 2","Trebuchet MS",sans-serif;color:#f8f9fa}' +
        '.pw-checklist .pw-title{margin:0 0 6px;color:#fdf3b8;font-size:.82rem}' +
        '.pw-checklist li.pw-item{display:flex;align-items:center;gap:10px;padding:3px 0;' +
        'color:rgba(248,249,250,.72);transition:color .2s ease}' +
        '.pw-checklist .pw-ico{flex:0 0 18px;width:18px;height:18px;display:grid;place-items:center;' +
        'border:1.5px solid rgba(248,249,250,.4);border-radius:50%;font-size:.68rem;font-weight:800;' +
        'color:transparent;transition:background .2s ease,border-color .2s ease,color .2s ease}' +
        '.pw-checklist li.met{color:#cfe8b0}' +
        '.pw-checklist li.met .pw-ico{background:#6f8f4d;border-color:#6f8f4d;color:#fff}' +
        '.pw-checklist .pw-done{margin:8px 0 0;color:#cfe8b0;font-size:.82rem}' +
        '.pw-match{display:flex;align-items:center;gap:10px;margin:8px 0 4px;padding:8px 12px;text-align:left;' +
        'background:rgba(13,27,42,.6);border:1px solid rgba(255,255,255,.16);border-radius:12px;' +
        'font:600 .88rem/1.4 "Baloo 2","Trebuchet MS",sans-serif;color:rgba(248,249,250,.72);transition:color .2s ease}' +
        '.pw-match[hidden]{display:none}' +
        '.pw-match .pw-ico{flex:0 0 18px;width:18px;height:18px;display:grid;place-items:center;' +
        'border:1.5px solid rgba(248,249,250,.4);border-radius:50%;font-size:.68rem;font-weight:800;' +
        'color:transparent;transition:background .2s ease,border-color .2s ease,color .2s ease}' +
        '.pw-match.met{color:#cfe8b0}' +
        '.pw-match.met .pw-ico{background:#6f8f4d;border-color:#6f8f4d;color:#fff}';
    document.head.appendChild(style);

    /* ---------- Markup ---------- */
    var list = document.getElementById('pw-checklist');
    var autoPlaced = false;

    if (!list) {
        list = document.createElement('ul');
        list.id = 'pw-checklist';
        autoPlaced = true;
    }

    list.className = 'pw-checklist';
    list.hidden = true;
    list.setAttribute('aria-live', 'polite');
    list.innerHTML = '<li class="pw-title" style="list-style:none">Your password needs:</li>';

    var items = {};
    rules.forEach(function (rule) {
        var li = document.createElement('li');
        li.className = 'pw-item';
        li.innerHTML = '<span class="pw-ico" aria-hidden="true">&#10003;</span><span class="pw-text"></span>';
        li.querySelector('.pw-text').textContent = rule.label;
        list.appendChild(li);
        items[rule.id] = li;
    });

    var done = document.createElement('li');
    done.className = 'pw-done';
    done.style.listStyle = 'none';
    done.hidden = true;
    done.textContent = 'Nice, that is a strong password.';
    list.appendChild(done);

    if (autoPlaced) {
        // Right under the password box (or under its wrapper when the eye button is inside it).
        anchorFor(input).insertAdjacentElement('afterend', list);
    }

    /* ---------- "Passwords Match" line, under the confirm box ---------- */
    var matchLine = null;

    if (confirmInput) {
        matchLine = document.getElementById('pw-match');
        var matchAuto = !matchLine;

        if (matchAuto) { matchLine = document.createElement('p'); matchLine.id = 'pw-match'; }

        matchLine.className = 'pw-match';
        matchLine.hidden = true;
        matchLine.setAttribute('aria-live', 'polite');
        matchLine.innerHTML = '<span class="pw-ico" aria-hidden="true">&#10003;</span><span class="pw-text">Passwords Match</span>';

        if (matchAuto) {
            anchorFor(confirmInput).insertAdjacentElement('afterend', matchLine);
        }
    }

    /* ---------- Behaviour ---------- */
    function update() {
        var value = input.value;
        var allMet = true;

        rules.forEach(function (rule) {
            var ok = rule.test(value);
            items[rule.id].classList.toggle('met', ok);
            if (!ok) { allMet = false; }
        });

        done.hidden = !allMet;

        if (matchLine) {
            matchLine.classList.toggle('met', value.length > 0 && value === confirmInput.value);
        }
    }

    function show() { list.hidden = false; update(); }

    function showMatch() {
        if (matchLine) { matchLine.hidden = false; }
        update();
    }

    input.addEventListener('focus', show);
    input.addEventListener('input', function () { show(); });
    input.addEventListener('change', update);

    if (confirmInput) {
        confirmInput.addEventListener('focus', showMatch);
        confirmInput.addEventListener('input', showMatch);
        confirmInput.addEventListener('change', update);
    }

    // The browser may autofill or restore a value before the user touches the box.
    if (input.value) { show(); }
    if (confirmInput && confirmInput.value) { showMatch(); }
})();