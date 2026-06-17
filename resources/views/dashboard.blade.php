@extends('layouts.app')

@section('styles')
<style>
    /* Design & Alignment */
    body { font-family: 'Inter', sans-serif; background-color: #f4f7f9; color: #2d3436; }
    .top-navbar { 
        background: #fff; 
        border-bottom: 1px solid #e3e8ed; 
        padding: 0.6rem 0; 
        position: sticky; 
        top: 0; 
        z-index: 1000; 
    }
    .nav-link-top { 
        color: #636e72; 
        font-weight: 500; 
        font-size: 0.9rem; 
        padding: 0.6rem 1rem; 
        transition: 0.2s; 
        border-radius: 8px; 
        text-decoration: none; 
        display: flex; 
        align-items: center; 
        gap: 8px; 
    }
    .nav-link-top:hover, .nav-link-top.active { background: #f1f2f6; color: #1e293b; }
    
    .avatar-initials { 
        width: 38px; height: 38px; 
        background: #1e293b; color: #fff; 
        display: flex; align-items: center; justify-content: center; 
        border-radius: 8px; font-weight: 700; font-size: 0.8rem; 
    }
    
    .menu-card { border: none; border-radius: 16px; background: #fff; box-shadow: 0 4px 20px rgba(0,0,0,0.04); }
    .chart-box { position: relative; height: 350px; width: 100%; }
    
    /* Dropdown Styling */
    .dropdown-menu-custom { 
        border: none; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        border-radius: 12px; 
        padding: 10px; 
        min-width: 220px; 
    }
    .action-item:not(:last-child) { border-bottom: 1px solid #f1f2f6; margin-bottom: 15px; padding-bottom: 15px; }

.top-navbar {
    overflow: visible !important; /* Prevents clipping of dropdowns */
}

/* Ensure the dropdown appears above the charts */
.dropdown-menu {
    z-index: 1050 !important;
}

</style>
@endsection

@section('content')
<!-- Top Navigation Bar -->
<nav class="top-navbar mb-4">
    <div class="container-fluid px-lg-5 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <span class="fw-bold fs-4 text-dark me-3">{{ $user->company }}<span class="text-primary">.</span></span>
            <div class="d-none d-lg-flex gap-1">
                <a href="#" class="nav-link-top active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
                
                <!-- Modules Dropdown -->
                <div class="dropdown">
                    <button class="nav-link-top dropdown-toggle border-0 bg-transparent" 
                            type="button" 
                            id="modulesDropdown" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false">
                        <i class="fa-solid fa-layer-group"></i> Modules
                    </button>
                    <ul class="dropdown-menu dropdown-menu-custom shadow" aria-labelledby="modulesDropdown">
                       {{-- Admin Section --}}
                        @if(auth()->user()?->role == 'Admin')
                            <li>
                                <a class="dropdown-item text-danger fw-bold" href="{{ route('users.index') }}">
                                    <i class="fa-solid fa-users-gear me-2"></i> User Management
                                </a>
                            </li>
                            <li><hr class="dropdown-divider opacity-10"></li>
                        @endif

                        {{-- Finance Section (Accountant, Director, or Admin) --}}
                        @if(auth()->check() && in_array(auth()->user()->role, ['Accountant', 'Director', 'Admin']))
                            <li>
                                <a class="dropdown-item text-primary fw-bold" href="{{ route('finance.vouchers.show') }}">
                                    <i class="fa-solid fa-money-bill-transfer me-2"></i> Finance
                                </a>
                            </li>
                            <li><hr class="dropdown-divider opacity-10"></li>
                        @endif

                        {{-- General Links --}}
                        <li>
                            <a class="dropdown-item" href="#">
                                <i class="fa-solid fa-scale-balanced me-2"></i> Regulations
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="{{ route('insurance_broking.dashboard', ['action' => 'insurance_broking']) }}" id="ib">
                                <i class="fa-solid fa-handshake me-2"></i> Broking Services
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Profile Dropdown -->
        <div class="dropdown">
            <div class="d-flex align-items-center gap-3 dropdown-toggle" 
                 style="cursor: pointer;" 
                 id="userProfileDropdown"  
                 data-bs-toggle="dropdown" 
                 aria-expanded="false">
                <div class="text-end d-none d-sm-block">
                    <h6 class="fw-bold mb-0 text-dark small">{{ $user->first_name }}</h6>
                    <small class="text-muted" style="font-size: 0.7rem;">{{ $user->role }}</small>
                </div>
                <div class="avatar-initials">{{ $initials }}</div>
            </div>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-custom shadow mt-2" aria-labelledby="userProfileDropdown">
                
                {{-- Check if the current user's role equals 'admin' --}}
                @if(auth()->user()->role === 'Admin') 
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('admin.profile.edit') }}">
                            <i class="fa-solid fa-sliders me-2"></i> Tenant Settings
                        </a>
                    </li>
                @else
                    <li>
                        <a class="dropdown-item py-2" href="#">
                            <i class="fa-regular fa-user me-2"></i> My Profile
                        </a>
                    </li>
                @endif
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 text-danger">
                            <i class="fa-solid fa-power-off me-2"></i> Sign Out
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>


<div class="container-fluid px-lg-5">
    <!-- Row 1: Key Performance Charts -->
    <div class="row g-4 mb-4">
        <div class="col-xl-8">
            <div class="card menu-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-bold mb-0">Policy Distribution</h6></div>
                <div class="card-body"><div class="chart-box"><canvas id="policyDoughnut"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card menu-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-bold mb-0">Claims Status</h6></div>
                <div class="card-body"><div class="chart-box"><canvas id="claimsPie"></canvas></div></div>
            </div>
        </div>
    </div>

    <!-- Row 2: Admin Section -->
    @if($user->role == 'Admin')
    <div class="row g-4 mb-4">
        <div class="col-xl-7">
            <div class="card menu-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Site Visits Analysis</h6>
                    <small class="text-muted">Weekly Traffic</small>
                </div>
                <div class="card-body"><div style="height: 300px;"><canvas id="siteVisitsChart"></canvas></div></div>
            </div>
        </div>
        <div class="col-xl-5">
            <div class="card menu-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-bold mb-0">Recent Actions Log</h6></div>
                <div class="card-body px-4" style="max-height: 330px; overflow-y: auto;">
                    @foreach($adminData['recentActions'] as $action)
                    <div class="d-flex align-items-start gap-3 action-item">
                        <div class="p-2 rounded bg-light {{ $action['color'] }}"><i class="fa-solid fa-circle-check fs-5"></i></div>
                        <div class="flex-grow-1">
                            <p class="mb-0 fw-bold small text-dark">{{ $action['task'] }}</p>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">Ref: {{ $action['ref'] }}</small>
                        </div>
                        <span class="text-muted" style="font-size: 0.7rem;">{{ $action['time'] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Row 3: Premium Comparison -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card menu-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4"><h6>Premium (ZMW) by Insurer</h6></div>
                <div class="card-body"><canvas id="barZMW" height="300"></canvas></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card menu-card h-100">
                <div class="card-header bg-white border-0 pt-4 px-4"><h6>Premium (USD) by Insurer</h6></div>
                <div class="card-body"><canvas id="barUSD" height="300"></canvas></div>
            </div>
        </div>
    </div>
</div>

@push('scripts')

<script>
document.addEventListener("DOMContentLoaded", function() {
    const policyData = @json($jsData ?? []);
    const premiumData = @json($premiumData ?? []);
    const totalSlips = {{ $totalSlips ?? 0 }};
    const colors = ['#1e293b', '#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'];

    // 1. Policy Doughnut
    new Chart(document.getElementById('policyDoughnut'), {
        type: 'doughnut',
        data: {
            labels: policyData.map(d => d.insurer),
            datasets: [{ data: policyData.map(d => d.count), backgroundColor: colors, cutout: '78%', borderWidth: 4, borderColor: '#fff' }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { position: 'right', labels: { usePointStyle: true, padding: 25 } } }
        },
        plugins: [{
            id: 'centerText',
            beforeDraw(chart) {
                const { ctx } = chart;
                const centerX = (chart.chartArea.left + chart.chartArea.right) / 2;
                const centerY = (chart.chartArea.top + chart.chartArea.bottom) / 2;
                ctx.restore();
                ctx.textAlign = "center"; ctx.textBaseline = "middle";
                ctx.font = "bold 35px Inter"; ctx.fillStyle = "#1e293b";
                ctx.fillText(totalSlips, centerX, centerY - 8);
                ctx.font = "700 10px Inter"; ctx.fillStyle = "#94a3b8";
                ctx.fillText("TOTAL SLIPS", centerX, centerY + 22);
                ctx.save();
            }
        }]
    });

    // 2. Claims Pie
    new Chart(document.getElementById('claimsPie'), {
        type: 'pie',
        data: {
            labels: ['Pending', 'Settled'],
            datasets: [{ data: [{{ $claimsPending ?? 0 }}, {{ $claimsSettled ?? 0 }}], backgroundColor: ['#ef4444', '#22c55e'], borderWidth: 0 }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    // 3. Admin Visits Chart
    @if($user->role == 'Admin')
    const visitData = @json($adminData['siteVisits'] ?? []);
    new Chart(document.getElementById('siteVisitsChart'), {
        type: 'line',
        data: {
            labels: visitData.map(d => d.day),
            datasets: [{
                data: visitData.map(d => d.visits),
                borderColor: '#1e293b', backgroundColor: 'rgba(30, 41, 59, 0.05)',
                fill: true, tension: 0.4, pointRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
        }
    });
    @endif

    // 4. Premium Bar Charts
    const barOptions = { indexAxis: 'y', plugins: { legend: { display: false } }, responsive: true, maintainAspectRatio: false };
    
    new Chart(document.getElementById('barZMW'), {
        type: 'bar',
        data: { 
            labels: premiumData.map(d => d.insurer), 
            datasets: [{ data: premiumData.map(d => d.zmw_total), backgroundColor: '#1e293b', borderRadius: 5 }] 
        },
        options: barOptions
    });

    new Chart(document.getElementById('barUSD'), {
        type: 'bar',
        data: { 
            labels: premiumData.map(d => d.insurer), 
            datasets: [{ data: premiumData.map(d => d.usd_total), backgroundColor: '#4e73df', borderRadius: 5 }] 
        },
        options: barOptions
    });
});


document.addEventListener("DOMContentLoaded", function() {
    var dropdownElementList = [].slice.call(document.querySelectorAll('.dropdown-toggle'))
    var dropdownList = dropdownElementList.map(function (dropdownToggleEl) {
        return new bootstrap.Dropdown(dropdownToggleEl)
    });
});

</script>
<!-- Ensure this is the BUNDLE version, as it includes Popper.js
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> -->

@endpush
@endsection
