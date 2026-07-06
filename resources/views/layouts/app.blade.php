<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $pageTitle ?? config('app.name', 'Profstand') }}</title>
  
    <link rel="icon" type="image/jpg" href="{{ asset('img/profstand_logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ global_asset('prof_css/assets/dist/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root { --rib-red: #e20613; --rib-dark: #1e293b; }
        body { font-family: 'Inter', sans-serif; }
        .main-header { background: #ffffff; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
        .nav-link-custom { font-size: 14px; font-weight: 500; color: var(--rib-dark) !important; text-transform: uppercase; letter-spacing: 0.5px; padding: 0.5rem 1.2rem !important; position: relative; }
        .nav-link-custom::after { content: ''; position: absolute; width: 0; height: 2px; bottom: 0; left: 50%; background-color: var(--rib-red); transition: 0.3s; transform: translateX(-50%); }
        .nav-link-custom:hover::after { width: 60%; }
        .nav-link-custom:hover { color: var(--rib-red) !important; }
        .btn-signup-rib { background-color: var(--rib-red); border: none; color: white; font-weight: 600; padding: 10px 24px; border-radius: 6px; }
        
        /* Inline styling for header integrated menu button */
        .header-menu-btn {
            background: #0004FF;
            color: #ffffff;
            border: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 13px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-left: 15px;
            transition: background-color 0.2s;
        }
        .header-menu-btn:hover { background-color: #0002cc; color: white; }
    </style>
     @stack('styles')
</head>
<body>

<header class="main-header sticky-top py-2">
    <div class="container d-flex align-items-center justify-content-between">

        <div class="d-flex align-items-center">
            <a href="{{ url('/') }}" class="d-flex align-items-center text-decoration-none">
                <img src="{{ global_asset('img/profstand_logo.png') }}" alt="Profstand Logo" style="height: 50px;">
            </a>
            
            {{-- Cleaned Check: Only show Menu button if user is logged in, and NOT on Home or Dashboard --}}
            @if(Auth::check() && !request()->is('/') && !request()->is('dashboard'))
                <button class="header-menu-btn" onclick="toggleSidebar(true)">
                    <i class="fas fa-bars"></i> Menu
                </button>
            @endif
        </div>

        <nav>
            <ul class="nav">
                <li><a href="{{ url('/') }}" class="nav-link nav-link-custom">Home</a></li>
                <li><a href="#" class="nav-link nav-link-custom">Services</a></li>
                <li><a href="#" class="nav-link nav-link-custom">About</a></li>
            </ul>
        </nav>
      
        <div class="d-flex align-items-center">
            @auth
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-signup-rib">
                        <i class="fa-solid fa-right-from-bracket me-2"></i>Logout
                    </button>
                </form>
            @else
                @if (Route::has('login'))
                    <a href="{{ route('login') }}" class="btn btn-link text-dark text-decoration-none me-3">Sign In</a>
                @endif

                @if (Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-signup-rib">Get Started</a>
                @endif
            @endauth
        </div>
    </div>
</header>

{{-- Include the drawer menu markup --}}
@if(Auth::check() && !request()->is('/') && !request()->is('dashboard'))
    @include('inc.sidebar')
@endif

{{-- Full width container rules --}}
<main class="{{ (request()->is('/') || request()->is('dashboard')) ? '' : 'container mt-4' }}">
    @yield('content')
</main>

@include('inc.footer')

<script src="{{ global_asset('prof_css/assets/dist/js/bootstrap.bundle.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@stack('scripts')

</body>
</html>