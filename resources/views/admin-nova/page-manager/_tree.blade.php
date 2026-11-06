{{-- Menu structure: one collapsible group per menu location, top-level items with their
     sub-items, drag to reorder (siblings), and the pages that are in no menu. --}}
@php
    $pmStatusClass = ['published' => 'is-published', 'draft' => 'is-draft', 'private' => 'is-private'];
@endphp
<aside class="pm-tree tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
    <div class="pm-search">
        <i class="bi bi-search"></i>
        <input type="search" id="pmSearch" class="pg-input" placeholder="Search menus and pages…" autocomplete="off">
    </div>

    @foreach($categories as $categoryKey => $categoryLabel)
        @php $pmTop = $topItems->get($categoryKey, collect()); @endphp
        <details class="pm-group" open>
            <summary>
                <span>{{ $categoryLabel }}</span>
                <span class="pm-count">{{ $pmTop->count() }}</span>
                <a href="{{ route('admin.page-manager.index', ['new' => 1, 'category' => $categoryKey]) }}" class="pm-add" data-tooltip="Add to {{ $categoryLabel }}">
                    <i class="bi bi-plus-lg"></i>
                </a>
            </summary>

            <ul class="pm-list" data-sortable data-parent="" data-category="{{ $categoryKey }}">
                @forelse($pmTop as $item)
                    @include('admin-nova.page-manager._tree-item', ['item' => $item])
                @empty
                    <li class="pm-none">No items yet.</li>
                @endforelse
            </ul>
        </details>
    @endforeach

    {{-- pages that no menu links to (still reachable at /page/{slug} when published) --}}
    <details class="pm-group" {{ $orphanPages->isNotEmpty() ? 'open' : '' }}>
        <summary>
            <span>Pages not in a menu</span>
            <span class="pm-count">{{ $orphanPages->count() }}</span>
        </summary>
        <ul class="pm-list">
            @forelse($orphanPages as $orphan)
                <li class="pm-node" data-search="{{ \Illuminate\Support\Str::lower($orphan->name . ' ' . $orphan->slug) }}">
                    <a href="{{ route('admin.page-manager.index', ['page' => $orphan->id]) }}"
                       class="pm-row {{ !$menu && $page && $page->id === $orphan->id ? 'is-active' : '' }}">
                        <i class="bi bi-file-earmark-text pm-ico"></i>
                        <span class="pm-name">{{ $orphan->name }}</span>
                        <span class="pm-badge {{ $pmStatusClass[$orphan->status] ?? '' }}">{{ ucfirst($orphan->status) }}</span>
                    </a>
                </li>
            @empty
                <li class="pm-none">Every page is in a menu.</li>
            @endforelse
        </ul>
    </details>
</aside>
