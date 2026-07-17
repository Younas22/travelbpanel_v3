# Agent B2B — Part 8: Admin Sidebar Update & Final Implementation Order

---

## Admin Sidebar — Add Agent Links

**File:** `resources/views/admin/layouts/sidebar.blade.php`

Add this block **after the Suppliers nav-item** (line ~60):

```blade
{{-- Agents Management --}}
<div class="nav-item dropdown">
    <a href="#" class="nav-link dropdown-toggle {{ request()->routeIs('admin.agents*') ? 'active' : '' }}" data-bs-toggle="dropdown">
        <i class="bi bi-person-badge"></i>
        Agents
        @php $pendingAgents = \App\Models\User::agents()->where('approval_status', 'pending')->count(); @endphp
        @if($pendingAgents > 0)
            <span class="badge bg-warning text-dark ms-auto">{{ $pendingAgents }}</span>
        @endif
    </a>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item {{ request()->routeIs('admin.agents.index') ? 'active' : '' }}"
               href="{{ route('admin.agents.index') }}">
                <i class="bi bi-people"></i> All Agents
            </a>
        </li>
        <li>
            <a class="dropdown-item {{ request()->routeIs('admin.agents.create') ? 'active' : '' }}"
               href="{{ route('admin.agents.create') }}">
                <i class="bi bi-person-plus"></i> Add New Agent
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <a class="dropdown-item {{ request()->routeIs('admin.topup*') ? 'active' : '' }}"
               href="{{ route('admin.topup.index') }}">
                <i class="bi bi-wallet2"></i> Top-Up Requests
                @php $pendingTopups = \App\Models\AgentTopupRequest::pending()->count(); @endphp
                @if($pendingTopups > 0)
                    <span class="badge bg-danger ms-1">{{ $pendingTopups }}</span>
                @endif
            </a>
        </li>
    </ul>
</div>
```

---

## Add Create Agent Form

**File:** `resources/views/admin/agents/create.blade.php`

```blade
@extends('admin.layouts.app')
@section('title', 'Add New Agent')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Add New Agent</h4>
    <a href="{{ route('admin.agents.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('admin.agents.store') }}">
            @csrf
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Personal Information</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror"
                                   value="{{ old('first_name') }}" required>
                            @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" name="last_name" class="form-control @error('last_name') is-invalid @enderror"
                                   value="{{ old('last_name') }}" required>
                            @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required>
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Company Information</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Company Name</label>
                            <input type="text" name="company_name" class="form-control" value="{{ old('company_name') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Company Phone</label>
                            <input type="text" name="company_phone" class="form-control" value="{{ old('company_phone') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">CNIC / Business Reg No.</label>
                            <input type="text" name="cnic_or_reg_number" class="form-control" value="{{ old('cnic_or_reg_number') }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Commission Rate (%)</label>
                            <input type="number" name="commission_rate" class="form-control" min="0" max="100" step="0.01"
                                   value="{{ old('commission_rate', 0) }}">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Company Address</label>
                            <textarea name="company_address" class="form-control" rows="2">{{ old('company_address') }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Internal Notes</label>
                            <textarea name="internal_notes" class="form-control" rows="2" placeholder="Admin-only notes...">{{ old('internal_notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-person-plus"></i> Create Agent
            </button>
        </form>
    </div>
</div>
@endsection
```

---

## Implementation Order — Step by Step

### Phase 1: Database (Run these first)
```
Step 1.1  php artisan make:migration add_agent_fields_to_users_table
          → Paste Migration 1 code → php artisan migrate

Step 1.2  php artisan make:migration create_agent_wallets_table
          → Paste Migration 2 code → php artisan migrate

Step 1.3  php artisan make:migration create_agent_wallet_transactions_table
          → Paste Migration 3 code → php artisan migrate

Step 1.4  php artisan make:migration create_agent_permissions_table
          → Paste Migration 4 code → php artisan migrate

Step 1.5  php artisan make:migration create_agent_topup_requests_table
          → Paste Migration 5 code → php artisan migrate

Step 1.6  php artisan make:migration add_agent_id_to_booking_tables
          → Paste Migration 6 code → php artisan migrate
```

### Phase 2: Models
```
Step 2.1  Create app/Models/AgentWallet.php           (Part 2 code)
Step 2.2  Create app/Models/AgentWalletTransaction.php (Part 2 code)
Step 2.3  Create app/Models/AgentPermission.php        (Part 2 code)
Step 2.4  Create app/Models/AgentTopupRequest.php      (Part 2 code)
Step 2.5  Update app/Models/User.php                   (Part 2 additions)
```

### Phase 3: Middleware & Routes
```
Step 3.1  Create app/Http/Middleware/AgentMiddleware.php
Step 3.2  Create app/Http/Middleware/AgentPermissionMiddleware.php
Step 3.3  Update bootstrap/app.php (register middleware aliases + agent route file)
Step 3.4  Create routes/agent.php
Step 3.5  Add agent routes to routes/admin.php
```

### Phase 4: Admin Controllers
```
Step 4.1  Create app/Http/Controllers/Admin/AgentController.php
Step 4.2  Create app/Http/Controllers/Admin/AgentWalletController.php
Step 4.3  Create app/Http/Controllers/Admin/TopupRequestController.php
```

### Phase 5: Admin Views
```
Step 5.1  Create resources/views/admin/agents/index.blade.php
Step 5.2  Create resources/views/admin/agents/create.blade.php
Step 5.3  Create resources/views/admin/agents/edit.blade.php
Step 5.4  Create resources/views/admin/agents/show.blade.php
Step 5.5  Create resources/views/admin/agents/_tab_permissions.blade.php
Step 5.6  Create resources/views/admin/agents/_tab_wallet.blade.php
Step 5.7  Create resources/views/admin/agents/transactions.blade.php
Step 5.8  Create resources/views/admin/topup-requests/index.blade.php
Step 5.9  Create resources/views/admin/topup-requests/show.blade.php
Step 5.10 Update resources/views/admin/layouts/sidebar.blade.php (add Agents link)
```

### Phase 6: Agent Auth + Core
```
Step 6.1  Create app/Http/Controllers/Agent/AuthController.php
Step 6.2  Create resources/views/agent/auth/login.blade.php
Step 6.3  Create resources/views/agent/layouts/app.blade.php
Step 6.4  Create resources/views/agent/layouts/sidebar.blade.php
Step 6.5  Create resources/views/agent/layouts/navbar.blade.php
Step 6.6  Create resources/views/agent/pending.blade.php
Step 6.7  Create resources/views/agent/suspended.blade.php
Step 6.8  Create resources/views/agent/403.blade.php
```

### Phase 7: Agent Dashboard + Wallet + Bookings
```
Step 7.1  Create app/Http/Controllers/Agent/DashboardController.php
Step 7.2  Create resources/views/agent/dashboard.blade.php
Step 7.3  Create app/Http/Controllers/Agent/WalletController.php
Step 7.4  Create resources/views/agent/wallet/index.blade.php
Step 7.5  Create resources/views/agent/wallet/transactions.blade.php
Step 7.6  Create resources/views/agent/wallet/topup.blade.php
Step 7.7  Create app/Http/Controllers/Agent/BookingController.php
Step 7.8  Create resources/views/agent/bookings/index.blade.php
Step 7.9  Create resources/views/agent/bookings/show.blade.php
Step 7.10 Create app/Http/Controllers/Agent/ProfileController.php
Step 7.11 Create resources/views/agent/profile/index.blade.php
```

### Phase 8: Agent Booking Modules
```
Step 8.1  Create app/Http/Controllers/Agent/HotelController.php
Step 8.2  Create agent hotel views (index, search, details, booking, invoice)
Step 8.3  Create app/Http/Controllers/Agent/FlightController.php
Step 8.4  Create agent flight views
Step 8.5  Create app/Http/Controllers/Agent/TourController.php
Step 8.6  Create agent tour views
Step 8.7  Create app/Http/Controllers/Agent/UmrahController.php
Step 8.8  Create agent umrah views
Step 8.9  Create app/Http/Controllers/Agent/VisaController.php
Step 8.10 Create agent visa views
```

### Phase 9: Test Everything
```
Step 9.1  Create agent via admin → verify agent_code generated
Step 9.2  Login as agent → check dashboard
Step 9.3  Admin sets permissions → verify sidebar shows/hides modules
Step 9.4  Agent submits top-up → admin approves → check wallet balance
Step 9.5  Agent makes hotel booking → check wallet deducted
Step 9.6  Try booking with 0 balance → verify block message
Step 9.7  Check booking appears in admin bookings with "Agent" badge
```

---

## Summary of All Files to Create/Modify

### New Files (37 total)
```
Migrations (6):
  database/migrations/2026_03_15_000001_add_agent_fields_to_users_table.php
  database/migrations/2026_03_15_000002_create_agent_wallets_table.php
  database/migrations/2026_03_15_000003_create_agent_wallet_transactions_table.php
  database/migrations/2026_03_15_000004_create_agent_permissions_table.php
  database/migrations/2026_03_15_000005_create_agent_topup_requests_table.php
  database/migrations/2026_03_15_000006_add_agent_id_to_booking_tables.php

Models (4):
  app/Models/AgentWallet.php
  app/Models/AgentWalletTransaction.php
  app/Models/AgentPermission.php
  app/Models/AgentTopupRequest.php

Middleware (2):
  app/Http/Middleware/AgentMiddleware.php
  app/Http/Middleware/AgentPermissionMiddleware.php

Routes (1):
  routes/agent.php

Admin Controllers (3):
  app/Http/Controllers/Admin/AgentController.php
  app/Http/Controllers/Admin/AgentWalletController.php
  app/Http/Controllers/Admin/TopupRequestController.php

Agent Controllers (9):
  app/Http/Controllers/Agent/AuthController.php
  app/Http/Controllers/Agent/DashboardController.php
  app/Http/Controllers/Agent/ProfileController.php
  app/Http/Controllers/Agent/WalletController.php
  app/Http/Controllers/Agent/BookingController.php
  app/Http/Controllers/Agent/HotelController.php
  app/Http/Controllers/Agent/FlightController.php
  app/Http/Controllers/Agent/TourController.php
  app/Http/Controllers/Agent/UmrahController.php
  app/Http/Controllers/Agent/VisaController.php

Views — Admin (9):
  resources/views/admin/agents/index.blade.php
  resources/views/admin/agents/create.blade.php
  resources/views/admin/agents/edit.blade.php
  resources/views/admin/agents/show.blade.php
  resources/views/admin/agents/_tab_permissions.blade.php
  resources/views/admin/agents/_tab_wallet.blade.php
  resources/views/admin/agents/transactions.blade.php
  resources/views/admin/topup-requests/index.blade.php
  resources/views/admin/topup-requests/show.blade.php

Views — Agent (25+):
  resources/views/agent/layouts/app.blade.php
  resources/views/agent/layouts/sidebar.blade.php
  resources/views/agent/layouts/navbar.blade.php
  resources/views/agent/auth/login.blade.php
  resources/views/agent/pending.blade.php
  resources/views/agent/suspended.blade.php
  resources/views/agent/403.blade.php
  resources/views/agent/dashboard.blade.php
  resources/views/agent/profile/index.blade.php
  resources/views/agent/wallet/index.blade.php
  resources/views/agent/wallet/transactions.blade.php
  resources/views/agent/wallet/topup.blade.php
  resources/views/agent/bookings/index.blade.php
  resources/views/agent/bookings/show.blade.php
  resources/views/agent/hotels/index.blade.php
  resources/views/agent/hotels/search.blade.php
  resources/views/agent/hotels/details.blade.php
  resources/views/agent/hotels/booking.blade.php
  resources/views/agent/hotels/invoice.blade.php
  resources/views/agent/flights/index.blade.php
  resources/views/agent/flights/results.blade.php
  resources/views/agent/flights/booking.blade.php
  resources/views/agent/flights/invoice.blade.php
  resources/views/agent/tours/index.blade.php
  resources/views/agent/tours/details.blade.php
  resources/views/agent/tours/booking.blade.php
  resources/views/agent/tours/invoice.blade.php
  resources/views/agent/umrah/index.blade.php
  resources/views/agent/umrah/details.blade.php
  resources/views/agent/umrah/booking.blade.php
  resources/views/agent/umrah/invoice.blade.php
  resources/views/agent/visa/index.blade.php
  resources/views/agent/visa/apply.blade.php
```

### Files to Modify (3):
```
app/Models/User.php               → Add agent fields, relations, methods
bootstrap/app.php                 → Register middleware + agent route file
routes/admin.php                  → Add agent + topup routes
resources/views/admin/layouts/sidebar.blade.php → Add Agents menu item
```
