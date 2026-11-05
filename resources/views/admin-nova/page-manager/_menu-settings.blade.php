{{-- Settings of the selected menu item (PageManagerController@updateMenu). Its page is
     never created or deleted from here. Expects: $menu, $categories, $topItems, $allPages, $languages. --}}
@php
    $pmLink = old('link_type', $menu->page_id ? 'page' : ($menu->url ? 'url' : 'dropdown'));
    $pmCategory = old('category', $menu->category);
    $pmHasKids = $menu->children()->exists();
@endphp
<form action="{{ route('admin.page-manager.menus.update', $menu) }}" method="POST" id="menuForm"
      class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
    @csrf
    @method('PUT')
    <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
        <h3 class="pm-section-title mb-0"><i class="bi bi-menu-button-wide"></i> Menu settings</h3>
        <button type="submit" class="pg-btn-nova pg-btn-primary px-4 py-2 text-xs font-semibold"><i class="bi bi-check2"></i> Save menu</button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="pg-label" for="menu_name">Menu title <span class="pg-required">*</span></label>
            <input type="text" name="name" id="menu_name" class="pg-input @error('name') is-invalid @enderror" value="{{ old('name', $menu->name) }}" required maxlength="255">
            @error('name') <p class="pg-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="pg-label" for="menu_category">Menu location</label>
            <select name="category" id="menu_category" class="pg-input" data-parent-filter="#menu_parent">
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ $pmCategory === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="pg-label" for="menu_parent">Parent (dropdown)</label>
            <select name="parent_id" id="menu_parent" class="pg-input @error('parent_id') is-invalid @enderror" {{ $pmHasKids ? 'disabled' : '' }}>
                <option value="">None — top level</option>
                @foreach($topItems as $topCategory => $tops)
                    @foreach($tops->where('id', '!=', $menu->id) as $top)
                        <option value="{{ $top->id }}" data-category="{{ $topCategory }}" {{ (string) old('parent_id', $menu->parent_id) === (string) $top->id ? 'selected' : '' }}>{{ $top->name }}</option>
                    @endforeach
                @endforeach
            </select>
            @if($pmHasKids)
                <input type="hidden" name="parent_id" value="">
                <p class="pg-help">This item has sub-items, so it stays at the top level.</p>
            @endif
            @error('parent_id') <p class="pg-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="pg-label">Link</label>
            <div class="flex flex-wrap gap-4 py-2">
                @foreach(['page' => 'Page', 'url' => 'Custom URL', 'dropdown' => 'No link (dropdown)'] as $type => $typeLabel)
                    <label class="flex items-center gap-2 text-xs font-semibold text-novatext">
                        <input type="radio" name="link_type" value="{{ $type }}" {{ $pmLink === $type ? 'checked' : '' }}> {{ $typeLabel }}
                    </label>
                @endforeach
            </div>
        </div>
        <div data-link-pane="page" {{ $pmLink === 'page' ? '' : 'hidden' }} class="md:col-span-2">
            <label class="pg-label" for="menu_page">Linked page</label>
            <select name="page_id" id="menu_page" class="pg-input @error('page_id') is-invalid @enderror">
                <option value="">Choose a page…</option>
                @foreach($allPages as $pg)
                    <option value="{{ $pg->id }}" {{ (string) old('page_id', $menu->page_id) === (string) $pg->id ? 'selected' : '' }}>{{ $pg->name }} — /page/{{ $pg->slug }} ({{ $pg->status }})</option>
                @endforeach
            </select>
            <p class="pg-help">Switching to another page or link keeps the current page (it moves to "Pages not in a menu").</p>
            @error('page_id') <p class="pg-error">Choose a page.</p> @enderror
        </div>
        <div data-link-pane="url" {{ $pmLink === 'url' ? '' : 'hidden' }} class="md:col-span-2">
            <label class="pg-label" for="menu_url">URL</label>
            <input type="text" name="url" id="menu_url" class="pg-input @error('url') is-invalid @enderror" value="{{ old('url', $menu->page_id ? '' : $menu->url) }}" placeholder="/blog or https://example.com">
            @error('url') <p class="pg-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="pg-label" for="menu_icon">Icon <span class="text-novamuted">(optional)</span></label>
            <input type="text" name="icon" id="menu_icon" class="pg-input" value="{{ old('icon', $menu->icon) }}" placeholder="e.g. fas fa-plane or bi bi-house">
        </div>
        <div>
            <label class="pg-label" for="menu_target">Opens in</label>
            <select name="target" id="menu_target" class="pg-input">
                <option value="_self" {{ old('target', $menu->target) === '_self' ? 'selected' : '' }}>Same tab</option>
                <option value="_blank" {{ old('target', $menu->target) === '_blank' ? 'selected' : '' }}>New tab</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="flex items-center gap-3">
                <span class="pg-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $menu->is_active) ? 'checked' : '' }}>
                    <span class="pg-slider"></span>
                </span>
                <span class="text-xs font-semibold text-novatext">Visible on the website</span>
            </label>
        </div>
    </div>

    {{-- menu text per language; empty = the page title in that language (or the menu title) --}}
    <details class="pm-seo mt-4" {{ $errors->has('translations.*') ? 'open' : '' }}>
        <summary><i class="bi bi-translate"></i> Menu name per language <span class="text-novamuted">(optional — empty uses the page title)</span></summary>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 pt-3">
            @foreach($languages as $language)
                <div>
                    <label class="pg-label" for="mt_{{ $language->code }}">{{ $language->native_name ?: $language->name }} <span class="text-novamuted">{{ strtoupper($language->code) }}</span></label>
                    <input type="text" name="translations[{{ $language->code }}]" id="mt_{{ $language->code }}" dir="{{ $language->direction }}" maxlength="100"
                           class="pg-input" value="{{ old('translations.' . $language->code, $menu->translations[$language->code] ?? '') }}"
                           placeholder="{{ $menu->page?->translation('title', $language->code, false) ?: $menu->name }}">
                </div>
            @endforeach
        </div>
    </details>
</form>
