# Agent B2B — Part 1: Migrations (Actual Code)

---

## User Type Logic — Zaruri Samajhna

> **Note:** `user_type` field ALREADY existing users table migration mein hai:
> ```php
> $table->enum('user_type', ['user', 'admin', 'agent'])->default('user');
> ```
> Isliye Migration 1 mein dobara add karne ki zaroorat nahi.

### Users Table mein 4 tarhay ke records hain:

```
user_type = 'admin'                              → Admin panel user
user_type = 'agent'                              → Travel Agent (B2B)
user_type = 'user'  + parent_agent_id = NULL     → Direct customer (B2C)
user_type = 'user'  + parent_agent_id = agent.id → Agent ka customer (B2B client)
```

**Example:**
- Agent "Ali Travel" (id=10, user_type='agent') ne apne client "Ahmed" ka account banaya
- Ahmed ka record: (user_type='user', parent_agent_id=10)
- Ahmed k baray mein booking agent ke wallet se hogi

---

## Migration 1: Add Agent Fields to Users Table

**File:** `database/migrations/2026_03_15_000001_add_agent_fields_to_users_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // ── Agent-specific fields ──────────────────────────────────────
            $table->string('company_name')->nullable()->after('last_name');
            $table->string('agent_code', 20)->unique()->nullable()->after('company_name');
            $table->enum('approval_status', ['pending', 'active', 'suspended'])->default('pending')->after('agent_code');
            $table->unsignedBigInteger('approved_by')->nullable()->after('approval_status');
            $table->timestamp('approved_at')->nullable()->after('approved_by');
            $table->text('rejection_reason')->nullable()->after('approved_at');
            $table->text('company_address')->nullable()->after('rejection_reason');
            $table->string('company_phone', 30)->nullable()->after('company_address');
            $table->string('company_logo')->nullable()->after('company_phone');
            $table->string('cnic_or_reg_number', 100)->nullable()->after('company_logo');

            // ── NEW: Agent ke customers ka linkage ─────────────────────────
            // Agar koi user kisi agent ne create kiya ho to us agent ka id yahan store hoga
            // Normal B2C users ka yeh field NULL hoga
            // user_type = 'user' + parent_agent_id = agent.id → Agent's client
            $table->unsignedBigInteger('parent_agent_id')->nullable()->after('cnic_or_reg_number');

            // ── Foreign Keys ───────────────────────────────────────────────
            $table->foreign('approved_by')->references('id')->on('users')->nullOnDelete();
            $table->foreign('parent_agent_id')->references('id')->on('users')->nullOnDelete();

            // ── Indexes ────────────────────────────────────────────────────
            $table->index('parent_agent_id');
            $table->index('approval_status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['approved_by']);
            $table->dropForeign(['parent_agent_id']);
            $table->dropIndex(['parent_agent_id']);
            $table->dropIndex(['approval_status']);
            $table->dropColumn([
                'company_name', 'agent_code', 'approval_status',
                'approved_by', 'approved_at', 'rejection_reason',
                'company_address', 'company_phone', 'company_logo',
                'cnic_or_reg_number', 'parent_agent_id',
            ]);
        });
    }
};
```

---

## Migration 2: Create Agent Wallets Table

**File:** `database/migrations/2026_03_15_000002_create_agent_wallets_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id')->unique();
            $table->decimal('balance', 12, 2)->default(0.00);
            $table->string('currency', 10)->default('PKR');
            $table->decimal('total_credited', 12, 2)->default(0.00);
            $table->decimal('total_debited', 12, 2)->default(0.00);
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_wallets');
    }
};
```

---

## Migration 3: Create Agent Wallet Transactions Table

**File:** `database/migrations/2026_03_15_000003_create_agent_wallet_transactions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_wallet_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->enum('type', ['credit', 'debit']);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('reference', 100)->nullable();
            $table->string('booking_type', 50)->nullable(); // hotel/flight/tour/umrah/visa
            $table->unsignedBigInteger('booking_id')->nullable();
            $table->text('note')->nullable();
            $table->string('payment_method', 100)->nullable(); // for credits only
            $table->unsignedBigInteger('performed_by'); // admin or agent user id
            $table->enum('status', ['completed', 'pending', 'reversed'])->default('completed');
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users');
            $table->index(['agent_id', 'type']);
            $table->index(['agent_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_wallet_transactions');
    }
};
```

---

## Migration 4: Create Agent Permissions Table

**File:** `database/migrations/2026_03_15_000004_create_agent_permissions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->string('permission_key', 100);
            $table->boolean('is_enabled')->default(true);
            $table->timestamps();

            $table->unique(['agent_id', 'permission_key']);
            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_permissions');
    }
};
```

---

## Migration 5: Create Agent Topup Requests Table

**File:** `database/migrations/2026_03_15_000005_create_agent_topup_requests_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_topup_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('agent_id');
            $table->decimal('amount', 12, 2);
            $table->string('payment_method', 100)->nullable();
            $table->string('payment_proof')->nullable(); // file path
            $table->text('note')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->text('rejection_note')->nullable();
            $table->timestamps();

            $table->foreign('agent_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('reviewed_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_topup_requests');
    }
};
```

---

## Migration 6: Add Agent ID to Booking Tables

**File:** `database/migrations/2026_03_15_000006_add_agent_id_to_booking_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['hotels_booking', 'flights_booking', 'tours_booking', 'umrah_bookings', 'visa_requests'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                // Kaun se agent ne book kiya
                $table->unsignedBigInteger('agent_id')->nullable()->after('id');
                // Direct B2C ya agent through B2B
                $table->enum('booked_via', ['direct', 'agent'])->default('direct')->after('agent_id');
                // Agent ne kis customer k liye book kiya (agar agent ka registered client hai)
                // NULL = agent ne apne naam pe ya unregistered client k liye book kiya
                $table->unsignedBigInteger('booked_for_user_id')->nullable()->after('booked_via');

                $table->foreign('agent_id')->references('id')->on('users')->nullOnDelete();
                $table->foreign('booked_for_user_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        $tables = ['hotels_booking', 'flights_booking', 'tours_booking', 'umrah_bookings', 'visa_requests'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                $table->dropForeign([$tableName . '_agent_id_foreign']);
                $table->dropColumn(['agent_id', 'booked_via']);
            });
        }
    }
};
```
