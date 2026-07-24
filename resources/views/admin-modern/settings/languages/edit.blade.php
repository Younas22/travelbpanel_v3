@extends('admin-modern.layouts.app')

@section('title', 'Edit Language')

@section('content')
<div class="content-area">
    <!-- Page Header -->
    <div class="page-header">
        <h2 class="mb-1">Edit Language</h2>
        <p class="text-muted mb-0">Update language information</p>
    </div>

    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.settings.languages.update', $language) }}" method="POST">
                @csrf
                @method('PATCH')

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Language Code *</label>
                        <input type="text"
                               name="code"
                               class="form-control @error('code') is-invalid @enderror"
                               placeholder="e.g., en, nl, ar, ur"
                               value="{{ old('code', $language->code) }}"
                               maxlength="10"
                               required>
                        <small class="form-text text-muted">ISO 639-1 code (2-letter)</small>
                        @error('code')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label">Language Name *</label>
                        <input type="text"
                               name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               placeholder="e.g., English, Dutch, Arabic"
                               value="{{ old('name', $language->name) }}"
                               maxlength="100"
                               required>
                        <small class="form-text text-muted">English name of the language</small>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Native Name</label>
                        <input type="text"
                               name="native_name"
                               class="form-control @error('native_name') is-invalid @enderror"
                               placeholder="e.g., English, Nederlands, العربية"
                               value="{{ old('native_name', $language->native_name) }}"
                               maxlength="100">
                        <small class="form-text text-muted">Native language name</small>
                        @error('native_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Direction *</label>
                        <select name="direction" class="form-select @error('direction') is-invalid @enderror" required>
                            <option value="ltr" {{ old('direction', $language->direction) == 'ltr' ? 'selected' : '' }}>LTR (Left to Right)</option>
                            <option value="rtl" {{ old('direction', $language->direction) == 'rtl' ? 'selected' : '' }}>RTL (Right to Left)</option>
                        </select>
                        <small class="form-text text-muted">Text direction</small>
                        @error('direction')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-3 mb-3">
                        <label class="form-label">Sort Order</label>
                        <input type="number"
                               name="sort_order"
                               class="form-control @error('sort_order') is-invalid @enderror"
                               value="{{ old('sort_order', $language->sort_order) }}"
                               min="0">
                        <small class="form-text text-muted">Display order</small>
                        @error('sort_order')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Status *</label>
                        <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                            <option value="1" {{ old('status', $language->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $language->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label d-block">Default Language</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input"
                                   type="checkbox"
                                   name="is_default"
                                   id="is_default"
                                   value="1"
                                   {{ old('is_default', $language->is_default) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_default">
                                Set as default language
                            </label>
                        </div>
                        <small class="form-text text-muted">Only one language can be set as default</small>
                        @error('is_default')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Update Language
                    </button>
                    <a href="{{ route('admin.settings.languages.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
