@extends('admin.layouts.app')

@section('title', 'Manage Languages')

@section('content')
<div class="content-area">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">Language Management</h2>
                <p class="text-muted mb-0">Manage supported languages for your application</p>
            </div>
            <div class="col-md-4 text-end text-nowrap">
                <a href="{{ route('admin.content.translations.index') }}" class="btn btn-info d-inline-block">
                    <i class="bi bi-translate"></i> Manage Translations
                </a>
                <a href="{{ route('admin.settings.languages.create') }}" class="btn btn-primary d-inline-block ms-2">
                    <i class="bi bi-plus-lg"></i> Add Language
                </a>
            </div>
        </div>
    </div>

    <!-- Languages Table -->
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 8%">Order</th>
                        <th style="width: 10%">Code</th>
                        <th style="width: 18%">Name</th>
                        <th style="width: 18%">Native Name</th>
                        <th style="width: 10%">Direction</th>
                        <th style="width: 10%">Status</th>
                        <th style="width: 10%">Default</th>
                        <th style="width: 16%">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($languages as $language)
                    <tr>
                        <td>
                            <span class="badge bg-secondary">{{ $language->sort_order }}</span>
                        </td>
                        <td>
                            <code class="fw-semibold">{{ $language->code }}</code>
                        </td>
                        <td>{{ $language->name }}</td>
                        <td>{{ $language->native_name }}</td>
                        <td>
                            <span class="badge bg-info">{{ strtoupper($language->direction) }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.settings.languages.toggle-status', $language) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="btn btn-sm btn-{{ $language->status ? 'success' : 'secondary' }}">
                                    <i class="bi bi-{{ $language->status ? 'toggle-on' : 'toggle-off' }}"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <form action="{{ route('admin.settings.languages.toggle-default', $language) }}"
                                  method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        class="btn btn-sm btn-{{ $language->is_default ? 'primary' : 'secondary' }}"
                                        title="{{ $language->is_default ? 'Default' : 'Set as Default' }}">
                                    <i class="bi bi-{{ $language->is_default ? 'star-fill' : 'star' }}"></i>
                                </button>
                            </form>
                        </td>
                        <td>
                            <a href="{{ route('admin.settings.languages.edit', $language) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('admin.settings.languages.destroy', $language) }}"
                                  method="POST" class="d-inline"
                                  onsubmit="return confirm('Delete this language?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4">
                            <p class="text-muted mb-0">No languages found. Add one to get started.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Info Alert -->
    <div class="alert alert-info mt-4">
        <i class="bi bi-info-circle"></i>
        <strong>Note:</strong> Only active languages will be available in translation forms and frontend. Only one language can be set as default at a time.
    </div>
</div>
@endsection
