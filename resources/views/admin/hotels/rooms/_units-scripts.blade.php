{{-- Shared by the Room Types and Rooms lists: delete confirmation (names the record) and a small toast. --}}
@push('scripts')
<script>
function tbpUnitsToast(msg, isError) {
    var t = document.createElement('div');
    t.className = 'tbp-units-toast' + (isError ? ' is-error' : '');
    t.setAttribute('role', 'status');
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(function () { t.remove(); }, 3000);
}
document.querySelectorAll('.js-units-delete').forEach(function (form) {
    form.setAttribute('onsubmit', 'return confirm(this.dataset.confirm)');
});
// "more actions" menus float under their button (outside the table's scroll box); one open at a time
document.querySelectorAll('.tbp-units-menu').forEach(function (menu) {
    menu.addEventListener('toggle', function () {
        if (!menu.open) return;
        var r = menu.querySelector('summary').getBoundingClientRect(), list = menu.querySelector('div');
        var below = r.bottom + 6 + list.offsetHeight < innerHeight;
        list.style.left = Math.max(8, r.right - list.offsetWidth) + 'px';
        list.style.top = (below ? r.bottom + 6 : r.top - 6 - list.offsetHeight) + 'px';
    });
});
document.addEventListener('click', function (e) {
    document.querySelectorAll('.tbp-units-menu[open]').forEach(function (m) { if (!m.contains(e.target)) m.open = false; });
});
['scroll', 'resize'].forEach(function (ev) {
    window.addEventListener(ev, function () { document.querySelectorAll('.tbp-units-menu[open]').forEach(function (m) { m.open = false; }); }, true);
});
</script>
@endpush
