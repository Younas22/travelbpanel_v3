{{-- Status label + active toggle + More menu. Expects $hotel. Used in both list and grid views. --}}
@php $approval = $hotel->approval_status ?? 'approved'; @endphp
<div class="tbp-hotels-status">
    @if($approval === 'pending')
        <span class="tbp-hotels-pill is-pending">Pending</span>
    @elseif($approval === 'rejected')
        <span class="tbp-hotels-pill is-rejected">Rejected</span>
    @else
        <span class="tbp-hotels-pill js-status-label {{ $hotel->status ? 'is-active' : '' }}" data-id="{{ $hotel->id }}">{{ $hotel->status ? 'Active' : 'Inactive' }}</span>
    @endif
    <label class="tbp-hotels-switch" title="Active / Inactive">
        <input type="checkbox" class="js-toggle-status" data-id="{{ $hotel->id }}" @checked($hotel->status) aria-label="{{ $hotel->name }} active">
        <i aria-hidden="true"></i>
    </label>
</div>
