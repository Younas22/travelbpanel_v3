{{-- Admin-wide confirmation popup (Nova). Replaces the browser's native confirm() everywhere:
     the click / submit / toggle that asked is put on hold, the popup is shown, and if the admin
     confirms, the same action is replayed with confirm() answering "yes". Delete forms that had
     no confirmation at all (DELETE method) are asked too. --}}
<div class="tbp-confirm" id="tbpConfirm" hidden role="dialog" aria-modal="true" aria-labelledby="tbpConfirmTitle">
    <div class="tbp-confirm-box">
        <button type="button" class="tbp-confirm-x" data-confirm-cancel aria-label="Close"><i class="bi bi-x-lg"></i></button>
        <span class="tbp-confirm-icon"><i class="bi bi-trash3"></i></span>
        <h2 id="tbpConfirmTitle">Delete record?</h2>
        <p id="tbpConfirmText">Are you sure you want to delete this record? This action cannot be undone.</p>
        <div class="tbp-confirm-actions">
            <button type="button" class="tbp-confirm-btn" data-confirm-cancel>Cancel</button>
            <button type="button" class="tbp-confirm-btn is-danger" data-confirm-ok>Delete</button>
        </div>
    </div>
</div>
<script>
(function () {
    var box = document.getElementById('tbpConfirm');
    var title = document.getElementById('tbpConfirmTitle'), text = document.getElementById('tbpConfirmText');
    var okBtn = box.querySelector('[data-confirm-ok]');
    var last = null;          // the user event that is asking for confirmation
    var bypass = false;       // true while replaying a confirmed action
    var pending = null;       // replay function for the action on hold

    // remember which interaction is happening (capture phase runs before any page handler)
    ['click', 'submit', 'change'].forEach(function (type) {
        document.addEventListener(type, function (e) {
            last = { type: type, target: e.target, checked: e.target && e.target.type === 'checkbox' ? e.target.checked : null };
        }, true);
    });

    function isDelete(msg, el) {
        var s = (msg || '') + ' ' + (el ? (el.getAttribute('title') || '') + ' ' + (el.textContent || '') + ' ' + (el.className || '') : '');
        return /delet|remove|trash|destroy/i.test(s);
    }

    function open(msg, el, replay) {
        var del = isDelete(msg, el);
        title.textContent = del ? 'Delete record?' : 'Please confirm';
        text.textContent = msg || 'Are you sure you want to delete this record? This action cannot be undone.';
        okBtn.textContent = del ? 'Delete' : 'Confirm';
        box.classList.toggle('is-delete', del);
        pending = replay;
        box.hidden = false;
        okBtn.focus();
    }
    function close() { box.hidden = true; pending = null; }

    // window.confirm() → our popup. Returns false now; the action is replayed on "Delete/Confirm".
    var nativeConfirm = window.confirm.bind(window);
    window.confirm = function (msg) {
        if (bypass) return true;
        if (!last) return nativeConfirm(msg);
        var ev = last, t = ev.target;
        open(msg, t && t.closest ? t.closest('button, a, form, input, label') : null, function () {
            if (ev.type === 'submit') {
                var form = t.closest('form') || t;
                form.requestSubmit ? form.requestSubmit() : form.submit();
            } else if (ev.type === 'change' && ev.checked !== null) {
                t.checked = ev.checked; t.dispatchEvent(new Event('change', { bubbles: true }));
            } else {
                t.click();
            }
        });
        return false;
    };

    okBtn.addEventListener('click', function () {
        var run = pending; close();
        if (!run) return;
        bypass = true;
        try { run(); } finally { setTimeout(function () { bypass = false; }, 0); }
    });
    box.querySelectorAll('[data-confirm-cancel]').forEach(function (b) { b.addEventListener('click', close); });
    box.addEventListener('click', function (e) { if (e.target === box) close(); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && !box.hidden) close(); });

    // DELETE forms with no confirmation of their own still get asked
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (bypass || !(form instanceof HTMLFormElement)) return;
        var method = form.querySelector('input[name="_method"]');
        if (!method || method.value.toUpperCase() !== 'DELETE') return;
        if ((form.getAttribute('onsubmit') || '').indexOf('confirm(') !== -1) return; // the onsubmit will ask
        e.preventDefault(); e.stopImmediatePropagation();
        open('Are you sure you want to delete this record? This action cannot be undone.', form, function () { form.submit(); });
    }, true);
})();
</script>
