@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

<style>
    .input-group > .form-floating { flex: 1 1 auto; width: 1%; }
    #togglePassword { 
        z-index: 5; 
        border: 1px solid #dee2e6 !important; 
        background-color: #f8f9fa; 
    }
</style>

<div class="container d-flex justify-content-center align-items-center vh-100">
    <div class="card shadow-lg border-0" style="max-width: 400px; width: 100%; border-radius: 1.25rem;">
        <div class="card-body p-5">
            <h3 class="fw-bold mb-4 text-center text-primary">Log In</h3>
            
            <form id="loginForm" method="POST" action="{{ route('login') }}">
                @csrf {{-- Essential Laravel Security Token --}}

                {{-- Display Laravel Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0 list-unstyled">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="form-floating mb-3">
                    <input type="email" class="form-control bg-light border-0 @error('email') is-invalid @enderror" 
                           id="email" name="email" placeholder="name@example.com" 
                           value="{{ old('email') }}" required autofocus>
                    <label for="email">Email Address</label>
                </div>
            
                <div class="input-group mb-3">
                    <div class="form-floating">
                        <input type="password" class="form-control bg-light border-0 @error('password') is-invalid @enderror" 
                               id="password" name="password" placeholder="Password" 
                               required style="border-top-right-radius: 0; border-bottom-right-radius: 0;">
                        <label for="password">Password</label>
                    </div>
                    <button class="btn btn-outline-secondary border-0" type="button" id="togglePassword" style="border-top-left-radius: 0; border-bottom-left-radius: 0;">
                        <i class="bi bi-eye" id="eyeIcon"></i>
                    </button>
                </div>

                <div class="d-flex justify-content-between mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="remember">Remember me</label>
                    </div>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none small">Forgot Password?</a>
                    @endif
                </div>

                <button class="btn btn-primary btn-lg w-100 fw-bold shadow-sm" type="submit" id="submitBtn">
                    <span id="btnText">Sign In</span>
                    <span id="btnSpinner" class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>

                <div class="text-center mt-4">
                    <p class="small text-muted mb-0">New here? 
                        <a href="{{ route('register') }}" class="fw-bold text-decoration-none">Create Account</a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Password Toggle
    document.querySelector('#togglePassword').addEventListener('click', function () {
        const passInput = document.querySelector('#password');
        const icon = document.querySelector('#eyeIcon');
        const type = passInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passInput.setAttribute('type', type);
        icon.classList.toggle('bi-eye');
        icon.classList.toggle('bi-eye-slash');
    });

    // Loading State
    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        btn.disabled = true;
        document.getElementById('btnText').innerText = 'Signing In...';
        document.getElementById('btnSpinner').classList.remove('d-none');
    });
</script>
@endsection