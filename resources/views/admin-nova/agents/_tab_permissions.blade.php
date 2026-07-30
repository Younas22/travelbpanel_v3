{{-- Expects: $agent, $agentPermissions (AgentPermission::getAgentPermissions()
     result — same variables the Classic partial uses, permission keys/labels
     kept byte-identical). Shared by show.blade.php's Permissions tab and the
     standalone permissions.blade.php page. --}}
<form method="POST" action="{{ route('admin.agents.permissions.save', $agent) }}">
    @csrf

    <div class="tt-card bg-white rounded-2xl border border-novaborder shadow-sm p-4 sm:p-5">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
            <h2 class="text-sm font-semibold text-novatext flex items-center gap-2">
                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6l-8-3Z"/></svg>
                Agent Permissions
            </h2>
            <button type="submit" class="ag-btn-nova ag-btn-primary px-4 py-2 text-xs font-semibold">Save Permissions</button>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-4">

            {{-- Wallet Permissions --}}
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted flex items-center gap-1.5 mb-2">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="6" width="18" height="13" rx="2"/><path d="M3 10h18M16 14.5h1.5"/></svg>
                    Wallet
                </p>
                @foreach([
                    'wallet.view'    => 'View Wallet Balance',
                    'wallet.request' => 'Request Wallet Top-up',
                ] as $key => $label)
                    <div class="flex items-center justify-between gap-3 py-2.5 border-b border-novaborder last:border-0">
                        <label for="perm_{{ str_replace('.', '_', $key) }}" class="text-xs text-novatext">{{ $label }}</label>
                        <label class="ag-switch">
                            <input class="perm-switch-input" type="checkbox" name="permissions[{{ $key }}]" value="1"
                                   id="perm_{{ str_replace('.', '_', $key) }}"
                                {{ isset($agentPermissions[$key]) && $agentPermissions[$key] ? 'checked' : '' }}>
                            <span class="ag-slider"></span>
                        </label>
                    </div>
                @endforeach
            </div>

            {{-- Booking Permissions --}}
            <div>
                <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted flex items-center gap-1.5 mb-2">
                    <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="17" rx="2"/><path d="M3 9h18M8 2v4M16 2v4"/></svg>
                    Bookings
                </p>
                @foreach([
                    'bookings.make' => 'Make Bookings (via Website)',
                    'bookings.view' => 'View Own Bookings',
                ] as $key => $label)
                    <div class="flex items-center justify-between gap-3 py-2.5 border-b border-novaborder last:border-0">
                        <label for="perm_{{ str_replace('.', '_', $key) }}" class="text-xs text-novatext">{{ $label }}</label>
                        <label class="ag-switch">
                            <input class="perm-switch-input" type="checkbox" name="permissions[{{ $key }}]" value="1"
                                   id="perm_{{ str_replace('.', '_', $key) }}"
                                {{ isset($agentPermissions[$key]) && $agentPermissions[$key] ? 'checked' : '' }}>
                            <span class="ag-slider"></span>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="border-t border-novaborder my-5"></div>

        {{-- Add Property Permissions --}}
        <p class="text-[10px] font-semibold uppercase tracking-wide text-novamuted mb-3">
            Add Manual Properties
            <span class="text-novamuted normal-case font-normal">— agent can add/edit/delete these from their panel</span>
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            @foreach([
                'hotels.add' => ['icon' => 'M4 21V6a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v15M4 21h16M12 21V11a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v10M8 8h.01M8 12h.01M8 16h.01', 'label' => 'Add Hotels', 'desc' => 'Can add/edit/delete manual hotels'],
                'tours.add'  => ['icon' => 'M9 3v15l6 3V6L9 3ZM9 3 3 6v15l6-3M15 6l6-3v15l-6 3', 'label' => 'Add Tour Packages', 'desc' => 'Can add/edit/delete tour packages'],
                'umrah.add'  => ['icon' => 'M12 3a7 7 0 1 0 6.3 10.03A7 7 0 0 1 12 3Z', 'label' => 'Add Umrah Packages', 'desc' => 'Can add/edit/delete umrah packages'],
            ] as $key => $perm)
                @php $isActive = isset($agentPermissions[$key]) && $agentPermissions[$key]; @endphp
                <div class="rounded-2xl border p-3.5 transition-colors {{ $isActive ? 'border-novablue bg-blue-50/40' : 'border-novaborder' }}" data-perm-card>
                    <div class="flex items-start justify-between gap-2 mb-1.5">
                        <span class="text-xs font-semibold text-novatext flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round"><path d="{{ $perm['icon'] }}"/></svg>
                            {{ $perm['label'] }}
                        </span>
                        <label class="ag-switch">
                            <input class="perm-switch-input" type="checkbox" name="permissions[{{ $key }}]" value="1"
                                   id="perm_{{ str_replace('.', '_', $key) }}" data-perm-toggle
                                {{ $isActive ? 'checked' : '' }}>
                            <span class="ag-slider"></span>
                        </label>
                    </div>
                    <p class="text-[11px] text-novamuted">{{ $perm['desc'] }}</p>
                </div>
            @endforeach
        </div>
    </div>
</form>

@push('scripts')
    <script>
        document.querySelectorAll('[data-perm-toggle]').forEach(function (input) {
            input.addEventListener('change', function () {
                const card = this.closest('[data-perm-card]');
                if (card) {
                    card.classList.toggle('border-novablue', this.checked);
                    card.classList.toggle('bg-blue-50/40', this.checked);
                    card.classList.toggle('border-novaborder', !this.checked);
                }
            });
        });
    </script>
@endpush
