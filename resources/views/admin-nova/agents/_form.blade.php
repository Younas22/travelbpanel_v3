{{-- Expects: $agent (User|null — null on create, the model on edit).
     Shared by create.blade.php / edit.blade.php; the parents add the header and actions. --}}
@php
    $field = fn ($name, $default = '') => old($name, $agent->{$name} ?? $default);
    $inputClass = fn ($name) => 'tbp-agent-form-input' . ($errors->has($name) ? ' is-invalid' : '');
@endphp

@if($errors->any())
    <div class="tbp-agent-form-errors">
        <strong><i class="bi bi-exclamation-circle"></i> Please fix the following:</strong>
        <ul>@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
@endif

<section class="tbp-agent-form-card">
    <header><i class="bi bi-person"></i><div><h2>Personal Information</h2><p>{{ $agent ? 'Basic details of the agent account' : 'Basic details for the new agent account' }}</p></div></header>
    <div class="tbp-agent-form-grid">
        <label class="tbp-agent-form-field"><span>First Name <b>*</b></span>
            <input type="text" name="first_name" class="{{ $inputClass('first_name') }}" value="{{ $field('first_name') }}" placeholder="Enter first name" required maxlength="100"></label>
        <label class="tbp-agent-form-field"><span>Last Name <b>*</b></span>
            <input type="text" name="last_name" class="{{ $inputClass('last_name') }}" value="{{ $field('last_name') }}" placeholder="Enter last name" required maxlength="100"></label>
        <label class="tbp-agent-form-field"><span>Email <b>*</b></span>
            <input type="email" name="email" class="{{ $inputClass('email') }}" value="{{ $field('email') }}" placeholder="Enter email address" required></label>
        <label class="tbp-agent-form-field"><span>Phone</span>
            <input type="text" name="phone" class="{{ $inputClass('phone') }}" value="{{ $field('phone') }}" placeholder="Enter phone number" maxlength="30"></label>
        @unless($agent)
            <label class="tbp-agent-form-field"><span>Password <b>*</b></span>
                <span class="tbp-agent-form-password">
                    <input type="password" name="password" class="{{ $inputClass('password') }}" placeholder="Create password" required minlength="8" autocomplete="new-password">
                    <button type="button" data-toggle-password title="Show password"><i class="bi bi-eye"></i></button>
                </span></label>
            <label class="tbp-agent-form-field"><span>Confirm Password <b>*</b></span>
                <span class="tbp-agent-form-password">
                    <input type="password" name="password_confirmation" class="{{ $inputClass('password') }}" placeholder="Confirm password" required minlength="8" autocomplete="new-password">
                    <button type="button" data-toggle-password title="Show password"><i class="bi bi-eye"></i></button>
                </span></label>
        @endunless
    </div>
</section>

<section class="tbp-agent-form-card">
    <header><i class="bi bi-building"></i><div><h2>Company Information</h2><p>Company and commission details</p></div></header>
    <div class="tbp-agent-form-grid">
        <label class="tbp-agent-form-field"><span>Company Name</span>
            <input type="text" name="company_name" class="{{ $inputClass('company_name') }}" value="{{ $field('company_name') }}" placeholder="Enter company name"></label>
        <label class="tbp-agent-form-field"><span>Company Phone</span>
            <input type="text" name="company_phone" class="{{ $inputClass('company_phone') }}" value="{{ $field('company_phone') }}" placeholder="Enter company phone"></label>
        <label class="tbp-agent-form-field"><span>Tax Number</span>
            <input type="text" name="tax_number" class="{{ $inputClass('tax_number') }}" value="{{ $field('tax_number') }}" placeholder="Enter tax number"></label>
        <label class="tbp-agent-form-field"><span>Commission Type <b>*</b></span>
            <select name="commission_type" id="commissionType" class="{{ $inputClass('commission_type') }}" required>
                <option value="percentage" @selected($field('commission_type', 'percentage') === 'percentage')>Percentage</option>
                <option value="fixed" @selected($field('commission_type', 'percentage') === 'fixed')>Fixed Amount</option>
            </select></label>
        <label class="tbp-agent-form-field"><span id="commissionRateLabel">Commission Rate (%)</span>
            <input type="number" name="commission_rate" id="commissionRate" class="{{ $inputClass('commission_rate') }}" min="0" max="100" step="0.01" value="{{ $field('commission_rate', 0) }}"></label>
        <label class="tbp-agent-form-field"><span>Company Address</span>
            <textarea name="company_address" class="{{ $inputClass('company_address') }}" rows="2" placeholder="Enter company address">{{ $field('company_address') }}</textarea></label>
    </div>
</section>

<section class="tbp-agent-form-card">
    <header><i class="bi bi-file-earmark-text"></i><div><h2>Internal Notes</h2><p>Visible only to admins, not to the agent</p></div></header>
    <label class="tbp-agent-form-field tbp-agent-form-notes">
        <textarea name="internal_notes" id="internalNotes" class="{{ $inputClass('internal_notes') }}" rows="1" maxlength="500" placeholder="Admin-only notes...">{{ $field('internal_notes') }}</textarea>
        <small><span data-notes-count>0</span>/500</small>
    </label>
</section>

@push('scripts')
<script>
(function () {
    var type = document.getElementById('commissionType'), rate = document.getElementById('commissionRate'), label = document.getElementById('commissionRateLabel');
    function sync() {
        var fixed = type.value === 'fixed';
        label.textContent = fixed ? 'Commission Amount' : 'Commission Rate (%)';
        fixed ? rate.removeAttribute('max') : rate.setAttribute('max', '100');
    }
    type.addEventListener('change', sync); sync();

    var notes = document.getElementById('internalNotes'), count = document.querySelector('[data-notes-count]');
    function countNotes() { count.textContent = notes.value.length; }
    notes.addEventListener('input', countNotes); countNotes();

    document.querySelectorAll('[data-toggle-password]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.previousElementSibling, show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.innerHTML = '<i class="bi bi-eye' + (show ? '-slash' : '') + '"></i>';
        });
    });

    var form = notes.closest('form');
    form.addEventListener('submit', function () {
        var btn = form.querySelector('.tbp-agent-form-submit');
        if (btn) { btn.disabled = true; btn.querySelector('span').textContent = btn.dataset.loading; }
    });
})();
</script>
@endpush
