{{-- Printable booking list for one agent; the browser's print dialog saves it as PDF.
     Expects: $agent, $bookings (AgentController::agentBookings() rows). Theme-independent. --}}
@php
    $filters = collect([
        'Service' => request('bk_type') ? ucfirst(request('bk_type')) : null,
        'Status'  => request('bk_status') ? ucfirst(request('bk_status')) : null,
        'Payment' => request('bk_payment') ? ucfirst(request('bk_payment')) : null,
        'From'    => request('bk_from'),
        'To'      => request('bk_to'),
        'Search'  => request('bk_search'),
    ])->filter();
    $businessName = getSetting('business_name', 'main', 'Travel Booking Panel');
@endphp
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bookings — {{ $agent->full_name }} ({{ $agent->agent_code }})</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 32px; font-family: -apple-system, 'Segoe UI', Roboto, Arial, sans-serif; color: #111827; background: #fff; font-size: 12px; }
        header { display: flex; justify-content: space-between; align-items: flex-start; gap: 24px; padding-bottom: 16px; border-bottom: 2px solid #2563EB; }
        h1 { margin: 0 0 4px; font-size: 20px; }
        .muted { color: #64748B; }
        .meta { text-align: right; line-height: 1.6; }
        .filters { margin: 14px 0 0; color: #64748B; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: .04em; background: #F8FAFC; padding: 8px; border-bottom: 1px solid #E5E7EB; }
        td { padding: 8px; border-bottom: 1px solid #F1F5F9; vertical-align: top; }
        td small { display: block; color: #64748B; }
        .empty { margin-top: 40px; text-align: center; color: #64748B; font-size: 14px; }
        .toolbar { margin-bottom: 20px; display: flex; gap: 8px; }
        .toolbar button { height: 36px; padding: 0 16px; border-radius: 8px; border: 1px solid #2563EB; background: #2563EB; color: #fff; font-weight: 600; cursor: pointer; }
        .toolbar button.alt { background: #fff; color: #111827; border-color: #E5E7EB; }
        @media print { body { padding: 0; } .toolbar { display: none; } tr { page-break-inside: avoid; } }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Save as PDF</button>
        <button type="button" class="alt" onclick="window.close()">Close</button>
    </div>
    <header>
        <div>
            <h1>Agent Bookings</h1>
            <div><strong>{{ $agent->full_name }}</strong> · {{ $agent->agent_code }}</div>
            <div class="muted">{{ $agent->email }}@if($agent->company_name) · {{ $agent->company_name }}@endif</div>
        </div>
        <div class="meta">
            <strong>{{ $businessName }}</strong><br>
            <span class="muted">Generated {{ now()->format('d M Y, H:i') }}</span><br>
            <span class="muted">{{ $bookings->count() }} {{ \Illuminate\Support\Str::plural('booking', $bookings->count()) }}</span>
        </div>
    </header>
    @if($filters->isNotEmpty())
        <p class="filters">Filters: {{ $filters->map(fn ($v, $k) => "$k: $v")->implode(' · ') }}</p>
    @endif

    @if($bookings->isEmpty())
        <p class="empty">No bookings</p>
    @else
        <table>
            <thead><tr><th>#</th><th>Reference</th><th>Service</th><th>Details</th><th>Customer</th><th>Amount</th><th>Status</th><th>Payment</th><th>Date</th></tr></thead>
            <tbody>
                @foreach($bookings as $i => $b)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $b['ref'] ?: '#' . $b['id'] }}@if($b['pnr'])<small>PNR {{ $b['pnr'] }}</small>@endif</td>
                        <td>{{ ucfirst($b['type']) }}</td>
                        <td>{{ $b['title'] }}@if($b['subtitle'])<small>{{ $b['subtitle'] }}</small>@endif</td>
                        <td>{{ $b['customer'] ?: '—' }}</td>
                        <td>{{ $b['amount'] }}</td>
                        <td>{{ ucfirst($b['status']) }}</td>
                        <td>{{ ucfirst($b['payment']) }}</td>
                        <td>{{ $b['date']?->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
    <script>window.addEventListener('load', function () { setTimeout(function () { window.print(); }, 300); });</script>
</body>
</html>
