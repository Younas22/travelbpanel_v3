{{-- New menu item (PageManagerController@storeMenu). "New blank page" creates the page
     with it and opens the editor; an existing page, a URL or a dropdown create no page.
     Expects: $categories, $topItems, $allPages. --}}
@php
    $pmPresetPage = request('page');
    $pmLink = old('link_type', $pmPresetPage ? 'page' : 'new_page');
    $pmCategory = old('category', request('category', 'header'));
@endphp
<form action="{{ route('admin.page-manager.menus.store') }}" method="POST" id="createForm"
      class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
    @csrf
    <div class="flex flex-wrap items-center justify-between gap-2 mb-1">
        <h2 class="text-base font-bold text-novatext">Add new menu item</h2>
        <a href="{{ route('admin.page-manager.index') }}" class="pg-btn-nova px-4 py-2 text-xs font-semibold">Cancel</a>
    </div>
    <p class="pg-help mb-4">With "New blank page", the page is created together with the menu item and opens in the editor so you can add content and translations right away.</p>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="pg-label" for="name">Menu title <span class="pg-required">*</span></label>
            <input type="text" name="name" id="name" class="pg-input @error('name') is-invalid @enderror" value="{{ old('name') }}" required maxlength="255" autofocus placeholder="e.g. About Us">
            @error('name') <p class="pg-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="pg-label">What does it open?</label>
            <div class="flex flex-wrap gap-x-4 gap-y-2 py-2">
                @foreach(['new_page' => 'New blank page', 'page' => 'Existing page', 'builtin' => 'Built-in page', 'url' => 'Custom URL', 'dropdown' => 'No link (dropdown)'] as $type => $typeLabel)
                    <label class="flex items-center gap-2 text-xs font-semibold text-novatext">
                        <input type="radio" name="link_type" value="{{ $type }}" {{ $pmLink === $type ? 'checked' : '' }}> {{ $typeLabel }}
                    </label>
                @endforeach
            </div>
        </div>

        <div data-link-pane="new_page" {{ $pmLink === 'new_page' ? '' : 'hidden' }} class="md:col-span-2">
            <label class="pg-label" for="slug">Page slug</label>
            <div class="flex items-center gap-2">
                <span class="text-xs text-novamuted flex-shrink-0">{{ url('/page') }}/</span>
                <input type="text" name="slug" id="slug" class="pg-input @error('slug') is-invalid @enderror" value="{{ old('slug') }}" placeholder="made from the menu title">
            </div>
            <p class="pg-help">The page starts as a draft and is hidden from the website until you publish it.</p>
            @error('slug') <p class="pg-error">{{ $message }}</p> @enderror
        </div>
        <div data-link-pane="page" {{ $pmLink === 'page' ? '' : 'hidden' }} class="md:col-span-2">
            <label class="pg-label" for="page_id">Page</label>
            <select name="page_id" id="page_id" class="pg-input @error('page_id') is-invalid @enderror">
                <option value="">Choose a page…</option>
                @foreach($allPages as $pg)
                    <option value="{{ $pg->id }}" data-name="{{ $pg->name }}" {{ (string) old('page_id', $pmPresetPage) === (string) $pg->id ? 'selected' : '' }}>{{ $pg->name }} — /page/{{ $pg->slug }} ({{ $pg->status }})</option>
                @endforeach
            </select>
            @error('page_id') <p class="pg-error">Choose a page.</p> @enderror
        </div>
        @include('admin-nova.page-manager._builtin-select', ['pmLinkType' => $pmLink, 'pmBuiltInSelected' => null, 'pmFormKey' => 'create'])
        <div data-link-pane="url" {{ $pmLink === 'url' ? '' : 'hidden' }} class="md:col-span-2">
            <label class="pg-label" for="url">URL</label>
            <input type="text" name="url" id="url" class="pg-input @error('url') is-invalid @enderror" value="{{ old('url') }}" placeholder="/blog or https://example.com">
            <p class="pg-help">Another website (https://…) or any other address on this site. No page is made.</p>
            @error('url') <p class="pg-error">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="pg-label" for="create_category">Menu location</label>
            <select name="category" id="create_category" class="pg-input" data-parent-filter="#create_parent">
                @foreach($categories as $key => $label)
                    <option value="{{ $key }}" {{ $pmCategory === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="pg-label" for="create_parent">Parent (dropdown)</label>
            <select name="parent_id" id="create_parent" class="pg-input @error('parent_id') is-invalid @enderror">
                <option value="">None — top level</option>
                @foreach($topItems as $topCategory => $tops)
                    @foreach($tops as $top)
                        <option value="{{ $top->id }}" data-category="{{ $topCategory }}" {{ (string) old('parent_id', request('parent')) === (string) $top->id ? 'selected' : '' }}>{{ $top->name }}</option>
                    @endforeach
                @endforeach
            </select>
            @error('parent_id') <p class="pg-error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="pg-label" for="icon">Icon <span class="text-novamuted">(optional)</span></label>
            <input type="text" name="icon" id="icon" class="pg-input" value="{{ old('icon') }}" placeholder="e.g. fas fa-plane or bi bi-house">
        </div>
        <div>
            <label class="pg-label" for="target">Opens in</label>
            <select name="target" id="target" class="pg-input">
                <option value="_self">Same tab</option>
                <option value="_blank" {{ old('target') === '_blank' ? 'selected' : '' }}>New tab</option>
            </select>
        </div>
        <div class="md:col-span-2">
            <label class="flex items-center gap-3">
                <span class="pg-switch">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }}>
                    <span class="pg-slider"></span>
                </span>
                <span class="text-xs font-semibold text-novatext">Visible on the website (a draft page stays hidden until published)</span>
            </label>
        </div>
    </div>

    <div class="flex justify-end mt-5">
        <button type="submit" class="pg-btn-nova pg-btn-primary px-5 py-2.5 text-sm font-semibold"><i class="bi bi-check2"></i> Save and open editor</button>
    </div>
</form>
