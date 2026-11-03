{{-- Title, content and SEO per language: one tab for each active language (admin Languages) --}}
<div class="form-group">
    <label class="form-label">
        <i class="fas fa-language me-1"></i>Content & SEO (Multi-language)
    </label>

    <ul class="nav nav-tabs" id="languageTabs" role="tablist">
        @foreach($languages as $language)
            <li class="nav-item" role="presentation">
                <button class="nav-link {{ $loop->first ? 'active' : '' }}" id="{{ $language->code }}-tab" data-lang="{{ $language->code }}"
                        data-bs-toggle="tab" data-bs-target="#{{ $language->code }}-content" type="button" role="tab">
                    {{ $language->native_name ?: $language->name }}
                    <small class="text-muted ms-1">{{ strtoupper($language->code) }}</small>
                    @if($language->is_default)
                        <i class="fas fa-star text-warning ms-1" title="Default language"></i>
                    @endif
                </button>
            </li>
        @endforeach
    </ul>

    <div class="tab-content border p-4 bg-white" id="languageTabsContent">
        @foreach($languages as $language)
            @php
                $code = $language->code;
                $value = fn ($field) => old("translations.$code.$field", isset($page) ? $page->translation($field, $code, false) : '');
            @endphp
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="{{ $code }}-content" role="tabpanel">
                <h5 class="mb-3">{{ $language->name }} Content</h5>
                @if(!$language->is_default)
                    <p class="text-muted small">Leave empty to show the {{ $languages->firstWhere('is_default', true)?->name ?? 'default' }} version in this language.</p>
                @endif

                <div class="form-group">
                    <label for="title_{{ $code }}" class="form-label">Page Title</label>
                    <input type="text" name="translations[{{ $code }}][title]" id="title_{{ $code }}" dir="{{ $language->direction }}"
                           class="form-control @error("translations.$code.title") is-invalid @enderror" value="{{ $value('title') }}" placeholder="Heading shown on the page">
                    @error("translations.$code.title")
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="content_{{ $code }}" class="form-label">Page Content</label>
                    <textarea name="translations[{{ $code }}][content]" id="content_{{ $code }}" data-lang="{{ $code }}"
                              class="form-control ckeditor d-none">{{ $value('content') }}</textarea>
                </div>

                <div class="form-group">
                    <label for="meta_title_{{ $code }}" class="form-label">Meta Title</label>
                    <input type="text" name="translations[{{ $code }}][meta_title]" id="meta_title_{{ $code }}" dir="{{ $language->direction }}"
                           class="form-control @error("translations.$code.meta_title") is-invalid @enderror" value="{{ $value('meta_title') }}" placeholder="SEO optimized title">
                    @error("translations.$code.meta_title")
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="meta_description_{{ $code }}" class="form-label">Meta Description</label>
                    <textarea name="translations[{{ $code }}][meta_description]" id="meta_description_{{ $code }}" dir="{{ $language->direction }}"
                              class="form-control @error("translations.$code.meta_description") is-invalid @enderror" rows="3" placeholder="Brief description for search engines">{{ $value('meta_description') }}</textarea>
                    @error("translations.$code.meta_description")
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="meta_keywords_{{ $code }}" class="form-label">Meta Keywords</label>
                    <input type="text" name="translations[{{ $code }}][meta_keywords]" id="meta_keywords_{{ $code }}" dir="{{ $language->direction }}"
                           class="form-control @error("translations.$code.meta_keywords") is-invalid @enderror" value="{{ $value('meta_keywords') }}" placeholder="keyword1, keyword2, keyword3">
                    @error("translations.$code.meta_keywords")
                        <span class="invalid-feedback" role="alert"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
            </div>
        @endforeach
    </div>
</div>
