@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="row g-4"> {{-- Added Grid Row --}}

        <div class="col-12 col-md-9 col-xl-10">

            {{-- 1. PLACING SLIP LIST --}}
            @if($action == 'view_slip_list')
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 px-4 border-bottom-0">
                        <h6 class="mb-0 fw-bold text-primary">
                        <i class="bi bi-file-earmark-text me-1"></i> Placing Slips
                        </h6>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge rounded-pill bg-light text-primary border" id="resultCount">
                                {{ $placements->count() }} Total Active Records
                            </span>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted small"></i></span>
                            <input type="text" id="slipSearchInput" class="form-control bg-light border-0" placeholder="Type to search insured, insurer or ID...">
                        </div>
                    </div>

                    <div class="list-group list-group-flush border-top" id="slipList">
                        @forelse($placements as $placement)
                            @php 
                                $searchString = strtolower($placement->insured . ' ' . $placement->insurer . ' slpn-' . $placement->id . ' ' . $placement->id); 
                            @endphp
                            
                            {{-- Integrated list-groupitem classes straight into the wrapper anchor element to avoid item selection blocking errors --}}
                            <a href="{{ url('/insurance_broking/view_slip/index.php?id='.$placement->id) }}" 
                               class="policy-link-item list-group-item list-group-item-action py-2 px-4 border-bottom animate-item" 
                               data-search="{{ $searchString }}">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <div class="fw-bold text-dark text-truncate mb-0" style="font-size: 0.9rem;">{{ $placement->insured }}</div>
                                        <div class="text-muted small d-flex align-items-center">
                                            <span class="text-primary fw-medium">{{ $placement->insurer }}</span>
                                            <span class="mx-1">|</span>
                                            <span>SLPN-{{ $placement->id }}</span>
                                        </div>
                                    </div>
                                    <div class="col-md-4 d-none d-md-block border-start py-1">
                                        <div class="row g-0">
                                            <div class="col-6 text-center">
                                                <small class="text-muted d-block font-xxs">START</small>
                                                <span class="small fw-semibold text-nowrap">{{ $placement->policy_start_date ? \Carbon\Carbon::parse($placement->policy_start_date)->format('d M y') : 'N/A' }}</span>
                                            </div>
                                            <div class="col-6 text-center border-start">
                                                <small class="text-muted d-block font-xxs">EXPIRY</small>
                                                <span class="small fw-semibold text-nowrap text-danger">{{ $placement->policy_expiry_date ? \Carbon\Carbon::parse($placement->policy_expiry_date)->format('d M y') : 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 text-end border-start py-1">
                                        <small class="text-muted d-block font-xxs">Gross Premium</small>
                                        <span class="fw-bold text-success">{{ $placement->policy_currency }} {{ number_format($placement->gross_premium, 2) }}</span>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-4 text-center text-muted small">No active records found.</div>
                        @endforelse
                        
                        <div id="noResults" class="p-4 text-center d-none">
                            <span class="text-muted small">No records match your search.</span>
                        </div>
                    </div>
                </div>

            {{-- 2. CLAIMS LIST --}}
            @elseif($action == 'view_claim_list')
                <div class="container-fluid mb-4 px-0">
                    <div class="row align-items-center g-3">
                        <div class="col-md-5">
                            <h3 class="fw-bold text-dark mb-0">Claims Registry</h3>
                            <p class="text-muted small mb-0">Track and manage claim aging and settlement status</p>
                        </div>
                        <div class="col-md-7">
                            <div class="d-flex gap-2">
                                <div class="input-group shadow-sm border-0">
                                    <span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span>
                                    <input type="text" id="claimsSearch" class="form-control border-0 py-2" placeholder="Search claims...">
                                </div>
                                <div class="bg-primary text-white px-3 py-2 rounded shadow-sm d-flex align-items-center">
                                    <span class="small fw-bold text-nowrap" id="claimsCount">{{ $claims->count() }} Total</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-4" id="claimsList">
                    @forelse($claims as $claim)
                        @php
                            $notificationDate = $claim->date_of_notification ? \Carbon\Carbon::createFromFormat('d/m/Y', $claim->date_of_notification)->startOfDay() : now();
                            $daysOld = $notificationDate->diffInDays(now()->startOfDay(), false);
                            $absDays = abs($daysOld);
                            
                            if ($daysOld > 0) { $ageLabel = "Future"; $ageClass = "bg-light"; }
                            elseif ($absDays == 0) { $ageLabel = "Today"; $ageClass = "bg-success-subtle text-success"; }
                            else { 
                                $ageLabel = $absDays . " Days Old";
                                $ageClass = ($absDays >= 60) ? "bg-danger-subtle text-danger" : (($absDays >= 30) ? "bg-warning-subtle text-warning" : "bg-success-subtle text-success");
                            }
                            $claimSearchStr = strtolower($claim->client_name . ' clm-' . str_pad($claim->id, 4, '0', STR_PAD_LEFT) . ' ' . $claim->claim_status . ' ' . $claim->type_of_claim);
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4 claim-item animate-item" data-search="{{ $claimSearchStr }}">
                            <a href="{{ url('/insurance_broking/view_claim/index.php?id='.$claim->id) }}" class="text-decoration-none card h-100 border-0 shadow-sm custom-card">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="fw-bold text-dark">CLM-{{ str_pad($claim->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        <span class="badge rounded-pill {{ $ageClass }} px-3 py-2 small">{{ $ageLabel }}</span>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1 text-truncate">{{ $claim->client_name }}</h5>
                                    <p class="text-muted small mb-3">{{ $claim->type_of_claim }}</p>
                                    <div class="bg-light rounded p-3 mb-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="d-block text-muted text-uppercase fw-bold font-xxs">Status</small>
                                            <span class="badge bg-primary mt-1">{{ strtoupper($claim->claim_status) }}</span>
                                        </div>
                                        <div class="text-end">
                                            <small class="d-block text-muted text-uppercase fw-bold font-xxs">Amount</small>
                                            <span class="h6 fw-bold mb-0 text-dark">{{ $claim->policy_currency }} {{ number_format($claim->claim_amount, 2) }}</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted small p-4">No claim records available.</div>
                    @endforelse
                    <div id="claimsNoResults" class="col-12 text-center p-4 d-none">
                        <span class="text-muted small">No claims match your search parameters.</span>
                    </div>
                </div>

            {{-- 3. VEHICLE LIST --}}
            @elseif($action == 'view_vehicle_list')
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-primary"><i class="bi bi-truck me-2"></i> Vehicle Registry</h6>
                            <span class="badge bg-light text-primary border rounded-pill" id="vehiclesCount">{{ $vehicles->count() }} Total Items</span>
                        </div>
                        <input type="text" id="liveSearchInput" class="form-control bg-light border-0 mt-2" placeholder="Search Reg #, Client, or Make...">
                    </div>
                    <div class="list-group list-group-flush border-top" id="vehiclesContainer">
                        @forelse($vehicles as $row)
                            <div class="vehicle-item list-group-item py-2 px-4 animate-item" data-search="{{ strtolower($row->reg_number . ' ' . $row->client_name) }}">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <span class="badge bg-secondary me-2 font-monospace">{{ $row->reg_number }}</span>
                                        <span class="fw-bold">{{ $row->client_name }}</span>
                                    </div>
                                    <div class="col-md-4 text-center border-start">
                                        <small class="text-muted font-xxs d-block">EXPIRY</small>
                                        <span class="text-danger fw-bold">{{ $row->policy_expiry_date }}</span>
                                    </div>
                                    <div class="col-md-3 text-end border-start">
                                        <small class="text-muted font-xxs d-block">TOTAL PREMIUM</small>
                                        <span class="text-success fw-bold">{{ $row->policy_currency }} {{ number_format($row->total_premium, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">No vehicles registered.</div>
                        @endforelse
                        <div id="vehiclesNoResults" class="p-4 text-center d-none">
                            <span class="text-muted small">No matching vehicle records found.</span>
                        </div>
                    </div>
                </div>

            {{-- 4. CANCELLATION LIST --}}
            @elseif($action == 'view_cancelled_slip_list')
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 px-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="mb-0 fw-bold text-danger"><i class="bi bi-file-earmark-x me-1"></i> Cancellation Advices</h6>
                            <span class="badge bg-light text-danger border rounded-pill" id="cancelCount">{{ $cancellations->count() }} Total</span>
                        </div>
                        <input type="text" id="cancelSearchInput" class="form-control bg-light border-0 mt-2" placeholder="Search cancellations...">
                    </div>
                    <div class="list-group list-group-flush border-top" id="cancellationsContainer">
                        @forelse($cancellations as $cancel)
                            <div class="cancel-link-item list-group-item py-2 px-4 animate-item" data-search="{{ strtolower($cancel->insured_name . ' ' . $cancel->insurance_policy) }}">
                                <div class="row align-items-center">
                                    <div class="col-md-5">
                                        <div class="fw-bold text-dark">{{ $cancel->insured_name }}</div>
                                        <div class="text-danger small">POL: {{ $cancel->insurance_policy }}</div>
                                    </div>
                                    <div class="col-md-4 text-center border-start">
                                        <small class="text-muted font-xxs d-block">CANCELLED ON</small>
                                        <span class="fw-bold text-danger">{{ $cancel->cancellation_date ? \Carbon\Carbon::parse($cancel->cancellation_date)->format('d M Y') : 'N/A' }}</span>
                                    </div>
                                    <div class="col-md-3 text-end border-start">
                                        <small class="text-muted font-xxs d-block">REFUND</small>
                                        <span class="text-danger fw-bold">{{ $cancel->policy_currency }} {{ number_format($cancel->premium_refund, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">No cancellation records found.</div>
                        @endforelse
                        <div id="cancelNoResults" class="p-4 text-center d-none">
                            <span class="text-muted small">No cancellations match your criteria.</span>
                        </div>
                    </div>
                </div>
            @endif

        </div> {{-- End Main Content Column --}}
    </div> {{-- End Row --}}
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // Core Engine function to register independent listeners perfectly
    function registerLiveFilter(inputId, itemsSelector, badgeId, noResultsId, suffix) {
        const inputEl = document.getElementById(inputId);
        if (!inputEl) return; // Tab is inactive or absent in layout context, abort cleanly

        const targetItems = document.querySelectorAll(itemsSelector);
        const badgeEl = document.getElementById(badgeId);
        const noResultsEl = document.getElementById(noResultsId);

        inputEl.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            let totalMatchCount = 0;

            targetItems.forEach(item => {
                const searchableDataString = item.getAttribute('data-search') || '';
                
                // Directly check data matches
                if (searchableDataString.indexOf(searchTerm) !== -1) {
                    item.setAttribute('style', 'display: block !important;');
                    totalMatchCount++;
                } else {
                    item.setAttribute('style', 'display: none !important;');
                }
            });

            // Update respective badges dynamically
            if (badgeEl) {
                badgeEl.textContent = `${totalMatchCount}${suffix}`;
            }

            // Handle empty state layout alerts
            if (noResultsEl) {
                if (totalMatchCount === 0 && searchTerm !== "") {
                    noResultsEl.setAttribute('style', 'display: block !important;');
                } else {
                    noResultsEl.setAttribute('style', 'display: none !important;');
                }
            }
        });
    }

    // Initialize all 4 channels independently safely
    registerLiveFilter('slipSearchInput', '.policy-link-item', 'resultCount', 'noResults', ' Active Records Match');
    registerLiveFilter('claimsSearch', '.claim-item', 'claimsCount', 'claimsNoResults', ' Found');
    registerLiveFilter('liveSearchInput', '.vehicle-item', 'vehiclesCount', 'vehiclesNoResults', ' Matching Items');
    registerLiveFilter('cancelSearchInput', '.cancel-link-item', 'cancelCount', 'cancelNoResults', ' Matches');
});
</script>
@endpush