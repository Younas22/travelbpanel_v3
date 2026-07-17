@extends('common.layout')
@section('content')

    <div class="flight-container">
        <!-- Form Header -->
        <div class="page-header">
            <h1>{{t('visa.pageTitle')}}</h1>
            <p>{{t('visa.pageSubtitle')}} <strong>*</strong></p>
        </div>

        <!-- Form Wrapper -->
        <form action="{{ route('visa.store') }}" method="POST" enctype="multipart/form-data" class="form-wrapper- contact-info">
            @csrf

            <!-- Visa Type Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-passport"></i> {{t('visa.visaInformation')}}
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.visaType')}}</label>
                        <select class="form-select" name="visa_type" required>
                            <option value="">{{t('visa.selectVisaType')}}</option>
                            <option value="tourist">{{t('visa.touristVisa')}}</option>
                            <option value="business">{{t('visa.businessVisa')}}</option>
                            <option value="student">{{t('visa.studentVisa')}}</option>
                            <option value="work">{{t('visa.workVisa')}}</option>
                            <option value="family">{{t('visa.familyVisa')}}</option>
                            <option value="transit">{{t('visa.transitVisa')}}</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.visaPlan')}}</label>
                        <select class="form-select" name="visa_plan" required>
                            <option value="">{{t('visa.selectPlan')}}</option>
                            <option value="single_entry">{{t('visa.singleEntry')}}</option>
                            <option value="multiple_entry">{{t('visa.multipleEntry')}}</option>
                            <option value="30_days">{{t('visa.thirtyDays')}}</option>
                            <option value="60_days">{{t('visa.sixtyDays')}}</option>
                            <option value="90_days">{{t('visa.ninetyDays')}}</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Passenger Details Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-user"></i> {{t('visa.passengerDetails')}}
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.firstName')}}</label>
                        <input type="text" name="first_name" class="form-input" placeholder="{{t('visa.enterFirstName')}}" value="{{ old('first_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">{{t('visa.middleName')}}</label>
                        <input type="text" name="middle_name" class="form-input" placeholder="{{t('visa.enterMiddleName')}}" value="{{ old('middle_name') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.surname')}}</label>
                        <input type="text" name="surname" class="form-input" placeholder="{{t('visa.enterSurname')}}" value="{{ old('surname') }}" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.fatherName')}}</label>
                        <input type="text" name="father_name" class="form-input" placeholder="{{t('visa.enterFatherName')}}" value="{{ old('father_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.motherName')}}</label>
                        <input type="text" name="mother_name" class="form-input" placeholder="{{t('visa.enterMotherName')}}" value="{{ old('mother_name') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.placeOfBirth')}}</label>
                        <input type="text" name="place_birth" class="form-input" placeholder="{{t('visa.enterPlaceOfBirth')}}" value="{{ old('place_birth') }}" required>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.occupation')}}</label>
                        <input type="text" name="occupation" class="form-input" placeholder="{{t('visa.enterOccupation')}}" value="{{ old('occupation') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.religion')}}</label>
                        <input type="text" name="religion" class="form-input" placeholder="{{t('visa.enterReligion')}}" value="{{ old('religion') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.maritalStatus')}}</label>
                        <select class="form-select" name="marital_status" required>
                            <option value="">{{t('visa.selectMaritalStatus')}}</option>
                            <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>{{t('visa.married')}}</option>
                            <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>{{t('visa.unmarried')}}</option>
                            <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>{{t('visa.divorced')}}</option>
                            <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>{{t('visa.widowed')}}</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.nationality')}}</label>
                        <select class="form-select" name="nationality" required>
                            <option value="">{{t('visa.selectNationality')}}</option>
                            @foreach($countries as $country)
                                <option value="{{ strtolower($country->country_code) }}" {{ old('nationality') == strtolower($country->country_code) ? 'selected' : '' }}>
                                    {{ $country->country }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label required">{{t('visa.passportNumber')}}</label>
                        <input type="text" name="passport_no" class="form-input" placeholder="{{t('visa.enterPassportNumber')}}" value="{{ old('passport_no') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.gender')}}</label>
                        <div class="radio-group">
                            <div class="radio-item">
                                <input type="radio" id="male" name="gender" value="male" {{ old('gender') == 'male' ? 'checked' : '' }} required>
                                <label for="male">{{t('visa.male')}}</label>
                            </div>
                            <div class="radio-item">
                                <input type="radio" id="female" name="gender" value="female" {{ old('gender') == 'female' ? 'checked' : '' }}>
                                <label for="female">{{t('visa.female')}}</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-grid two-col">
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.passportIssueDate')}}</label>
                        <input type="date" name="passport_issue_date" class="form-input" value="{{ old('passport_issue_date') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.passportExpiryDate')}}</label>
                        <input type="date" name="passport_expiry_date" class="form-input" value="{{ old('passport_expiry_date') }}" required>
                    </div>
                </div>
            </div>

            <!-- Document Uploads Section -->
            <div class="form-section">
                <div class="section-title">
                    <i class="fas fa-file-upload"></i> {{t('visa.documentUploads')}}
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.passportFrontImage')}}</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="passport_front" id="passport-front" class="file-upload-input" accept="image/*,.pdf" required>
                            <label for="passport-front" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i> {{t('visa.clickToUpload')}}
                            </label>
                            <div class="file-size-hint">{{t('visa.supportedFormatsImage')}}</div>
                        </div>
                        @error('passport_front')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.passportBackImage')}}</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="passport_back" id="passport-back" class="file-upload-input" accept="image/*,.pdf" required>
                            <label for="passport-back" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i> {{t('visa.clickToUpload')}}
                            </label>
                            <div class="file-size-hint">{{t('visa.supportedFormatsImage')}}</div>
                        </div>
                        @error('passport_back')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label required">{{t('visa.passportSizePhoto')}}</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="passport_photo" id="passport-photo" class="file-upload-input" accept="image/*" required>
                            <label for="passport-photo" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i> {{t('visa.clickToUpload')}}
                            </label>
                            <div class="file-size-hint">{{t('visa.supportedFormatsPhoto')}}</div>
                        </div>
                        @error('passport_photo')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="form-grid full">
                    <div class="form-group">
                        <label class="form-label">{{t('visa.uploadAdditionalDocument')}}</label>
                        <div class="file-upload-wrapper">
                            <input type="file" name="other_document" id="additional-doc" class="file-upload-input" accept=".pdf,.doc,.docx,image/*">
                            <label for="additional-doc" class="file-upload-label">
                                <i class="fas fa-cloud-upload-alt"></i> {{t('visa.clickToUploadOptional')}}
                            </label>
                            <div class="file-size-hint">{{t('visa.supportedFormatsDocument')}}</div>
                        </div>
                        @error('other_document')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Agreement Section -->
            <div class="form-section">
                <div class="agreement-box">
                    <input type="checkbox" name="agreed_terms" id="agreement" value="on" required>
                    <label for="agreement" class="agreement-text">
                        {!!t('visa.agreementText')!!}
                    </label>
                </div>
                @error('agreed_terms')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <!-- Error Messages -->
            @if($errors->any())
                <div class="form-section">
                    <div class="alert alert-danger">
                        <ul style="margin: 0; padding-left: 20px;">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <!-- Button Group -->
            <div class="button-group">
                <button type="reset" class="btn btn-reset">
                    <i class="fas fa-redo"></i> {{t('visa.clearForm')}}
                </button>
                <button type="submit" class="btn btn-submit">
                    <i class="fas fa-check-circle"></i> {{t('visa.submitApplication')}}
                </button>
            </div>
        </form>
    </div>

    <style>
        .error-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 0.25rem;
            display: block;
        }
        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.25rem;
        }
        .alert-danger {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>

    <script>
        // File upload preview
        document.querySelectorAll('.file-upload-input').forEach(input => {
            input.addEventListener('change', function(e) {
                const fileName = e.target.files[0]?.name;
                const label = this.nextElementSibling;
                if (fileName) {
                    label.innerHTML = `<i class="fas fa-check-circle"></i> ${fileName}`;
                    label.style.color = '#28a745';
                }
            });
        });

        // Show loader on visa form submit
        document.querySelector('form[action="{{ route("visa.store") }}"]').addEventListener('submit', function(e) {
            // Show loader
            const loader = document.getElementById('pageLoader');
            if (loader) {
                loader.classList.remove('hidden');
            }
        });
    </script>
@endsection