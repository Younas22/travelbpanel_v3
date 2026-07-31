@extends('admin-nova.layouts.app')

@section('title', 'Umrah Packages')

@push('styles')
<script src="https://cdn.tailwindcss.com"></script>
<script>
    tailwind.config = {
        corePlugins: { preflight: false },
        theme: {
            extend: {
                fontFamily: { jakarta: ['Plus Jakarta Sans', 'sans-serif'] },
                colors: {
                    novabg: '#F7F8FC', novablue: '#2563EB', novacyan: '#06B6D4',
                    novatext: '#000000', novamuted: '#000000', novaborder: '#E5E7EB',
                    novasuccess: '#22C55E', novawarning: '#F59E0B', novadanger: '#EF4444',
                },
            },
        },
    };
</script>
<style>
    #umPage, #umPage *, #umPage *::before, #umPage *::after { box-sizing: border-box; }
    #umPage { font-family: 'Plus Jakarta Sans', sans-serif; }
    #umPage h1, #umPage h2, #umPage h3, #umPage p { margin: 0; padding: 0; }
    #umPage a { text-decoration: none; color: inherit; }
    #umPage button { font: inherit; color: inherit; background: none; border: none; cursor: pointer; padding: 0; }
    #umPage svg { display: block; }
    #umPage .tt-fade-in { animation: umFadeIn .5s ease both; }
    @keyframes umFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

    #umPage .um-card { transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
    #umPage .um-card:hover { transform: translateY(-3px); box-shadow: 0 16px 32px -12px rgba(37,99,235,.15); border-color: #DBEAFE; }

    #umPage .um-btn-nova {
        display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        border: 1px solid #E5E7EB; border-radius: 9999px; color: #000; background: #fff;
        white-space: nowrap; transition: background .2s ease, border-color .2s ease;
    }
    #umPage .um-btn-nova:hover { background: #F7F8FC; }
    #umPage .um-btn-primary { background: #2563EB; color: #fff; border-color: #2563EB; }
    #umPage .um-btn-primary:hover { background: #1D4ED8; border-color: #1D4ED8; }
    #umPage .um-icon-btn {
        width: 30px; height: 30px; border-radius: 9999px; border: 1px solid #E5E7EB;
        display: inline-flex; align-items: center; justify-content: center; background: #fff;
        transition: background .2s ease, border-color .2s ease;
    }
    #umPage .um-icon-btn:hover { background: #F7F8FC; }
    #umPage .um-icon-warn:hover { background: #FFFBEB; border-color: #F59E0B; color: #F59E0B; }
    #umPage .um-icon-success:hover { background: #ECFDF5; border-color: #22C55E; color: #22C55E; }
    #umPage .um-icon-danger:hover { background: #FEF2F2; border-color: #EF4444; color: #EF4444; }

    #umPage .um-star-btn { color: #E5E7EB; transition: color .2s ease, transform .2s ease; }
    #umPage .um-star-btn:hover { transform: scale(1.15); }
    #umPage .um-star-btn.um-star-active { color: #F59E0B; }

    #umPage .um-details-panel { display: none; }
    #umPage .um-details-panel.um-open { display: block; }
    #umPage .um-details-toggle svg { transition: transform .2s ease; }
    #umPage .um-details-toggle.um-open svg { transform: rotate(180deg); }

    #umPage [data-tooltip] { position: relative; }
    #umPage [data-tooltip]::after {
        content: attr(data-tooltip); position: absolute; bottom: calc(100% + 8px); left: 50%;
        transform: translateX(-50%) translateY(4px); background: #1F2937; color: #fff;
        font-size: 11px; font-weight: 600; line-height: 1; padding: 6px 10px; border-radius: 6px;
        white-space: nowrap; box-shadow: 0 6px 16px rgba(0,0,0,.18); opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, transform .15s ease, visibility .15s ease; z-index: 60;
    }
    #umPage [data-tooltip]::before {
        content: ''; position: absolute; bottom: calc(100% + 3px); left: 50%; transform: translateX(-50%);
        border: 5px solid transparent; border-top-color: #1F2937; opacity: 0; visibility: hidden;
        pointer-events: none; transition: opacity .15s ease, visibility .15s ease; z-index: 60;
    }
    #umPage [data-tooltip]:hover::after, #umPage [data-tooltip]:focus-visible::after { opacity: 1; visibility: visible; transform: translateX(-50%) translateY(0); transition-delay: .25s; }
    #umPage [data-tooltip]:hover::before, #umPage [data-tooltip]:focus-visible::before { opacity: 1; visibility: visible; transition-delay: .25s; }
</style>
@endpush

@section('content')
<div id="umPage" class="tt-fade-in font-jakarta">

    {{-- ============ HEADER ============ --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-lg font-bold text-novatext">Umrah Packages</h1>
            <p class="text-xs text-novamuted mt-1">Manage all Umrah packages</p>
        </div>
        <a href="{{ route('admin.umrah.packages.create') }}" class="um-btn-nova um-btn-primary px-4 py-2.5 text-xs font-semibold">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
            Add New Package
        </a>
    </div>

    {{-- ============ KPI CARDS ============ --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 mb-5">
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Total</p>
                <div class="w-7 h-7 rounded-full bg-novablue text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="14" rx="2"/><path d="M3 9h18M8 4v5"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['total']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Active</p>
                <div class="w-7 h-7 rounded-full bg-novasuccess text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12.75 11.25 15 15 9.75"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['active']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Inactive</p>
                <div class="w-7 h-7 rounded-full bg-novadanger text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['inactive']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Featured</p>
                <div class="w-7 h-7 rounded-full bg-novawarning text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M12 2.5 15 9l7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['featured']) }}</span>
        </div>
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-3">
            <div class="flex items-center justify-between mb-2">
                <p class="text-xs font-medium text-novamuted">Pending</p>
                <div class="w-7 h-7 rounded-full bg-novacyan text-white flex items-center justify-center flex-shrink-0">
                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
                </div>
            </div>
            <span class="block text-lg font-bold text-novatext">{{ number_format($stats['pending']) }}</span>
        </div>
    </div>

    {{-- ============ FILTERS ============ --}}
    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
        <form method="GET" action="{{ route('admin.umrah.packages.index') }}">
            <div class="flex flex-wrap items-end gap-3">
                <div class="flex-1 min-w-[220px]">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Search packages</label>
                    <div class="relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-novamuted" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
                        <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Name or location..."
                               class="w-full text-sm border border-novaborder rounded-full pl-10 pr-4 py-2.5">
                    </div>
                </div>
                <div class="w-40">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Status</label>
                    <select name="status" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All status</option>
                        <option value="1" {{ ($status ?? '') == '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ ($status ?? '') === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="w-48">
                    <label class="block text-xs font-semibold text-novamuted mb-1.5">Package Type</label>
                    <select name="package_type" class="w-full text-sm border border-novaborder rounded-full px-3.5 py-2.5">
                        <option value="">All types</option>
                        @foreach($packageTypes as $type)
                            <option value="{{ $type->packege_type }}" {{ ($packageType ?? '') == $type->packege_type ? 'selected' : '' }}>{{ ucfirst($type->packege_type) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <button type="submit" class="um-btn-nova um-btn-primary w-10 h-10" data-tooltip="Apply filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16M7 12h10M10 19h4"/></svg>
                    </button>
                    <a href="{{ route('admin.umrah.packages.index') }}" class="um-btn-nova w-10 h-10" data-tooltip="Reset filters">
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 1 2.64 6.36M3 12v6m0-6h6"/></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- ============ PACKAGES GRID ============ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($packages as $package)
            @php
                $approval = $package->approval_status ?? 'approved';
                $hasDetails = !empty($package->inclusions) || !empty($package->exclusions);
            @endphp
            <div class="um-card bg-white rounded-2xl border border-novaborder shadow-sm overflow-hidden">
                <div class="relative h-36 bg-novabg">
                    @if($package->images->first())
                        <img src="{{ asset('public/assets/images/' . $package->images->first()->image) }}" alt="" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-novaborder">
                            <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                        </div>
                    @endif
                    <div class="absolute top-2 left-2 flex items-center gap-1.5">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold {{ $package->status == '1' ? 'bg-emerald-500 text-white' : 'bg-slate-500 text-white' }}">
                            {{ $package->status == '1' ? 'Active' : 'Inactive' }}
                        </span>
                        @if($approval === 'pending')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-amber-500 text-white">Pending</span>
                        @elseif($approval === 'rejected')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-red-500 text-white">Rejected</span>
                        @endif
                    </div>
                    <form action="{{ route('admin.umrah.packages.toggle-featured', $package->id) }}" method="POST" class="absolute top-2 right-2">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="um-star-btn w-8 h-8 rounded-full bg-white/90 flex items-center justify-center {{ $package->featured == '1' ? 'um-star-active' : '' }}" data-tooltip="Toggle featured">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="{{ $package->featured == '1' ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="m12 2.5 3 6.5 7 .8-5.2 4.8L18.2 21 12 17.3 5.8 21l1.4-6.4L2 9.8 9 9l3-6.5Z"/></svg>
                        </button>
                    </form>
                </div>

                <div class="p-4">
                    <p class="text-sm font-semibold text-novatext truncate">{{ $package->name }}</p>
                    <p class="text-[11px] text-novamuted mt-0.5 flex items-center gap-1 truncate">
                        <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21s7-6.5 7-11.5A7 7 0 0 0 5 9.5C5 14.5 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.25"/></svg>
                        {{ $package->loaction }}
                    </p>

                    <div class="flex items-center flex-wrap gap-1.5 mt-3">
                        <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-blue-50 text-novablue">{{ ucfirst($package->packege_type) }}</span>
                        <span class="text-sm font-bold text-novatext">{{ $package->currceny }} {{ number_format($package->price) }}</span>
                        <span class="text-[11px] text-novamuted">&middot; {{ $package->duration }}</span>
                    </div>

                    @if(($package->leaving_from && isset($airports[$package->leaving_from])) || ($package->going_to && isset($airports[$package->going_to])))
                        <div class="flex items-center gap-1.5 mt-2 text-[11px] text-novamuted">
                            <svg class="w-3 h-3 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M10.5 3 5 8l-2 .5L14 20l1-2-8.5-8.5L11.5 4Z M21 12l-5-5-3 3 5 5 3-3Z"/></svg>
                            <span class="truncate">
                                {{ $package->leaving_from && isset($airports[$package->leaving_from]) ? $airports[$package->leaving_from]->city : '—' }}
                                →
                                {{ $package->going_to && isset($airports[$package->going_to]) ? $airports[$package->going_to]->city : '—' }}
                            </span>
                        </div>
                    @endif

                    @if($approval === 'pending')
                        <div class="flex items-center gap-1.5 mt-3">
                            <form action="{{ route('admin.umrah.packages.approve', $package->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="um-btn-nova w-full py-1.5 text-[11px] font-semibold" style="background:#22C55E; color:#fff; border-color:#22C55E;">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                    Approve
                                </button>
                            </form>
                            <form action="{{ route('admin.umrah.packages.reject', $package->id) }}" method="POST" class="flex-1">
                                @csrf
                                <button type="submit" class="um-btn-nova w-full py-1.5 text-[11px] font-semibold" style="color:#EF4444; border-color:#EF4444;">
                                    <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 6 6 18M6 6l12 12"/></svg>
                                    Reject
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($hasDetails)
                        <button type="button" class="um-details-toggle mt-3 flex items-center gap-1 text-[11px] font-semibold text-novablue" onclick="umToggleDetails({{ $package->id }}, this)">
                            <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 9 6 6 6-6"/></svg>
                            Details
                        </button>
                        <div id="umDetails{{ $package->id }}" class="um-details-panel mt-2 pt-2 border-t border-novaborder space-y-2">
                            @if(!empty($package->inclusions))
                                <div>
                                    <p class="text-[10px] font-semibold text-novasuccess mb-1">Inclusions</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($package->inclusions as $incId)
                                            @if(isset($allInclusions[$incId]))
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-emerald-50 text-emerald-600">{{ $allInclusions[$incId]->name }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                            @if(!empty($package->exclusions))
                                <div>
                                    <p class="text-[10px] font-semibold text-novadanger mb-1">Exclusions</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($package->exclusions as $excId)
                                            @if(isset($allExclusions[$excId]))
                                                <span class="px-2 py-0.5 rounded-full text-[9px] font-semibold bg-red-50 text-novadanger">{{ $allExclusions[$excId]->name }}</span>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    <div class="flex items-center gap-1.5 mt-4 pt-3 border-t border-novaborder">
                        <a href="{{ route('admin.umrah.packages.edit', $package->id) }}" class="um-icon-btn" data-tooltip="Edit">
                            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M16.474 5.408 18.592 7.526M4 20l1.11-3.92a2 2 0 0 1 .53-.9l9.9-9.9a1.5 1.5 0 0 1 2.12 0l1.06 1.06a1.5 1.5 0 0 1 0 2.12l-9.9 9.9a2 2 0 0 1-.9.53L4 20Z"/></svg>
                        </a>
                        <form action="{{ route('admin.umrah.packages.toggle-status', $package->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="um-icon-btn {{ $package->status == '1' ? 'um-icon-warn' : 'um-icon-success' }}" data-tooltip="{{ $package->status == '1' ? 'Deactivate' : 'Activate' }}">
                                @if($package->status == '1')
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="6" y="5" width="4" height="14" rx="1"/><rect x="14" y="5" width="4" height="14" rx="1"/></svg>
                                @else
                                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M6 4.5v15l13-7.5-13-7.5Z"/></svg>
                                @endif
                            </button>
                        </form>
                        <form action="{{ route('admin.umrah.packages.destroy', $package->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this package?')" class="ml-auto">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="um-icon-btn um-icon-danger" data-tooltip="Delete">
                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0-1 14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2L4 6h16Z"/></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16">
                <svg class="w-10 h-10 text-novaborder mx-auto mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="m21 15-5-5L5 21"/></svg>
                <p class="text-sm font-semibold text-novatext">No packages found</p>
                <p class="text-xs text-novamuted mt-1">Try adjusting your search or filters.</p>
            </div>
        @endforelse
    </div>

    @if($packages->hasPages())
        <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mt-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <p class="text-xs text-novamuted">Showing {{ $packages->firstItem() }} to {{ $packages->lastItem() }} of {{ number_format($packages->total()) }} entries</p>
                {{ $packages->withQueryString()->links() }}
            </div>
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
    function umToggleDetails(id, btn) {
        var panel = document.getElementById('umDetails' + id);
        panel.classList.toggle('um-open');
        btn.classList.toggle('um-open');
    }
</script>
@endpush
