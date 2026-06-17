@extends('layouts.app')

@section('content')
<div class="card custom-account-card">
    <div class="custom-account-header text-center">
        <h4 class="mb-0">Edit Account Details</h4>
    </div>
    <div class="card-body p-4 p-md-5">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form method="POST" action="{{ route('account.update') }}">
            @csrf
            
            <div class="section-divider text-uppercase">Personal Information</div>
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">First Name*</label>
                    <input type="text" class="form-control" name="first_name" value="{{ old('first_name', $user->first_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Middle Name</label>
                    <input type="text" class="form-control" name="middle_name" value="{{ old('middle_name', $user->middle_name) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Last Name*</label>
                    <input type="text" class="form-control" name="last_name" value="{{ old('last_name', $user->last_name) }}" required>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label small">Gender</label>
                    <select class="form-control" name="gender">
                        <option value="Male" {{ $user->gender == 'Male' ? 'selected' : '' }}>Male</option>
                        <option value="Female" {{ $user->gender == 'Female' ? 'selected' : '' }}>Female</option>
                        <option value="Other" {{ $user->gender == 'Other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Date of Birth*</label>
                    <input type="date" class="form-control" name="date_of_birth" value="{{ old('date_of_birth', $user->date_of_birth) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label small">Nationality</label>
                    <input type="text" class="form-control" name="nationality" value="{{ old('nationality', $user->nationality) }}">
                </div>
            </div>

            <div class="section-divider text-uppercase mt-4">Security</div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label small">New Password</label>
                    <input type="password" class="form-control" name="passwd">
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Confirm Password</label>
                    <input type="password" class="form-control" name="passwd_confirmation">
                </div>

                <div class="col-12 text-center mt-4">
                    <button type="submit" class="btn btn-primary btn-update text-white">Save Changes</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection