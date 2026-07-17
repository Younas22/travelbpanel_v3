# Agent B2B — Part 6: Agent Panel Views (Actual Blade Code)

---

## View 1: Agent Layout — app.blade.php

**File:** `resources/views/agent/layouts/app.blade.php`

```blade
<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Agent Panel') - {{ getSetting('business_name', 'main', 'TravelPanel') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <link href="{{ asset('public/assets/css/admin2.css') }}" rel="stylesheet">
    @stack('styles')
</head>
<body>
    @include('agent.layouts.sidebar')

    <div class="main-content">
        @include('agent.layouts.navbar')

        <div class="content-area p-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    {{ $errors->first() }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
    @stack('scripts')
</body>
</html>
```

---

## View 2: Agent Sidebar (Permission-Based)

**File:** `resources/views/agent/layouts/sidebar.blade.php`

```blade
@php $agent = auth()->user(); @endphp

<div class="sidebar">
    <div class="sidebar-header">
        <button class="sidebar-toggle d-lg-none" type="button" onclick="toggleSidebar()">
            <i class="bi bi-list"></i>
        </button>
        <a href="{{ route('agent.dashboard') }}" class="sidebar-brand">
            <img src="{{ getSettingImage('business_logo','branding') }}" alt="Logo" class="sidebar-logo">
        </a>
    </div>

    {{-- Agent Info Badge --}}
    <div class="px-3 py-2">
        <div class="d-flex align-items-center gap-2 p-2 rounded" style="background: rgba(255,255,255,0.1);">
            <div class="rounded-circle bg-warning d-flex align-items-center justify-content-center" style="width:35px;height:35px;">
                <span class="text-dark fw-bold small">{{ $agent->initials }}</span>
            </div>
            <div>
                <div class="text-white small fw-semibold">{{ $agent->full_name }}</div>
                <div class="text-white-50" style="font-size:11px;">{{ $agent->agent_code }}</div>
            </div>
        </div>
    </div>

    <nav class="sidebar-nav">

        {{-- Dashboard --}}
        <div class="nav-item">
            <a href="{{ route('agent.dashboard') }}" class="nav-link {{ request()->routeIs('agent.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </div>

        {{-- Wallet --}}
        @if($agent->hasPermission('wallet.view'))
        <div class="nav-item">
            <a href="{{ route('agent.wallet.index') }}" class="nav-link {{ request()->routeIs('agent.wallet*') ? 'active' : '' }}">
                <i class="bi bi-wallet2"></i> My Wallet
                @if($agent->wallet)
                    <span class="ms-auto badge bg-success" style="font-size:10px;">
                        PKR {{ number_format($agent->wallet->balance, 0) }}
                    </span>
                @endif
            </a>
        </div>
        @endif

        {{-- All Bookings --}}
        <div class="nav-item">
            <a href="{{ route('agent.bookings.index') }}" class="nav-link {{ request()->routeIs('agent.bookings*') ? 'active' : '' }}">
                <i class="bi bi-calendar-check"></i> My Bookings
            </a>
        </div>

        {{-- Modules Section --}}
        @if($agent->hasPermission('module.hotels') || $agent->hasPermission('module.flights') || $agent->hasPermission('module.tours') || $agent->hasPermission('module.umrah') || $agent->hasPermission('module.visa'))
        <div class="nav-section-divider">
            <span class="nav-section-title p-2">Modules</span>
        </div>
        @endif

        @if($agent->hasPermission('module.hotels'))
        <div class="nav-item">
            <a href="{{ route('agent.hotels.index') }}" class="nav-link {{ request()->routeIs('agent.hotels*') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Hotels
            </a>
        </div>
        @endif

        @if($agent->hasPermission('module.flights'))
        <div class="nav-item">
            <a href="{{ route('agent.flights.index') }}" class="nav-link {{ request()->routeIs('agent.flights*') ? 'active' : '' }}">
                <i class="bi bi-airplane"></i> Flights
            </a>
        </div>
        @endif

        @if($agent->hasPermission('module.tours'))
        <div class="nav-item">
            <a href="{{ route('agent.tours.index') }}" class="nav-link {{ request()->routeIs('agent.tours*') ? 'active' : '' }}">
                <i class="bi bi-geo-alt"></i> Tours
            </a>
        </div>
        @endif

        @if($agent->hasPermission('module.umrah'))
        <div class="nav-item">
            <a href="{{ route('agent.umrah.index') }}" class="nav-link {{ request()->routeIs('agent.umrah*') ? 'active' : '' }}">
                <i class="bi bi-moon-stars"></i> Umrah
            </a>
        </div>
        @endif

        @if($agent->hasPermission('module.visa'))
        <div class="nav-item">
            <a href="{{ route('agent.visa.index') }}" class="nav-link {{ request()->routeIs('agent.visa*') ? 'active' : '' }}">
                <i class="bi bi-passport"></i> Visa
            </a>
        </div>
        @endif

        {{-- Account --}}
        <div class="nav-section-divider">
            <span class="nav-section-title p-2">Account</span>
        </div>

        @if($agent->hasPermission('wallet.request'))
        <div class="nav-item">
            <a href="{{ route('agent.wallet.topup') }}" class="nav-link">
                <i class="bi bi-plus-circle"></i> Request Top-Up
            </a>
        </div>
        @endif

        <div class="nav-item">
            <a href="{{ route('agent.profile.index') }}" class="nav-link {{ request()->routeIs('agent.profile*') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i> My Profile
            </a>
        </div>

        <div class="nav-item">
            <form method="POST" action="{{ route('agent.logout') }}">
                @csrf
                <button type="submit" class="nav-link border-0 bg-transparent w-100 text-start">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>

    </nav>
</div>
```

---

## View 3: Agent Dashboard

**File:** `resources/views/agent/dashboard.blade.php`

```blade
@extends('agent.layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Welcome back, {{ auth()->user()->first_name }}!</h4>
        <small class="text-muted">{{ auth()->user()->company_name }} &bull; {{ auth()->user()->agent_code }}</small>
    </div>
    @if(auth()->user()->hasPermission('wallet.view'))
    <a href="{{ route('agent.wallet.index') }}" class="btn btn-outline-success">
        <i class="bi bi-wallet2"></i>
        Wallet: <strong>PKR {{ number_format($wallet?->balance ?? 0, 0) }}</strong>
    </a>
    @endif
</div>

{{-- Stats Cards --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                    <i class="bi bi-calendar-check text-primary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Bookings</div>
                    <div class="fs-4 fw-bold">{{ $stats['total'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-success bg-opacity-10 p-3">
                    <i class="bi bi-calendar-month text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">This Month</div>
                    <div class="fs-4 fw-bold">{{ $stats['this_month'] }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-warning bg-opacity-10 p-3">
                    <i class="bi bi-wallet2 text-warning fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Wallet Balance</div>
                    <div class="fs-4 fw-bold">PKR {{ number_format($wallet?->balance ?? 0, 0) }}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="rounded-circle bg-info bg-opacity-10 p-3">
                    <i class="bi bi-graph-up text-info fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Total Spent</div>
                    <div class="fs-4 fw-bold">PKR {{ number_format($wallet?->total_debited ?? 0, 0) }}</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Quick Actions --}}
<div class="row g-3 mb-4">
    @if(auth()->user()->hasPermission('module.hotels'))
    <div class="col-6 col-md-3">
        <a href="{{ route('agent.hotels.index') }}" class="card text-center text-decoration-none border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <i class="bi bi-building fs-2 text-primary"></i>
                <div class="mt-2 small fw-semibold">Book Hotel</div>
            </div>
        </a>
    </div>
    @endif
    @if(auth()->user()->hasPermission('module.flights'))
    <div class="col-6 col-md-3">
        <a href="{{ route('agent.flights.index') }}" class="card text-center text-decoration-none border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <i class="bi bi-airplane fs-2 text-success"></i>
                <div class="mt-2 small fw-semibold">Book Flight</div>
            </div>
        </a>
    </div>
    @endif
    @if(auth()->user()->hasPermission('module.tours'))
    <div class="col-6 col-md-3">
        <a href="{{ route('agent.tours.index') }}" class="card text-center text-decoration-none border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <i class="bi bi-geo-alt fs-2 text-warning"></i>
                <div class="mt-2 small fw-semibold">Book Tour</div>
            </div>
        </a>
    </div>
    @endif
    @if(auth()->user()->hasPermission('module.umrah'))
    <div class="col-6 col-md-3">
        <a href="{{ route('agent.umrah.index') }}" class="card text-center text-decoration-none border-0 shadow-sm h-100">
            <div class="card-body py-3">
                <i class="bi bi-moon-stars fs-2 text-info"></i>
                <div class="mt-2 small fw-semibold">Book Umrah</div>
            </div>
        </a>
    </div>
    @endif
</div>

<div class="row g-3">
    {{-- Recent Bookings --}}
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Recent Bookings</h6>
                <a href="{{ route('agent.bookings.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentBookings->isEmpty())
                    <div class="text-center py-4 text-muted">No bookings yet.</div>
                @else
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Reference</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentBookings as $booking)
                            <tr>
                                <td><code>{{ $booking['booking_code'] ?? 'N/A' }}</code></td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($booking['booking_type']) }}</span>
                                </td>
                                <td>PKR {{ number_format($booking['total_fare'] ?? 0, 0) }}</td>
                                <td>{{ \Carbon\Carbon::parse($booking['created_at'])->format('d M Y') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Recent Wallet Transactions --}}
    @if(auth()->user()->hasPermission('wallet.view'))
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Wallet Activity</h6>
                <a href="{{ route('agent.wallet.transactions') }}" class="btn btn-sm btn-outline-success">View All</a>
            </div>
            <div class="card-body p-0">
                @if($recentTransactions->isEmpty())
                    <div class="text-center py-4 text-muted">No transactions yet.</div>
                @else
                <ul class="list-group list-group-flush">
                    @foreach($recentTransactions as $txn)
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small fw-semibold">{{ Str::limit($txn->note, 35) }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $txn->created_at->format('d M Y, h:i A') }}</div>
                        </div>
                        <span class="{{ $txn->type === 'credit' ? 'text-success' : 'text-danger' }} fw-semibold small">
                            {{ $txn->type === 'credit' ? '+' : '-' }} {{ number_format($txn->amount, 0) }}
                        </span>
                    </li>
                    @endforeach
                </ul>
                @endif
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
```

---

## View 4: Agent Wallet Index

**File:** `resources/views/agent/wallet/index.blade.php`

```blade
@extends('agent.layouts.app')
@section('title', 'My Wallet')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-wallet2"></i> My Wallet</h4>
    @if(auth()->user()->hasPermission('wallet.request'))
    <a href="{{ route('agent.wallet.topup') }}" class="btn btn-success">
        <i class="bi bi-plus-circle"></i> Request Top-Up
    </a>
    @endif
</div>

{{-- Wallet Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-4">
                <i class="bi bi-wallet2 text-success fs-2 mb-2"></i>
                <div class="text-muted small mb-1">Current Balance</div>
                <div class="fs-3 fw-bold text-success">PKR {{ number_format($wallet?->balance ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-4">
                <i class="bi bi-arrow-down-circle text-primary fs-2 mb-2"></i>
                <div class="text-muted small mb-1">Total Credited</div>
                <div class="fs-3 fw-bold text-primary">PKR {{ number_format($wallet?->total_credited ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm text-center">
            <div class="card-body py-4">
                <i class="bi bi-arrow-up-circle text-danger fs-2 mb-2"></i>
                <div class="text-muted small mb-1">Total Spent</div>
                <div class="fs-3 fw-bold text-danger">PKR {{ number_format($wallet?->total_debited ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Transactions --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">Recent Transactions</h6>
        <a href="{{ route('agent.wallet.transactions') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body p-0">
        @if($recentTransactions->isEmpty())
            <div class="text-center py-5 text-muted">No transactions yet.</div>
        @else
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Details</th>
                        <th>Amount</th>
                        <th>Balance After</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $txn)
                    <tr>
                        <td class="small text-muted">{{ $txn->created_at->format('d M Y, h:i A') }}</td>
                        <td><span class="{{ $txn->type_badge_class }}">{{ ucfirst($txn->type) }}</span></td>
                        <td class="small">{{ $txn->note }}</td>
                        <td class="{{ $txn->type === 'credit' ? 'text-success' : 'text-danger' }} fw-semibold">
                            {{ $txn->formatted_amount }}
                        </td>
                        <td>PKR {{ number_format($txn->balance_after, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>
@endsection
```

---

## View 5: Top-Up Request Form

**File:** `resources/views/agent/wallet/topup.blade.php`

```blade
@extends('agent.layouts.app')
@section('title', 'Request Top-Up')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle"></i> Request Wallet Top-Up</h5>
            </div>
            <div class="card-body">

                @if($pendingRequest)
                <div class="alert alert-warning">
                    <i class="bi bi-clock"></i>
                    You have a pending top-up request of <strong>PKR {{ number_format($pendingRequest->amount, 2) }}</strong>
                    submitted on {{ $pendingRequest->created_at->format('d M Y') }}. Please wait for admin review.
                </div>
                @else
                <p class="text-muted mb-4">
                    Submit your payment details below. Admin will verify and add balance to your wallet.
                </p>

                <form method="POST" action="{{ route('agent.wallet.topup.submit') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (PKR) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control @error('amount') is-invalid @enderror"
                               placeholder="e.g. 50000" min="100" step="1" value="{{ old('amount') }}" required>
                        @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="">Select method</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="Cash">Cash</option>
                            <option value="Easypaisa">Easypaisa</option>
                            <option value="JazzCash">JazzCash</option>
                            <option value="Other">Other</option>
                        </select>
                        @error('payment_method') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Proof (optional)</label>
                        <input type="file" name="payment_proof" class="form-control @error('payment_proof') is-invalid @enderror"
                               accept=".jpg,.jpeg,.png,.pdf">
                        <div class="form-text">Upload screenshot or receipt. Max 2MB. (JPG, PNG, PDF)</div>
                        @error('payment_proof') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label fw-semibold">Note (optional)</label>
                        <textarea name="note" class="form-control" rows="3" placeholder="Transaction ID, bank name, etc.">{{ old('note') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100">
                        <i class="bi bi-send"></i> Submit Request
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
```

---

## View 6: Agent Login Page

**File:** `resources/views/agent/auth/login.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card shadow border-0" style="width:420px;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-person-badge fs-1 text-warning"></i>
                <h4 class="mt-2">Agent Login</h4>
                <p class="text-muted small">Sign in to your agent panel</p>
            </div>

            @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('agent.login.submit') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required autofocus>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3 form-check">
                    <input type="checkbox" name="remember" class="form-check-input" id="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
                <button type="submit" class="btn btn-warning w-100 fw-semibold">
                    <i class="bi bi-box-arrow-in-right"></i> Sign In
                </button>
            </form>
        </div>
    </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```

---

## View 7: Pending Approval Page

**File:** `resources/views/agent/pending.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Pending Approval</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card shadow border-0 text-center" style="max-width:500px;">
        <div class="card-body p-5">
            <i class="bi bi-hourglass-split text-warning" style="font-size:4rem;"></i>
            <h4 class="mt-3">Approval Pending</h4>
            <p class="text-muted">
                Your agent account is under review. Admin will approve your account shortly.
                You will be notified once approved.
            </p>
            <form method="POST" action="{{ route('agent.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
```

---

## View 8: Suspended Page

**File:** `resources/views/agent/suspended.blade.php`

```blade
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Account Suspended</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.1/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="min-vh-100 d-flex align-items-center justify-content-center p-3">
    <div class="card shadow border-0 text-center" style="max-width:500px;">
        <div class="card-body p-5">
            <i class="bi bi-slash-circle text-danger" style="font-size:4rem;"></i>
            <h4 class="mt-3">Account Suspended</h4>
            <p class="text-muted">
                Your agent account has been suspended. Please contact admin for more details.
            </p>
            <form method="POST" action="{{ route('agent.logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </div>
</div>
</body>
</html>
```
