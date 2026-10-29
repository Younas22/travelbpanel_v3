{{-- Language Management card. Expects: $languages (ordered by sort_order).
     Shared by the Languages page and the Email Settings page. --}}
<section class="tbp-email-settings-languages">
    <header class="tbp-email-settings-languages-head">
        <div>
            <h2>Language Management</h2>
            <p>Manage supported languages for your application</p>
        </div>
        <div class="tbp-email-settings-head-actions">
            <a href="{{ route('admin.content.translations.index') }}" class="tbp-email-settings-btn is-outline"><i class="bi bi-translate"></i> Manage Translations</a>
            <a href="{{ route('admin.settings.languages.create') }}" class="tbp-email-settings-btn"><i class="bi bi-plus-lg"></i> Add Language</a>
        </div>
    </header>

    <div class="tbp-email-settings-table-wrap">
        <table class="tbp-email-settings-table">
            <thead>
                <tr><th>Order</th><th>Code</th><th>Name</th><th>Native Name</th><th>Direction</th><th>Status</th><th>Default</th><th class="is-right">Actions</th></tr>
            </thead>
            <tbody>
            @forelse($languages as $language)
                <tr>
                    <td><span class="tbp-email-settings-order">{{ $language->sort_order }}</span></td>
                    <td><strong>{{ $language->code }}</strong></td>
                    <td>{{ $language->name }}</td>
                    <td>{{ $language->native_name }}</td>
                    <td><span class="tbp-email-settings-dir">{{ strtoupper($language->direction) }}</span></td>
                    <td>
                        <form action="{{ route('admin.settings.languages.toggle-status', $language) }}" method="POST">
                            @csrf @method('PATCH')
                            <label class="tbp-email-settings-switch" title="{{ $language->status ? 'Active' : 'Inactive' }}">
                                <input type="checkbox" onchange="this.form.submit()" @checked($language->status) aria-label="Toggle {{ $language->name }}">
                                <i aria-hidden="true"></i>
                            </label>
                        </form>
                    </td>
                    <td>
                        <form action="{{ route('admin.settings.languages.toggle-default', $language) }}" method="POST">
                            @csrf @method('PATCH')
                            <button type="submit" class="tbp-email-settings-star {{ $language->is_default ? 'is-default' : '' }}" title="{{ $language->is_default ? 'Default language' : 'Set as default' }}">
                                <i class="bi bi-star{{ $language->is_default ? '-fill' : '' }}"></i>
                            </button>
                        </form>
                    </td>
                    <td class="is-right">
                        <div class="tbp-email-settings-row-actions">
                            <a href="{{ route('admin.settings.languages.edit', $language) }}" class="tbp-email-settings-icon-btn" title="Edit"><i class="bi bi-pencil"></i></a>
                            <form action="{{ route('admin.settings.languages.destroy', $language) }}" method="POST" onsubmit="return confirm('Delete this language?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="tbp-email-settings-icon-btn" title="Delete"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="8" class="tbp-email-settings-empty">No languages found. Add one to get started.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
