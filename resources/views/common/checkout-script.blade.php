{{--
    Step-by-step checkout in the browser (flight, tour, umrah booking pages):
    one form, a panel per step (.hb-panel[data-step]); each step's required
    fields are checked before the next one opens, and the form is posted once
    from the last step. Hooks: [data-checkout] wrapper, [data-continue],
    [data-back], [data-goto], [data-selected-method], [data-confirm], and an
    optional #timer (30 min booking timer).
--}}
<script>
(function () {
    const root = document.querySelector('[data-checkout]');
    if (!root) return;
    const form = root.querySelector('form');
    const panels = [...root.querySelectorAll('.hb-panel')];
    const last = panels.length;
    const text = {
        required: @json(t('hotelbooking.field_required')),
        email: @json(t('hotelbooking.invalid_email')),
        payment: @json(t('hotelbooking.select_payment')),
        processing: @json(t('hotelbooking.processing')),
        expired: @json(t('flightbooking.time_expired')),
    };
    const state = { current: +(root.dataset.start || 1), max: +(root.dataset.start || 1) };
    const panel = (step) => panels.find(p => +p.dataset.step === step);

    // ---- inline errors (slot = the field's own .hb-error) ----
    function clearErrors(scope) {
        scope.querySelectorAll('.hb-error').forEach(e => e.textContent = '');
        scope.querySelectorAll('.is-invalid').forEach(e => e.classList.remove('is-invalid'));
    }
    function check(step) {
        const scope = panel(step);
        clearErrors(scope);
        let first = null;
        scope.querySelectorAll('[required]').forEach(el => {
            const value = el.value.trim();
            let message = '';
            if (!value) message = text.required;
            else if (el.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) message = text.email;
            if (!message) return;
            el.classList.add('is-invalid');
            const slot = el.closest('.hb-field')?.querySelector('.hb-error');
            if (slot && !slot.textContent) slot.textContent = message;
            first = first || el;
        });
        const paySlot = scope.querySelector('[data-error-for="accept_payment"]');
        if (paySlot && !form.querySelector('[name="accept_payment"]:checked')) {
            paySlot.textContent = text.payment;
            first = first || scope.querySelector('.hb-pays');
        }
        if (first) {
            const fields = first.closest('.hb-fields');
            if (fields && fields.hidden) fields.hidden = false;
            first.focus?.({ preventScroll: false });
        }
        return !first;
    }

    // ---- stepper ----
    function show(step) {
        state.current = step;
        state.max = Math.max(state.max, step);
        panels.forEach(p => p.hidden = +p.dataset.step !== step);
        root.querySelectorAll('.hb-step').forEach(li => {
            const n = +li.dataset.step;
            const btn = li.querySelector('.hb-step-btn');
            li.classList.toggle('is-active', n === step);
            li.classList.toggle('is-done', n < state.max && n !== step);
            btn.disabled = n > state.max;
            n === step ? btn.setAttribute('aria-current', 'step') : btn.removeAttribute('aria-current');
        });
        root.querySelector('.hb-steps').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
    root.querySelectorAll('[data-continue]').forEach(btn => btn.addEventListener('click', () => {
        const step = +btn.dataset.continue;
        if (check(step)) show(step + 1);
    }));
    root.querySelectorAll('[data-back]').forEach(btn => btn.addEventListener('click', () => show(+btn.dataset.back)));
    root.querySelectorAll('[data-goto]').forEach(btn => btn.addEventListener('click', () => {
        const target = +btn.dataset.goto;
        // forward only through checked steps
        for (let s = state.current; s < target; s++) if (!check(s)) { show(s); return; }
        show(target);
    }));

    // a fixed field loses its error as soon as it has a value
    form.addEventListener('input', clearOne);
    form.addEventListener('change', clearOne);
    function clearOne(e) {
        const el = e.target;
        if (el.classList.contains('is-invalid') && el.value.trim()) {
            el.classList.remove('is-invalid');
            const field = el.closest('.hb-field');
            if (field && !field.querySelector('.is-invalid')) field.querySelector('.hb-error').textContent = '';
        }
        if (el.name === 'accept_payment') {
            const label = root.querySelector('[data-selected-method]');
            if (label) label.textContent = el.dataset.label;
            const slot = root.querySelector('[data-error-for="accept_payment"]');
            if (slot) slot.textContent = '';
        }
    }

    // ---- mobile summary ----
    const sumToggle = root.querySelector('.hb-sum-toggle');
    if (sumToggle) sumToggle.addEventListener('click', () => {
        const open = root.querySelector('.hb-summary').classList.toggle('is-open');
        sumToggle.setAttribute('aria-expanded', String(open));
    });

    // ---- submit: only from the last step, once ----
    const confirmBtn = root.querySelector('[data-confirm]');
    form.addEventListener('submit', (e) => {
        if (state.current !== last) {
            e.preventDefault();
            panel(state.current).querySelector('[data-continue]')?.click();
            return;
        }
        // every step again (a field may have been emptied after moving on)
        for (let s = 1; s <= last; s++) {
            if (!check(s)) { e.preventDefault(); show(s); check(s); return; }
        }
        if (confirmBtn.disabled) { e.preventDefault(); return; }
        confirmBtn.disabled = true;
        confirmBtn.classList.add('is-loading');
        confirmBtn.querySelector('span').textContent = text.processing;
    });
    window.addEventListener('pageshow', (e) => {
        if (!e.persisted) return;
        confirmBtn.disabled = false;
        confirmBtn.classList.remove('is-loading');
    });

    // a booking sent back with an error (e.g. wallet balance) reopens the last step
    if (state.current > 1) show(state.current);

    // ---- optional 30 minute booking timer ----
    const timer = document.getElementById('timer');
    if (timer) {
        let timeLeft = 30 * 60;
        const tick = setInterval(() => {
            timeLeft--;
            timer.textContent = String(Math.floor(timeLeft / 60)).padStart(2, '0') + ':' + String(timeLeft % 60).padStart(2, '0');
            if (timeLeft < 300) timer.closest('.fb-timer').classList.add('is-ending');
            if (timeLeft <= 0) {
                clearInterval(tick);
                timer.textContent = '00:00';
                alert(text.expired);
            }
        }, 1000);
    }
})();
</script>
