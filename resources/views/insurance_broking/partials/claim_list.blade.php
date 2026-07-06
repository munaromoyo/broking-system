<div class="container-fluid mb-4">
    <div class="row align-items-center g-3">
        <div class="col-md-5">
            <h3 class="fw-bold text-dark mb-0">Claims Registry</h3>
            <p class="text-muted small mb-0">Track and manage claim aging and settlement status</p>
        </div>
        <div class="col-md-7">
            <div class="d-flex gap-2">
                <div class="input-group shadow-sm border-0">
                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                    <input type="text" id="claimsSearch" class="form-control border-0 py-2" placeholder="Search by client, ID, status, or age...">
                </div>
                <div class="bg-primary text-white px-3 py-2 rounded shadow-sm d-flex align-items-center">
                    <span class="small fw-bold text-nowrap">{{ count($claims) }} Total</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container-fluid">
    <div class="row g-4" id="claimsList">
        @forelse ($claims as $claim)
            @php
                $claimStatus = $claim['claim_status'] ?? 'Unknown';
                $rawDate = $claim['date_of_notification'] ?? '';
                $ageLabel = "Pending Date";
                $ageClass = "bg-light text-dark";
                $dotClass = "bg-secondary";

                if (!empty($rawDate)) {
                    try {
                        // Assuming your DB date is stored as d/m/Y based on your PHP snippet
                        $notificationDate = \Carbon\Carbon::createFromFormat('d/m/Y', $rawDate)->startOfDay();
                        $today = now()->startOfDay();
                        
                        $daysOld = $notificationDate->diffInDays($today, false);

                        if ($daysOld >= 0) {
                            $ageLabel = ($daysOld === 0) ? "Today" : $daysOld . " Days Old";
                            
                            if ($daysOld >= 60) { 
                                $ageClass = "text-danger bg-danger-subtle"; 
                                $dotClass = "bg-danger"; 
                            } elseif ($daysOld >= 30) { 
                                $ageClass = "text-warning bg-warning-subtle"; 
                                $dotClass = "bg-warning"; 
                            } else { 
                                $ageClass = "text-success bg-success-subtle"; 
                                $dotClass = "bg-success"; 
                            }
                        } else {
                            $ageLabel = "Future Dated";
                        }
                    } catch (\Exception $e) {
                        $ageLabel = "Invalid Date";
                    }
                }

                // Status Styling Logic
                $statusConfig = [
                    'Settled' => ['badge' => 'bg-success', 'accent' => '#198754'],
                    'Closed'  => ['badge' => 'bg-secondary', 'accent' => '#6c757d'],
                    'Pending' => ['badge' => 'bg-warning text-dark', 'accent' => '#ffc107'],
                ];
                $style = $statusConfig[$claimStatus] ?? ['badge' => 'bg-info text-white', 'accent' => '#0dcaf0'];

                $searchData = strtolower(($claim['client_name'] ?? '') . " " . ($claim['id'] ?? '') . " " . $claimStatus . " " . $ageLabel);
            @endphp

            <div class="col-12 col-md-6 col-xl-4 claim-item" data-search="{{ $searchData }}">
                <a href="{{ route('insurance_broking.claims.show', $claim->id) }}" 
                   class="text-decoration-none card h-100 border-0 shadow-sm custom-card">
                    
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="status-indicator {{ $dotClass }} me-2"></div>
                                <span class="fw-bold text-dark">CLM-{{ str_pad($claim['id'], 4, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <span class="badge rounded-pill {{ $ageClass }} px-3 py-2 small">
                                <i class="bi bi-clock-history me-1"></i> {{ $ageLabel }}
                            </span>
                        </div>

                        <h5 class="fw-bold text-dark mb-1 text-truncate">{{ $claim['client_name'] ?? 'N/A' }}</h5>
                        <p class="text-muted small mb-3"><i class="bi bi-shield-check me-1"></i> {{ $claim['type_of_claim'] ?? 'General' }}</p>

                        <div class="bg-light rounded p-3 mb-3 d-flex justify-content-between align-items-center">
                            <div>
                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Status</small>
                                <span class="badge {{ $style['badge'] }} mt-1">{{ strtoupper($claimStatus) }}</span>
                            </div>
                            <div class="text-end">
                                <small class="d-block text-muted text-uppercase fw-bold" style="font-size: 0.65rem;">Claim Amount</small>
                                <span class="h6 fw-bold mb-0 text-dark">
                                    {{ $claim['policy_currency'] ?? 'ZMW' }} {{ number_format($claim['claim_amount'] ?? 0, 2) }}
                                </span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i> Notified</span>
                            <span class="fw-semibold small text-dark">{{ $rawDate }}</span>
                        </div>
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="text-center py-5 bg-white rounded shadow-sm">
                    <i class="bi bi-shield-exclamation text-muted display-4"></i>
                    <p class="text-muted mt-3">No insurance claims currently found.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>

<div id="noResults" class="container-fluid d-none mt-5">
    <div class="text-center py-5 bg-white rounded shadow-sm">
        <i class="bi bi-search text-muted display-4"></i>
        <p class="text-muted mt-3">No claims match your current search.</p>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('claimsSearch').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const items = document.querySelectorAll('.claim-item');
    let visibleCount = 0;

    items.forEach(item => {
        const text = item.getAttribute('data-search');
        if (text.includes(searchTerm)) {
            item.classList.remove('d-none');
            visibleCount++;
        } else {
            item.classList.add('d-none');
        }
    });

    document.getElementById('noResults').classList.toggle('d-none', visibleCount > 0);
});
</script>
@endpush

@push('styles')
<style>
    .custom-card {
        border-radius: 12px;
        transition: all 0.3s cubic-bezier(.25,.8,.25,1);
        background: #ffffff;
    }
    .custom-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
    }
    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
    }
    .bg-danger-subtle { background-color: #f8d7da; color: #842029; }
    .bg-warning-subtle { background-color: #fff3cd; color: #664d03; }
    .bg-success-subtle { background-color: #d1e7dd; color: #0f5132; }
</style>
@endpush