@extends('admin.layouts.app')

@section('title', 'Edit Travel Partner')

@section('content')
    <div class="content-area">


        <!-- ===== PAGE HEADER ===== -->
        <div class="tpe-header">
            <div>
                <div class="tpe-title-row">
                    <h2 class="tpe-title">Edit Travel Partner</h2>
                    <span class="tpe-supplier-badge"><i class="bi bi-building"></i> {{ $partner->company_name }}</span>
                </div>
                <p class="tpe-subtitle">Update partner information and API configuration</p>
            </div>
            <a href="{{ route('admin.travel-partners.index') }}" class="tpe-back-btn">
                <i class="bi bi-arrow-left"></i> Back to partners
            </a>
        </div>

        <form action="{{ route('admin.travel-partners.update', $partner->id) }}"
              method="POST"
              enctype="multipart/form-data"
              id="travelPartnerForm">
            @csrf
            @method('PATCH')

            <!-- ===== BASIC INFORMATION ===== -->
            <div class="tpe-subcard">
                <div class="tpe-subcard-header"><i class="bi bi-shield-check"></i> Basic Information</div>
                <div class="tpe-subcard-body">
                    <div class="tpe-grid-3">
                        <div class="tpe-field">
                            <label>Company Name <span class="tpe-required">*</span></label>
                            <input type="text" readonly name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                   value="{{ old('company_name', $partner->company_name) }}" required>
                            <div class="tpe-lock-hint"><i class="bi bi-lock-fill"></i> Cannot be changed</div>
                            @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tpe-field">
                            <label>Module <span class="tpe-required">*</span></label>
                            <select disabled name="module_id" class="form-select @error('module_id') is-invalid @enderror" required>
                                <option value="">Select module</option>
                                @foreach($modules as $module)
                                    <option value="{{ $module->id }}" {{ old('module_id', $partner->module_id) == $module->id ? 'selected' : '' }}>
                                        {{ $module->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="tpe-lock-hint"><i class="bi bi-lock-fill"></i> Cannot be changed</div>
                            @error('module_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="tpe-field">
                            <label>Status <span class="tpe-required">*</span></label>
                            <div class="tpe-status-select-wrap">
                                <span id="statusDot" class="tpe-status-dot {{ old('status', $partner->status) == 'active' ? 'is-active' : '' }}"></span>
                                <select id="statusSelect" name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="active" {{ old('status', $partner->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="inactive" {{ old('status', $partner->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            @php
                // Computed unconditionally: referenced by the page's shared JS below
                // regardless of which branch (API supplier vs. Manual) renders.
                $supplierSlug = strtolower(trim($partner->company_name));
                // Proxied server-side (see TravelPartnerController::testCredentialsLive) so the
                // browser never has to be handed the partner's saved, decrypted credentials —
                // only a value the admin is actively typing (not yet saved) ever reaches it.
                $credentialCheckEndpoint = route('admin.travel-partners.test-credentials-live', $partner);
                $financeItems = [
                    ['label' => 'Commission', 'field' => 'commission_rate', 'type_field' => 'commission_type', 'icon' => 'bi-percent', 'color' => 'blue'],
                    ['label' => 'Discount', 'field' => 'discount_rate', 'type_field' => 'discount_type', 'icon' => 'bi-check-circle', 'color' => 'green'],
                    ['label' => 'B2B', 'field' => 'b2b_markup', 'type_field' => 'b2b_markup_type', 'icon' => 'bi-briefcase', 'color' => 'purple'],
                    ['label' => 'B2C', 'field' => 'b2c_markup', 'type_field' => 'b2c_markup_type', 'icon' => 'bi-person', 'color' => 'orange'],
                ];
            @endphp

            @if($partner->company_name != "Manual")
                @php
                    $showImportTab = strtolower(optional($partner->module)->name ?? '') === 'stay';
                @endphp
                <!-- ===== API CONFIGURATION / STEPPER FLOW ===== -->
                <div class="tpe-card">
                    <h5 class="tpe-section-title"><span class="tpe-title-icon"><i class="bi bi-key"></i></span> API Credentials &amp; Setup</h5>
                    <p class="tpe-stepper-intro">Follow these steps to configure, test, and import data for this partner.</p>

                    <!-- ===== STEPPER HEADER ===== -->
                    <div class="tpe-stepper" id="tpeStepper">
                        <button type="button" class="tpe-step @if($steps['credentials']['complete']) is-complete @endif" data-step="credentials">
                            <span class="tpe-step-circle"><span class="tpe-step-num">1</span><i class="bi bi-check-lg tpe-step-check"></i></span>
                            <span class="tpe-step-label"><span class="tpe-step-title">API Credentials</span><span class="tpe-step-desc">Add credentials</span></span>
                        </button>
                        <span class="tpe-step-line"></span>
                        <button type="button" class="tpe-step @if($steps['test-api']['complete']) is-complete @endif @if($steps['test-api']['status'] === 'failed') is-failed @endif" data-step="test-api">
                            <span class="tpe-step-circle"><span class="tpe-step-num">2</span><i class="bi bi-check-lg tpe-step-check"></i><i class="bi bi-exclamation-lg tpe-step-failed-icon"></i></span>
                            <span class="tpe-step-label"><span class="tpe-step-title">{{ $partner->company_name }} API Credential Check</span><span class="tpe-step-desc">Verify credentials</span></span>
                        </button>
                        <span class="tpe-step-line"></span>
                        <button type="button" class="tpe-step @if($steps['financial']['complete']) is-complete @endif" data-step="financial">
                            <span class="tpe-step-circle"><span class="tpe-step-num">3</span><i class="bi bi-check-lg tpe-step-check"></i></span>
                            <span class="tpe-step-label"><span class="tpe-step-title">Financial Information</span><span class="tpe-step-desc">Commission &amp; markups</span></span>
                        </button>
                        @if($showImportTab)
                            <span class="tpe-step-line"></span>
                            <button type="button" class="tpe-step @if($steps['import']['complete']) is-complete @endif" data-step="import">
                                <span class="tpe-step-circle"><span class="tpe-step-num">4</span><i class="bi bi-check-lg tpe-step-check"></i></span>
                                <span class="tpe-step-label"><span class="tpe-step-title">Import Data</span><span class="tpe-step-desc">Review &amp; import</span></span>
                            </button>
                        @endif
                    </div>

                    <div class="tpe-step-panels">

                        <!-- ===== STEP 1: API CONFIGURATION ===== -->
                        <div class="tpe-step-panel" data-step-panel="credentials">
                            <p class="tpe-stepper-intro">Configure {{ $partner->company_name }}'s API credentials and sandbox settings below.</p>

                            <div class="tpe-subcard">
                                <div class="tpe-subcard-header"><i class="bi bi-code-slash"></i> Development Mode</div>
                                <div class="tpe-subcard-body">
                                    <div class="tpe-dev-row">
                                        <div class="tpe-dev-desc">Enable test/sandbox environment for this partner</div>
                                        <label class="tpe-switch">
                                            <input type="checkbox"
                                                   name="development_mode"
                                                   value="1"
                                                   id="developmentModeSwitch"
                                                {{ old('development_mode', $partner->development_mode) ? 'checked' : '' }}>
                                            <span class="tpe-switch-slider"></span>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="tpe-subcard">
                                <div class="tpe-subcard-header"><i class="bi bi-key"></i> API Credentials</div>
                                <div class="tpe-subcard-body">
                                    <div class="tpe-cred-grid" id="credentialGrid">
                                        @for($i = 1; $i <= 6; $i++)
                                            @php
                                                // Never decrypt for display. The raw column is the encrypted
                                                // ciphertext itself — that's what's shown (and it's all the
                                                // field submits back unless the admin replaces it), so the
                                                // real credential never round-trips to the browser just for
                                                // viewing the page.
                                                $oldVal = old("api_credential_{$i}");
                                                $rawStored = $partner->getRawOriginal("api_credential_{$i}");
                                                $hasStored = $rawStored !== null && $rawStored !== '';
                                                $isEditing = $oldVal !== null;
                                                $displayVal = $isEditing ? $oldVal : ($hasStored ? $rawStored : '');
                                            @endphp
                                            <div class="tpe-field tpe-cred-slot @if($hasStored || $isEditing) tpe-cred-active @endif" data-cred-index="{{ $i }}">
                                                <label>API Credential {{ $i }} <i class="bi bi-lock-fill tpe-cred-lock" title="Encrypted at rest — never shown in plain text"></i></label>
                                                <div class="tpe-input-icon-wrap">
                                                    <input type="text" name="api_credential_{{ $i }}"
                                                           class="form-control tpe-cred-input @error('api_credential_'.$i) is-invalid @enderror"
                                                           placeholder="{{ ($hasStored && !$isEditing) ? '' : 'Enter credential' }}"
                                                           value="{{ $displayVal }}"
                                                           data-original-value="{{ $hasStored ? $rawStored : '' }}"
                                                           autocomplete="new-password"
                                                           {{ ($hasStored && !$isEditing) ? 'disabled' : '' }}>
                                                    @if($hasStored && !$isEditing)
                                                        <span class="tpe-input-check"><i class="bi bi-check-circle-fill"></i></span>
                                                    @endif
                                                    @if($hasStored)
                                                        <button type="button" class="tpe-cred-change" data-cred-index="{{ $i }}" title="{{ $isEditing ? 'Cancel' : 'Replace this credential' }}">
                                                            <i class="bi {{ $isEditing ? 'bi-x-lg' : 'bi-pencil-fill' }}"></i>
                                                        </button>
                                                    @endif
                                                </div>
                                                @error('api_credential_'.$i)
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <div class="tpe-step-actions">
                                <span></span>
                                <button type="button" class="tpe-btn tpe-btn-primary tpe-step-next" data-next="test-api">Continue to Test API <i class="bi bi-arrow-right"></i></button>
                            </div>
                        </div>

                        <!-- ===== STEP 2: {SUPPLIER} API CREDENTIAL CHECK ===== -->
                        <div class="tpe-step-panel" data-step-panel="test-api">
                            <p class="tpe-stepper-intro">
                                Verify that the saved <strong>{{ $partner->company_name }}</strong> credentials are valid by calling {{ $partner->company_name }}'s live credential-check endpoint.
                            </p>

                            <div class="tpe-credcheck-row">
                                <button type="button" id="testCredentialsBtn" class="tpe-btn tpe-btn-primary">
                                    <i class="bi bi-shield-check"></i> <span>Test Credentials</span>
                                </button>
                            </div>

                            @php $apiStatus = $partner->last_api_test_status; @endphp

                            <!-- Terminal-style test console -->
                            <div class="tpe-terminal">
                                <div class="tpe-terminal-header">
                                    <div class="tpe-terminal-dots"><span></span><span></span><span></span></div>
                                    <div class="tpe-terminal-title"><i class="bi bi-terminal"></i> {{ $partner->company_name }} API Test Console</div>
                                    <button type="button" class="tpe-terminal-clear" id="clearTerminalBtn" title="Clear console">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                                <div class="tpe-terminal-body" id="apiTestTerminalBody">
                                    @if(!$apiStatus)
                                        <div class="tpe-terminal-line tpe-terminal-line--muted">Waiting to run credential test for {{ $partner->company_name }}. Click "Test Credentials" to begin.<span class="tpe-terminal-cursor"></span></div>
                                    @else
                                        @php $ts = $partner->last_api_test_at?->format('H:i:s'); @endphp
                                        <div class="tpe-terminal-line tpe-terminal-line--muted"><span class="tpe-terminal-ts">[{{ $ts }}]</span>$ test-credentials --supplier={{ $supplierSlug }}</div>
                                        <div class="tpe-terminal-line tpe-terminal-line--{{ $apiStatus === 'success' ? 'success' : 'error' }} tpe-terminal-strong"><span class="tpe-terminal-ts">[{{ $ts }}]</span>{{ $apiStatus === 'success' ? '✓' : '✗' }} {{ $partner->last_api_test_message }}</div>
                                        @if($apiStatus === 'success')
                                            <div class="tpe-terminal-line tpe-terminal-line--success"><span class="tpe-terminal-ts">[{{ $ts }}]</span>✓ API credentials verified. You may proceed to the next step.<span class="tpe-terminal-cursor"></span></div>
                                        @else
                                            <div class="tpe-terminal-line tpe-terminal-line--error"><span class="tpe-terminal-ts">[{{ $ts }}]</span>✗ API credentials are invalid. Please enter the correct credentials before proceeding to the next step.<span class="tpe-terminal-cursor"></span></div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            <div id="apiVerifyWarning" class="tpe-verify-warning" @if($apiStatus !== 'failed') style="display:none" @endif>
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <div>API credentials are invalid. Please enter the correct credentials before proceeding to the next step.</div>
                            </div>

                            <div class="tpe-step-actions">
                                <button type="button" class="tpe-btn tpe-btn-outline tpe-step-prev" data-prev="credentials"><i class="bi bi-arrow-left"></i> Back</button>
                                <button type="button" id="financialContinueBtn" class="tpe-btn tpe-btn-primary tpe-step-next" data-next="financial"
                                        {{ $apiStatus !== 'success' ? 'disabled' : '' }}
                                        title="{{ $apiStatus !== 'success' ? 'Test and verify API credentials first' : '' }}">
                                    Continue to Financial Information <i class="bi bi-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        <!-- ===== STEP 3: FINANCIAL INFORMATION ===== -->
                        <div class="tpe-step-panel" data-step-panel="financial">
                            <p class="tpe-stepper-intro">Manage {{ $partner->company_name }}'s commission, discount, and B2B/B2C settings.</p>

                            <div class="tpe-subcard">
                                <div class="tpe-subcard-header"><i class="bi bi-currency-dollar"></i> Financial Information</div>
                                <div class="tpe-subcard-body">
                                    <div class="tpe-grid-4">
                                        @foreach($financeItems as $item)
                                            @php
                                                $typeVal = old($item['type_field'], $partner->getAttribute($item['type_field']) ?? 'percentage');
                                                $valueVal = old($item['field'], $partner->getAttribute($item['field']));
                                            @endphp
                                            <div class="tpe-field">
                                                <label for="{{ $item['type_field'] }}">{{ $item['label'] }} Type</label>
                                                <select name="{{ $item['type_field'] }}" id="{{ $item['type_field'] }}" class="form-select tpe-pricing-type" data-value-target="{{ $item['field'] }}">
                                                    <option value="percentage" {{ $typeVal == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                    <option value="fixed" {{ $typeVal == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                                </select>
                                            </div>
                                            <div class="tpe-field">
                                                <label for="{{ $item['field'] }}">{{ $item['label'] }} Value</label>
                                                <input type="number" name="{{ $item['field'] }}" id="{{ $item['field'] }}"
                                                       class="form-control @error($item['field']) is-invalid @enderror"
                                                       value="{{ $valueVal }}" min="0" step="0.01" placeholder="0"
                                                       {{ $typeVal == 'percentage' ? 'max=100' : '' }}>
                                                @error($item['field'])
                                                <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="tpe-step-actions">
                                <button type="button" class="tpe-btn tpe-btn-outline tpe-step-prev" data-prev="test-api"><i class="bi bi-arrow-left"></i> Back</button>
                                @if($showImportTab)
                                    <button type="button" class="tpe-btn tpe-btn-primary tpe-step-next" data-next="import">Continue to Import Data <i class="bi bi-arrow-right"></i></button>
                                @else
                                    <span></span>
                                @endif
                            </div>
                        </div>

                        @if($showImportTab)
                        <!-- ===== STEP 4: IMPORT DATA ===== -->
                        <div class="tpe-step-panel" data-step-panel="import">
                            <div class="tpe-grid-2">
                                <div class="tpe-field">
                                    <label>Import Type</label>
                                    <select id="importTypeSelect" class="form-select">
                                        <option value="">Select import type</option>
                                        <option value="hotels">Hotels</option>
                                        <option value="rates">Rates &amp; Availability</option>
                                        <option value="content">Static Content</option>
                                        <option value="images">Images</option>
                                    </select>
                                </div>
                                <div class="tpe-field">
                                    <label>File Format</label>
                                    <select id="fileFormatSelect" class="form-select">
                                        <option value="">Select format</option>
                                        <option value="csv">CSV</option>
                                        <option value="json">JSON</option>
                                        <option value="xml">XML</option>
                                        <option value="xlsx">Excel (XLSX)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="tpe-field" style="margin-top: 14px;">
                                <label>File Upload</label>
                                <div class="tpe-file-row">
                                    <label class="tpe-btn tpe-btn-outline tpe-file-choose-btn" for="importFileInput">
                                        <i class="bi bi-upload"></i> Choose File
                                    </label>
                                    <input type="file" id="importFileInput" class="tpe-file-input" accept=".csv,.txt,.json,.xml,.xlsx,.xls">
                                    <span id="importFileName" class="tpe-file-name">No file chosen</span>
                                    <button type="button" id="importContentBtn" class="tpe-btn tpe-btn-primary">
                                        <i class="bi bi-cloud-arrow-up"></i> <span>Import Content</span>
                                    </button>
                                </div>
                                <div id="importResult" class="tpe-inline-result"></div>
                            </div>

                            <!-- Imported records — read-only data management -->
                            <div class="tpe-import-list-header">
                                <h6>Imported Data</h6>
                                <span class="tpe-hint">View or delete previously imported files. Imported data cannot be edited here.</span>
                            </div>
                            <div class="tpe-import-table-wrap">
                                <table class="tpe-import-table" id="importTable">
                                    <thead>
                                        <tr>
                                            <th>File</th>
                                            <th>Type</th>
                                            <th>Format</th>
                                            <th>Status</th>
                                            <th>Records</th>
                                            <th>Date</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody id="importTableBody">
                                        @forelse($imports as $import)
                                            <tr data-import-id="{{ $import->id }}">
                                                <td>{{ $import->original_filename }}</td>
                                                <td>{{ ucfirst($import->import_type) }}</td>
                                                <td>{{ strtoupper($import->file_format) }}</td>
                                                <td><span class="tpe-status-badge tpe-status-{{ $import->status === 'imported' ? 'success' : 'failed' }}">{{ ucfirst($import->status) }}</span></td>
                                                <td>{{ $import->records_count ?? '—' }}</td>
                                                <td>{{ $import->created_at->diffForHumans() }}</td>
                                                <td class="tpe-import-actions">
                                                    <button type="button" class="tpe-icon-btn tpe-import-view-btn" data-import-id="{{ $import->id }}" title="View"><i class="bi bi-eye"></i></button>
                                                    <button type="button" class="tpe-icon-btn tpe-icon-btn-danger tpe-import-delete-btn" data-import-id="{{ $import->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr id="importTableEmptyRow">
                                                <td colspan="7" class="tpe-import-empty">No data imported yet. Upload a file above to get started.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                            <div class="tpe-step-actions">
                                <button type="button" class="tpe-btn tpe-btn-outline tpe-step-prev" data-prev="financial"><i class="bi bi-arrow-left"></i> Back</button>
                                <span></span>
                            </div>
                        </div>
                        @endif

                    </div>
                </div>
            @else
                <!-- ===== FINANCIAL INFORMATION (Manual supplier — no API credentials/test/import) ===== -->
                <div class="tpe-subcard">
                    <div class="tpe-subcard-header"><i class="bi bi-currency-dollar"></i> Financial Information</div>
                    <div class="tpe-subcard-body">
                        <div class="tpe-grid-4">
                            @foreach($financeItems as $item)
                                @php
                                    $typeVal = old($item['type_field'], $partner->getAttribute($item['type_field']) ?? 'percentage');
                                    $valueVal = old($item['field'], $partner->getAttribute($item['field']));
                                @endphp
                                <div class="tpe-field">
                                    <label for="{{ $item['type_field'] }}">{{ $item['label'] }} Type</label>
                                    <select name="{{ $item['type_field'] }}" id="{{ $item['type_field'] }}" class="form-select tpe-pricing-type" data-value-target="{{ $item['field'] }}">
                                        <option value="percentage" {{ $typeVal == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                        <option value="fixed" {{ $typeVal == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                    </select>
                                </div>
                                <div class="tpe-field">
                                    <label for="{{ $item['field'] }}">{{ $item['label'] }} Value</label>
                                    <input type="number" name="{{ $item['field'] }}" id="{{ $item['field'] }}"
                                           class="form-control @error($item['field']) is-invalid @enderror"
                                           value="{{ $valueVal }}" min="0" step="0.01" placeholder="0"
                                           {{ $typeVal == 'percentage' ? 'max=100' : '' }}>
                                    @error($item['field'])
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            <!-- ===== FORM ACTIONS ===== -->
            <div class="tpe-actions">
                <a href="{{ route('admin.travel-partners.index') }}" class="tpe-btn tpe-btn-outline">
                    <i class="bi bi-x-circle"></i> Cancel
                </a>
                <div class="tpe-actions-right">
                    @if($partner->status == 'active')
                        <button type="button" class="tpe-btn tpe-btn-warning" onclick="suspendPartner({{ $partner->id }})">
                            <i class="bi bi-pause-circle"></i> Suspend Partner
                        </button>
                    @else
                        <button type="button" class="tpe-btn tpe-btn-success" onclick="activatePartner({{ $partner->id }})">
                            <i class="bi bi-play-circle"></i> Activate Partner
                        </button>
                    @endif
                    <button type="submit" class="tpe-btn tpe-btn-primary">
                        <i class="bi bi-check-circle"></i> Update Partner
                    </button>
                </div>
            </div>
        </form>
    </div>

        <!-- ===== IMPORT PREVIEW MODAL (read-only) ===== -->
        <div class="modal fade" id="importViewModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="bi bi-eye"></i> Import Preview</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="importViewModalBody">
                        <div class="tpe-hint">Loading…</div>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
            <script>
                const BASE_URL = "{{ url('') }}";
                const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

                function suspendPartner(partnerId) {
                    if (confirm('Are you sure you want to suspend this partner?')) {
                        fetch(`${BASE_URL}/admin/travel-partners/${partnerId}/suspend`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.reload();
                                }
                            });
                    }
                }

                function activatePartner(partnerId) {
                    if (confirm('Are you sure you want to activate this partner?')) {
                        fetch(`${BASE_URL}/admin/travel-partners/${partnerId}/activate`, {
                            method: 'PATCH',
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    window.location.reload();
                                }
                            });
                    }
                }

                // ===== STEPPER NAVIGATION =====
                const stepButtons = Array.from(document.querySelectorAll('.tpe-step'));
                const stepPanels = Array.from(document.querySelectorAll('.tpe-step-panel'));

                function activateStep(stepKey) {
                    stepButtons.forEach(b => b.classList.toggle('is-active', b.dataset.step === stepKey));
                    stepPanels.forEach(p => p.classList.toggle('active', p.dataset.stepPanel === stepKey));
                }

                stepButtons.forEach(b => b.addEventListener('click', () => activateStep(b.dataset.step)));
                document.querySelectorAll('.tpe-step-next').forEach(b => b.addEventListener('click', () => activateStep(b.dataset.next)));
                document.querySelectorAll('.tpe-step-prev').forEach(b => b.addEventListener('click', () => activateStep(b.dataset.prev)));

                activateStep(@json($defaultStep ?? 'credentials'));

                function markStepStatus(stepKey, status) {
                    const btn = stepButtons.find(b => b.dataset.step === stepKey);
                    if (!btn) return;
                    btn.classList.remove('is-complete', 'is-failed');
                    if (status === 'success') btn.classList.add('is-complete');
                    if (status === 'failed') btn.classList.add('is-failed');
                }

                // Status dot color follows the select
                const statusSelect = document.getElementById('statusSelect');
                const statusDot = document.getElementById('statusDot');
                if (statusSelect) {
                    statusSelect.addEventListener('change', function () {
                        statusDot.classList.toggle('is-active', this.value === 'active');
                    });
                }

                // ===== API CREDENTIAL FIELDS — the field only ever shows the encrypted
                // value at rest (or is empty); the real credential is never decrypted for
                // display. "Change" clears it so a genuinely new value can be typed, and
                // that's the only content these fields ever submit or send for testing.
                document.querySelectorAll('.tpe-cred-change').forEach(function (changeBtn) {
                    changeBtn.addEventListener('click', function () {
                        const wrap = changeBtn.closest('.tpe-input-icon-wrap');
                        const input = wrap.querySelector('.tpe-cred-input');
                        const checkIcon = wrap.querySelector('.tpe-input-check');
                        if (!input) return;

                        if (input.disabled) {
                            // Enter edit mode — start blank, never prefilled with the old secret.
                            input.disabled = false;
                            input.value = '';
                            input.placeholder = 'Enter new credential to replace the saved one';
                            if (checkIcon) checkIcon.style.display = 'none';
                            changeBtn.innerHTML = '<i class="bi bi-x-lg"></i>';
                            changeBtn.title = 'Cancel';
                            input.focus();
                        } else {
                            // Cancel — restore the disabled, encrypted display.
                            input.disabled = true;
                            input.value = input.dataset.originalValue || '';
                            input.placeholder = '';
                            if (checkIcon) checkIcon.style.display = '';
                            changeBtn.innerHTML = '<i class="bi bi-pencil-fill"></i>';
                            changeBtn.title = 'Replace this credential';
                        }
                    });
                });

                // ===== DYNAMIC API CREDENTIAL CHECK — terminal-style console =====
                const testCredentialsBtn = document.getElementById('testCredentialsBtn');
                const terminalBody = document.getElementById('apiTestTerminalBody');
                const apiVerifyWarning = document.getElementById('apiVerifyWarning');
                const financialContinueBtn = document.getElementById('financialContinueBtn');
                const supplierName = @json($partner->company_name);
                const supplierSlug = @json($supplierSlug);
                const credentialCheckEndpoint = @json($credentialCheckEndpoint);
                let credentialTestInFlight = false;

                function terminalTimestamp() {
                    return new Date().toTimeString().slice(0, 8);
                }

                function appendTerminalLine(text, type) {
                    terminalBody.querySelectorAll('.tpe-terminal-cursor').forEach(c => c.remove());
                    const line = document.createElement('div');
                    line.className = 'tpe-terminal-line tpe-terminal-line--' + type;
                    line.innerHTML = `<span class="tpe-terminal-ts">[${terminalTimestamp()}]</span>${escapeHtml(text)}`;
                    terminalBody.appendChild(line);
                    terminalBody.scrollTop = terminalBody.scrollHeight;
                    return line;
                }

                function appendCursorTo(line) {
                    const cursor = document.createElement('span');
                    cursor.className = 'tpe-terminal-cursor';
                    line.appendChild(cursor);
                    terminalBody.scrollTop = terminalBody.scrollHeight;
                }

                function setVerified(ok) {
                    if (!financialContinueBtn) return;
                    financialContinueBtn.disabled = !ok;
                    financialContinueBtn.title = ok ? '' : 'Test and verify API credentials first';
                    if (apiVerifyWarning) apiVerifyWarning.style.display = ok ? 'none' : '';
                }

                document.getElementById('clearTerminalBtn')?.addEventListener('click', function () {
                    terminalBody.innerHTML = '';
                    const line = appendTerminalLine(`Console cleared. Waiting to run credential test for ${supplierName}.`, 'muted');
                    appendCursorTo(line);
                });

                if (testCredentialsBtn) {
                    testCredentialsBtn.addEventListener('click', function () {
                        if (credentialTestInFlight) return;

                        // Only a field the admin is actively replacing (enabled, not
                        // still showing the saved ciphertext) is sent — the server fills
                        // in everything else from the partner's own stored, decrypted value.
                        const payload = {};
                        for (let i = 1; i <= 6; i++) {
                            const input = document.querySelector(`input[name="api_credential_${i}"]`);
                            if (input && !input.disabled && input.value.trim() !== '') payload[`api_credential_${i}`] = input.value.trim();
                        }

                        credentialTestInFlight = true;
                        testCredentialsBtn.disabled = true;

                        appendTerminalLine(`$ test-credentials --supplier=${supplierSlug}`, 'muted');
                        const connectingLine = appendTerminalLine(`→ Connecting to ${supplierName} credential-check endpoint…`, 'info');
                        appendCursorTo(connectingLine);

                        fetch(credentialCheckEndpoint, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                            body: JSON.stringify(payload)
                        })
                            .then(async r => {
                                let data = null;
                                try { data = await r.json(); } catch (e) { /* non-JSON response */ }
                                if (!data) {
                                    throw new Error(r.status === 404
                                        ? `Credential testing is not available for "${supplierName}" yet.`
                                        : `Unexpected response (HTTP ${r.status}).`);
                                }
                                return data;
                            })
                            .then(data => {
                                const success = !!data.success;

                                if (data.connection) appendTerminalLine(`${success ? '✓' : '✗'} Connection: ${data.connection}`, success ? 'success' : 'error');
                                if (data.response_time != null) appendTerminalLine(`→ Response time: ${data.response_time}ms`, 'info');
                                if (!success && data.error_type) appendTerminalLine(`✗ Error type: ${data.error_type}`, 'error');

                                const msg = success ? (data.message || 'Connected successfully.') : (data.error_detail || data.message || 'Verification failed.');
                                const msgLine = appendTerminalLine(`${success ? '✓' : '✗'} ${msg}`, success ? 'success' : 'error');
                                msgLine.classList.add('tpe-terminal-strong');

                                const finalText = success
                                    ? '✓ API credentials verified. You may proceed to the next step.'
                                    : '✗ API credentials are invalid. Please enter the correct credentials before proceeding to the next step.';
                                const finalLine = appendTerminalLine(finalText, success ? 'success' : 'error');
                                appendCursorTo(finalLine);

                                setVerified(success);
                                markStepStatus('test-api', success ? 'success' : 'failed');

                                // Persist the outcome so the stepper survives a page reload
                                fetch(`${BASE_URL}/admin/travel-partners/{{ $partner->id }}/test-api`, {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': CSRF_TOKEN,
                                        'X-Requested-With': 'XMLHttpRequest',
                                        'Accept': 'application/json'
                                    },
                                    body: JSON.stringify({ success, message: msg })
                                }).catch(() => {});
                            })
                            .catch(err => {
                                appendTerminalLine(`✗ ${err.message}`, 'error');
                                const finalLine = appendTerminalLine('✗ API credentials are invalid. Please enter the correct credentials before proceeding to the next step.', 'error');
                                appendCursorTo(finalLine);
                                setVerified(false);
                                markStepStatus('test-api', 'failed');
                            })
                            .finally(() => {
                                credentialTestInFlight = false;
                                testCredentialsBtn.disabled = false;
                            });
                    });
                }

                // ===== FINANCIAL INFORMATION — Commission/Discount/B2B/B2C rows =====
                // A Value input is capped at 100 only while its Type is "Percentage"; a
                // "Fixed Amount" has no ceiling.
                document.querySelectorAll('.tpe-pricing-type').forEach(typeSelect => {
                    const valueInput = document.getElementById(typeSelect.dataset.valueTarget);
                    if (!valueInput) return;
                    typeSelect.addEventListener('change', function () {
                        if (this.value === 'percentage') {
                            valueInput.setAttribute('max', '100');
                        } else {
                            valueInput.removeAttribute('max');
                        }
                    });
                });

                function setInlineResult(el, ok, message) {
                    el.className = 'tpe-inline-result ' + (ok ? 'tpe-inline-success' : 'tpe-inline-failed');
                    el.innerHTML = `<i class="bi ${ok ? 'bi-check-circle-fill' : 'bi-x-circle-fill'}"></i> ${message}`;
                }

                // ===== IMPORT DATA =====
                const importFileInput = document.getElementById('importFileInput');
                const importFileName = document.getElementById('importFileName');
                if (importFileInput) {
                    importFileInput.addEventListener('change', function () {
                        importFileName.textContent = this.files.length ? this.files[0].name : 'No file chosen';
                    });
                }

                const importTableBody = document.getElementById('importTableBody');

                function escapeHtml(str) {
                    const div = document.createElement('div');
                    div.textContent = str ?? '';
                    return div.innerHTML;
                }

                function addImportRow(imp) {
                    const emptyRow = document.getElementById('importTableEmptyRow');
                    if (emptyRow) emptyRow.remove();

                    const statusClass = imp.status === 'imported' ? 'tpe-status-success' : 'tpe-status-failed';
                    const statusLabel = imp.status.charAt(0).toUpperCase() + imp.status.slice(1);

                    const row = document.createElement('tr');
                    row.dataset.importId = imp.id;
                    row.innerHTML = `
                        <td>${escapeHtml(imp.original_filename)}</td>
                        <td>${escapeHtml(imp.import_type.charAt(0).toUpperCase() + imp.import_type.slice(1))}</td>
                        <td>${escapeHtml(imp.file_format.toUpperCase())}</td>
                        <td><span class="tpe-status-badge ${statusClass}">${statusLabel}</span></td>
                        <td>${imp.records_count ?? '—'}</td>
                        <td>${escapeHtml(imp.created_at_human)}</td>
                        <td class="tpe-import-actions">
                            <button type="button" class="tpe-icon-btn tpe-import-view-btn" data-import-id="${imp.id}" title="View"><i class="bi bi-eye"></i></button>
                            <button type="button" class="tpe-icon-btn tpe-icon-btn-danger tpe-import-delete-btn" data-import-id="${imp.id}" title="Delete"><i class="bi bi-trash"></i></button>
                        </td>`;
                    importTableBody.prepend(row);
                    markStepStatus('import', 'success');
                }

                const importContentBtn = document.getElementById('importContentBtn');
                const importResult = document.getElementById('importResult');
                if (importContentBtn) {
                    importContentBtn.addEventListener('click', function () {
                        const importType = document.getElementById('importTypeSelect').value;
                        const fileFormat = document.getElementById('fileFormatSelect').value;
                        const file = importFileInput.files[0];

                        if (!importType || !fileFormat || !file) {
                            importResult.className = 'tpe-inline-result tpe-inline-failed';
                            importResult.innerHTML = '<i class="bi bi-x-circle-fill"></i> Select an import type, file format, and file before importing.';
                            return;
                        }

                        const formData = new FormData();
                        formData.append('import_type', importType);
                        formData.append('file_format', fileFormat);
                        formData.append('file', file);

                        importContentBtn.disabled = true;
                        importResult.className = 'tpe-inline-result tpe-inline-testing';
                        importResult.innerHTML = '<i class="bi bi-arrow-repeat tpe-spin"></i> Uploading…';

                        fetch(`${BASE_URL}/admin/travel-partners/{{ $partner->id }}/import-content`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            },
                            body: formData
                        })
                            .then(r => r.json())
                            .then(data => {
                                setInlineResult(importResult, data.success, data.message);
                                if (data.import) {
                                    addImportRow(data.import);
                                    importFileInput.value = '';
                                    importFileName.textContent = 'No file chosen';
                                }
                            })
                            .catch(err => setInlineResult(importResult, false, err.message))
                            .finally(() => { importContentBtn.disabled = false; });
                    });
                }

                // ===== IMPORT VIEW / DELETE (event delegation — rows can be added dynamically) =====
                const importViewModalEl = document.getElementById('importViewModal');
                const importViewModalBody = document.getElementById('importViewModalBody');
                const importViewModal = importViewModalEl ? new bootstrap.Modal(importViewModalEl) : null;

                function renderPreviewTable(previewData) {
                    if (!previewData || !previewData.length) {
                        return '<div class="tpe-hint">No preview rows available.</div>';
                    }
                    const columns = Object.keys(previewData[0]);
                    let html = '<div class="tpe-import-table-wrap"><table class="tpe-import-table"><thead><tr>';
                    columns.forEach(c => html += `<th>${escapeHtml(c)}</th>`);
                    html += '</tr></thead><tbody>';
                    previewData.forEach(row => {
                        html += '<tr>';
                        columns.forEach(c => html += `<td>${escapeHtml(row[c])}</td>`);
                        html += '</tr>';
                    });
                    html += '</tbody></table></div>';
                    return html;
                }

                document.body.addEventListener('click', function (e) {
                    const viewBtn = e.target.closest('.tpe-import-view-btn');
                    if (viewBtn) {
                        const id = viewBtn.dataset.importId;
                        importViewModalBody.innerHTML = '<div class="tpe-hint">Loading…</div>';
                        importViewModal?.show();

                        fetch(`${BASE_URL}/admin/travel-partners/{{ $partner->id }}/imports/${id}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        })
                            .then(r => r.json())
                            .then(data => {
                                const imp = data.import;
                                let html = `<div class="tpe-import-meta">
                                    <div><strong>File:</strong> ${escapeHtml(imp.original_filename)}</div>
                                    <div><strong>Type:</strong> ${escapeHtml(imp.import_type)} &middot; <strong>Format:</strong> ${escapeHtml(imp.file_format.toUpperCase())}</div>
                                    <div><strong>Status:</strong> ${escapeHtml(imp.status)} &middot; <strong>Records:</strong> ${imp.records_count ?? '—'}</div>
                                    <div><strong>Imported:</strong> ${escapeHtml(imp.created_at)}</div>
                                    ${imp.error_message ? `<div class="tpe-error-text">${escapeHtml(imp.error_message)}</div>` : ''}
                                </div>`;
                                html += renderPreviewTable(imp.preview_data);
                                importViewModalBody.innerHTML = html;
                            })
                            .catch(err => {
                                importViewModalBody.innerHTML = `<div class="tpe-error-text">Failed to load preview: ${escapeHtml(err.message)}</div>`;
                            });
                        return;
                    }

                    const deleteBtn = e.target.closest('.tpe-import-delete-btn');
                    if (deleteBtn) {
                        if (!confirm('Delete this imported file? This cannot be undone.')) return;
                        const id = deleteBtn.dataset.importId;
                        const row = deleteBtn.closest('tr');

                        fetch(`${BASE_URL}/admin/travel-partners/{{ $partner->id }}/imports/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': CSRF_TOKEN,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success) {
                                    row.remove();
                                    if (!importTableBody.querySelector('tr')) {
                                        importTableBody.innerHTML = '<tr id="importTableEmptyRow"><td colspan="7" class="tpe-import-empty">No data imported yet. Upload a file above to get started.</td></tr>';
                                        markStepStatus('import', null);
                                    }
                                }
                            });
                    }
                });
            </script>
        @endpush

@endsection
