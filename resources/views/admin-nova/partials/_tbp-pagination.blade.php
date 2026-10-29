{{-- Footer with "Showing x to y of z" + compact page buttons. Expects: $paginator. --}}
@php $p = $paginator->withQueryString(); @endphp
<footer class="tbp-admin-footer">
    <span>@if($p->total()) Showing {{ $p->firstItem() }} to {{ $p->lastItem() }} of {{ number_format($p->total()) }} entries @else No entries @endif</span>
    @if($p->hasPages())
        <nav class="tbp-admin-pages">
            <a class="{{ $p->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $p->previousPageUrl() ?? '#' }}" aria-label="Previous"><i class="bi bi-chevron-left"></i></a>
            @foreach($p->getUrlRange(max(1, $p->currentPage() - 2), min($p->lastPage(), $p->currentPage() + 2)) as $num => $url)
                <a class="{{ $num === $p->currentPage() ? 'is-active' : '' }}" href="{{ $url }}">{{ $num }}</a>
            @endforeach
            <a class="{{ $p->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $p->nextPageUrl() ?? '#' }}" aria-label="Next"><i class="bi bi-chevron-right"></i></a>
        </nav>
    @endif
</footer>
