<!-- resources/views/home.blade.php -->
@extends('layouts.app')

@section('content')
<style type="text/css">
    /* Hero Container Styling */
    .hero-section {
        background-image: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('/img/insurance_cover.jpg');
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
        padding: 100px 0; /* Adds breathing room around the logo */
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Logo Styling and Animation */
    .logo-animate {
        max-width: 350px;
        height: auto;
        border: 5px solid rgba(255, 255, 255, 0.3); /* Subtle border for a professional look */
        animation: spin 20s linear infinite; /* Slowed down for a more subtle professional feel */
    }

    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .logo-animate {
            max-width: 200px;
        }
        .hero-section {
            min-height: 350px;
        }
    }
</style>


<div class="container-fluid hero-section text-center">
    <div class="row">
        <div class="col-sm-12">
            <img src="/img/profstand_logo.png" 
                 class="img-responsive img-circle logo-animate" 
                 alt="RIB Professional Insurance Broking Logo">
            
            <h1 style="color: white; margin-top: 20px; font-weight: bold; text-shadow: 2px 2px 4px rgba(0,0,0,0.5);">
                Profstand Technologies Ltd
            </h1>
        </div>
    </div>
</div>
@endsection