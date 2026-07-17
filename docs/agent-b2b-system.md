# TravelPanel v2 — Agent B2B System Documentation

**Version:** 1.0
**Date:** 2026-03-15
**System:** TravelPanel v2 (Laravel 11)

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Architecture](#2-architecture)
3. [Database Schema](#3-database-schema)
4. [Permission System](#4-permission-system)
5. [Wallet System](#5-wallet-system)
6. [Agent Panel — Routes & Pages](#6-agent-panel--routes--pages)
7. [Admin Agent Management — Routes & Pages](#7-admin-agent-management--routes--pages)
8. [Controllers](#8-controllers)
9. [Models](#9-models)
10. [Middleware](#10-middleware)
11. [Flows & Logic](#11-flows--logic)
12. [Views Structure](#12-views-structure)
13. [Implementation Order](#13-implementation-order)

---

## 1. System Overview

### Current System (B2C)
```
Admin Panel   →  Manages everything
User/Customer →  Books flights, hotels, tours, umrah, visa
```

### New System (B2B)
```
Admin Panel   →  Manages everything + manages agents
Agent Panel   →  Books on behalf of customers (B2B)
User/Customer →  Still books directly (B2C)
```

### Agent Capabilities
- Agent apne panel se **Hotels, Flights, Tours, Umrah, Visa** book kar sakta hai
- Dono tarah use kar sakta hai: **API (Hotelbeds, Amadeus etc.)** aur **Manual (admin-added)**
- Booking **wallet balance** se hoti hai
- Admin decide karta hai **konse modules** agent ke liye visible hain
- Admin decide karta hai **granular permissions** (API vs Manual)

---

## 2. Architecture

### URL Structure
```
/admin/...          →  Admin Panel (existing)
/agent/...          →  Agent Panel (NEW)
/...                →  Frontend/User Panel (existing)
```

### Middleware Stack
```
Admin Routes  →  auth + AdminMiddleware (existing)
Agent Routes  →  auth + AgentMiddleware (NEW)
              →  AgentPermissionMiddleware per route (NEW)
```

### Role Hierarchy
```
admin
  └── Can manage agents, set permissions, add wallet balance
agent
  └── Can book (only permitted modules), manage own bookings, view wallet
user
  └── Can book directly (existing B2C flow)
```

---

## 3. Database Schema

### 3.1 Modify `users` Table
**Migration:** `add_agent_fields_to_users_table`

```sql
ALTER TABLE users ADD COLUMN company_name VARCHAR(255) NULL AFTER last_name;
ALTER TABLE users ADD COLUMN agent_code VARCHAR(20) UNIQUE NULL;
ALTER TABLE users ADD COLUMN approval_status ENUM('pending','active','suspended') DEFAULT 'pending';
ALTER TABLE users ADD COLUMN approved_by INT UNSIGNED NULL;        -- FK → users.id (admin)
ALTER TABLE users ADD COLUMN approved_at TIMESTAMP NULL;
ALTER TABLE users ADD COLUMN rejection_reason TEXT NULL;
ALTER TABLE users ADD COLUMN company_address TEXT NULL;
ALTER TABLE users ADD COLUMN company_phone VARCHAR(30) NULL;
ALTER TABLE users ADD COLUMN company_logo VARCHAR(255) NULL;
ALTER TABLE users ADD COLUMN cnic_or_reg_number VARCHAR(100) NULL; -- CNIC/Business Reg
```

---

### 3.2 New Table: `agent_wallets`
**Migration:** `create_agent_wallets_table`

```sql
CREATE TABLE agent_wallets (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent_id        INT UNSIGNED NOT NULL UNIQUE,
    balance         DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    currency        VARCHAR(10) NOT NULL DEFAULT 'PKR',
    total_credited  DECIMAL(12,2) NOT NULL DEFAULT 0.00,  -- lifetime total added
    total_debited   DECIMAL(12,2) NOT NULL DEFAULT 0.00,  -- lifetime total spent
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

### 3.3 New Table: `agent_wallet_transactions`
**Migration:** `create_agent_wallet_transactions_table`

```sql
CREATE TABLE agent_wallet_transactions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent_id        INT UNSIGNED NOT NULL,
    type            ENUM('credit','debit') NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,
    balance_before  DECIMAL(12,2) NOT NULL,
    balance_after   DECIMAL(12,2) NOT NULL,
    reference       VARCHAR(100) NULL,       -- booking ref ya payment ref
    booking_type    VARCHAR(50) NULL,        -- hotel/flight/tour/umrah/visa
    booking_id      INT UNSIGNED NULL,       -- booking table ka id
    note            TEXT NULL,               -- admin ya agent note
    payment_method  VARCHAR(100) NULL,       -- bank transfer, cash, etc (credit only)
    performed_by    INT UNSIGNED NOT NULL,   -- kaun ne kiya (admin/agent)
    status          ENUM('completed','pending','reversed') DEFAULT 'completed',
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (performed_by) REFERENCES users(id)
);
```

---

### 3.4 New Table: `agent_permissions`
**Migration:** `create_agent_permissions_table`

```sql
CREATE TABLE agent_permissions (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent_id        INT UNSIGNED NOT NULL,
    permission_key  VARCHAR(100) NOT NULL,
    is_enabled      TINYINT(1) NOT NULL DEFAULT 1,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    UNIQUE KEY unique_agent_permission (agent_id, permission_key),
    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE
);
```

#### Permission Keys (Full List)

| Permission Key       | Description                               |
|----------------------|-------------------------------------------|
| `module.hotels`      | Hotels section access                     |
| `module.flights`     | Flights section access                    |
| `module.tours`       | Tours section access                      |
| `module.umrah`       | Umrah section access                      |
| `module.visa`        | Visa section access                       |
| `hotels.api`         | API hotels (Hotelbeds, Agoda) use kar sakta hai |
| `hotels.manual`      | Admin-added manual hotels use kar sakta hai |
| `flights.api`        | API flights (Amadeus, Sabre) use kar sakta hai |
| `flights.manual`     | (future — manual flight routes)           |
| `tours.api`          | (future — tour API)                       |
| `tours.manual`       | Admin-added tours book kar sakta hai      |
| `umrah.api`          | (future)                                  |
| `umrah.manual`       | Admin-added umrah packages                |
| `visa.submit`        | Visa request submit kar sakta hai         |
| `bookings.view_all`  | Sirf apni bookings ya sab agent bookings  |
| `wallet.view`        | Wallet balance dekhna                     |
| `wallet.request`     | Top-up request bhejna                     |

---

### 3.5 New Table: `agent_topup_requests`
**Migration:** `create_agent_topup_requests_table`

Agent admin ko top-up request bhejta hai — admin approve karta hai aur wallet mein add karta hai.

```sql
CREATE TABLE agent_topup_requests (
    id              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    agent_id        INT UNSIGNED NOT NULL,
    amount          DECIMAL(12,2) NOT NULL,
    payment_method  VARCHAR(100) NULL,       -- bank, cash, etc
    payment_proof   VARCHAR(255) NULL,       -- screenshot/receipt upload
    note            TEXT NULL,
    status          ENUM('pending','approved','rejected') DEFAULT 'pending',
    reviewed_by     INT UNSIGNED NULL,       -- admin id
    reviewed_at     TIMESTAMP NULL,
    rejection_note  TEXT NULL,
    created_at      TIMESTAMP NULL,
    updated_at      TIMESTAMP NULL,

    FOREIGN KEY (agent_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewed_by) REFERENCES users(id)
);
```

---

### 3.6 Modify Existing Booking Tables
**Migration:** `add_agent_id_to_booking_tables`

Har booking table mein `agent_id` add karna hai taake pata chale booking agent ne ki ya direct user ne.

```sql
-- hotels_booking
ALTER TABLE hotels_booking  ADD COLUMN agent_id INT UNSIGNED NULL AFTER id;
ALTER TABLE hotels_booking  ADD COLUMN booked_via ENUM('direct','agent') DEFAULT 'direct';

-- flights_booking
ALTER TABLE flights_booking ADD COLUMN agent_id INT UNSIGNED NULL AFTER id;
ALTER TABLE flights_booking ADD COLUMN booked_via ENUM('direct','agent') DEFAULT 'direct';

-- tours_booking
ALTER TABLE tours_booking   ADD COLUMN agent_id INT UNSIGNED NULL AFTER id;
ALTER TABLE tours_booking   ADD COLUMN booked_via ENUM('direct','agent') DEFAULT 'direct';

-- umrah_bookings
ALTER TABLE umrah_bookings  ADD COLUMN agent_id INT UNSIGNED NULL AFTER id;
ALTER TABLE umrah_bookings  ADD COLUMN booked_via ENUM('direct','agent') DEFAULT 'direct';

-- visa_requests
ALTER TABLE visa_requests   ADD COLUMN agent_id INT UNSIGNED NULL AFTER id;
ALTER TABLE visa_requests   ADD COLUMN booked_via ENUM('direct','agent') DEFAULT 'direct';
```

---

## 4. Permission System

### 4.1 How It Works

```
Admin → Agent ki profile mein permission toggle karta hai
Agent → Login karne par session mein permissions load hoti hain
Middleware → Har agent route pe check karta hai permission hai ya nahi
Views → Sidebar sirf permitted modules dikhata hai
```

### 4.2 Default Permissions (Naye Agent ke Liye)

Jab admin naya agent create kare, **koi bhi permission default on nahi hoti**. Admin manually enable karta hai.

### 4.3 Permission Check Flow

```
Agent → /agent/hotels/search hits karta hai
        ↓
AgentMiddleware → Agent active hai?  No → redirect login
        ↓
AgentPermissionMiddleware('module.hotels') → Permission hai?
        ↓ No                               ↓ Yes
   403 page (module not allowed)      Controller chalega
```

### 4.4 Admin Permission UI

Admin ke agent detail page pe ek **Permissions tab** hoga:

```
[ Modules ]
  ☑ Hotels
  ☐ Flights
  ☑ Tours
  ☐ Umrah
  ☑ Visa

[ Hotel Options ]
  ☑ API Hotels (Hotelbeds/Agoda)
  ☑ Manual Hotels

[ Flight Options ]
  ☑ API Flights (Amadeus/Sabre)
  ☐ Manual Flights

[ Booking Options ]
  ☑ Can view all bookings
  ☑ Can request wallet top-up
```

---

## 5. Wallet System

### 5.1 Wallet Flow (Step by Step)

```
STEP 1: Agent → Admin ko payment karta hai (bank/cash/etc)
STEP 2: Agent → Panel mein top-up request submit karta hai
          → Amount enter karta hai
          → Payment method select karta hai
          → Receipt/proof upload karta hai (optional)
STEP 3: Admin → Top-up requests list dekhta hai
          → Proof verify karta hai
          → Approve karta hai → wallet balance automatically add ho jata hai
          → Ya Reject karta hai → rejection note ke sath
STEP 4: Agent → Booking karte waqt wallet se automatically deduct hota hai
          → Agar balance kam ho → booking rok di jati hai
STEP 5: Agent → Wallet page pe poori transaction history dekhta hai
```

### 5.2 Wallet Business Rules

```
- Minimum balance check: booking se pehle check hota hai
- Booking confirm hone par IMMEDIATELY deduct hota hai
- Booking cancel hone par admin manually refund kar sakta hai (credit)
- Admin directly bhi balance add/deduct kar sakta hai (manual adjustment)
- Agent negative balance mein nahi ja sakta
- Har transaction ka record rehta hai (credit/debit, amount, balance before/after)
```

### 5.3 Wallet Transaction Types

| Type   | Event                                   | Performed By |
|--------|-----------------------------------------|-------------|
| credit | Admin ne balance add kiya               | Admin       |
| credit | Top-up request approved                 | Admin       |
| debit  | Hotel booking                           | Agent       |
| debit  | Flight booking                          | Agent       |
| debit  | Tour booking                            | Agent       |
| debit  | Umrah booking                           | Agent       |
| debit  | Visa request fee                        | Agent       |
| credit | Booking cancel/refund                   | Admin       |
| credit | Manual adjustment (overpayment etc.)    | Admin       |
| debit  | Manual adjustment (correction etc.)     | Admin       |

---

## 6. Agent Panel — Routes & Pages

### 6.1 Route File: `routes/agent.php`

```php
// Middleware: auth + agent_active
Route::middleware(['auth', 'agent'])->prefix('agent')->name('agent.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [Agent\DashboardController::class, 'index'])
        ->name('dashboard');

    // Profile
    Route::get('/profile', [Agent\ProfileController::class, 'index'])->name('profile');
    Route::post('/profile/update', [Agent\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [Agent\ProfileController::class, 'changePassword'])->name('profile.password');

    // Wallet
    Route::middleware('agent.permission:wallet.view')->group(function () {
        Route::get('/wallet', [Agent\WalletController::class, 'index'])->name('wallet');
        Route::get('/wallet/transactions', [Agent\WalletController::class, 'transactions'])->name('wallet.transactions');
    });
    Route::middleware('agent.permission:wallet.request')->group(function () {
        Route::get('/wallet/topup', [Agent\WalletController::class, 'topupForm'])->name('wallet.topup');
        Route::post('/wallet/topup', [Agent\WalletController::class, 'topupRequest'])->name('wallet.topup.submit');
    });

    // Bookings (all agent's bookings)
    Route::get('/bookings', [Agent\BookingController::class, 'index'])->name('bookings');
    Route::get('/bookings/{type}/{id}', [Agent\BookingController::class, 'show'])->name('bookings.show');

    // Hotels Module
    Route::middleware('agent.permission:module.hotels')->prefix('hotels')->name('hotels.')->group(function () {
        Route::get('/', [Agent\HotelController::class, 'index'])->name('index');
        Route::get('/search', [Agent\HotelController::class, 'search'])->name('search');
        Route::get('/details/{id}', [Agent\HotelController::class, 'details'])->name('details');
        Route::get('/booking/{id}', [Agent\HotelController::class, 'bookingForm'])->name('booking');
        Route::post('/booking/confirm', [Agent\HotelController::class, 'confirmBooking'])->name('booking.confirm');
        Route::get('/invoice/{ref}', [Agent\HotelController::class, 'invoice'])->name('invoice');
    });

    // Flights Module
    Route::middleware('agent.permission:module.flights')->prefix('flights')->name('flights.')->group(function () {
        Route::get('/', [Agent\FlightController::class, 'index'])->name('index');
        Route::get('/search', [Agent\FlightController::class, 'search'])->name('search');
        Route::get('/results', [Agent\FlightController::class, 'results'])->name('results');
        Route::post('/booking', [Agent\FlightController::class, 'booking'])->name('booking');
        Route::post('/booking/confirm', [Agent\FlightController::class, 'confirmBooking'])->name('booking.confirm');
        Route::get('/invoice/{ref}', [Agent\FlightController::class, 'invoice'])->name('invoice');
    });

    // Tours Module
    Route::middleware('agent.permission:module.tours')->prefix('tours')->name('tours.')->group(function () {
        Route::get('/', [Agent\TourController::class, 'index'])->name('index');
        Route::get('/search', [Agent\TourController::class, 'search'])->name('search');
        Route::get('/details/{slug}', [Agent\TourController::class, 'details'])->name('details');
        Route::get('/booking/{id}', [Agent\TourController::class, 'bookingForm'])->name('booking');
        Route::post('/booking/confirm', [Agent\TourController::class, 'confirmBooking'])->name('booking.confirm');
        Route::get('/invoice/{ref}', [Agent\TourController::class, 'invoice'])->name('invoice');
    });

    // Umrah Module
    Route::middleware('agent.permission:module.umrah')->prefix('umrah')->name('umrah.')->group(function () {
        Route::get('/', [Agent\UmrahController::class, 'index'])->name('index');
        Route::get('/search', [Agent\UmrahController::class, 'search'])->name('search');
        Route::get('/details/{slug}', [Agent\UmrahController::class, 'details'])->name('details');
        Route::get('/booking/{id}', [Agent\UmrahController::class, 'bookingForm'])->name('booking');
        Route::post('/booking/confirm', [Agent\UmrahController::class, 'confirmBooking'])->name('booking.confirm');
        Route::get('/invoice/{ref}', [Agent\UmrahController::class, 'invoice'])->name('invoice');
    });

    // Visa Module
    Route::middleware('agent.permission:module.visa')->prefix('visa')->name('visa.')->group(function () {
        Route::get('/', [Agent\VisaController::class, 'index'])->name('index');
        Route::get('/apply', [Agent\VisaController::class, 'form'])->name('apply');
        Route::post('/apply', [Agent\VisaController::class, 'submit'])->name('submit');
        Route::get('/status/{id}', [Agent\VisaController::class, 'status'])->name('status');
    });
});
```

---

### 6.2 Agent Panel Pages (Detailed)

#### Page 1: Dashboard (`/agent/dashboard`)
```
┌─────────────────────────────────────────────────────┐
│  Welcome, [Agent Name] | [Company Name]             │
│  Agent Code: AGT-0001  | Status: Active             │
├──────────────┬──────────────┬───────────────────────┤
│ Wallet       │ Total        │ This Month            │
│ Balance      │ Bookings     │ Bookings              │
│ PKR 45,000   │ 128          │ 12                    │
├──────────────┴──────────────┴───────────────────────┤
│  Quick Actions                                      │
│  [Search Hotel] [Search Flight] [Book Tour] ...     │
├─────────────────────────────────────────────────────┤
│  Recent Bookings (last 5)                           │
│  REF      │ Type    │ Amount  │ Status │ Date       │
│  HTL-001  │ Hotel   │ 12,000  │ Conf.  │ 14 Mar     │
│  FLT-002  │ Flight  │ 45,000  │ Conf.  │ 13 Mar     │
├─────────────────────────────────────────────────────┤
│  Recent Wallet Transactions (last 5)                │
│  + 50,000  Admin Top-up         14 Mar             │
│  - 12,000  Hotel Booking HTL-001 14 Mar            │
└─────────────────────────────────────────────────────┘
```

#### Page 2: Wallet (`/agent/wallet`)
```
┌─────────────────────────────────────────────────────┐
│  MY WALLET                                          │
│                                                     │
│  Current Balance:  PKR 45,000                       │
│  Total Credited:   PKR 200,000                      │
│  Total Spent:      PKR 155,000                      │
│                                                     │
│  [Request Top-Up]                                   │
├─────────────────────────────────────────────────────┤
│  Transaction History                                │
│  Filter: [All] [Credits] [Debits] | Date Range      │
│                                                     │
│  Date    │ Type   │ Details          │ Amount       │
│  14 Mar  │ Credit │ Top-up approved  │ + 50,000     │
│  14 Mar  │ Debit  │ Hotel: HTL-001   │ - 12,000     │
│  13 Mar  │ Debit  │ Flight: FLT-002  │ - 45,000     │
└─────────────────────────────────────────────────────┘
```

#### Page 3: Top-Up Request (`/agent/wallet/topup`)
```
┌─────────────────────────────────────────────────────┐
│  REQUEST WALLET TOP-UP                              │
│                                                     │
│  Amount to Add:    [_____________] PKR              │
│  Payment Method:   [Bank Transfer ▼]                │
│  Bank/Account:     [_________________________]      │
│  Transaction ID:   [_________________________]      │
│  Payment Proof:    [Choose File] (optional)         │
│  Note:             [_________________________]      │
│                                                     │
│  [Submit Request]                                   │
└─────────────────────────────────────────────────────┘
```

#### Page 4: Bookings List (`/agent/bookings`)
```
┌─────────────────────────────────────────────────────┐
│  MY BOOKINGS                                        │
│                                                     │
│  Filter: [All ▼] [Hotels] [Flights] [Tours] ...    │
│  Status: [All ▼]  Date: [____] to [____]           │
│                                                     │
│  Ref       │ Type   │ Detail   │ Amount │ Status    │
│  HTL-001   │ Hotel  │ Marriott │ 12,000 │ Confirmed │
│  FLT-002   │ Flight │ PK-301   │ 45,000 │ Confirmed │
│  TUR-003   │ Tour   │ Murree   │  8,000 │ Pending   │
└─────────────────────────────────────────────────────┘
```

#### Page 5: Agent Sidebar (Conditional — based on permissions)
```
┌─────────────────┐
│ [Logo]          │
│ TravelPanel     │
├─────────────────┤
│ Dashboard       │ ← Always visible
│                 │
│ BOOKINGS        │
│ → All Bookings  │ ← Always visible
│                 │
│ MODULES         │
│ → Hotels        │ ← Only if module.hotels = true
│ → Flights       │ ← Only if module.flights = true
│ → Tours         │ ← Only if module.tours = true
│ → Umrah         │ ← Only if module.umrah = true
│ → Visa          │ ← Only if module.visa = true
│                 │
│ WALLET          │
│ → My Wallet     │ ← Only if wallet.view = true
│ → Request Top-Up│ ← Only if wallet.request = true
│                 │
│ → My Profile    │ ← Always visible
│ → Logout        │ ← Always visible
└─────────────────┘
```

---

## 7. Admin Agent Management — Routes & Pages

### 7.1 Admin Routes (add to `routes/admin.php`)

```php
// Agent Management
Route::prefix('agents')->name('admin.agents.')->group(function () {
    Route::get('/',                         [Admin\AgentController::class, 'index'])       ->name('index');
    Route::get('/create',                   [Admin\AgentController::class, 'create'])      ->name('create');
    Route::post('/',                        [Admin\AgentController::class, 'store'])       ->name('store');
    Route::get('/{agent}',                  [Admin\AgentController::class, 'show'])        ->name('show');
    Route::get('/{agent}/edit',             [Admin\AgentController::class, 'edit'])        ->name('edit');
    Route::put('/{agent}',                  [Admin\AgentController::class, 'update'])      ->name('update');
    Route::delete('/{agent}',               [Admin\AgentController::class, 'destroy'])     ->name('destroy');

    // Approval
    Route::post('/{agent}/approve',         [Admin\AgentController::class, 'approve'])     ->name('approve');
    Route::post('/{agent}/suspend',         [Admin\AgentController::class, 'suspend'])     ->name('suspend');
    Route::post('/{agent}/activate',        [Admin\AgentController::class, 'activate'])    ->name('activate');

    // Permissions
    Route::get('/{agent}/permissions',      [Admin\AgentController::class, 'permissions']) ->name('permissions');
    Route::post('/{agent}/permissions',     [Admin\AgentController::class, 'savePermissions'])->name('permissions.save');

    // Wallet
    Route::get('/{agent}/wallet',           [Admin\AgentWalletController::class, 'index'])       ->name('wallet');
    Route::post('/{agent}/wallet/credit',   [Admin\AgentWalletController::class, 'credit'])      ->name('wallet.credit');
    Route::post('/{agent}/wallet/debit',    [Admin\AgentWalletController::class, 'debit'])       ->name('wallet.debit');
    Route::get('/{agent}/wallet/transactions', [Admin\AgentWalletController::class, 'transactions'])->name('wallet.transactions');
});

// Top-Up Requests
Route::prefix('topup-requests')->name('admin.topup.')->group(function () {
    Route::get('/',             [Admin\TopupRequestController::class, 'index'])   ->name('index');
    Route::get('/{request}',    [Admin\TopupRequestController::class, 'show'])    ->name('show');
    Route::post('/{request}/approve', [Admin\TopupRequestController::class, 'approve'])->name('approve');
    Route::post('/{request}/reject',  [Admin\TopupRequestController::class, 'reject']) ->name('reject');
});
```

---

### 7.2 Admin Pages (Detailed)

#### Page 1: Agents List (`/admin/agents`)
```
┌─────────────────────────────────────────────────────────┐
│  AGENTS MANAGEMENT                    [+ Add Agent]     │
│                                                         │
│  Filter: Status [All ▼]  Search [_____________]        │
│                                                         │
│  Name        │ Code    │ Company  │ Status  │ Balance │ Actions │
│  Ali Ahmed   │ AGT-001 │ Ali Travels│ Active │ 45,000 │ 👁 ✏ 🔑 💰 │
│  Sara Khan   │ AGT-002 │ Sara Corp │ Pending │ 0      │ ✅ ❌    │
│  Zain Malik  │ AGT-003 │ Zain Tours│ Suspended│ 2,000 │ ▶ 🗑    │
└─────────────────────────────────────────────────────────┘

Icons: 👁 View  ✏ Edit  🔑 Permissions  💰 Wallet  ✅ Approve  ❌ Reject  ▶ Activate  🗑 Delete
```

#### Page 2: Agent Detail (`/admin/agents/{id}`)
```
Tabs:
[Overview] [Bookings] [Permissions] [Wallet] [Transactions]
```

**Overview Tab:**
```
┌─────────────────────────────────────────────────────┐
│ Agent Info                    │ Stats               │
│                               │                     │
│ Name: Ali Ahmed               │ Total Bookings: 128 │
│ Code: AGT-001                 │ Total Revenue: 890K │
│ Email: ali@travels.com        │ Commission: 8,900   │
│ Company: Ali Travels          │ Wallet Balance: 45K │
│ Phone: 0300-1234567           │                     │
│ Status: Active ✓              │                     │
│ Approved By: Admin (14 Mar)   │                     │
│                               │                     │
│ [Approve] [Suspend] [Edit]    │                     │
└─────────────────────────────────────────────────────┘
```

**Permissions Tab:**
```
┌─────────────────────────────────────────────────────┐
│ AGENT PERMISSIONS                    [Save Changes] │
│                                                     │
│ MODULE ACCESS                                       │
│ ┌─────────────────────────────────────────────────┐ │
│ │ [✓] Hotels                                      │ │
│ │     [✓] API Hotels (Hotelbeds/Agoda)            │ │
│ │     [✓] Manual Hotels                           │ │
│ │                                                 │ │
│ │ [✓] Flights                                     │ │
│ │     [✓] API Flights (Amadeus/Sabre)             │ │
│ │     [ ] Manual Flights                          │ │
│ │                                                 │ │
│ │ [✓] Tours                                       │ │
│ │     [✓] Manual Tours                            │ │
│ │                                                 │ │
│ │ [ ] Umrah                                       │ │
│ │                                                 │ │
│ │ [✓] Visa                                        │ │
│ └─────────────────────────────────────────────────┘ │
│                                                     │
│ WALLET                                              │
│ ┌─────────────────────────────────────────────────┐ │
│ │ [✓] Can view wallet balance                     │ │
│ │ [✓] Can request top-up                          │ │
│ └─────────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────────┘
```

**Wallet Tab:**
```
┌─────────────────────────────────────────────────────┐
│ AGENT WALLET — Ali Ahmed (AGT-001)                  │
│                                                     │
│  Current Balance:  PKR 45,000                       │
│  Total Credited:   PKR 200,000                      │
│  Total Debited:    PKR 155,000                      │
│                                                     │
│ ┌──────────────────┐  ┌──────────────────────────┐  │
│ │ ADD BALANCE      │  │ DEDUCT BALANCE           │  │
│ │ Amount: [_____]  │  │ Amount: [_____]          │  │
│ │ Method: [_____]  │  │ Reason: [_____]          │  │
│ │ Note:   [_____]  │  │ Note:   [_____]          │  │
│ │ [Add Credit]     │  │ [Deduct]                 │  │
│ └──────────────────┘  └──────────────────────────┘  │
└─────────────────────────────────────────────────────┘
```

#### Page 3: Top-Up Requests (`/admin/topup-requests`)
```
┌──────────────────────────────────────────────────────────┐
│  TOP-UP REQUESTS                                         │
│                                                          │
│  Filter: [Pending ▼]  Date: [____] to [____]            │
│                                                          │
│  Agent     │ Amount  │ Method      │ Date   │ Status │ Actions │
│  Ali Ahmed │ 50,000  │ Bank Trans. │ 14 Mar │ Pending│ ✅ ❌   │
│  Sara Khan │ 20,000  │ Cash        │ 13 Mar │ Pending│ ✅ ❌   │
│  Zain Malik│ 10,000  │ Bank Trans. │ 12 Mar │ Approved│ 👁     │
└──────────────────────────────────────────────────────────┘
```

---

## 8. Controllers

### 8.1 New Agent Controllers (`app/Http/Controllers/Agent/`)

| Controller | File | Responsibility |
|---|---|---|
| `DashboardController` | `Agent/DashboardController.php` | Dashboard stats, recent bookings, wallet summary |
| `ProfileController` | `Agent/ProfileController.php` | Profile view, update, password change |
| `WalletController` | `Agent/WalletController.php` | Wallet view, transaction history, top-up request |
| `BookingController` | `Agent/BookingController.php` | All bookings list (all types), detail view |
| `HotelController` | `Agent/HotelController.php` | Hotel search, details, booking form, confirm, invoice |
| `FlightController` | `Agent/FlightController.php` | Flight search, results, booking, confirm, invoice |
| `TourController` | `Agent/TourController.php` | Tour listing, search, details, booking, invoice |
| `UmrahController` | `Agent/UmrahController.php` | Umrah listing, search, details, booking, invoice |
| `VisaController` | `Agent/VisaController.php` | Visa form, submit, status check |

---

### 8.2 New Admin Controllers (`app/Http/Controllers/Admin/`)

| Controller | File | Responsibility |
|---|---|---|
| `AgentController` | `Admin/AgentController.php` | Agent CRUD, approve/suspend, permissions |
| `AgentWalletController` | `Admin/AgentWalletController.php` | Add/deduct balance, view wallet, transactions |
| `TopupRequestController` | `Admin/TopupRequestController.php` | View, approve, reject top-up requests |

---

### 8.3 Key Methods Per Controller

#### `Agent/WalletController`
```php
index()         // wallet balance + recent transactions
transactions()  // full transaction history with filters
topupForm()     // top-up request form
topupRequest()  // submit top-up request (validate, save, notify admin)
```

#### `Agent/HotelController`
```php
index()          // hotel search form
search()         // search results (API + Manual based on permissions)
details($id)     // hotel detail page
bookingForm($id) // booking form with wallet balance check
confirmBooking() // validate → check wallet → deduct → save booking → show confirmation
invoice($ref)    // printable invoice
```

#### `Admin/AgentController`
```php
index()             // agents list with filters
create()            // new agent form
store()             // save agent, create wallet record, generate agent_code
show($agent)        // agent detail (tabs)
edit($agent)        // edit form
update($agent)      // update agent info
destroy($agent)     // delete agent
approve($agent)     // set approval_status=active, set approved_by, send email
suspend($agent)     // set approval_status=suspended, send email
activate($agent)    // reactivate suspended agent
permissions($agent) // load permissions form
savePermissions()   // save/update permissions (upsert in agent_permissions)
```

#### `Admin/AgentWalletController`
```php
index($agent)        // wallet overview + recent transactions
credit($agent)       // add balance (manual admin credit)
debit($agent)        // deduct balance (manual admin deduction)
transactions($agent) // full transaction history
```

#### `Admin/TopupRequestController`
```php
index()         // all top-up requests with pending count
show($request)  // request detail with proof image
approve($request) // approve → add balance to wallet → create transaction → notify agent
reject($request)  // reject → save rejection note → notify agent
```

---

## 9. Models

### 9.1 New Models

#### `AgentWallet`
```php
// Table: agent_wallets
// Fillable: agent_id, balance, currency, total_credited, total_debited

// Relations:
public function agent()        // belongsTo User
public function transactions() // hasMany AgentWalletTransaction

// Methods:
public function hasSufficientBalance($amount): bool
public function credit($amount, $note, $performedBy, $method = null, $reference = null, $bookingType = null, $bookingId = null)
public function debit($amount, $note, $performedBy, $bookingType = null, $bookingId = null, $reference = null)

// Scopes:
public function scopeByAgent($query, $agentId)
```

#### `AgentWalletTransaction`
```php
// Table: agent_wallet_transactions
// Fillable: agent_id, type, amount, balance_before, balance_after,
//           reference, booking_type, booking_id, note, payment_method, performed_by, status

// Relations:
public function agent()       // belongsTo User
public function performedBy() // belongsTo User (foreign key: performed_by)

// Scopes:
public function scopeCredits($query)
public function scopeDebits($query)
public function scopeByAgent($query, $agentId)
public function scopeByDateRange($query, $from, $to)
```

#### `AgentPermission`
```php
// Table: agent_permissions
// Fillable: agent_id, permission_key, is_enabled

// Relations:
public function agent() // belongsTo User

// Static Methods:
public static function hasPermission($agentId, $key): bool
public static function getAgentPermissions($agentId): array  // ['module.hotels' => true, ...]
public static function savePermissions($agentId, array $permissions): void
```

#### `AgentTopupRequest`
```php
// Table: agent_topup_requests
// Fillable: agent_id, amount, payment_method, payment_proof, note,
//           status, reviewed_by, reviewed_at, rejection_note

// Relations:
public function agent()      // belongsTo User
public function reviewedBy() // belongsTo User (foreign key: reviewed_by)

// Scopes:
public function scopePending($query)
public function scopeApproved($query)
public function scopeRejected($query)
public function scopeByAgent($query, $agentId)
```

---

### 9.2 Update Existing Models

#### `User` Model — Add Relations
```php
// Add to fillable:
'company_name', 'agent_code', 'approval_status',
'approved_by', 'approved_at', 'rejection_reason',
'company_address', 'company_phone', 'company_logo', 'cnic_or_reg_number'

// Add relations:
public function wallet()          // hasOne AgentWallet
public function permissions()     // hasMany AgentPermission
public function topupRequests()   // hasMany AgentTopupRequest
public function agentBookings()   // (flight/hotel/tour/umrah where agent_id = this->id)

// Add methods:
public function hasPermission(string $key): bool
public function isActiveAgent(): bool
public function getAgentCode(): string  // auto-generate AGT-XXXX
public function approveAgent($adminId): void
public function suspendAgent(string $reason = null): void
```

---

## 10. Middleware

### 10.1 `AgentMiddleware`
**File:** `app/Http/Middleware/AgentMiddleware.php`

```php
// Checks:
// 1. User authenticated hai?        → No  → redirect to login
// 2. User type agent hai?            → No  → redirect to 403
// 3. Agent approval_status = active? → No  → redirect to pending page
//
// Pending page: /agent/pending  (batata hai ke admin ne abhi approve nahi kiya)
// Suspended page: /agent/suspended (batata hai account suspend hai)
```

### 10.2 `AgentPermissionMiddleware`
**File:** `app/Http/Middleware/AgentPermissionMiddleware.php`

```php
// Usage: Route::middleware('agent.permission:module.hotels')
//
// Checks:
// 1. auth()->user()->hasPermission($permissionKey)?
//    → No  → abort(403) with custom view "agent.403"
//    → Yes → continue
```

### 10.3 Register in `bootstrap/app.php`
```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'admin'            => AdminMiddleware::class,     // existing
        'agent'            => AgentMiddleware::class,     // new
        'agent.permission' => AgentPermissionMiddleware::class, // new
    ]);
})
```

---

## 11. Flows & Logic

### 11.1 Agent Registration Flow
```
Option A — Admin Creates Agent:
  Admin → /admin/agents/create
       → Fill form (name, email, company, password)
       → Save → auto generate agent_code (AGT-0001)
       → Create agent_wallet record (balance: 0)
       → Set approval_status = active
       → Send welcome email to agent

Option B — Agent Self-Registers (future):
  Agent → /agent/register
       → Fill form → Save → approval_status = pending
       → Admin gets notification
       → Admin reviews → Approve / Reject
```

### 11.2 Agent Booking Flow (Hotel Example)
```
Agent → /agent/hotels
     → Search hotels (API/Manual based on permissions)
     → Select hotel → view details
     → Fill booking form (guest info, dates, rooms)
     → Click "Confirm Booking"
        ↓
     [CONTROLLER LOGIC]
     1. Validate form data
     2. Check agent wallet balance >= booking amount
        → Insufficient? → redirect back with error "Insufficient wallet balance"
     3. Begin DB transaction
     4. Save booking to hotels_booking (with agent_id, booked_via='agent')
     5. Deduct from wallet: AgentWallet::debit(amount, 'Hotel Booking', agent_id, 'hotel', booking_id)
     6. Commit DB transaction
     7. Redirect to → /agent/hotels/invoice/{ref}
```

### 11.3 Wallet Top-Up Flow
```
Agent → /agent/wallet/topup
     → Fill form (amount, method, proof)
     → Submit → saved in agent_topup_requests (status=pending)
     → Admin gets notification/badge count in sidebar

Admin → /admin/topup-requests
     → See pending request
     → Click Approve
        ↓
     [CONTROLLER LOGIC]
     1. Set request status = approved
     2. AgentWallet::credit(amount, 'Top-up Request #ID approved', admin_id)
     3. Send email to agent: "Your top-up of PKR X has been approved"
     4. Redirect back with success

     → OR Click Reject
     1. Set status = rejected, save rejection_note
     2. Send email to agent: "Your top-up request was rejected: [reason]"
```

### 11.4 Permission Save Flow (Admin)
```
Admin → /admin/agents/{id}/permissions
     → Checkboxes for each permission key
     → Submit

[CONTROLLER]
foreach $permissions as $key:
    AgentPermission::updateOrCreate(
        ['agent_id' => $agent->id, 'permission_key' => $key],
        ['is_enabled' => $value]
    )

// Cache permissions per agent (optional optimization)
Cache::forget("agent_permissions_{$agent->id}");
```

---

## 12. Views Structure

### 12.1 Agent Panel Views (`resources/views/agent/`)

```
views/agent/
├── layouts/
│   ├── app.blade.php          ← Master layout (sidebar + navbar)
│   ├── sidebar.blade.php      ← Conditional sidebar (permissions-based)
│   └── navbar.blade.php
├── dashboard.blade.php
├── profile/
│   └── index.blade.php
├── wallet/
│   ├── index.blade.php        ← Balance + transactions
│   └── topup.blade.php        ← Top-up request form
├── bookings/
│   ├── index.blade.php        ← All bookings (all types)
│   └── show.blade.php         ← Booking detail
├── hotels/
│   ├── index.blade.php        ← Search form
│   ├── results.blade.php      ← Search results
│   ├── details.blade.php      ← Hotel detail
│   ├── booking.blade.php      ← Booking form
│   └── invoice.blade.php
├── flights/
│   ├── index.blade.php
│   ├── results.blade.php
│   ├── booking.blade.php
│   └── invoice.blade.php
├── tours/
│   ├── index.blade.php
│   ├── details.blade.php
│   ├── booking.blade.php
│   └── invoice.blade.php
├── umrah/
│   ├── index.blade.php
│   ├── details.blade.php
│   ├── booking.blade.php
│   └── invoice.blade.php
├── visa/
│   ├── index.blade.php
│   └── apply.blade.php
├── pending.blade.php          ← Approval pending page
└── suspended.blade.php        ← Account suspended page
```

### 12.2 Admin Agent Views (`resources/views/admin/agents/`)

```
views/admin/agents/
├── index.blade.php            ← Agents list
├── create.blade.php           ← Add agent form
├── edit.blade.php             ← Edit agent form
├── show.blade.php             ← Agent detail (tabs)
│   ├── _tab_overview.blade.php
│   ├── _tab_bookings.blade.php
│   ├── _tab_permissions.blade.php
│   └── _tab_wallet.blade.php
└── wallet/
    └── transactions.blade.php

views/admin/topup-requests/
├── index.blade.php
└── show.blade.php
```

---

## 13. Implementation Order

### Phase 1: Foundation
```
Step 1.1  Migration — users table (agent fields)
Step 1.2  Migration — agent_wallets table
Step 1.3  Migration — agent_wallet_transactions table
Step 1.4  Migration — agent_permissions table
Step 1.5  Migration — agent_topup_requests table
Step 1.6  Migration — add agent_id to booking tables
Step 1.7  Models (AgentWallet, AgentWalletTransaction, AgentPermission, AgentTopupRequest)
Step 1.8  Update User model (fillable, relations, methods)
Step 1.9  Middleware (AgentMiddleware, AgentPermissionMiddleware)
Step 1.10 Register middleware in bootstrap/app.php
Step 1.11 Create routes/agent.php + include in bootstrap/app.php
```

### Phase 2: Admin Agent Management
```
Step 2.1  Admin\AgentController (CRUD + approve/suspend)
Step 2.2  Admin\AgentWalletController
Step 2.3  Admin\TopupRequestController
Step 2.4  Add routes to routes/admin.php
Step 2.5  Admin views: agents/ (index, create, edit, show with tabs)
Step 2.6  Admin views: topup-requests/ (index, show)
Step 2.7  Admin sidebar — add Agents + Top-up Requests links
Step 2.8  Admin sidebar — pending top-up badge count
```

### Phase 3: Agent Panel Core
```
Step 3.1  Agent layout (app.blade.php, sidebar, navbar)
Step 3.2  Agent\DashboardController + view
Step 3.3  Agent\ProfileController + view
Step 3.4  Agent\WalletController + views
Step 3.5  Agent\BookingController (all bookings list) + view
Step 3.6  Pending + Suspended pages
```

### Phase 4: Agent Booking Modules
```
Step 4.1  Agent\HotelController (search, details, booking, confirm, invoice)
Step 4.2  Agent Hotel views
Step 4.3  Agent\FlightController
Step 4.4  Agent Flight views
Step 4.5  Agent\TourController
Step 4.6  Agent Tour views
Step 4.7  Agent\UmrahController
Step 4.8  Agent Umrah views
Step 4.9  Agent\VisaController
Step 4.10 Agent Visa views
```

### Phase 5: Testing & Polish
```
Step 5.1  Test wallet deduct during booking
Step 5.2  Test top-up request → approve flow
Step 5.3  Test permissions — toggle on/off and verify access
Step 5.4  Test agent booking shows in admin bookings with "Agent" badge
Step 5.5  Commission calculation on agent bookings
```

---

## Summary Table

| What | Count |
|---|---|
| New DB Migrations | 6 |
| Modified Tables | 5 (booking tables) |
| New Models | 4 |
| Updated Models | 1 (User) |
| New Middleware | 2 |
| New Agent Controllers | 9 |
| New Admin Controllers | 3 |
| New Agent Views (approx) | 25 |
| New Admin Views (approx) | 10 |
| Total Permission Keys | 16 |

---

*Documentation End — TravelPanel v2 Agent B2B System*
