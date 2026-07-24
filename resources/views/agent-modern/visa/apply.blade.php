{{-- Disclosure: mirrors agent/visa/apply.blade.php — dead/unreachable code,
     see agent-modern/visa/index.blade.php for full explanation. --}}
@extends('agent-modern.layouts.app')
@section('title', 'New Visa Application')

@section('content')
<div class="flex justify-between items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-passport"></i> New Visa Application</h4>
    <a href="{{ route('agent.visa.index') }}" class="ap-btn-outline">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('agent.visa.submit') }}" enctype="multipart/form-data">
    @csrf

    <div class="ap-card mb-3">
        <div class="ap-card-header"><h6 class="mb-0">Visa Information</h6></div>
        <div class="ap-card-body">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <div>
                    <label class="form-label">Visa Type *</label>
                    <select name="visa_type" class="form-select @error('visa_type') is-invalid @enderror" required>
                        <option value="">Select Visa Type</option>
                        <option value="tourist" {{ old('visa_type') === 'tourist' ? 'selected' : '' }}>Tourist</option>
                        <option value="business" {{ old('visa_type') === 'business' ? 'selected' : '' }}>Business</option>
                        <option value="visit" {{ old('visa_type') === 'visit' ? 'selected' : '' }}>Visit</option>
                        <option value="umrah" {{ old('visa_type') === 'umrah' ? 'selected' : '' }}>Umrah</option>
                        <option value="work" {{ old('visa_type') === 'work' ? 'selected' : '' }}>Work</option>
                    </select>
                    @error('visa_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Visa Plan *</label>
                    <select name="visa_plan" class="form-select @error('visa_plan') is-invalid @enderror" required>
                        <option value="">Select Plan</option>
                        <option value="single" {{ old('visa_plan') === 'single' ? 'selected' : '' }}>Single Entry</option>
                        <option value="multiple" {{ old('visa_plan') === 'multiple' ? 'selected' : '' }}>Multiple Entry</option>
                        <option value="30days" {{ old('visa_plan') === '30days' ? 'selected' : '' }}>30 Days</option>
                        <option value="90days" {{ old('visa_plan') === '90days' ? 'selected' : '' }}>90 Days</option>
                    </select>
                    @error('visa_plan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="ap-card mb-3">
        <div class="ap-card-header"><h6 class="mb-0">Personal Information</h6></div>
        <div class="ap-card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="form-label">First Name *</label>
                    <input type="text" name="first_name" class="form-control @error('first_name') is-invalid @enderror" value="{{ old('first_name') }}" required>
                    @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Middle Name</label>
                    <input type="text" name="middle_name" class="form-control" value="{{ old('middle_name') }}">
                </div>
                <div>
                    <label class="form-label">Surname *</label>
                    <input type="text" name="surname" class="form-control @error('surname') is-invalid @enderror" value="{{ old('surname') }}" required>
                    @error('surname') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Father's Name *</label>
                    <input type="text" name="father_name" class="form-control @error('father_name') is-invalid @enderror" value="{{ old('father_name') }}" required>
                    @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Mother's Name *</label>
                    <input type="text" name="mother_name" class="form-control @error('mother_name') is-invalid @enderror" value="{{ old('mother_name') }}" required>
                    @error('mother_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Place of Birth *</label>
                    <input type="text" name="place_birth" class="form-control @error('place_birth') is-invalid @enderror" value="{{ old('place_birth') }}" required>
                    @error('place_birth') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Gender *</label>
                    <select name="gender" class="form-select @error('gender') is-invalid @enderror" required>
                        <option value="">Select</option>
                        <option value="male" {{ old('gender') === 'male' ? 'selected' : '' }}>Male</option>
                        <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Female</option>
                    </select>
                    @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Marital Status *</label>
                    <select name="marital_status" class="form-select @error('marital_status') is-invalid @enderror" required>
                        <option value="">Select</option>
                        <option value="single" {{ old('marital_status') === 'single' ? 'selected' : '' }}>Single</option>
                        <option value="married" {{ old('marital_status') === 'married' ? 'selected' : '' }}>Married</option>
                        <option value="divorced" {{ old('marital_status') === 'divorced' ? 'selected' : '' }}>Divorced</option>
                        <option value="widowed" {{ old('marital_status') === 'widowed' ? 'selected' : '' }}>Widowed</option>
                    </select>
                    @error('marital_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Occupation *</label>
                    <input type="text" name="occupation" class="form-control @error('occupation') is-invalid @enderror" value="{{ old('occupation') }}" required>
                    @error('occupation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Religion *</label>
                    <input type="text" name="religion" class="form-control @error('religion') is-invalid @enderror" value="{{ old('religion') }}" required>
                    @error('religion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Nationality *</label>
                    <select name="nationality" class="form-select @error('nationality') is-invalid @enderror" required>
                        <option value="">Select</option>
                        @foreach($countries as $c)
                        <option value="{{ $c->country_code }}" {{ old('nationality') === $c->country_code ? 'selected' : '' }}>{{ $c->country }}</option>
                        @endforeach
                    </select>
                    @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="ap-card mb-3">
        <div class="ap-card-header"><h6 class="mb-0">Passport Information</h6></div>
        <div class="ap-card-body">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                <div>
                    <label class="form-label">Passport Number *</label>
                    <input type="text" name="passport_no" class="form-control @error('passport_no') is-invalid @enderror" value="{{ old('passport_no') }}" required>
                    @error('passport_no') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Issue Date *</label>
                    <input type="date" name="passport_issue_date" class="form-control @error('passport_issue_date') is-invalid @enderror" value="{{ old('passport_issue_date') }}" required>
                    @error('passport_issue_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Expiry Date *</label>
                    <input type="date" name="passport_expiry_date" class="form-control @error('passport_expiry_date') is-invalid @enderror" value="{{ old('passport_expiry_date') }}" required>
                    @error('passport_expiry_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    <div class="ap-card mb-3">
        <div class="ap-card-header"><h6 class="mb-0">Documents</h6></div>
        <div class="ap-card-body">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="form-label">Passport Front *</label>
                    <input type="file" name="passport_front" class="form-control @error('passport_front') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                    @error('passport_front') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Passport Back *</label>
                    <input type="file" name="passport_back" class="form-control @error('passport_back') is-invalid @enderror" accept=".jpg,.jpeg,.png,.pdf" required>
                    @error('passport_back') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Passport Photo *</label>
                    <input type="file" name="passport_photo" class="form-control @error('passport_photo') is-invalid @enderror" accept=".jpg,.jpeg,.png" required>
                    @error('passport_photo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Other Document</label>
                    <input type="file" name="other_document" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                </div>
            </div>
        </div>
    </div>

    <div class="ap-card mb-3">
        <div class="ap-card-body">
            <div class="form-check">
                <input type="checkbox" name="agreed_terms" id="agreedTerms" class="form-check-input @error('agreed_terms') is-invalid @enderror" required>
                <label class="form-check-label" for="agreedTerms">
                    I confirm all information provided is accurate and I agree to the terms and conditions.
                </label>
                @error('agreed_terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>
    </div>

    <button type="submit" class="ap-btn-success w-full py-3">
        <i class="bi bi-send"></i> Submit Visa Application
    </button>
</form>
@endsection
