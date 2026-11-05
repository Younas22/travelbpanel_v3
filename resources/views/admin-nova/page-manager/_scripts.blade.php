{{-- Scripts of the Page & Menu Manager: CKEditor per language (same setup and image
     upload as before), tabs, draft / publish buttons, slug, header image preview, link
     type panes, parent list per location, tree search and drag-to-reorder. --}}
@push('scripts')
<script src="https://cdn.ckeditor.com/ckeditor5/39.0.1/classic/ckeditor.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<script>
(function () {
    const editors = {};

    // ---------- CKEditor (one per language, created when its tab first opens) ----------
    class UploadAdapter {
        constructor(loader) { this.loader = loader; }
        upload() {
            return this.loader.file.then(file => new Promise((resolve, reject) => {
                const xhr = this.xhr = new XMLHttpRequest();
                xhr.open('POST', '{{ route("admin.pages.upload-image") }}', true);
                xhr.setRequestHeader('X-CSRF-TOKEN', '{{ csrf_token() }}');
                xhr.responseType = 'json';
                xhr.addEventListener('error', () => reject(`Couldn't upload file: ${file.name}.`));
                xhr.addEventListener('abort', () => reject());
                xhr.addEventListener('load', () => {
                    const res = xhr.response;
                    if (!res || xhr.status !== 200 || !res.url) { return reject((res && (res.message || res.error?.message)) || `Couldn't upload file: ${file.name}.`); }
                    resolve({ default: res.url });
                });
                const data = new FormData();
                data.append('upload', file);
                xhr.send(data);
            }));
        }
        abort() { if (this.xhr) { this.xhr.abort(); } }
    }
    function uploadPlugin(editor) {
        editor.plugins.get('FileRepository').createUploadAdapter = loader => new UploadAdapter(loader);
    }
    function initEditor(id) {
        const el = document.getElementById(id);
        if (!el || editors[id] || typeof ClassicEditor === 'undefined') { return; }
        editors[id] = true;
        ClassicEditor.create(el, {
            extraPlugins: [uploadPlugin],
            language: { ui: 'en', content: el.dataset.lang || 'en' },
            toolbar: { items: ['heading', '|', 'bold', 'italic', 'underline', '|', 'link', 'imageUpload', '|', 'bulletedList', 'numberedList', '|', 'blockQuote', 'insertTable', '|', 'undo', 'redo'] },
            image: { toolbar: ['imageTextAlternative', 'imageStyle:inline', 'imageStyle:block'] }
        }).then(editor => {
            editors[id] = editor;
            editor.model.document.on('change:data', () => { el.value = editor.getData(); });
        }).catch(error => {
            console.error(`Editor ${id}:`, error);
            el.classList.remove('hidden');
        });
    }
    function syncEditors() {
        Object.entries(editors).forEach(([id, editor]) => {
            if (editor && editor.getData) { document.getElementById(id).value = editor.getData(); }
        });
    }

    document.querySelectorAll('#languageTabs [data-lang]').forEach((tab, index) => {
        const id = 'content_' + tab.dataset.lang;
        if (index === 0) { initEditor(id); }
        tab.addEventListener('shown.bs.tab', () => initEditor(id));
        tab.addEventListener('click', function () {
            document.querySelectorAll('#languageTabs .pg-tab').forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });

    // ---------- page form: status buttons, slug, header image ----------
    const pageForm = document.getElementById('pageForm');
    if (pageForm) {
        const status = document.getElementById('status');
        document.querySelector('[data-status-select]')?.addEventListener('change', function () { status.value = this.value; });
        pageForm.querySelectorAll('[data-set-status]').forEach(btn => {
            btn.addEventListener('click', () => { status.value = btn.dataset.setStatus; });
        });
        pageForm.addEventListener('submit', syncEditors);

        const input = document.getElementById('header_image');
        const preview = document.getElementById('headerImagePreview');
        const note = document.getElementById('headerImageNote');
        const remove = document.getElementById('remove_header_image');
        const original = preview.getAttribute('src');
        input.addEventListener('change', function () {
            if (!this.files[0]) { return; }
            preview.src = URL.createObjectURL(this.files[0]);
            preview.classList.remove('hidden');
            note.textContent = 'New image (saved with the page)';
            if (remove) { remove.checked = false; }
        });
        remove?.addEventListener('change', function () {
            preview.src = this.checked ? preview.dataset.default : original;
            note.textContent = this.checked ? 'Using the homepage cover' : 'Own image';
        });
    }

    // same slug the server makes (Str::slug)
    function slugify(text) {
        return text.normalize('NFKD').replace(/[̀-ͯ]/g, '').toLowerCase()
            .replace(/[^a-z0-9\s-]/g, '').trim().replace(/[\s-]+/g, '-');
    }
    window.generateSlug = function () {
        const slug = document.getElementById('slug');
        if (slug) { slug.value = slugify(document.getElementById('name').value); }
    };

    // new menu item: the slug follows the title until edited by hand; an existing page fills an empty title
    const createForm = document.getElementById('createForm');
    if (createForm) {
        const name = document.getElementById('name');
        const slug = document.getElementById('slug');
        let slugByHand = false;
        name.addEventListener('input', () => { if (!slugByHand) { slug.value = slugify(name.value); } });
        slug.addEventListener('input', () => { slugByHand = slug.value !== ''; });
        document.getElementById('page_id').addEventListener('change', function () {
            const opt = this.options[this.selectedIndex];
            if (!name.value.trim() && opt.dataset.name) { name.value = opt.dataset.name; }
        });
    }

    // ---------- link type panes + parent options of the chosen location ----------
    document.querySelectorAll('#createForm, #menuForm').forEach(form => {
        form.querySelectorAll('input[name="link_type"]').forEach(radio => {
            radio.addEventListener('change', () => {
                form.querySelectorAll('[data-link-pane]').forEach(pane => { pane.hidden = pane.dataset.linkPane !== radio.value; });
            });
        });
        const category = form.querySelector('[data-parent-filter]');
        const parent = category && form.querySelector(category.dataset.parentFilter);
        if (category && parent) {
            const filter = () => {
                parent.querySelectorAll('option[data-category]').forEach(opt => {
                    opt.hidden = opt.dataset.category !== category.value;
                    if (opt.hidden && opt.selected) { parent.value = ''; }
                });
            };
            category.addEventListener('change', filter);
            filter();
        }
    });

    // ---------- tree: search ----------
    document.getElementById('pmSearch')?.addEventListener('input', function () {
        const q = this.value.trim().toLowerCase();
        document.querySelectorAll('.pm-tree .pm-node').forEach(node => {
            const own = (node.dataset.search || '').includes(q);
            const kid = [...node.querySelectorAll('.pm-node')].some(k => (k.dataset.search || '').includes(q));
            node.hidden = q !== '' && !own && !kid;
        });
        if (q) { document.querySelectorAll('.pm-tree details').forEach(d => { d.open = true; }); }
    });

    // ---------- tree: drag to reorder (within the same list), saved right away ----------
    if (typeof Sortable !== 'undefined') {
        document.querySelectorAll('.pm-tree [data-sortable]').forEach(list => {
            Sortable.create(list, {
                handle: '.pm-handle',
                draggable: '.pm-node',
                animation: 150,
                ghostClass: 'pm-ghost',
                onEnd: () => {
                    const items = [...list.children].filter(li => li.dataset.id).map((li, i) => ({
                        id: li.dataset.id, sort_order: i + 1, parent_id: list.dataset.parent || null,
                    }));
                    fetch('{{ route("admin.menus.update-sort-order") }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        body: JSON.stringify({ items }),
                    }).then(r => r.json()).then(res => {
                        if (window.Toastify) { Toastify({ text: res.success ? 'Menu order saved' : 'Order not saved', duration: 2500, gravity: 'top', position: 'right' }).showToast(); }
                    }).catch(() => alert('The new order could not be saved.'));
                },
            });
        });
    }
})();
</script>
@endpush
