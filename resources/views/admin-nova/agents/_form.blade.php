{{-- Expects: $agent (User|null — null on create, the model on edit).
     Shared by create.blade.php / edit.blade.php so both stay pixel-identical
     except for the password fields and the action buttons, which each parent
     file adds around this include. --}}

@if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-2xl p-4 mb-5">
        <p class="text-xs font-semibold text-novadanger flex items-center gap-1.5 mb-2">
            <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 8v5M12 16h.01"/></svg>
            Please fix the following:
        </p>
        <ul class="text-xs text-novadanger list-disc pl-5 space-y-0.5">
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ===== PERSONAL INFORMATION ===== --}}
<div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
    <h2 class="text-sm font-semibold text-novatext flex items-center gap-2 mb-4">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="3.25"/><path d="M4.75 19c.6-3.7 3.4-6 7.25-6s6.65 2.3 7.25 6"/></svg>
        Personal Information
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="ag-label">First Name <span class="ag-required">*</span></label>
            <input type="text" name="first_name" class="ag-input" value="{{ old('first_name', $agent->first_name ?? '') }}" required>
        </div>
        <div>
            <label class="ag-label">Last Name <span class="ag-required">*</span></label>
            <input type="text" name="last_name" class="ag-input" value="{{ old('last_name', $agent->last_name ?? '') }}" required>
        </div>
        <div>
            <label class="ag-label">Email <span class="ag-required">*</span></label>
            <input type="email" name="email" class="ag-input" value="{{ old('email', $agent->email ?? '') }}" required>
        </div>
        <div>
            <label class="ag-label">Phone</label>
            <input type="text" name="phone" class="ag-input" value="{{ old('phone', $agent->phone ?? '') }}">
        </div>

        @unless($agent)
            <div>
                <label class="ag-label">Password <span class="ag-required">*</span></label>
                <input type="password" name="password" class="ag-input" required>
            </div>
            <div>
                <label class="ag-label">Confirm Password <span class="ag-required">*</span></label>
                <input type="password" name="password_confirmation" class="ag-input" required>
            </div>
        @endunless
    </div>
</div>

{{-- ===== COMPANY INFORMATION ===== --}}
<div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5 mb-5">
    <h2 class="text-sm font-semibold text-novatext flex items-center gap-2 mb-4">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="9" width="16" height="12" rx="1.5"/><path d="M8 9V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v4"/></svg>
        Company Information
    </h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
        <div>
            <label class="ag-label">Company Name</label>
            <input type="text" name="company_name" class="ag-input" value="{{ old('company_name', $agent->company_name ?? '') }}">
        </div>
        <div>
            <label class="ag-label">Company Phone</label>
            <input type="text" name="company_phone" class="ag-input" value="{{ old('company_phone', $agent->company_phone ?? '') }}">
        </div>
        <div>
            <label class="ag-label">CNIC / Business Reg No.</label>
            <input type="text" name="cnic_or_reg_number" class="ag-input" value="{{ old('cnic_or_reg_number', $agent->cnic_or_reg_number ?? '') }}">
        </div>
        <div>
            <label class="ag-label">Commission Rate (%)</label>
            <input type="number" name="commission_rate" class="ag-input" min="0" max="100" step="0.01" value="{{ old('commission_rate', $agent->commission_rate ?? 0) }}">
        </div>
    </div>

    <div class="mb-4">
        <label class="ag-label">Company Address</label>
        <textarea name="company_address" class="ag-input" rows="2">{{ old('company_address', $agent->company_address ?? '') }}</textarea>
    </div>

    <div>
        <label class="ag-label">Internal Notes</label>
        <textarea name="internal_notes" class="ag-input" rows="2" placeholder="Admin-only notes...">{{ old('internal_notes', $agent->internal_notes ?? '') }}</textarea>
        <p class="ag-field-help">Visible only to admins, not to the agent</p>
    </div>
</div>
