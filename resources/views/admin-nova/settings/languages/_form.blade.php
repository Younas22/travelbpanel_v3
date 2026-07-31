{{-- Expects: $language (Language|null — null on create, the model on edit). --}}
<div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 max-w-3xl">
    <form action="{{ $language ? route('admin.settings.languages.update', $language) : route('admin.settings.languages.store') }}" method="POST">
        @csrf
        @if($language)
            @method('PATCH')
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="lg-label">Language Code <span class="lg-required">*</span></label>
                <input type="text" name="code" class="lg-input @error('code') is-invalid @enderror"
                       placeholder="e.g., en, nl, ar, ur" value="{{ old('code', $language->code ?? '') }}" maxlength="10" required>
                <p class="lg-help">ISO 639-1 code (2-letter)</p>
                @error('code') <p class="lg-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="lg-label">Language Name <span class="lg-required">*</span></label>
                <input type="text" name="name" class="lg-input @error('name') is-invalid @enderror"
                       placeholder="e.g., English, Dutch, Arabic" value="{{ old('name', $language->name ?? '') }}" maxlength="100" required>
                <p class="lg-help">English name of the language</p>
                @error('name') <p class="lg-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
            <div class="sm:col-span-1">
                <label class="lg-label">Native Name</label>
                <input type="text" name="native_name" class="lg-input @error('native_name') is-invalid @enderror"
                       placeholder="e.g., Nederlands, العربية" value="{{ old('native_name', $language->native_name ?? '') }}" maxlength="100">
                <p class="lg-help">Native language name</p>
                @error('native_name') <p class="lg-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="lg-label">Direction <span class="lg-required">*</span></label>
                <select name="direction" class="lg-input @error('direction') is-invalid @enderror" required>
                    <option value="ltr" {{ old('direction', $language->direction ?? 'ltr') == 'ltr' ? 'selected' : '' }}>LTR (Left to Right)</option>
                    <option value="rtl" {{ old('direction', $language->direction ?? '') == 'rtl' ? 'selected' : '' }}>RTL (Right to Left)</option>
                </select>
                <p class="lg-help">Text direction</p>
                @error('direction') <p class="lg-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="lg-label">Sort Order</label>
                <input type="number" name="sort_order" class="lg-input @error('sort_order') is-invalid @enderror"
                       value="{{ old('sort_order', $language->sort_order ?? 0) }}" min="0">
                <p class="lg-help">Display order</p>
                @error('sort_order') <p class="lg-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            <div>
                <label class="lg-label">Status <span class="lg-required">*</span></label>
                <select name="status" class="lg-input @error('status') is-invalid @enderror" required>
                    <option value="1" {{ old('status', $language->status ?? '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('status', $language->status ?? '1') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
                @error('status') <p class="lg-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="lg-label">Default Language</label>
                <label class="flex items-center gap-3 mt-1">
                    <span class="lg-switch">
                        <input type="checkbox" name="is_default" value="1" {{ old('is_default', $language->is_default ?? false) ? 'checked' : '' }}>
                        <span class="lg-slider"></span>
                    </span>
                    <span class="text-xs text-novatext">Set as default language</span>
                </label>
                <p class="lg-help">Only one language can be set as default</p>
                @error('is_default') <p class="lg-error">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex items-center gap-2 pt-4 border-t border-novaborder">
            <button type="submit" class="lg-btn-nova lg-btn-primary px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                {{ $language ? 'Update Language' : 'Add Language' }}
            </button>
            <a href="{{ route('admin.settings.languages.index') }}" class="lg-btn-nova px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                Cancel
            </a>
        </div>
    </form>
</div>
