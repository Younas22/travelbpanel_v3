{{-- Shared bulk/single delete wiring for every admin-nova/bookings/* page —
     same endpoints and payload shape as the Classic table (see
     resources/views/admin/bookings/all.blade.php's @push('scripts')),
     just targeting the row-card markup in _list.blade.php instead of a
     <table>. --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var bookingCheckboxes = document.querySelectorAll('.booking-checkbox');
        var bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
        var selectedCountSpan = document.getElementById('selectedCount');

        function updateBulkDeleteButton() {
            var count = document.querySelectorAll('.booking-checkbox:checked').length;
            if (!bulkDeleteBtn) return;
            bulkDeleteBtn.classList.toggle('bk-visible', count > 0);
            if (selectedCountSpan) selectedCountSpan.textContent = count;
        }

        bookingCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', updateBulkDeleteButton);
        });

        if (bulkDeleteBtn) {
            bulkDeleteBtn.addEventListener('click', function () {
                var checkedBoxes = document.querySelectorAll('.booking-checkbox:checked');
                var bookings = [];
                checkedBoxes.forEach(function (checkbox) {
                    bookings.push({ id: checkbox.dataset.id, type: checkbox.dataset.type });
                });

                if (bookings.length === 0) {
                    alert('Please select at least one booking to delete.');
                    return;
                }

                if (confirm('Are you sure you want to delete ' + bookings.length + ' booking(s)? This action cannot be undone.')) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ route("admin.bookings.bulk-delete") }}';

                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    var bookingsInput = document.createElement('input');
                    bookingsInput.type = 'hidden'; bookingsInput.name = 'bookings'; bookingsInput.value = JSON.stringify(bookings);
                    form.appendChild(bookingsInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }

        document.querySelectorAll('.delete-single-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = this.dataset.id, type = this.dataset.type, ref = this.dataset.ref;
                if (confirm('Are you sure you want to delete booking #' + ref + '? This action cannot be undone.')) {
                    var form = document.createElement('form');
                    form.method = 'POST';
                    form.action = '{{ url("admin/bookings") }}/' + type + '/' + id;

                    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    var csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden'; csrfInput.name = '_token'; csrfInput.value = csrfToken;
                    form.appendChild(csrfInput);

                    var methodInput = document.createElement('input');
                    methodInput.type = 'hidden'; methodInput.name = '_method'; methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    });
</script>
