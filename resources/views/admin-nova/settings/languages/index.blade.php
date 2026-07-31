@extends('admin-nova.layouts.app')

@section('title', 'Manage Languages')

@push('styles')
@include('admin-nova.settings.languages._styles')
@endpush

@section('content')
<div id="lgPage" class="tt-fade-in font-jakarta">

    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Language Management</h1>
            <p class="text-xs text-novamuted mt-1">Manage supported languages for your application</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.content.translations.index') }}" class="lg-btn-nova lg-btn-cyan px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h7M7 3v2.5C7 9 5 12 2.5 13.5M5 8.5c1 2 3 4 5.5 5M14 21l4-9 4 9M15.3 18h5.4"/></svg>
                Manage Translations
            </a>
            <a href="{{ route('admin.settings.languages.create') }}" class="lg-btn-nova lg-btn-primary px-4 py-2.5 text-xs font-semibold">
                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                Add Language
            </a>
        </div>
    </div>

    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                <tr class="border-b border-novaborder">
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Order</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Code</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Name</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Native Name</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Direction</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Status</th>
                    <th class="text-left text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Default</th>
                    <th class="text-right text-[10px] font-semibold uppercase tracking-wide text-novamuted py-2.5 px-3">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($languages as $language)
                    <tr class="tt-row border-b border-novaborder last:border-0">
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center justify-center w-6 h-6 rounded-full bg-slate-100 text-[10px] font-semibold text-novatext">{{ $language->sort_order }}</span>
                        </td>
                        <td class="py-3 px-3"><code class="font-semibold text-novatext">{{ $language->code }}</code></td>
                        <td class="py-3 px-3 text-novatext">{{ $language->name }}</td>
                        <td class="py-3 px-3 text-novamuted">{{ $language->native_name }}</td>
                        <td class="py-3 px-3">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-cyan-50 text-novacyan">{{ strtoupper($language->direction) }}</span>
                        </td>
                        <td class="py-3 px-3">
                            <form action="{{ route('admin.settings.languages.toggle-status', $language) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <label class="lg-switch">
                                    <input type="checkbox" onchange="this.form.submit()" {{ $language->status ? 'checked' : '' }}>
                                    <span class="lg-slider"></span>
                                </label>
                            </form>
                        </td>
                        <td class="py-3 px-3">
                            <form action="{{ route('admin.settings.languages.toggle-default', $language) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="lg-icon-btn {{ $language->is_default ? 'lg-icon-active' : '' }}" data-tooltip="{{ $language->is_default ? 'Default' : 'Set as Default' }}">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="{{ $language->is_default ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.5 3 6.5 7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                                </button>
                            </form>
                        </td>
                        <td class="py-3 px-3">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ route('admin.settings.languages.edit', $language) }}" class="lg-icon-btn" data-tooltip="Edit">
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                                </a>
                                <form action="{{ route('admin.settings.languages.destroy', $language) }}" method="POST" onsubmit="return confirm('Delete this language?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="lg-icon-btn lg-icon-danger" data-tooltip="Delete">
                                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-16">
                            <p class="text-sm font-semibold text-novatext">No languages found</p>
                            <p class="text-xs text-novamuted mt-1">Add one to get started.</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="tt-card bg-blue-50/40 rounded-2xl border border-blue-100 p-4 sm:p-5 flex items-start gap-3">
        <svg class="w-4 h-4 text-novablue flex-shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v4M12 16h.01"/></svg>
        <p class="text-xs text-novatext"><strong>Note:</strong> Only active languages will be available in translation forms and frontend. Only one language can be set as default at a time.</p>
    </div>
</div>
@endsection
