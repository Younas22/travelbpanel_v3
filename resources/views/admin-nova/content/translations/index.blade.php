@extends('admin-nova.layouts.app')

@section('title', 'Manage Translations')

@section('content')
@php
    $currentLang = $languages->firstWhere('code', $selectedLang);
    $highlight = function ($text) use ($search) {
        $escaped = e($text);
        if ($search === '') return $escaped;
        return preg_replace('/' . preg_quote(e($search), '/') . '/iu', '<mark>$0</mark>', $escaped);
    };
@endphp
<div class="tbp-translations-page">

    <div class="tbp-translations-header">
        <div>
            <h1>Translation Management</h1>
            <p>Manage JSON translation files for multiple languages</p>
        </div>
        <div class="tbp-translations-header-actions">
            <a href="{{ route('admin.settings.languages.index') }}" class="tbp-translations-btn is-outline"><i class="bi bi-globe"></i> Languages</a>
            <a href="{{ route('admin.content.translations.create') }}" class="tbp-translations-btn"><i class="bi bi-plus-lg"></i> Create Translation Group</a>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.content.translations.index') }}" class="tbp-translations-filters">
        <label class="tbp-translations-field"><span><i class="bi bi-globe"></i> Select Language</span>
            <select name="language" onchange="this.form.submit()">
                @foreach($languages as $language)
                    <option value="{{ $language->code }}" @selected($selectedLang === $language->code)>{{ $language->name }} ({{ $language->native_name }})</option>
                @endforeach
            </select>
        </label>
        <label class="tbp-translations-field"><span><i class="bi bi-folder2"></i> Select Translation Group</span>
            <select name="group" onchange="this.form.submit()">
                <option value="">-- All Groups --</option>
                @foreach($groups as $group)
                    <option value="{{ $group }}" @selected($selectedGroup === $group)>{{ ucfirst($group) }}</option>
                @endforeach
            </select>
        </label>
        <label class="tbp-translations-field"><span><i class="bi bi-search"></i> Search Key / Value</span>
            <div class="tbp-translations-search">
                <input type="search" name="q" value="{{ $search }}" placeholder="Type a key or value and press Enter…" autocomplete="off">
                <button type="submit" title="Search"><i class="bi bi-search"></i></button>
            </div>
        </label>
    </form>

    @if($searchResults !== null)
        <section class="tbp-translations-card">
            <header class="tbp-translations-card-head">
                <span class="tbp-translations-icon"><i class="bi bi-search"></i></span>
                <div>
                    <h2>{{ count($searchResults) }} result{{ count($searchResults) === 1 ? '' : 's' }} for “{{ $search }}”</h2>
                    <p>Searching {{ $selectedGroup ? ucfirst($selectedGroup) . ' group' : 'all groups' }} – {{ strtoupper($selectedLang) }}@if($currentLang) ({{ $currentLang->name }})@endif</p>
                </div>
                <a href="{{ route('admin.content.translations.index', array_filter(['language' => $selectedLang, 'group' => $selectedGroup])) }}" class="tbp-translations-btn is-small is-outline"><i class="bi bi-x-lg"></i> Clear</a>
            </header>
            @if(count($searchResults))
                <div class="tbp-translations-table-wrap">
                    <table class="tbp-translations-table">
                        <thead><tr><th>Group</th><th>Key</th><th>Value</th><th class="is-actions">Actions</th></tr></thead>
                        <tbody>
                            @foreach($searchResults as $row)
                                @php $text = is_string($row['value']) ? $row['value'] : json_encode($row['value'], JSON_UNESCAPED_UNICODE); @endphp
                                <tr>
                                    <td><a href="{{ route('admin.content.translations.index', ['language' => $selectedLang, 'group' => $row['group']]) }}" class="tbp-translations-group-tag">{{ ucfirst($row['group']) }}</a></td>
                                    <td><code>{!! $highlight($row['key']) !!}</code></td>
                                    <td class="is-value" title="{{ $text }}">{!! $highlight($text) !!}</td>
                                    <td class="is-actions">
                                        <a href="{{ route('admin.content.translations.edit', $row['group']) }}" class="tbp-translations-edit" title="Edit group"><i class="bi bi-pencil"></i></a>
                                        <form action="{{ route('admin.content.translations.delete-key', [$row['group'], $row['key']]) }}" method="POST" onsubmit="return confirm('Delete the key “{{ $row['key'] }}”?');">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="tbp-translations-delete" title="Delete key"><i class="bi bi-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="tbp-translations-empty">
                    <i class="bi bi-search"></i>
                    <h2>No matches</h2>
                    <p>No key or value contains “{{ $search }}” for this language.</p>
                </div>
            @endif
        </section>
    @elseif(!empty($translations))
        <section class="tbp-translations-card">
            <header class="tbp-translations-card-head">
                <span class="tbp-translations-icon"><i class="bi bi-file-earmark-text"></i></span>
                <div>
                    <h2>{{ ucfirst($selectedGroup) }} – {{ strtoupper($selectedLang) }}@if($currentLang) ({{ $currentLang->name }})@endif</h2>
                    <p>Manage translation keys and values for this group</p>
                </div>
                <a href="{{ route('admin.content.translations.edit', $selectedGroup) }}" class="tbp-translations-btn is-small"><i class="bi bi-pencil"></i> Edit All</a>
            </header>
            <div class="tbp-translations-table-wrap">
                <table class="tbp-translations-table">
                    <thead><tr><th>Key</th><th>Value</th><th class="is-actions">Actions</th></tr></thead>
                    <tbody>
                        @foreach($translations as $key => $value)
                            <tr>
                                <td><code>{{ $key }}</code></td>
                                <td class="is-value" title="{{ is_string($value) ? $value : '' }}">{{ is_string($value) ? $value : json_encode($value) }}</td>
                                <td class="is-actions">
                                    <form action="{{ route('admin.content.translations.delete-key', [$selectedGroup, $key]) }}" method="POST" onsubmit="return confirm('Delete the key “{{ $key }}”?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="tbp-translations-delete" title="Delete key"><i class="bi bi-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @else
        <section class="tbp-translations-card tbp-translations-empty">
            <i class="bi bi-inbox"></i>
            <h2>No translations found</h2>
            <p>{{ $selectedGroup ? 'This group has no keys for the selected language yet.' : 'Pick a translation group above, or create your first one.' }}</p>
            @if($selectedGroup)
                <a href="{{ route('admin.content.translations.edit', $selectedGroup) }}" class="tbp-translations-btn"><i class="bi bi-pencil"></i> Add translations</a>
            @else
                <a href="{{ route('admin.content.translations.create') }}" class="tbp-translations-btn"><i class="bi bi-plus-lg"></i> Create Translation Group</a>
            @endif
        </section>
    @endif
</div>
@endsection
