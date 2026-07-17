@extends($layout ?? 'admin.layouts.app')
@section('title', 'Tour Exclusions')

@section('content')

    <!-- ===== BREADCRUMB ===== -->
    <div class="tex-breadcrumb">
        <a href="{{ route('agent.dashboard') }}">Dashboard</a>
        <i class="bi bi-chevron-right"></i>
        <a href="{{ route('agent.tours.index') }}">My Tours</a>
        <i class="bi bi-chevron-right"></i>
        <span>Exclusions</span>
    </div>

    <!-- ===== PAGE HEADER ===== -->
    <div class="tex-header">
        <div class="tex-header-left">
            <div class="tex-header-icon"><i class="bi bi-x-circle"></i></div>
            <div>
                <h2 class="tex-title">Tour Exclusions</h2>
                <p class="tex-subtitle">Manage exclusions for tour packages</p>
            </div>
        </div>
        <button onclick="openModal('addModal')" class="tex-add-btn">
            <i class="bi bi-plus-circle"></i> Add Exclusion
        </button>
    </div>

    <!-- ===== TABLE CARD ===== -->
    <div class="tex-card">
        <div class="table-responsive">
            <table class="tex-table">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Created At</th>
                    <th style="text-align:right">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($exclusions as $exclusion)
                    <tr>
                        <td><span class="tex-meta">{{ $exclusion->id }}</span></td>
                        <td><span class="tex-name">{{ $exclusion->name }}</span></td>
                        <td><span class="tex-meta">{{ $exclusion->created_at->format('d M Y') }}</span></td>
                        <td>
                            <div class="tex-actions">
                                <button onclick="openModal('editModal{{ $exclusion->id }}')" class="tex-btn tex-btn-edit" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <form action="{{ route(($routePrefix ?? 'admin.tours.exclusions') . '.destroy', $exclusion->id) }}"
                                      method="POST" class="d-inline"
                                      onsubmit="return confirm('Are you sure?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="tex-btn tex-btn-delete" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4">
                            <div class="tex-empty">
                                <div class="tex-empty-icon"><i class="bi bi-x-circle"></i></div>
                                <h5>No exclusions found</h5>
                                <p>Add your first exclusion to get started.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if($exclusions->hasPages())
            <div class="tex-pagination">
                {{ $exclusions->links() }}
            </div>
        @endif
    </div>

    <!-- ===== ADD MODAL ===== -->
    <div id="addModal" class="tex-modal-overlay hidden">
        <div class="tex-modal">
            <form action="{{ route(($routePrefix ?? 'admin.tours.exclusions') . '.store') }}" method="POST">
                @csrf
                <div class="tex-modal-header">
                    <h5>Add Exclusion</h5>
                    <button type="button" onclick="closeModal('addModal')" class="tex-modal-close">&times;</button>
                </div>
                <div class="tex-modal-body">
                    <div class="tex-field">
                        <label>Exclusion Name</label>
                        <input type="text" name="name" class="form-control"
                               placeholder="e.g., Additional Meals, Room Extras" required>
                    </div>
                </div>
                <div class="tex-modal-footer">
                    <button type="button" onclick="closeModal('addModal')" class="tex-btn tex-btn-cancel">Cancel</button>
                    <button type="submit" class="tex-btn tex-btn-primary">Add Exclusion</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ===== EDIT MODALS ===== -->
    @foreach($exclusions as $exclusion)
        <div id="editModal{{ $exclusion->id }}" class="tex-modal-overlay hidden">
            <div class="tex-modal">
                <form action="{{ route(($routePrefix ?? 'admin.tours.exclusions') . '.update', $exclusion->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="tex-modal-header">
                        <h5>Edit Exclusion</h5>
                        <button type="button" onclick="closeModal('editModal{{ $exclusion->id }}')" class="tex-modal-close">&times;</button>
                    </div>
                    <div class="tex-modal-body">
                        <div class="tex-field">
                            <label>Exclusion Name</label>
                            <input type="text" name="name" class="form-control"
                                   value="{{ $exclusion->name }}" required>
                        </div>
                    </div>
                    <div class="tex-modal-footer">
                        <button type="button" onclick="closeModal('editModal{{ $exclusion->id }}')" class="tex-btn tex-btn-cancel">Cancel</button>
                        <button type="submit" class="tex-btn tex-btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    @endforeach

@endsection

@push('styles')
    <style>
        /* ===== BREADCRUMB ===== */
        .tex-breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin-bottom: 1rem;
        }
        .tex-breadcrumb a {
            color: #0C6DFD;
            text-decoration: none;
        }
        .tex-breadcrumb a:hover { text-decoration: underline; }
        .tex-breadcrumb i { font-size: 10px; color: var(--bs-secondary-color); }

        /* ===== PAGE HEADER ===== */
        .tex-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            margin-bottom: 1.25rem;
        }
        .tex-header-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .tex-header-icon {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #E3F0FF;
            color: #0C6DFD;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            flex-shrink: 0;
        }
        [data-bs-theme="dark"] .tex-header-icon { background: #0a2a4d; color: #6ba8ff; }
        .tex-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0 2px;
        }
        .tex-subtitle {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin: 0;
        }
        .tex-add-btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 9px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            background: #0C6DFD;
            border: 1px solid #0C6DFD;
            color: #fff;
            cursor: pointer;
            transition: opacity .15s;
        }
        .tex-add-btn:hover { opacity: .9; }

        /* ===== TABLE CARD ===== */
        .tex-card {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            overflow: hidden;
        }
        .tex-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .tex-table thead th {
            padding: 10px 14px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: .4px;
            color: var(--bs-secondary-color);
            border-bottom: 1px solid var(--bs-border-color);
            white-space: nowrap;
            text-align: left;
            background: var(--bs-secondary-bg);
        }
        .tex-table tbody td {
            padding: 11px 14px;
            border-bottom: 1px solid var(--bs-border-color);
            vertical-align: middle;
            color: var(--bs-body-color);
        }
        .tex-table tbody tr:last-child td { border-bottom: none; }
        .tex-table tbody tr:hover td { background: var(--bs-tertiary-bg); }

        .tex-meta { font-size: 12px; color: var(--bs-secondary-color); }
        .tex-name { font-size: 13px; font-weight: 500; color: var(--bs-body-color); }

        /* ===== ACTION BUTTONS ===== */
        .tex-actions { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }
        .tex-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            padding: 7px 14px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border: 1px solid var(--bs-border-color);
            transition: background .12s, color .12s, opacity .12s;
            white-space: nowrap;
        }
        .tex-btn-edit {
            background: var(--bs-secondary-bg);
            color: var(--bs-body-color);
            width: 30px; height: 30px;
            padding: 0;
        }
        .tex-btn-edit:hover { background: var(--bs-tertiary-bg); }
        .tex-btn-delete {
            background: #FCEBEB;
            color: #A32D2D;
            border-color: transparent;
            width: 30px; height: 30px;
            padding: 0;
        }
        .tex-btn-delete:hover { background: #f7c1c1; }
        [data-bs-theme="dark"] .tex-btn-delete { background: #2e0a0a; color: #f08080; }
        .tex-btn-primary {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .tex-btn-primary:hover { opacity: .9; color: #fff; }
        .tex-btn-cancel {
            background: var(--bs-secondary-bg);
            color: var(--bs-secondary-color);
        }
        .tex-btn-cancel:hover { background: var(--bs-tertiary-bg); color: var(--bs-body-color); }

        /* ===== EMPTY STATE ===== */
        .tex-empty {
            text-align: center;
            padding: 3rem 1rem;
        }
        .tex-empty-icon {
            width: 52px;
            height: 52px;
            border-radius: 50%;
            background: #E3F0FF;
            color: #0C6DFD;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 22px;
            margin: 0 auto 12px;
        }
        [data-bs-theme="dark"] .tex-empty-icon { background: #0a2a4d; color: #6ba8ff; }
        .tex-empty h5 {
            font-size: 14px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0 0 4px;
        }
        .tex-empty p {
            font-size: 12px;
            color: var(--bs-secondary-color);
            margin: 0;
        }

        /* ===== PAGINATION ===== */
        .tex-pagination {
            padding: 14px 20px;
            border-top: 1px solid var(--bs-border-color);
        }
        .tex-pagination .pagination { margin: 0; }
        .tex-pagination .page-link {
            border-radius: 7px;
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
            font-size: 13px;
            margin: 0 2px;
            background: var(--bs-body-bg);
        }
        .tex-pagination .page-item.active .page-link {
            background: #0C6DFD;
            border-color: #0C6DFD;
            color: #fff;
        }
        .tex-pagination .page-item.disabled .page-link {
            color: var(--bs-secondary-color);
            background: var(--bs-secondary-bg);
        }

        /* ===== MODAL ===== */
        .tex-modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.45);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 1rem;
        }
        .tex-modal-overlay.hidden { display: none; }
        .tex-modal {
            background: var(--bs-body-bg);
            border: 1px solid var(--bs-border-color);
            border-radius: 14px;
            width: 100%;
            max-width: 440px;
            overflow: hidden;
        }
        .tex-modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 1rem 1.25rem;
            border-bottom: 1px solid var(--bs-border-color);
        }
        .tex-modal-header h5 {
            font-size: 14px;
            font-weight: 600;
            color: var(--bs-body-color);
            margin: 0;
        }
        .tex-modal-close {
            background: none;
            border: none;
            font-size: 20px;
            line-height: 1;
            color: var(--bs-secondary-color);
            cursor: pointer;
            padding: 0;
            transition: color .12s;
        }
        .tex-modal-close:hover { color: var(--bs-body-color); }
        .tex-modal-body { padding: 1.25rem; }
        .tex-modal-footer {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            padding: 1rem 1.25rem;
            border-top: 1px solid var(--bs-border-color);
        }

        /* ===== FORM FIELD ===== */
        .tex-field label {
            font-size: 12px;
            font-weight: 500;
            color: var(--bs-secondary-color);
            margin-bottom: 6px;
            display: block;
        }
        .tex-modal .form-control {
            border-radius: 8px;
            border-color: var(--bs-border-color);
            font-size: 13px;
            background: var(--bs-secondary-bg);
        }
        .tex-modal .form-control:focus {
            border-color: #0C6DFD;
            box-shadow: 0 0 0 3px rgba(12, 109, 253, .12);
            background: var(--bs-body-bg);
        }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 480px) {
            .tex-header { align-items: flex-start; }
            .tex-add-btn { width: 100%; justify-content: center; }
        }
    </style>
@endpush

@push('scripts')
    <script>
        function openModal(id) {
            document.getElementById(id).classList.remove('hidden');
        }
        function closeModal(id) {
            document.getElementById(id).classList.add('hidden');
        }
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('tex-modal-overlay')) {
                closeModal(e.target.id);
            }
        });
    </script>
@endpush
