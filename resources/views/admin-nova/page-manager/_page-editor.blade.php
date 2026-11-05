{{-- Page editor: basic information, content + SEO per language (tabs with translation
     status), header image. Saved by PageManagerController@updatePage. Expects: $page,
     $menu (or null), $languages, $translationStatus. --}}
@php
    $pmDefault = $languages->firstWhere('is_default', true) ?? $languages->first();
    $pmDone = collect($translationStatus)->filter(fn ($s) => $s === 'complete')->count();
    $pmHeaderImage = $page->header_image_url;
    $pmDefaultCover = getSettingImage('cover_image', 'homepage');
    $pmStatusText = ['complete' => 'Translated', 'partial' => 'Partly translated', 'missing' => 'Not translated'];
@endphp
<form action="{{ route('admin.page-manager.pages.update', $page) }}" method="POST" id="pageForm" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @method('PATCH')
    @if($menu)
        <input type="hidden" name="menu" value="{{ $menu->id }}">
    @endif
    <input type="hidden" name="status" id="status" value="{{ old('status', $page->status) }}">

    {{-- ===== TOP BAR ===== --}}
    <div class="pm-bar tt-card bg-white rounded-2xl border border-novaborder shadow-sm">
        <div class="min-w-0">
            <div class="flex flex-wrap items-center gap-2">
                <h2 class="text-base font-bold text-novatext truncate">{{ $page->name }}</h2>
                <span class="pm-badge is-{{ $page->status }}">{{ ucfirst($page->status) }}</span>
                @if($menu)
                    <span class="pm-badge">{{ $menu->category_label }}{{ $menu->parent ? ' › ' . $menu->parent->name : '' }}</span>
                @endif
            </div>
            <a href="{{ url('/page/' . $page->slug) }}" target="_blank" rel="noopener" class="pm-url">
                {{ url('/page/' . $page->slug) }} <i class="bi bi-box-arrow-up-right"></i>
            </a>
            @if($page->status !== 'published')
                <p class="pg-help">Not live yet: visitors see this page (and its menu link) after you publish it.</p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <button type="submit" class="pg-btn-nova px-4 py-2.5 text-xs font-semibold" data-set-status="draft">
                <i class="bi bi-floppy"></i> Save draft
            </button>
            <button type="submit" class="pg-btn-nova pg-btn-primary px-4 py-2.5 text-xs font-semibold" data-set-status="published">
                <i class="bi bi-check2"></i> {{ $page->status === 'published' ? 'Update' : 'Publish' }}
            </button>
        </div>
    </div>

    {{-- ===== BASIC INFORMATION ===== --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <h3 class="pm-section-title"><i class="bi bi-info-circle"></i> Basic information</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="pg-label" for="name">Page name <span class="pg-required">*</span></label>
                <input type="text" name="name" id="name" class="pg-input @error('name') is-invalid @enderror" value="{{ old('name', $page->name) }}" required maxlength="255">
                <p class="pg-help">Admin name; the heading on the website is the Page Title of each language below.</p>
                @error('name') <p class="pg-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="pg-label" for="slug">Page slug</label>
                <div class="flex items-center gap-2">
                    <span class="text-xs text-novamuted flex-shrink-0">/page/</span>
                    <input type="text" name="slug" id="slug" class="pg-input @error('slug') is-invalid @enderror" value="{{ old('slug', $page->slug) }}">
                    <button type="button" class="pg-icon-btn flex-shrink-0" onclick="generateSlug()" data-tooltip="Make from page name"><i class="bi bi-arrow-repeat"></i></button>
                </div>
                <p class="pg-help">Changing it changes the page address; menu links follow automatically. One slug for all languages.</p>
                @error('slug') <p class="pg-error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="pg-label" for="statusSelect">Status</label>
                <select id="statusSelect" class="pg-input" data-status-select>
                    @foreach(['draft' => 'Draft', 'published' => 'Published', 'private' => 'Private'] as $statusKey => $statusLabel)
                        <option value="{{ $statusKey }}" {{ old('status', $page->status) === $statusKey ? 'selected' : '' }}>{{ $statusLabel }}</option>
                    @endforeach
                </select>
                @error('status') <p class="pg-error">{{ $message }}</p> @enderror
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-3">
                    <span class="pg-switch">
                        <input type="checkbox" name="is_homepage" value="1" {{ old('is_homepage', $page->is_homepage) ? 'checked' : '' }}>
                        <span class="pg-slider"></span>
                    </span>
                    <span class="text-xs font-semibold text-novatext">Set as homepage</span>
                </label>
            </div>
        </div>

        {{-- header image: none = the homepage cover --}}
        <div class="pm-image mt-4">
            <img src="{{ $pmHeaderImage ?: $pmDefaultCover }}" alt="" id="headerImagePreview" data-default="{{ $pmDefaultCover }}"
                 class="{{ ($pmHeaderImage ?: $pmDefaultCover) ? '' : 'hidden' }}">
            <div class="min-w-0 space-y-2">
                <label class="pg-label" for="header_image">Header image</label>
                <p class="pg-help" id="headerImageNote">{{ $pmHeaderImage ? 'Own image' : 'Using the homepage cover' }}</p>
                <input type="file" name="header_image" id="header_image" accept="image/jpeg,image/png,image/webp" class="pg-input @error('header_image') is-invalid @enderror">
                <p class="pg-help">JPG, PNG or WebP up to 4 MB, about 1920 × 600.</p>
                @error('header_image') <p class="pg-error">{{ $message }}</p> @enderror
                @if($pmHeaderImage)
                    <label class="flex items-center gap-3">
                        <span class="pg-switch">
                            <input type="checkbox" name="remove_header_image" id="remove_header_image" value="1">
                            <span class="pg-slider"></span>
                        </span>
                        <span class="text-xs font-semibold text-novatext">Remove and use the homepage cover</span>
                    </label>
                @endif
            </div>
        </div>
    </div>

    {{-- ===== CONTENT & TRANSLATIONS ===== --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-2 mb-3">
            <h3 class="pm-section-title mb-0"><i class="bi bi-translate"></i> Content &amp; translations</h3>
            <span class="pm-progress">{{ $pmDone }} / {{ $languages->count() }} languages translated</span>
        </div>

        <div class="flex flex-wrap items-center gap-2 mb-4" id="languageTabs" role="tablist">
            @foreach($languages as $language)
                @php $pmState = $translationStatus[$language->code] ?? 'missing'; @endphp
                <button class="pg-tab {{ $loop->first ? 'active' : '' }}" id="{{ $language->code }}-tab" data-lang="{{ $language->code }}"
                        data-bs-toggle="tab" data-bs-target="#{{ $language->code }}-content" type="button" role="tab"
                        data-tooltip="{{ $pmStatusText[$pmState] }}{{ $language->is_default ? ' · default language' : '' }}">
                    <span class="pm-dot is-{{ $pmState }}"></span>
                    {{ $language->native_name ?: $language->name }}
                    <span class="opacity-60">{{ strtoupper($language->code) }}</span>
                </button>
            @endforeach
        </div>

        <div class="tab-content">
            @foreach($languages as $language)
                @php
                    $code = $language->code;
                    $pmValue = fn ($field) => old("translations.$code.$field", $page->translation($field, $code, false));
                    $pmState = $translationStatus[$code] ?? 'missing';
                @endphp
                <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $code }}-content" role="tabpanel">
                    @if($pmState !== 'complete')
                        <p class="pm-note is-{{ $pmState }}">
                            <i class="bi {{ $pmState === 'missing' ? 'bi-exclamation-circle' : 'bi-hourglass-split' }}"></i>
                            {{ $pmState === 'missing' ? 'No ' . $language->name . ' translation yet.' : 'The ' . $language->name . ' translation is missing its title or content.' }}
                            @unless($language->is_default)
                                Visitors using {{ $language->name }} see the {{ $pmDefault?->name }} version until it is added.
                            @endunless
                        </p>
                    @endif

                    <div class="mb-4">
                        <label class="pg-label" for="title_{{ $code }}">Page title ({{ $language->name }})</label>
                        <input type="text" name="translations[{{ $code }}][title]" id="title_{{ $code }}" dir="{{ $language->direction }}" maxlength="255"
                               class="pg-input @error("translations.$code.title") is-invalid @enderror" value="{{ $pmValue('title') }}" placeholder="Heading shown on the page and in the menu">
                        @error("translations.$code.title") <p class="pg-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="pg-label">Page content ({{ $language->name }})</label>
                        <textarea name="translations[{{ $code }}][content]" id="content_{{ $code }}" data-lang="{{ $code }}" class="hidden">{{ $pmValue('content') }}</textarea>
                    </div>

                    <details class="pm-seo" {{ $errors->has("translations.$code.meta_title") || $errors->has("translations.$code.meta_description") ? 'open' : '' }}>
                        <summary><i class="bi bi-search"></i> SEO settings ({{ $language->name }})</summary>
                        <div class="space-y-4 pt-3">
                            <div>
                                <label class="pg-label" for="meta_title_{{ $code }}">Meta title</label>
                                <input type="text" name="translations[{{ $code }}][meta_title]" id="meta_title_{{ $code }}" dir="{{ $language->direction }}" maxlength="255"
                                       class="pg-input @error("translations.$code.meta_title") is-invalid @enderror" value="{{ $pmValue('meta_title') }}">
                                @error("translations.$code.meta_title") <p class="pg-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="pg-label" for="meta_description_{{ $code }}">Meta description</label>
                                <textarea name="translations[{{ $code }}][meta_description]" id="meta_description_{{ $code }}" dir="{{ $language->direction }}" rows="3" maxlength="500"
                                          class="pg-input @error("translations.$code.meta_description") is-invalid @enderror">{{ $pmValue('meta_description') }}</textarea>
                                @error("translations.$code.meta_description") <p class="pg-error">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="pg-label" for="meta_keywords_{{ $code }}">Meta keywords</label>
                                <input type="text" name="translations[{{ $code }}][meta_keywords]" id="meta_keywords_{{ $code }}" dir="{{ $language->direction }}" maxlength="255"
                                       class="pg-input" value="{{ $pmValue('meta_keywords') }}" placeholder="keyword1, keyword2">
                            </div>
                        </div>
                    </details>

                    <div class="flex justify-end mt-4">
                        <button type="submit" name="save_lang" value="{{ $code }}" class="pg-btn-nova px-4 py-2.5 text-xs font-semibold">
                            <i class="bi bi-floppy"></i> Save {{ $language->name }} only
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</form>

{{-- delete the page itself (its menu links go with it) --}}
<form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="pm-danger"
      onsubmit="return confirm('Delete the page “{{ addslashes($page->name) }}” and all its translations?{{ $page->menuItems->isNotEmpty() ? ' Its menu links are removed too.' : '' }}')">
    @csrf
    @method('DELETE')
    <span class="pg-help">Deleting removes the page and every language of it.</span>
    <button type="submit" class="pg-btn-nova px-4 py-2 text-xs font-semibold is-danger"><i class="bi bi-trash3"></i> Delete page</button>
</form>
