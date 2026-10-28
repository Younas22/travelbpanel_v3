@extends('admin-modern.layouts.app')

@section('title', 'Edit Travel Partner')

@section('content')
        <div class="tbp-partner-edit-page">

        @php
            $supplierSlug = strtolower(trim($partner->company_name));
            $partnerLogoFile = 'public/assets/images/partners/'.$partner->company_name.'.png';
            $partnerLogo = is_file(base_path($partnerLogoFile)) ? asset($partnerLogoFile) : null;
            $credentialCheckEndpoint = route('admin.travel-partners.test-credentials-live', $partner);
            $financeItems = [
                ['label' => 'Commission', 'field' => 'commission_rate', 'type_field' => 'commission_type'],
                ['label' => 'Discount', 'field' => 'discount_rate', 'type_field' => 'discount_type'],
                ['label' => 'B2B', 'field' => 'b2b_markup', 'type_field' => 'b2b_markup_type'],
                ['label' => 'B2C', 'field' => 'b2c_markup', 'type_field' => 'b2c_markup_type'],
            ];
            $isManual = $partner->company_name == 'Manual';
            $showImportTab = !$isManual && strtolower(optional($partner->module)->name ?? '') === 'stay';
            $apiStatus = $partner->last_api_test_status;

            $credentialCount = 0;
            for ($i = 1; $i <= 6; $i++) {
                $raw = $partner->getRawOriginal("api_credential_{$i}");
                if ($raw !== null && $raw !== '') $credentialCount++;
            }
            $hasAnyCredential = $credentialCount > 0;
            $hasCommission = !is_null($partner->commission_rate) && (float) $partner->commission_rate > 0;

            // Manual suppliers have no external API, so they skip the API
            // Credentials and Development Mode steps entirely.
            $stepList = ['basic' => ['icon' => 'bi-building', 'title' => 'Basic Information', 'desc' => 'Company & services']];
            if (!$isManual) $stepList['credentials'] = ['icon' => 'bi-link-45deg', 'title' => 'API Credentials', 'desc' => 'Enter API keys'];
            $stepList['financial'] = ['icon' => 'bi-database', 'title' => 'Financial Information', 'desc' => 'Commission & markups'];
            if (!$isManual) $stepList['devmode'] = ['icon' => 'bi-code-slash', 'title' => 'Development Mode', 'desc' => 'Test / Sandbox settings'];
            if ($showImportTab) $stepList['import'] = ['icon' => 'bi-cloud-arrow-up', 'title' => 'Import Data', 'desc' => 'Review & import'];
            $stepList['review'] = ['icon' => 'bi-check-circle-fill', 'title' => 'Review & Save', 'desc' => 'Confirm configuration'];
            $n = 1;
            foreach ($stepList as $k => $s) $stepList[$k]['num'] = $n++;
            $stepKeys = array_keys($stepList);

            // Open the step holding the first server-side validation error;
            // otherwise resume where the controller says the admin left off.
            $errorStep = null;
            foreach ($errors->keys() as $key) {
                $errorStep = match (true) {
                    str_starts_with($key, 'api_credential_') => 'credentials',
                    in_array($key, ['commission_rate', 'commission_type', 'discount_rate', 'discount_type', 'b2b_markup', 'b2b_markup_type', 'b2c_markup', 'b2c_markup_type']) => 'financial',
                    $key === 'development_mode' => 'devmode',
                    default => 'basic',
                };
                break;
            }
            $defaultStepKey = $errorStep ?? match ($defaultStep ?? null) {
                'test-api' => 'credentials',
                'credentials', 'financial', 'import' => $defaultStep,
                default => 'basic',
            };
            if (!array_key_exists($defaultStepKey, $stepList)) $defaultStepKey = 'basic';
        @endphp

        <!-- ===== PAGE HEADER ===== -->
        <a href="{{ route('admin.travel-partners.index') }}" class="tbp-partner-edit-back">
            <i class="bi bi-arrow-left"></i> Back to Partners
        </a>
        <div class="tbp-partner-edit-header">
            <div class="tbp-partner-edit-header-row">
                <h2 class="tbp-partner-edit-title">Edit Travel Partner</h2>
                <span class="tbp-partner-edit-badge">@if($partnerLogo)<img src="{{ $partnerLogo }}" alt="" class="tbp-partner-edit-badge-logo">@else<i class="bi bi-building"></i>@endif {{ $partner->company_name }}</span>
            </div>
            <p class="tbp-partner-edit-subtitle">Update partner information, configure API credentials and activate services.</p>
        </div>

        <!-- ===== STEPPER ===== -->
        <div class="tbp-partner-edit-stepper" id="tbpStepper" role="tablist" style="--tbp-steps: {{ count($stepList) }};">
            @foreach($stepList as $key => $s)
                <button type="button" role="tab"
                        class="tbp-partner-edit-step @if($steps[$key]['complete'] ?? false) tbp-partner-edit-step-complete @endif @if(($steps[$key]['status'] ?? null) === 'failed') tbp-partner-edit-step-failed @endif"
                        data-step="{{ $key }}" title="{{ $s['title'] }}">
                    <span class="tbp-partner-edit-step-circle">
                        <span class="tbp-partner-edit-step-num">{{ $s['num'] }}</span>
                        <i class="bi bi-check-lg tbp-partner-edit-step-check"></i>
                    </span>
                    <span class="tbp-partner-edit-step-icon"><i class="bi {{ $s['icon'] }}"></i></span>
                    <span class="tbp-partner-edit-step-text">
                        <span class="tbp-partner-edit-step-title">{{ $s['title'] }}</span>
                        <span class="tbp-partner-edit-step-desc">{{ $s['desc'] }}</span>
                    </span>
                </button>
            @endforeach
        </div>

        <form action="{{ route('admin.travel-partners.update', $partner->id) }}"
              method="POST" enctype="multipart/form-data" id="travelPartnerForm" novalidate>
            @csrf
            @method('PATCH')
            <input type="hidden" name="status" id="statusHiddenInput" value="{{ old('status', $partner->status) }}">

            <div class="tbp-partner-edit-layout">
                <div class="tbp-partner-edit-main">

                    <!-- ===== STEP: BASIC INFORMATION ===== -->
                    <section class="tbp-partner-edit-step-content tbp-partner-edit-basic-info" data-step-panel="basic" role="tabpanel">
                        <div class="tbp-partner-edit-card">
                            <div class="tbp-partner-edit-card-head">
                                <div class="tbp-partner-edit-card-badge">{{ $stepList['basic']['num'] }}</div>
                                <div class="tbp-partner-edit-card-icon"><i class="bi bi-building"></i></div>
                                <div>
                                    <div class="tbp-partner-edit-card-title">Basic Information</div>
                                    <div class="tbp-partner-edit-card-desc">Enter the partner details to identify this supplier in your system.</div>
                                </div>
                            </div>
                            <div class="tbp-partner-edit-card-body">
                                <div class="tbp-partner-edit-grid tbp-partner-edit-grid-3">
                                    <div class="tbp-partner-edit-field">
                                        <label>Company Name <span class="tbp-partner-edit-required">*</span></label>
                                        <input type="text" readonly name="company_name" class="form-control @error('company_name') is-invalid @enderror"
                                               value="{{ old('company_name', $partner->company_name) }}" required>
                                        <div class="tbp-partner-edit-lock-hint"><i class="bi bi-lock-fill"></i> Cannot be changed</div>
                                        @error('company_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="tbp-partner-edit-field">
                                        <label>Module <span class="tbp-partner-edit-required">*</span></label>
                                        <select disabled name="module_id" class="form-select @error('module_id') is-invalid @enderror">
                                            <option value="">Select module</option>
                                            @foreach($modules as $module)
                                                <option value="{{ $module->id }}" {{ old('module_id', $partner->module_id) == $module->id ? 'selected' : '' }}>{{ $module->name }}</option>
                                            @endforeach
                                        </select>
                                        <div class="tbp-partner-edit-lock-hint"><i class="bi bi-lock-fill"></i> Cannot be changed</div>
                                        @error('module_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                    <div class="tbp-partner-edit-field">
                                        <label>Status <span class="tbp-partner-edit-required">*</span></label>
                                        <div class="tbp-partner-edit-status-select-wrap">
                                            <span id="statusDot" class="tbp-partner-edit-status-dot {{ old('status', $partner->status) == 'active' ? 'is-active' : '' }}"></span>
                                            <select id="statusSelect" class="form-select @error('status') is-invalid @enderror" required>
                                                <option value="active" {{ old('status', $partner->status) == 'active' ? 'selected' : '' }}>Active</option>
                                                <option value="inactive" {{ old('status', $partner->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                <option value="pending" {{ old('status', $partner->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                            </select>
                                        </div>
                                        @error('status')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    </div>
                                </div>
                                <div class="tbp-partner-edit-step-error" data-step-error="basic"></div>
                            </div>
                        </div>
                    </section>

                    @if(!$isManual)
                    <!-- ===== STEP: API CREDENTIALS ===== -->
                    <section class="tbp-partner-edit-step-content tbp-partner-edit-api-credentials" data-step-panel="credentials" role="tabpanel">
                        <div class="tbp-partner-edit-card">
                            <div class="tbp-partner-edit-card-head">
                                <div class="tbp-partner-edit-card-badge">{{ $stepList['credentials']['num'] }}</div>
                                <div class="tbp-partner-edit-card-icon"><i class="bi bi-link-45deg"></i></div>
                                <div>
                                    <div class="tbp-partner-edit-card-title">API Credentials &amp; Setup</div>
                                    <div class="tbp-partner-edit-card-desc">Add your {{ $partner->company_name }} API credentials and verify the connection.</div>
                                </div>
                            </div>
                            <div class="tbp-partner-edit-card-body">
                                <div class="tbp-partner-edit-banner">
                                    <i class="bi bi-info-circle"></i>
                                    <span>Credentials are stored encrypted and only the encrypted value is shown. Type a new value to replace one, then run <strong>Test Credentials</strong> before continuing.</span>
                                </div>

                                <div class="tbp-partner-edit-cred-grid" id="credentialGrid">
                                    @for($i = 1; $i <= 6; $i++)
                                        @php
                                            // Only the encrypted value is ever sent to the browser; the real
                                            // secret stays server-side. An unchanged value is never re-saved.
                                            $rawStored = $partner->getRawOriginal("api_credential_{$i}");
                                            $credVal = old("api_credential_{$i}", $rawStored);
                                            $hasVal = $credVal !== null && $credVal !== '';
                                        @endphp
                                        <div class="tbp-partner-edit-field tbp-partner-edit-cred-slot" data-cred-index="{{ $i }}" @unless($hasVal) hidden @endunless>
                                            @php $credLabels = $supplierSlug === 'webbeds' ? [1 => 'Username', 2 => 'Password', 3 => 'ID'] : []; @endphp
                                            <label>{{ $credLabels[$i] ?? 'API Credential '.$i }}</label>
                                            <div class="tbp-partner-edit-input-wrap">
                                                <input type="password" name="api_credential_{{ $i }}"
                                                       class="form-control tbp-partner-edit-cred-input @error('api_credential_'.$i) is-invalid @enderror"
                                                       placeholder="Enter credential"
                                                       value="{{ $credVal }}"
                                                       data-original-value="{{ $rawStored }}"
                                                       autocomplete="new-password">
                                                <span class="tbp-partner-edit-input-action">
                                                    <button type="button" class="tbp-partner-edit-cred-toggle" title="Show / hide"><i class="bi bi-eye"></i></button>
                                                </span>
                                            </div>
                                            @error('api_credential_'.$i)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    @endfor
                                </div>

                                <div class="tbp-partner-edit-testrow">
                                    <button type="button" id="testCredentialsBtn" class="tbp-partner-edit-btn tbp-partner-edit-btn-primary">
                                        <i class="bi bi-shield-check"></i> <span>Test Credentials</span>
                                    </button>
                                </div>

                                <div class="tbp-partner-edit-terminal">
                                    <div class="tbp-partner-edit-terminal-header">
                                        <div class="tbp-partner-edit-terminal-dots"><span></span><span></span><span></span></div>
                                        <div class="tbp-partner-edit-terminal-title"><i class="bi bi-terminal"></i> {{ $partner->company_name }} API Test Console</div>
                                        <button type="button" class="tbp-partner-edit-terminal-clear" id="clearTerminalBtn" title="Clear console"><i class="bi bi-x-lg"></i></button>
                                    </div>
                                    <div class="tbp-partner-edit-terminal-body" id="apiTestTerminalBody">
                                        @if(!$apiStatus)
                                            <div class="tbp-partner-edit-terminal-line tbp-partner-edit-terminal-line--muted">Waiting to run credential test for {{ $partner->company_name }}. Click "Test Credentials" to begin.<span class="tbp-partner-edit-terminal-cursor"></span></div>
                                        @else
                                            @php $ts = $partner->last_api_test_at?->format('H:i:s'); @endphp
                                            <div class="tbp-partner-edit-terminal-line tbp-partner-edit-terminal-line--muted"><span class="tbp-partner-edit-terminal-ts">[{{ $ts }}]</span>$ test-credentials --supplier={{ $supplierSlug }}</div>
                                            <div class="tbp-partner-edit-terminal-line tbp-partner-edit-terminal-line--{{ $apiStatus === 'success' ? 'success' : 'error' }} tbp-partner-edit-terminal-strong"><span class="tbp-partner-edit-terminal-ts">[{{ $ts }}]</span>{{ $apiStatus === 'success' ? '✓' : '✗' }} {{ $partner->last_api_test_message }}</div>
                                            @if($apiStatus === 'success')
                                                <div class="tbp-partner-edit-terminal-line tbp-partner-edit-terminal-line--success"><span class="tbp-partner-edit-terminal-ts">[{{ $ts }}]</span>✓ API credentials verified. You may proceed to the next step.<span class="tbp-partner-edit-terminal-cursor"></span></div>
                                            @else
                                                <div class="tbp-partner-edit-terminal-line tbp-partner-edit-terminal-line--error"><span class="tbp-partner-edit-terminal-ts">[{{ $ts }}]</span>✗ API credentials are invalid. Please enter the correct credentials before proceeding to the next step.<span class="tbp-partner-edit-terminal-cursor"></span></div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="tbp-partner-edit-step-error" data-step-error="credentials"></div>
                            </div>
                        </div>
                    </section>
                    @endif

                    <!-- ===== STEP: FINANCIAL INFORMATION ===== -->
                    <section class="tbp-partner-edit-step-content tbp-partner-edit-financial-info" data-step-panel="financial" role="tabpanel">
                        <div class="tbp-partner-edit-card">
                            <div class="tbp-partner-edit-card-head">
                                <div class="tbp-partner-edit-card-badge">{{ $stepList['financial']['num'] }}</div>
                                <div class="tbp-partner-edit-card-icon"><i class="bi bi-database"></i></div>
                                <div>
                                    <div class="tbp-partner-edit-card-title">Financial Information</div>
                                    <div class="tbp-partner-edit-card-desc">Manage {{ $partner->company_name }}'s commission, discount, and B2B/B2C markups.</div>
                                </div>
                            </div>
                            <div class="tbp-partner-edit-card-body">
                                <div class="tbp-partner-edit-grid tbp-partner-edit-grid-4">
                                    @foreach($financeItems as $item)
                                        @php
                                            $typeVal = old($item['type_field'], $partner->getAttribute($item['type_field']) ?? 'percentage');
                                            $valueVal = old($item['field'], $partner->getAttribute($item['field']));
                                        @endphp
                                        <div class="tbp-partner-edit-field">
                                            <label for="{{ $item['type_field'] }}">{{ $item['label'] }} Type</label>
                                            <select name="{{ $item['type_field'] }}" id="{{ $item['type_field'] }}" class="form-select tbp-partner-edit-pricing-type" data-value-target="{{ $item['field'] }}">
                                                <option value="percentage" {{ $typeVal == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                                <option value="fixed" {{ $typeVal == 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                                            </select>
                                        </div>
                                        <div class="tbp-partner-edit-field">
                                            <label for="{{ $item['field'] }}">{{ $item['label'] }} Value</label>
                                            <input type="number" name="{{ $item['field'] }}" id="{{ $item['field'] }}"
                                                   class="form-control tbp-partner-edit-finance-input @error($item['field']) is-invalid @enderror"
                                                   value="{{ $valueVal }}" min="0" step="0.01" placeholder="0"
                                                   {{ $typeVal == 'percentage' ? 'max=100' : '' }}>
                                            @error($item['field'])<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                        </div>
                                    @endforeach
                                </div>
                                <div class="tbp-partner-edit-step-error" data-step-error="financial"></div>
                            </div>
                        </div>
                    </section>

                    @if(!$isManual)
                    <!-- ===== STEP: DEVELOPMENT MODE ===== -->
                    <section class="tbp-partner-edit-step-content tbp-partner-edit-development-mode" data-step-panel="devmode" role="tabpanel">
                        <div class="tbp-partner-edit-card">
                            <div class="tbp-partner-edit-card-head">
                                <div class="tbp-partner-edit-card-badge">{{ $stepList['devmode']['num'] }}</div>
                                <div class="tbp-partner-edit-card-icon"><i class="bi bi-code-slash"></i></div>
                                <div>
                                    <div class="tbp-partner-edit-card-title">Development Mode</div>
                                    <div class="tbp-partner-edit-card-desc">Use the supplier's sandbox environment for testing before going live.</div>
                                </div>
                            </div>
                            <div class="tbp-partner-edit-card-body">
                                <div class="tbp-partner-edit-devmode-row" style="margin-top:0;">
                                    <div class="tbp-partner-edit-devmode-left">
                                        <div class="tbp-partner-edit-devmode-icon"><i class="bi bi-code-slash"></i></div>
                                        <div>
                                            <div class="tbp-partner-edit-devmode-title">Development Mode</div>
                                            <div class="tbp-partner-edit-devmode-desc">Enable sandbox environment for testing before going live. Turn this off for production.</div>
                                        </div>
                                    </div>
                                    <div class="tbp-partner-edit-devmode-left">
                                        <label class="tbp-partner-edit-switch">
                                            <input type="checkbox" name="development_mode" value="1" id="developmentModeSwitch"
                                                {{ old('development_mode', $partner->development_mode) ? 'checked' : '' }}>
                                            <span class="tbp-partner-edit-switch-slider"></span>
                                        </label>
                                        <span id="devModeSummaryBadge" class="tbp-partner-edit-devmode-summary-badge {{ old('development_mode', $partner->development_mode) ? 'is-on' : 'is-off' }}">
                                            {{ old('development_mode', $partner->development_mode) ? 'Sandbox enabled' : 'Sandbox disabled' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    @endif

                    @if($showImportTab)
                    <!-- ===== STEP: IMPORT DATA ===== -->
                    <section class="tbp-partner-edit-step-content tbp-partner-edit-import" data-step-panel="import" role="tabpanel">
                        <div class="tbp-partner-edit-card">
                            <div class="tbp-partner-edit-card-head">
                                <div class="tbp-partner-edit-card-badge">{{ $stepList['import']['num'] }}</div>
                                <div class="tbp-partner-edit-card-icon"><i class="bi bi-cloud-arrow-up"></i></div>
                                <div>
                                    <div class="tbp-partner-edit-card-title">Import Data</div>
                                    <div class="tbp-partner-edit-card-desc">{{ $supplierSlug === 'webbeds' ? 'Import hotels for every WebBeds city into the local database — refreshed automatically every month.' : 'Upload and manage data imports for this supplier.' }}</div>
                                </div>
                            </div>
                            <div class="tbp-partner-edit-card-body">
                                @if($supplierSlug === 'webbeds')
                                    @include('admin.travel-partners._webbeds-import')
                                @else
                                <div class="tbp-partner-edit-grid tbp-partner-edit-grid-2">
                                    <div class="tbp-partner-edit-field">
                                        <label>Import Type</label>
                                        <select id="importTypeSelect" class="form-select">
                                            <option value="">Select import type</option>
                                            <option value="hotels">Hotels</option>
                                            <option value="rates">Rates &amp; Availability</option>
                                            <option value="content">Static Content</option>
                                            <option value="images">Images</option>
                                        </select>
                                    </div>
                                    <div class="tbp-partner-edit-field">
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
                                <div class="tbp-partner-edit-field" style="margin-top:16px;">
                                    <label>File Upload</label>
                                    <div class="tbp-partner-edit-file-row">
                                        <label class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" for="importFileInput"><i class="bi bi-upload"></i> Choose File</label>
                                        <input type="file" id="importFileInput" class="tbp-partner-edit-file-input" accept=".csv,.txt,.json,.xml,.xlsx,.xls">
                                        <span id="importFileName" class="tbp-partner-edit-file-name">No file chosen</span>
                                        <button type="button" id="importContentBtn" class="tbp-partner-edit-btn tbp-partner-edit-btn-primary"><i class="bi bi-cloud-arrow-up"></i> <span>Import Content</span></button>
                                    </div>
                                    <div id="importResult" class="tbp-partner-edit-inline-result"></div>
                                </div>
                                <div class="tbp-partner-edit-import-list-header">
                                    <h6>Imported Data</h6>
                                    <span class="tbp-partner-edit-hint">View or delete previously imported files. Imported data cannot be edited here.</span>
                                </div>
                                <div class="tbp-partner-edit-import-table-wrap">
                                    <table class="tbp-partner-edit-import-table">
                                        <thead><tr><th>File</th><th>Type</th><th>Format</th><th>Status</th><th>Records</th><th>Date</th><th></th></tr></thead>
                                        <tbody id="importTableBody">
                                            @forelse($imports as $import)
                                                <tr data-import-id="{{ $import->id }}">
                                                    <td>{{ $import->original_filename }}</td>
                                                    <td>{{ ucfirst($import->import_type) }}</td>
                                                    <td>{{ strtoupper($import->file_format) }}</td>
                                                    <td><span class="tbp-partner-edit-status-badge tbp-partner-edit-status-{{ $import->status === 'imported' ? 'success' : 'failed' }}">{{ ucfirst($import->status) }}</span></td>
                                                    <td>{{ $import->records_count ?? '—' }}</td>
                                                    <td>{{ $import->created_at->diffForHumans() }}</td>
                                                    <td>
                                                        <button type="button" class="tbp-partner-edit-icon-btn tbp-partner-edit-import-view" data-import-id="{{ $import->id }}" title="View"><i class="bi bi-eye"></i></button>
                                                        <button type="button" class="tbp-partner-edit-icon-btn tbp-partner-edit-icon-btn-danger tbp-partner-edit-import-delete" data-import-id="{{ $import->id }}" title="Delete"><i class="bi bi-trash"></i></button>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr id="importTableEmptyRow"><td colspan="7" class="tbp-partner-edit-import-empty">No data imported yet. Upload a file above to get started.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                @endif
                            </div>
                        </div>
                    </section>
                    @endif

                    <!-- ===== STEP: REVIEW & SAVE ===== -->
                    <section class="tbp-partner-edit-step-content tbp-partner-edit-review" data-step-panel="review" role="tabpanel">
                        <div class="tbp-partner-edit-card">
                            <div class="tbp-partner-edit-card-head">
                                <div class="tbp-partner-edit-card-badge">{{ $stepList['review']['num'] }}</div>
                                <div class="tbp-partner-edit-card-icon"><i class="bi bi-check-circle"></i></div>
                                <div>
                                    <div class="tbp-partner-edit-card-title">Review &amp; Save</div>
                                    <div class="tbp-partner-edit-card-desc">Review the configuration below, then save the partner.</div>
                                </div>
                            </div>
                            <div class="tbp-partner-edit-card-body">
                                <div class="tbp-partner-edit-review-grid">
                                    <div class="tbp-partner-edit-review-block">
                                        <div class="tbp-partner-edit-review-block-title">Basic Information</div>
                                        <div class="tbp-partner-edit-review-row"><span>Company</span><span>{{ $partner->company_name }}</span></div>
                                        <div class="tbp-partner-edit-review-row"><span>Module</span><span>{{ optional($partner->module)->name ?? '—' }}</span></div>
                                        <div class="tbp-partner-edit-review-row"><span>Status</span><span data-sync="status">{{ ucfirst(old('status', $partner->status)) }}</span></div>
                                    </div>
                                    @if(!$isManual)
                                    <div class="tbp-partner-edit-review-block">
                                        <div class="tbp-partner-edit-review-block-title">API Credentials</div>
                                        <div class="tbp-partner-edit-review-row"><span>Configured</span><span data-sync="credcount">{{ $credentialCount }} of 6</span></div>
                                        <div class="tbp-partner-edit-review-row"><span>Last test result</span><span data-sync="apitest">{{ $apiStatus ? ucfirst($apiStatus) : 'Not tested' }}</span></div>
                                        <div class="tbp-partner-edit-review-row"><span>Development mode</span><span data-sync="devmode">{{ $partner->development_mode ? 'Enabled' : 'Disabled' }}</span></div>
                                    </div>
                                    @endif
                                    <div class="tbp-partner-edit-review-block">
                                        <div class="tbp-partner-edit-review-block-title">Financial Information</div>
                                        @foreach($financeItems as $item)
                                            @php $v = old($item['field'], $partner->getAttribute($item['field'])); @endphp
                                            <div class="tbp-partner-edit-review-row"><span>{{ $item['label'] }}</span><span data-sync-finance="{{ $item['field'] }}">{{ $v !== null && $v !== '' ? number_format((float) $v, 2) : 'Not configured' }}</span></div>
                                        @endforeach
                                    </div>
                                    @if($showImportTab)
                                    <div class="tbp-partner-edit-review-block">
                                        <div class="tbp-partner-edit-review-block-title">Import Data</div>
                                        <div class="tbp-partner-edit-review-row"><span>Files imported</span><span>{{ $imports->where('status', 'imported')->count() }}</span></div>
                                    </div>
                                    @endif
                                </div>
                                <div class="tbp-partner-edit-step-actions">
                                    <span class="tbp-partner-edit-hint">Partner availability:</span>
                                    @if($partner->status == 'active')
                                        <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-warning" onclick="suspendPartner({{ $partner->id }})"><i class="bi bi-pause-circle"></i> Suspend Partner</button>
                                    @else
                                        <button type="button" class="tbp-partner-edit-btn tbp-partner-edit-btn-success" onclick="activatePartner({{ $partner->id }})"><i class="bi bi-play-circle"></i> Activate Partner</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>


            <!-- ===== FOOTER ACTION BAR ===== -->
            <div class="tbp-partner-edit-footer">
                <a href="{{ route('admin.travel-partners.index') }}" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline">
                    <i class="bi bi-arrow-counterclockwise"></i> Cancel
                </a>
                <div class="tbp-partner-edit-footer-right">
                    <button type="button" id="tbpBackBtn" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline"><i class="bi bi-arrow-left"></i> Back</button>
                    <button type="submit" id="saveAsDraftBtn" class="tbp-partner-edit-btn tbp-partner-edit-btn-outline" style="color:var(--primary-color);">
                        <i class="bi bi-floppy"></i> Save as Draft
                    </button>
                    <button type="button" id="tbpContinueBtn" class="tbp-partner-edit-btn tbp-partner-edit-btn-primary">
                        <span id="tbpContinueLabel">Continue</span> <i class="bi bi-arrow-right"></i>
                    </button>
                    <button type="submit" id="tbpSaveBtn" class="tbp-partner-edit-btn tbp-partner-edit-btn-primary">
                        <i class="bi bi-check-circle"></i> Update Partner
                    </button>
                </div>
            </div>
                </div>

                <!-- ===== SIDEBAR ===== -->
                <aside class="tbp-partner-edit-sidebar">
                    <div class="tbp-partner-edit-ov-card">
                        <div class="tbp-partner-edit-ov-title">Partner Overview</div>
                        <div class="tbp-partner-edit-ov-header">
                            <div class="tbp-partner-edit-ov-avatar @if($partnerLogo) has-logo @endif">@if($partnerLogo)<img src="{{ $partnerLogo }}" alt="{{ $partner->company_name }}">@else{{ strtoupper(substr($partner->company_name, 0, 1)) }}@endif</div>
                            <div>
                                <div class="tbp-partner-edit-ov-name">{{ $partner->company_name }}</div>
                                <div class="tbp-partner-edit-ov-slug">{{ $supplierSlug }}</div>
                            </div>
                        </div>
                        <div class="tbp-partner-edit-ov-row"><span class="tbp-partner-edit-ov-label"><i class="bi bi-diagram-3"></i> Module</span><span class="tbp-partner-edit-ov-value">{{ optional($partner->module)->name ?? '—' }}</span></div>
                        <div class="tbp-partner-edit-ov-row"><span class="tbp-partner-edit-ov-label"><i class="bi bi-circle-half"></i> Status</span><span class="tbp-partner-edit-ov-value" data-sync="status">{{ ucfirst(old('status', $partner->status)) }}</span></div>
                        @if(!$isManual)
                        <div class="tbp-partner-edit-ov-row"><span class="tbp-partner-edit-ov-label"><i class="bi bi-key"></i> API Credentials</span><span class="tbp-partner-edit-ov-value {{ $hasAnyCredential ? 'is-positive' : 'is-muted' }}" data-sync="credstate">{{ $hasAnyCredential ? 'Configured' : 'Not configured' }}</span></div>
                        @endif
                        <div class="tbp-partner-edit-ov-row"><span class="tbp-partner-edit-ov-label"><i class="bi bi-percent"></i> Commission</span><span class="tbp-partner-edit-ov-value {{ $hasCommission ? 'is-positive' : 'is-muted' }}" data-sync="commission">{{ $hasCommission ? number_format($partner->commission_rate, 2) . ($partner->commission_type === 'fixed' ? '' : '%') : 'Not configured' }}</span></div>
                        @if(!$isManual)
                        <div class="tbp-partner-edit-ov-row"><span class="tbp-partner-edit-ov-label"><i class="bi bi-code-slash"></i> Development Mode</span><span class="tbp-partner-edit-ov-value {{ $partner->development_mode ? 'is-positive' : 'is-muted' }}" data-sync="devmode">{{ $partner->development_mode ? 'Enabled' : 'Disabled' }}</span></div>
                        @endif
                    </div>
                </aside>
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
                <div class="modal-body" id="importViewModalBody"><div class="tbp-partner-edit-hint">Loading…</div></div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            const BASE_URL = "{{ url('') }}";
            const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
            const STEP_KEYS = @json($stepKeys);
            const STEP_TITLES = @json(array_map(fn($s) => $s['title'], $stepList));
            const IS_MANUAL = @json($isManual);
            let apiVerified = @json($apiStatus === 'success');

            function suspendPartner(partnerId) {
                if (!confirm('Are you sure you want to suspend this partner?')) return;
                fetch(`${BASE_URL}/admin/travel-partners/${partnerId}/suspend`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => { if (d.success) window.location.reload(); });
            }
            function activatePartner(partnerId) {
                if (!confirm('Are you sure you want to activate this partner?')) return;
                fetch(`${BASE_URL}/admin/travel-partners/${partnerId}/activate`, { method: 'PATCH', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => { if (d.success) window.location.reload(); });
            }

            function escapeHtml(str) { const d = document.createElement('div'); d.textContent = str ?? ''; return d.innerHTML; }

            // ===== TABS — single activeStep; only that step's panel is displayed =====
            const stepButtons = Array.from(document.querySelectorAll('.tbp-partner-edit-step'));
            const stepPanels = Array.from(document.querySelectorAll('.tbp-partner-edit-step-content'));
            const backBtn = document.getElementById('tbpBackBtn');
            const continueBtn = document.getElementById('tbpContinueBtn');
            const continueLabel = document.getElementById('tbpContinueLabel');
            const saveBtn = document.getElementById('tbpSaveBtn');
            let activeStep = null;

            function setActiveStep(key) {
                if (!STEP_KEYS.includes(key)) return;
                activeStep = key;
                const idx = STEP_KEYS.indexOf(key);
                stepButtons.forEach(b => {
                    const on = b.dataset.step === key;
                    b.classList.toggle('tbp-partner-edit-step-active', on);
                    b.setAttribute('aria-selected', on ? 'true' : 'false');
                });
                stepPanels.forEach(p => p.classList.toggle('is-active', p.dataset.stepPanel === key));

                const isLast = idx === STEP_KEYS.length - 1;
                backBtn.style.display = idx === 0 ? 'none' : '';
                continueBtn.style.display = isLast ? 'none' : '';
                saveBtn.style.display = isLast ? '' : 'none';
                if (!isLast) continueLabel.textContent = 'Continue to ' + STEP_TITLES[STEP_KEYS[idx + 1]];
                refreshContinueState();
                if (key === 'review') syncSummary();
            }

            function showStepError(key, message) {
                const box = document.querySelector(`[data-step-error="${key}"]`);
                if (!box) return;
                box.textContent = message || '';
                box.classList.toggle('is-visible', !!message);
            }

            // Validates the inputs inside the current panel only.
            function validateStep(key) {
                const panel = stepPanels.find(p => p.dataset.stepPanel === key);
                if (!panel) return true;
                const fields = Array.from(panel.querySelectorAll('input, select')).filter(el => !el.disabled && el.willValidate);
                const invalid = fields.find(el => !el.checkValidity());
                if (invalid) {
                    invalid.classList.add('is-invalid');
                    invalid.reportValidity();
                    showStepError(key, 'Please fix the highlighted field before continuing.');
                    return false;
                }
                if (key === 'credentials' && !apiVerified) {
                    showStepError(key, 'API credentials have not been verified. Run "Test Credentials" successfully before continuing.');
                    return false;
                }
                showStepError(key, '');
                return true;
            }

            function refreshContinueState() {
                const blocked = activeStep === 'credentials' && !apiVerified;
                continueBtn.disabled = blocked;
                continueBtn.title = blocked ? 'Test and verify API credentials first' : '';
            }

            stepButtons.forEach(b => b.addEventListener('click', () => setActiveStep(b.dataset.step)));
            continueBtn.addEventListener('click', () => {
                if (!validateStep(activeStep)) return;
                setActiveStep(STEP_KEYS[STEP_KEYS.indexOf(activeStep) + 1]);
            });
            backBtn.addEventListener('click', () => setActiveStep(STEP_KEYS[STEP_KEYS.indexOf(activeStep) - 1]));
            document.querySelectorAll('.tbp-partner-edit-step-content input').forEach(el => el.addEventListener('input', () => el.classList.remove('is-invalid')));

            // ===== STATUS (visible select mirrors into the hidden posted input) =====
            const statusSelect = document.getElementById('statusSelect');
            const statusHiddenInput = document.getElementById('statusHiddenInput');
            const statusDot = document.getElementById('statusDot');
            function setSync(name, text, positive) {
                document.querySelectorAll(`[data-sync="${name}"]`).forEach(el => {
                    el.textContent = text;
                    if (positive !== undefined && el.classList.contains('tbp-partner-edit-ov-value')) {
                        el.classList.toggle('is-positive', positive);
                        el.classList.toggle('is-muted', !positive);
                    }
                });
            }
            function syncStatus() {
                const val = statusSelect.value;
                statusHiddenInput.value = val;
                statusDot.classList.toggle('is-active', val === 'active');
                setSync('status', val.charAt(0).toUpperCase() + val.slice(1));
            }
            statusSelect.addEventListener('change', syncStatus);
            syncStatus();

            // "Save as Draft" posts the form as-is but with status=pending.
            document.getElementById('saveAsDraftBtn').addEventListener('click', () => { statusHiddenInput.value = 'pending'; });
            saveBtn.addEventListener('click', () => { statusHiddenInput.value = statusSelect.value; });

            // ===== API CREDENTIAL FIELDS =====
            document.querySelectorAll('.tbp-partner-edit-cred-toggle').forEach(btn => btn.addEventListener('click', () => {
                const input = btn.closest('.tbp-partner-edit-input-wrap').querySelector('input');
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.innerHTML = `<i class="bi ${show ? 'bi-eye-slash' : 'bi-eye'}"></i>`;
            }));
            const credInputs = Array.from(document.querySelectorAll('.tbp-partner-edit-cred-input'));
            const credChanged = input => input.value.trim() !== '' && input.value !== (input.dataset.originalValue || '');
            // Typing over a saved (encrypted) value replaces it entirely.
            credInputs.forEach(input => input.addEventListener('focus', () => { if (input.value && !credChanged(input)) input.select(); }));
            // Unchanged credentials are not submitted, so the stored value is kept as-is.
            document.getElementById('travelPartnerForm').addEventListener('submit', () => {
                credInputs.forEach(input => { if (!credChanged(input)) input.disabled = true; });
            });

            // ===== CREDENTIAL TEST — terminal console =====
            const testCredentialsBtn = document.getElementById('testCredentialsBtn');
            const terminalBody = document.getElementById('apiTestTerminalBody');
            const supplierName = @json($partner->company_name);
            const supplierSlug = @json($supplierSlug);
            const credentialCheckEndpoint = @json($credentialCheckEndpoint);
            let credentialTestInFlight = false;

            function appendTerminalLine(text, type) {
                if (!terminalBody) return null;
                terminalBody.querySelectorAll('.tbp-partner-edit-terminal-cursor').forEach(c => c.remove());
                const line = document.createElement('div');
                line.className = 'tbp-partner-edit-terminal-line tbp-partner-edit-terminal-line--' + type;
                line.innerHTML = `<span class="tbp-partner-edit-terminal-ts">[${new Date().toTimeString().slice(0, 8)}]</span>${escapeHtml(text)}`;
                terminalBody.appendChild(line);
                terminalBody.scrollTop = terminalBody.scrollHeight;
                return line;
            }
            function appendCursorTo(line) {
                if (!line) return;
                const c = document.createElement('span');
                c.className = 'tbp-partner-edit-terminal-cursor';
                line.appendChild(c);
            }
            function markStep(key, status) {
                const b = stepButtons.find(x => x.dataset.step === key);
                if (!b) return;
                b.classList.remove('tbp-partner-edit-step-complete', 'tbp-partner-edit-step-failed');
                if (status === 'success') b.classList.add('tbp-partner-edit-step-complete');
                if (status === 'failed') b.classList.add('tbp-partner-edit-step-failed');
            }
            function setVerified(ok) {
                apiVerified = ok;
                setSync('apitest', ok ? 'Success' : 'Failed');
                markStep('credentials', ok ? 'success' : 'failed');
                if (ok) showStepError('credentials', '');
                refreshContinueState();
            }

            document.getElementById('clearTerminalBtn')?.addEventListener('click', () => {
                terminalBody.innerHTML = '';
                appendCursorTo(appendTerminalLine(`Console cleared. Waiting to run credential test for ${supplierName}.`, 'muted'));
            });

            testCredentialsBtn?.addEventListener('click', () => {
                if (credentialTestInFlight) return;
                const payload = {};
                for (let i = 1; i <= 6; i++) {
                    const input = document.querySelector(`input[name="api_credential_${i}"]`);
                    if (input && credChanged(input)) payload[`api_credential_${i}`] = input.value.trim();
                }
                credentialTestInFlight = true;
                testCredentialsBtn.disabled = true;
                appendTerminalLine(`$ test-credentials --supplier=${supplierSlug}`, 'muted');
                appendCursorTo(appendTerminalLine(`→ Connecting to ${supplierName} credential-check endpoint…`, 'info'));

                fetch(credentialCheckEndpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify(payload) })
                    .then(async r => {
                        let data = null;
                        try { data = await r.json(); } catch (e) {}
                        if (!data) throw new Error(r.status === 404 ? `Credential testing is not available for "${supplierName}" yet.` : `Unexpected response (HTTP ${r.status}).`);
                        return data;
                    })
                    .then(data => {
                        const success = !!data.success;
                        if (data.connection) appendTerminalLine(`${success ? '✓' : '✗'} Connection: ${data.connection}`, success ? 'success' : 'error');
                        if (data.response_time != null) appendTerminalLine(`→ Response time: ${data.response_time}ms`, 'info');
                        if (!success && data.error_type) appendTerminalLine(`✗ Error type: ${data.error_type}`, 'error');
                        const msg = success ? (data.message || 'Connected successfully.') : (data.error_detail || data.message || 'Verification failed.');
                        appendTerminalLine(`${success ? '✓' : '✗'} ${msg}`, success ? 'success' : 'error')?.classList.add('tbp-partner-edit-terminal-strong');
                        appendCursorTo(appendTerminalLine(success
                            ? '✓ API credentials verified. You may proceed to the next step.'
                            : '✗ API credentials are invalid. Please enter the correct credentials before proceeding to the next step.', success ? 'success' : 'error'));
                        setVerified(success);
                        fetch(`${BASE_URL}/admin/travel-partners/{{ $partner->id }}/test-api`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                            body: JSON.stringify({ success, message: msg })
                        }).catch(() => {});
                    })
                    .catch(err => {
                        appendTerminalLine(`✗ ${err.message}`, 'error');
                        appendCursorTo(appendTerminalLine('✗ API credentials are invalid. Please enter the correct credentials before proceeding to the next step.', 'error'));
                        setVerified(false);
                    })
                    .finally(() => { credentialTestInFlight = false; testCredentialsBtn.disabled = false; });
            });

            // ===== FINANCIAL — "Percentage" values are capped at 100 =====
            document.querySelectorAll('.tbp-partner-edit-pricing-type').forEach(sel => {
                const input = document.getElementById(sel.dataset.valueTarget);
                sel.addEventListener('change', () => {
                    if (sel.value === 'percentage') input.setAttribute('max', '100'); else input.removeAttribute('max');
                    syncSummary();
                });
            });
            document.querySelectorAll('.tbp-partner-edit-finance-input, .tbp-partner-edit-cred-input').forEach(el => el.addEventListener('input', syncSummary));

            // ===== DEVELOPMENT MODE =====
            const devModeSwitch = document.getElementById('developmentModeSwitch');
            const devBadge = document.getElementById('devModeSummaryBadge');
            devModeSwitch?.addEventListener('change', () => {
                const on = devModeSwitch.checked;
                devBadge.textContent = on ? 'Sandbox enabled' : 'Sandbox disabled';
                devBadge.classList.toggle('is-on', on);
                devBadge.classList.toggle('is-off', !on);
                setSync('devmode', on ? 'Enabled' : 'Disabled', on);
            });

            // ===== LIVE SUMMARY (sidebar + Review step) =====
            function syncSummary() {
                let filled = 0;
                for (let i = 1; i <= 6; i++) {
                    const input = document.querySelector(`input[name="api_credential_${i}"]`);
                    if (input && input.value.trim() !== '') filled++;
                }
                if (!IS_MANUAL) {
                    setSync('credcount', `${filled} of 6`);
                    setSync('credstate', filled > 0 ? 'Configured' : 'Not configured', filled > 0);
                }
                document.querySelectorAll('[data-sync-finance]').forEach(el => {
                    const v = parseFloat(document.getElementById(el.dataset.syncFinance)?.value);
                    el.textContent = isNaN(v) ? 'Not configured' : v.toFixed(2);
                });
                const c = parseFloat(document.getElementById('commission_rate')?.value);
                const pct = document.getElementById('commission_type')?.value !== 'fixed';
                setSync('commission', !isNaN(c) && c > 0 ? `${c.toFixed(2)}${pct ? '%' : ''}` : 'Not configured', !isNaN(c) && c > 0);
            }

            // ===== IMPORT DATA =====
            const importFileInput = document.getElementById('importFileInput');
            const importFileName = document.getElementById('importFileName');
            const importTableBody = document.getElementById('importTableBody');
            const importResult = document.getElementById('importResult');
            const importContentBtn = document.getElementById('importContentBtn');
            importFileInput?.addEventListener('change', function () { importFileName.textContent = this.files.length ? this.files[0].name : 'No file chosen'; });

            function setInlineResult(el, ok, message) {
                el.className = 'tbp-partner-edit-inline-result ' + (ok ? 'tbp-partner-edit-inline-success' : 'tbp-partner-edit-inline-failed');
                el.innerHTML = `<i class="bi ${ok ? 'bi-check-circle-fill' : 'bi-x-circle-fill'}"></i> ${escapeHtml(message)}`;
            }
            function addImportRow(imp) {
                document.getElementById('importTableEmptyRow')?.remove();
                const row = document.createElement('tr');
                row.dataset.importId = imp.id;
                row.innerHTML = `
                    <td>${escapeHtml(imp.original_filename)}</td>
                    <td>${escapeHtml(imp.import_type.charAt(0).toUpperCase() + imp.import_type.slice(1))}</td>
                    <td>${escapeHtml(imp.file_format.toUpperCase())}</td>
                    <td><span class="tbp-partner-edit-status-badge ${imp.status === 'imported' ? 'tbp-partner-edit-status-success' : 'tbp-partner-edit-status-failed'}">${escapeHtml(imp.status.charAt(0).toUpperCase() + imp.status.slice(1))}</span></td>
                    <td>${imp.records_count ?? '—'}</td>
                    <td>${escapeHtml(imp.created_at_human)}</td>
                    <td>
                        <button type="button" class="tbp-partner-edit-icon-btn tbp-partner-edit-import-view" data-import-id="${imp.id}" title="View"><i class="bi bi-eye"></i></button>
                        <button type="button" class="tbp-partner-edit-icon-btn tbp-partner-edit-icon-btn-danger tbp-partner-edit-import-delete" data-import-id="${imp.id}" title="Delete"><i class="bi bi-trash"></i></button>
                    </td>`;
                importTableBody.prepend(row);
                markStep('import', 'success');
            }
            importContentBtn?.addEventListener('click', () => {
                const importType = document.getElementById('importTypeSelect').value;
                const fileFormat = document.getElementById('fileFormatSelect').value;
                const file = importFileInput.files[0];
                if (!importType || !fileFormat || !file) { setInlineResult(importResult, false, 'Select an import type, file format, and file before importing.'); return; }
                const fd = new FormData();
                fd.append('import_type', importType);
                fd.append('file_format', fileFormat);
                fd.append('file', file);
                importContentBtn.disabled = true;
                importResult.className = 'tbp-partner-edit-inline-result tbp-partner-edit-inline-testing';
                importResult.innerHTML = '<i class="bi bi-arrow-repeat tbp-partner-edit-spin"></i> Uploading…';
                fetch(`${BASE_URL}/admin/travel-partners/{{ $partner->id }}/import-content`, { method: 'POST', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, body: fd })
                    .then(r => r.json())
                    .then(data => {
                        setInlineResult(importResult, data.success, data.message);
                        if (data.import) { addImportRow(data.import); importFileInput.value = ''; importFileName.textContent = 'No file chosen'; }
                    })
                    .catch(err => setInlineResult(importResult, false, err.message))
                    .finally(() => { importContentBtn.disabled = false; });
            });

            const importViewModalEl = document.getElementById('importViewModal');
            const importViewModalBody = document.getElementById('importViewModalBody');
            const importViewModal = importViewModalEl && window.bootstrap ? new bootstrap.Modal(importViewModalEl) : null;
            function renderPreviewTable(rows) {
                if (!rows || !rows.length) return '<div class="tbp-partner-edit-hint">No preview rows available.</div>';
                const cols = Object.keys(rows[0]);
                return '<div class="tbp-partner-edit-import-table-wrap"><table class="tbp-partner-edit-import-table"><thead><tr>'
                    + cols.map(c => `<th>${escapeHtml(c)}</th>`).join('') + '</tr></thead><tbody>'
                    + rows.map(r => '<tr>' + cols.map(c => `<td>${escapeHtml(r[c])}</td>`).join('') + '</tr>').join('')
                    + '</tbody></table></div>';
            }
            document.body.addEventListener('click', e => {
                const viewBtn = e.target.closest('.tbp-partner-edit-import-view');
                if (viewBtn) {
                    importViewModalBody.innerHTML = '<div class="tbp-partner-edit-hint">Loading…</div>';
                    importViewModal?.show();
                    fetch(`${BASE_URL}/admin/travel-partners/{{ $partner->id }}/imports/${viewBtn.dataset.importId}`, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(({ import: imp }) => {
                            importViewModalBody.innerHTML = `<div class="tbp-partner-edit-import-meta">
                                <div><strong>File:</strong> ${escapeHtml(imp.original_filename)}</div>
                                <div><strong>Type:</strong> ${escapeHtml(imp.import_type)} &middot; <strong>Format:</strong> ${escapeHtml(imp.file_format.toUpperCase())}</div>
                                <div><strong>Status:</strong> ${escapeHtml(imp.status)} &middot; <strong>Records:</strong> ${imp.records_count ?? '—'}</div>
                                <div><strong>Imported:</strong> ${escapeHtml(imp.created_at)}</div>
                                ${imp.error_message ? `<div class="tbp-partner-edit-error-text">${escapeHtml(imp.error_message)}</div>` : ''}
                            </div>` + renderPreviewTable(imp.preview_data);
                        })
                        .catch(err => { importViewModalBody.innerHTML = `<div class="tbp-partner-edit-error-text">Failed to load preview: ${escapeHtml(err.message)}</div>`; });
                    return;
                }
                const deleteBtn = e.target.closest('.tbp-partner-edit-import-delete');
                if (deleteBtn) {
                    if (!confirm('Delete this imported file? This cannot be undone.')) return;
                    const row = deleteBtn.closest('tr');
                    fetch(`${BASE_URL}/admin/travel-partners/{{ $partner->id }}/imports/${deleteBtn.dataset.importId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF_TOKEN, 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } })
                        .then(r => r.json())
                        .then(data => {
                            if (!data.success) return;
                            row.remove();
                            if (!importTableBody.querySelector('tr')) {
                                importTableBody.innerHTML = '<tr id="importTableEmptyRow"><td colspan="7" class="tbp-partner-edit-import-empty">No data imported yet. Upload a file above to get started.</td></tr>';
                                markStep('import', null);
                            }
                        });
                }
            });

            syncSummary();
            setActiveStep(@json($defaultStepKey));
        </script>
    @endpush
@endsection
