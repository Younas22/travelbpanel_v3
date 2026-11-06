{{-- Page & Menu Manager (PageManagerController@index): menu structure on the left, the
     selected item's page editor, page translations and menu settings on the right.
     Expects: $categories, $topItems (by category), $childItems (by parent id), $orphanPages,
     $allPages, $languages, $menu, $page, $creating, $translationStatus. --}}
@extends('admin-nova.layouts.app')

@section('title', 'Page & Menu Manager')

@push('styles')
@include('admin-nova.page-manager._styles')
@endpush

@section('content')
<div id="pgPage" class="pm tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-5">
        <div>
            <h1 class="text-lg font-bold text-novatext">Page &amp; Menu Manager</h1>
            <p class="text-xs text-novamuted mt-1">Menus, their pages and every language in one place. A new menu item gets its own blank page.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ url('/') }}" target="_blank" rel="noopener" class="pg-btn-nova px-4 py-2.5 text-xs font-semibold">
                <i class="bi bi-box-arrow-up-right"></i> Preview website
            </a>
            <a href="{{ route('admin.page-manager.index', ['new' => 1]) }}" class="pg-btn-nova pg-btn-primary px-4 py-2.5 text-xs font-semibold">
                <i class="bi bi-plus-lg"></i> Add New Menu
            </a>
        </div>
    </div>

    <div class="pm-layout">
        {{-- ============ LEFT: MENU STRUCTURE ============ --}}
        @include('admin-nova.page-manager._tree')

        {{-- ============ RIGHT: EDITOR ============ --}}
        <section class="pm-main min-w-0 space-y-5">
            @if($creating)
                @include('admin-nova.page-manager._create')
            @elseif($page)
                @include('admin-nova.page-manager._page-editor')
                @if($menu)
                    @include('admin-nova.page-manager._menu-settings')
                @else
                    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <h3 class="pm-section-title mb-0"><i class="bi bi-menu-button-wide"></i> Menu</h3>
                            <p class="pg-help">
                                @if($page->menuItems->isNotEmpty())
                                    Linked from: {{ $page->menuItems->map(fn ($m) => $m->category_label . ' › ' . $m->name)->implode(', ') }}
                                @else
                                    This page is not in any menu. It still opens at its address when published.
                                @endif
                            </p>
                        </div>
                        <a href="{{ route('admin.page-manager.index', ['new' => 1, 'page' => $page->id]) }}" class="pg-btn-nova px-4 py-2 text-xs font-semibold">
                            <i class="bi bi-plus-lg"></i> Add to a menu
                        </a>
                    </div>
                @endif
            @elseif($menu)
                <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
                    <h2 class="text-sm font-semibold text-novatext">{{ $menu->name }}</h2>
                    <p class="pg-help">
                        @if($menu->isBuiltIn())
                            Built-in page ({{ $menu->url }}): its content comes from the website itself. Here you change only the menu name, place and order.
                        @elseif($menu->url)
                            Links to another website ({{ $menu->url }}).
                        @else
                            This is a dropdown: it holds sub-items and has no page of its own.
                        @endif
                    </p>
                </div>
                @include('admin-nova.page-manager._menu-settings')
            @else
                <div class="pm-empty tt-card bg-white rounded-2xl border border-novaborder shadow-sm">
                    <i class="bi bi-layout-sidebar-inset"></i>
                    <h2>Select a menu item or page</h2>
                    <p>Pick an item on the left to edit its page, translations and menu settings — or add a new menu item; its blank page is created for you.</p>
                    <a href="{{ route('admin.page-manager.index', ['new' => 1]) }}" class="pg-btn-nova pg-btn-primary px-4 py-2.5 text-xs font-semibold">
                        <i class="bi bi-plus-lg"></i> Add New Menu
                    </a>
                </div>
            @endif
        </section>
    </div>
</div>
@endsection

@include('admin-nova.page-manager._scripts')
