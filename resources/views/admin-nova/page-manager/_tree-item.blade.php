{{-- One menu item row (+ its sub-items). Expects: $item, $childItems, $menu, $pmStatusClass. --}}
@php
    $pmKids = $childItems->get($item->id, collect());
    $pmSearch = \Illuminate\Support\Str::lower($item->name . ' ' . ($item->page?->name ?? '') . ' ' . ($item->page?->slug ?? $item->url ?? ''));
@endphp
<li class="pm-node" data-id="{{ $item->id }}" data-search="{{ $pmSearch }}">
    <div class="pm-row {{ $menu && $menu->id === $item->id ? 'is-active' : '' }} {{ $item->is_active ? '' : 'is-off' }}">
        <span class="pm-handle" data-tooltip="Drag to reorder"><i class="bi bi-grip-vertical"></i></span>
        <a href="{{ route('admin.page-manager.index', ['menu' => $item->id]) }}" class="pm-link">
            <i class="{{ $item->page_id ? 'bi bi-file-earmark-text' : ($item->url ? 'bi bi-link-45deg' : 'bi bi-menu-button-wide') }} pm-ico"></i>
            <span class="pm-name">{{ $item->name }}</span>
            @if($item->page)
                <span class="pm-badge {{ $pmStatusClass[$item->page->status] ?? '' }}">{{ ucfirst($item->page->status) }}</span>
            @elseif($item->url)
                <span class="pm-badge">Link</span>
            @else
                <span class="pm-badge">Dropdown</span>
            @endif
            @unless($item->is_active)
                <span class="pm-badge is-off">Hidden</span>
            @endunless
        </a>
        <form method="POST" action="{{ route('admin.page-manager.menus.toggle', $item) }}" class="pm-act">
            @csrf
            <button type="submit" data-tooltip="{{ $item->is_active ? 'Hide on website' : 'Show on website' }}">
                <i class="bi {{ $item->is_active ? 'bi-eye' : 'bi-eye-slash' }}"></i>
            </button>
        </form>
        <form method="POST" action="{{ route('admin.page-manager.menus.destroy', $item) }}" class="pm-act"
              onsubmit="return confirm('Delete the menu item “{{ addslashes($item->name) }}”{{ $pmKids->isNotEmpty() ? ' and its ' . $pmKids->count() . ' sub-items' : '' }}? Pages are kept (they move to “Pages not in a menu”).')">
            @csrf
            @method('DELETE')
            <button type="submit" class="is-danger" data-tooltip="Delete menu item"><i class="bi bi-trash3"></i></button>
        </form>
    </div>

    @if($pmKids->isNotEmpty())
        <ul class="pm-list pm-children" data-sortable data-parent="{{ $item->id }}">
            @foreach($pmKids as $kid)
                @include('admin-nova.page-manager._tree-item', ['item' => $kid])
            @endforeach
        </ul>
    @endif
</li>
