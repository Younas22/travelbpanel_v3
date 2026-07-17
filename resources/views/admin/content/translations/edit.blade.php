@extends('admin.layouts.app')

@section('title', 'Edit Translation Group')

@section('content')
<div class="content-area">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">Edit Translation Group</h2>
                <p class="text-muted mb-0">Edit: <strong>{{ ucfirst($group) }}</strong></p>
            </div>
            <div class="col-md-4 text-end">
                <form action="{{ route('admin.content.translations.destroy', $group) }}" 
                      method="POST" class="d-inline"
                      onsubmit="return confirm('Delete entire group?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger">
                        <i class="bi bi-trash"></i> Delete Group
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="card">
        <div class="card-body">
            <form action="{{ route('admin.content.translations.update', $group) }}" method="POST">
                @csrf
                @method('PATCH')

                <!-- Language Tabs -->
                <ul class="nav nav-tabs mb-3" role="tablist">
                    @foreach($languages as $index => $language)
                        <li class="nav-item">
                            <button class="nav-link {{ $index === 0 ? 'active' : '' }}"
                                    id="lang-{{ $language->code }}-tab"
                                    data-bs-toggle="tab"
                                    data-bs-target="#lang-{{ $language->code }}"
                                    type="button">
                                {{ $language->name }} ({{ $language->native_name }})
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content border p-3">
                    @foreach($languages as $index => $language)
                        <div class="tab-pane fade {{ $index === 0 ? 'show active' : '' }}"
                             id="lang-{{ $language->code }}"
                             role="tabpanel">

                            @forelse($translations as $key => $values)
                                <div class="mb-3">
                                    <label class="form-label">
                                        <code>{{ $key }}</code>
                                    </label>
                                    <textarea name="translations[{{ $key }}][{{ $language->code }}]"
                                              class="form-control"
                                              rows="3"
                                              placeholder="Enter {{ $language->name }} translation...">{{ $values[$language->code] ?? '' }}</textarea>
                                </div>
                            @empty
                                <p class="text-muted">No translations to edit</p>
                            @endforelse
                        </div>
                    @endforeach
                </div>

                <!-- Form Actions -->
                <div class="d-flex gap-2 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.content.translations.index', ['group' => $group]) }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-lg"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Add New Key Section -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0">Add New Key</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('admin.content.translations.add-key', $group) }}" method="POST">
                @csrf

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Key Name *</label>
                        <input type="text" 
                               name="key" 
                               class="form-control"
                               placeholder="e.g., new_section_title"
                               pattern="[a-z_]+"
                               required>
                    </div>
                </div>

                <!-- Language Inputs for New Key -->
                <div class="row">
                    @foreach($languages as $language)
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                {{ $language->name }} ({{ $language->native_name }})
                            </label>
                            <textarea name="translations[{{ $language->code }}]"
                                      class="form-control"
                                      rows="2"
                                      placeholder="Enter {{ $language->name }} value"
                                      required></textarea>
                        </div>
                    @endforeach
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-plus-lg"></i> Add Key
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    code {
        padding: 2px 6px;
        background-color: #f0f0f0;
        border-radius: 3px;
        font-size: 13px;
    }
</style>
@endsection