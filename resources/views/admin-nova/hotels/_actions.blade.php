{{-- Row actions, all visible: Approve/Reject (pending only), Featured, Edit, Delete. Expects $hotel. --}}
@php $approval = $hotel->approval_status ?? 'approved'; @endphp
<div class="tbp-hotels-actions">
    @if($approval === 'pending')
        <form method="POST" action="{{ route('admin.hotels.approve', $hotel) }}">@csrf
            <button type="submit" class="tbp-hotels-chip is-approve" title="Approve this hotel" aria-label="Approve"><i class="bi bi-check2"></i> <span class="tbp-hotels-chip-label">Approve</span></button>
        </form>
        <form method="POST" action="{{ route('admin.hotels.reject', $hotel) }}" onsubmit="return confirm('Reject {{ addslashes($hotel->name) }}?');">@csrf
            <button type="submit" class="tbp-hotels-chip is-reject" title="Reject this hotel" aria-label="Reject"><i class="bi bi-x-lg"></i> <span class="tbp-hotels-chip-label">Reject</span></button>
        </form>
    @endif
    <button type="button" class="tbp-hotels-chip is-featured js-toggle-featured {{ $hotel->featured == '1' ? 'is-on' : '' }}" data-id="{{ $hotel->id }}" data-featured="{{ $hotel->featured == '1' ? '1' : '0' }}" title="{{ $hotel->featured == '1' ? 'Remove from featured' : 'Mark as featured' }}">
        <i class="bi {{ $hotel->featured == '1' ? 'bi-star-fill' : 'bi-star' }}"></i> <span class="tbp-hotels-chip-label">{{ $hotel->featured == '1' ? 'Featured' : 'Feature' }}</span>
    </button>
    <a href="{{ route('admin.hotels.edit', $hotel) }}" class="tbp-hotels-icon" title="Edit hotel"><i class="bi bi-pencil"></i></a>
    <button type="button" class="tbp-hotels-icon is-danger js-delete-hotel" title="Delete hotel" data-id="{{ $hotel->id }}" data-name="{{ $hotel->name }}" data-rooms="{{ $hotel->rooms_count }}" data-types="{{ $hotel->room_types_count }}"><i class="bi bi-trash3"></i></button>
</div>
