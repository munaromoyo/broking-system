@extends('layouts.app')

@section('content')
<div class="container d-flex justify-content-center align-items-center py-5">
    <div class="card shadow-lg border-0" style="max-width: 500px; width: 100%; border-radius: 1rem;">
        <div class="card-body p-5">
            <h3 class="fw-bold mb-4 text-center">Create an Account</h3>

            <form id="signupForm" method="POST" action="{{ route('register') }}">
                @csrf

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small mb-4">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Name Fields -->
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="first_name" name="first_name" placeholder="First Name" value="{{ old('first_name') }}" required>
                            <label for="first_name">First Name*</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="middle_name" name="middle_name" placeholder="Middle Name" value="{{ old('middle_name') }}">
                            <label for="middle_name">Middle Name</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="last_name" name="last_name" placeholder="Last Name" value="{{ old('last_name') }}" required>
                            <label for="last_name">Last Name*</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <input type="text" class="form-control" id="company" name="company" placeholder="Company Name" value="{{ old('company') }}" required>
                            <label for="company">Company Name*</label>
                        </div>
                    </div>
                </div>

                <!-- Email Address -->
                <div class="form-floating mb-3">
                    <input type="email" class="form-control" id="email" name="email" placeholder="Email Address" value="{{ old('email') }}" required>
                    <label for="email">Email Address*</label>
                </div>
                
                <!-- Password with Toggle -->
                <div class="input-group mb-3">
                    <div class="form-floating flex-grow-1">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Password" required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <label for="password">Password*</label>
                    </div>
                    <button class="btn btn-outline-secondary" type="button" id="togglePassword" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>

                <!-- Role Dropdown (Updated from 'user' to 'role') -->
                <div class="form-floating mb-4">
                    <select class="form-select" id="role" name="role" required>
                        <option value="">Select User Type</option>
                        @foreach (["Admin", "Agent", "Insurer", "Individual Client", "Corporate Client", "Regulator", "Employee", "Director"] as $type)
                            <option value="{{ $type }}" {{ old('role') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    <label for="role">User Type*</label>
                </div>

                <button class="btn btn-primary btn-lg w-100 fw-bold shadow-sm mb-4" type="submit" id="submitBtn">
                    <span id="btnText">Register Account</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
                         
                <div class="text-center">
                    <p class="small text-muted mb-0">Already have an account? 
                        <a href="{{ route('login') }}" class="fw-bold text-decoration-none">Log In Here</a>
                    </p>
                </div>
            </form>
         </div>
    </div>
</div>

<script>
    // Password Toggle
    document.querySelector('#togglePassword').addEventListener('click', function () {
        const password = document.querySelector('#password');
        const icon = document.querySelector('#eyeIcon');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });

    // Loading State
    const signupForm = document.getElementById('signupForm');
    signupForm.addEventListener('submit', function() {
        if (signupForm.checkValidity()) {
            document.getElementById('submitBtn').disabled = true;
            document.getElementById('btnText').innerText = 'Registering...';
            document.getElementById('btnSpinner').classList.remove('d-none');
        }
    });
</script>
@endsection