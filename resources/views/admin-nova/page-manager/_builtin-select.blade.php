{{-- Built-in page picker. Expects: $pmBuiltInSelected (current address or null). --}}
<div data-link-pane="builtin" {{ $pmLinkType === 'builtin' ? '' : 'hidden' }} class="md:col-span-2">
    <label class="pg-label" for="builtin_url_{{ $pmFormKey }}">Built-in page</label>
    <select name="builtin_url" id="builtin_url_{{ $pmFormKey }}" class="pg-input @error('builtin_url') is-invalid @enderror">
        <option value="">Choose a page…</option>
        @foreach(\App\Models\MenuItem::BUILT_IN_PAGES as $builtUrl => $builtName)
            <option value="{{ $builtUrl }}" data-name="{{ $builtName }}" {{ old('builtin_url', $pmBuiltInSelected) === $builtUrl ? 'selected' : '' }}>{{ $builtName }} — {{ $builtUrl }}</option>
        @endforeach
    </select>
    <p class="pg-help">Part of the website itself: its content is not edited here, only the menu name, place and order.</p>
    @error('builtin_url') <p class="pg-error">Choose a page.</p> @enderror
</div>
