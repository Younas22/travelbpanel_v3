@extends('admin.layouts.app')

@section('title', 'Manage Translations')

@section('content')
<div class="content-area">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-1">Translation Management</h2>
                <p class="text-muted mb-0">Manage JSON translation files for multiple languages</p>
            </div>
            <div class="col-md-4 text-end text-nowrap">
                <a href="{{ route('admin.settings.languages.index') }}" class="btn btn-info d-inline-block">
                    <i class="bi bi-globe"></i> Languages
                </a>
                <a href="{{ route('admin.content.translations.create') }}" class="btn btn-primary d-inline-block ms-2">
                    <i class="bi bi-plus-lg"></i> Create Translation Group
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('admin.content.translations.index') }}" class="row g-3 align-items-end">
            <div class="col-md-6">
                <label class="form-label">Select Language</label>
                <select name="language" class="form-select" onchange="this.form.submit()">
                    @foreach($languages as $language)
                        <option value="{{ $language->code }}" {{ $selectedLang === $language->code ? 'selected' : '' }}>
                            {{ $language->name }} ({{ $language->native_name }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Select Translation Group</label>
                <select name="group" class="form-select" onchange="this.form.submit()">
                    <option value="">-- All Groups --</option>
                    @foreach($groups as $group)
                        <option value="{{ $group }}" {{ $selectedGroup === $group ? 'selected' : '' }}>
                            {{ ucfirst($group) }}
                        </option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Translations Table -->
    @if(!empty($translations))
    <div class="translations-table card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">
                    {{ ucfirst($selectedGroup) }} - {{ strtoupper($selectedLang) }}
                    @php
                        $currentLang = $languages->firstWhere('code', $selectedLang);
                    @endphp
                    @if($currentLang)
                        ({{ $currentLang->name }})
                    @endif
                </h5>
            </div>
            <div>
                <a href="{{ route('admin.content.translations.edit', $selectedGroup) }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-pencil"></i> Edit All
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="w-30p">Key</th>
                        <th class="w-60p">Value</th>
                        <th class="w-10p">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($translations as $key => $value)
                    <tr>
                        <td>
                            <code class="fw-semibold">{{ $key }}</code>
                        </td>
                        <td>
                            <span class="text-muted">{{ Str::limit($value, 100) }}</span>
                        </td>
                        <td>
                            <form action="{{ route('admin.content.translations.delete-key', [$selectedGroup, $key]) }}" 
                                  method="POST" class="d-inline" 
                                  onsubmit="return confirm('Delete this key?');">
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
                        <td colspan="3" class="text-center py-4">
                            <p class="text-muted mb-0">No translations found. <a href="{{ route('admin.content.translations.edit', $selectedGroup) }}">Create one</a></p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="card text-center py-5">
        <div class="mb-3">
            <i class="bi bi-inbox display-1 text-muted"></i>
        </div>
        <h5 class="text-muted">No translations found</h5>
        <p class="text-muted mb-3">Create your first translation group to get started</p>
        <a href="{{ route('admin.content.translations.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Create Translation Group
        </a>
    </div>
    @endif
</div>

@endsection