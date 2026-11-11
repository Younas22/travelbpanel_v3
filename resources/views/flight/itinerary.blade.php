{{--
    Booked flight, segment by segment (same look as the flight list "Details").
    $legs: array of legs, each an array of segment arrays (outbound, return).
--}}
@php
    $itinLogo = fn($code) => 'https://assets.duffel.com/img/airlines/for-light-background/full-color-logo/' . $code . '.svg';
    $itinBag = fn($raw) => ($raw === null || $raw === '') ? null : (is_numeric($raw) ? ((int) $raw <= 0 ? null : (int) $raw . ' PC') : (string) $raw);
    // time on the ground between two flights (both local times of the same airport)
    $itinLayover = function ($arrival, $departure) {
        try {
            $mins = \Carbon\Carbon::parse($arrival)->diffInMinutes(\Carbon\Carbon::parse($departure));
        } catch (\Throwable $e) {
            return null;
        }
        return $mins > 0 ? intdiv($mins, 60) . 'h ' . str_pad($mins % 60, 2, '0', STR_PAD_LEFT) . 'm' : null;
    };
@endphp
<div class="fl-itin">
    @foreach($legs as $legIndex => $leg)
        @php $leg = array_values($leg); $legLast = end($leg); $legStops = count($leg) - 1; @endphp
        <div class="fl-modal-head">
            <span>{{ $legIndex === 0 ? t('flightlist.outbound') : t('flightform.return') }}</span>
            <b><bdi>{{ $leg[0]['departure']['airport'] ?? '' }}</bdi> <i class="fas fa-arrow-right"></i> <bdi>{{ $legLast['arrival']['airport'] ?? '' }}</bdi></b>
            <small>{{ $leg[0]['departure']['date_convert'] ?? '' }} · {{ $legStops == 0 ? t('flightlist.direct') : $legStops . ' ' . ($legStops == 1 ? t('flightlist.stop') : t('flightlist.stops')) }}</small>
        </div>
        @foreach($leg as $i => $seg)
            @php $code = $seg['carrier']['operating'] ?? ($seg['carrier']['marketing'] ?? ''); @endphp
            <div class="fl-seg">
                <div class="fl-seg-airline">
                    <img src="{{ $itinLogo($code) }}" alt="" onerror="this.onerror=null;this.src='{{ url('public/assets/images/list-plane.png') }}';">
                    <div>
                        <b>{{ $seg['airline_name'] ?? $code }}</b>
                        <small>{{ $code }} {{ $seg['flight_number'] ?? '' }}@if(!empty($seg['class'])) · {{ ucwords(str_replace('_', ' ', $seg['class'])) }}@endif</small>
                    </div>
                    <div class="fl-seg-bags">
                        @if($itinBag($seg['cabin_baggage'] ?? null))<span><i class="fas fa-suitcase"></i> {{ t('flightlist.cabin_bag') }} {{ $itinBag($seg['cabin_baggage']) }}</span>@endif
                        @if($itinBag($seg['baggage'] ?? null))<span><i class="fas fa-suitcase-rolling"></i> {{ t('flightlist.checked_bag') }} {{ $itinBag($seg['baggage']) }}</span>@endif
                    </div>
                </div>
                <div class="fl-seg-route">
                    <div>
                        <b dir="ltr">{{ $seg['departure']['time'] ?? '' }} {{ $seg['departure']['airport'] ?? '' }}</b>
                        <small>{{ $seg['departure']['date_convert'] ?? '' }}</small>
                        <small>{{ $seg['departure']['city_name'] ?? '' }}@if(!empty($seg['departure']['terminal'])) · {{ t('flightbooking.terminal') }} {{ $seg['departure']['terminal'] }}@endif</small>
                    </div>
                    <div class="fl-seg-dur"><i class="fas fa-plane"></i> {{ $seg['duration'] ?? '' }}</div>
                    <div class="is-end">
                        <b dir="ltr">{{ $seg['arrival']['time'] ?? '' }} {{ $seg['arrival']['airport'] ?? '' }}</b>
                        <small>{{ $seg['arrival']['date_convert'] ?? '' }}</small>
                        <small>{{ $seg['arrival']['city_name'] ?? '' }}@if(!empty($seg['arrival']['terminal'])) · {{ t('flightbooking.terminal') }} {{ $seg['arrival']['terminal'] }}@endif</small>
                    </div>
                </div>
            </div>
            @if(isset($leg[$i + 1]))
                @php $wait = $itinLayover($seg['arrival']['booking_time'] ?? null, $leg[$i + 1]['departure']['booking_time'] ?? null); @endphp
                <div class="fl-layover">
                    <i class="far fa-clock"></i>
                    {{ str_replace(':city', $seg['arrival']['city_name'] ?? ($seg['arrival']['airport'] ?? ''), t('flightbooking.layover')) }}@if($wait) · <b dir="ltr">{{ $wait }}</b>@endif
                </div>
            @endif
        @endforeach
    @endforeach
</div>
