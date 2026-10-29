@php $p = $hotels->withQueryString(); @endphp
<footer class="tbp-hotels-footer">
    <span>Showing {{ $p->firstItem() }} to {{ $p->lastItem() }} of {{ number_format($p->total()) }} hotels</span>
    @if($p->hasPages())
        <nav class="tbp-hotels-pages">
            <a class="{{ $p->onFirstPage() ? 'is-disabled' : '' }}" href="{{ $p->previousPageUrl() ?? '#' }}" aria-label="Previous"><i class="bi bi-chevron-left"></i></a>
            @foreach($p->getUrlRange(max(1, $p->currentPage() - 2), min($p->lastPage(), $p->currentPage() + 2)) as $num => $url)
                <a class="{{ $num === $p->currentPage() ? 'is-active' : '' }}" href="{{ $url }}">{{ $num }}</a>
            @endforeach
            <a class="{{ $p->hasMorePages() ? '' : 'is-disabled' }}" href="{{ $p->nextPageUrl() ?? '#' }}" aria-label="Next"><i class="bi bi-chevron-right"></i></a>
        </nav>
    @endif
</footer>
