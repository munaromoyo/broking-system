@extends('layouts.app')

@section('title', 'Cancelled Slips')

@push('styles')
<style>
    /* View-specific style overrules */
    .registry-header {
        border-left: 4px solid #dc3545;
        padding-left: 1rem;
    }
    .cancel-link-item:hover { 
        background-color: #fff5f5 !important; 
        border-left: 3px solid #dc3545 !important; 
        padding-left: calc(1.5rem - 3px) !important; 
        transition: 0.15s; 
    }
    .extra-small { font-size: 0.75rem; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex align-items-center justify-content-between mb-4 registry-header">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Placing Slips & Cancellations</h4>
            <p class="text-muted small mb-0">Manage active insurance broking placements and risk modifications.</p>
        </div>
    </div>

    {{-- Insert your responsive list-group card layout --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 px-4 border-bottom-0">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <h6 class="mb-0 fw-bold text-danger">
                    <i class="bi bi-file-earmark-x me-1"></i> Cancellation Advices
                </h6>
                <span class="badge rounded-pill bg-light text-danger border" id="cancelCount">
                    {{ $cancellations->count() }} Records
                </span>
            </div>
            
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted small"></i></span>
                <input type="text" id="cancelSearchInput" class="form-control bg-light border-0" placeholder="Search cancellations by insured, policy or remarks...">
            </div>
        </div>

        <div class="list-group list-group-flush border-top" id="cancelList">
            @if($cancellations && $cancellations->isNotEmpty())
                @foreach($cancellations as $cancel)
                    @php
                        $searchString = strtolower(trim(
                            ($cancel->insured_name ?? '') . ' ' . 
                            ($cancel->insurance_policy ?? '') . ' ' . 
                            ($cancel->slip_id ?? '') . ' ' . 
                            ($cancel->remarks ?? '') . ' ' . 
                            ($cancel->cancelled_by ?? '')
                        ));
                    @endphp
                    
                    <a href="{{ route('insurance_broking.cancelled_slips.show', $cancel->slip_id) }}" 
                       class="cancel-link-item list-group-item list-group-item-action py-2 px-4"
                       data-search="{{ $searchString }}">
                        
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="fw-bold text-dark text-truncate mb-0" style="font-size: 0.9rem;">
                                    {{ $cancel->insured_name }}
                                </div>
                                <div class="text-muted small d-flex align-items-center">
                                    <span class="text-danger fw-medium">POL: {{ $cancel->insurance_policy }}</span>
                                    <span class="mx-1">•</span>
                                    <span class="text-truncate text-capitalize">By: {{ $cancel->cancelled_by }}</span>
                                </div>
                            </div>

                            <div class="col-md-4 d-none d-md-block border-start py-1">
                                <div class="text-center">
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">CANCELLATION DATE</small>
                                    <span class="small fw-semibold text-nowrap text-danger">
                                        {{ \Carbon\Carbon::parse($cancel->cancellation_date)->format('d M Y') }}
                                    </span>
                                </div>
                            </div>

                            <div class="col-md-3 text-end border-start py-1">
                                <small class="text-muted d-block text-uppercase" style="font-size: 0.65rem;">Refund Amount</small>
                                <span class="fw-bold text-danger">
                                    {{ $cancel->policy_currency ?? 'ZMW' }} {{ number_format($cancel->premium_refund, 2) }}
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            @endif

            <div id="noCancelResults" class="p-4 text-center d-none">
                <i class="bi bi-exclamation-circle text-muted mb-2 d-block" style="font-size: 1.5rem;"></i>
                <span class="text-muted small">No records match your search filtering parameters.</span>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cancelInput = document.getElementById('cancelSearchInput');
    const cancelItems = document.querySelectorAll('.cancel-link-item');
    const noCancelResults = document.getElementById('noCancelResults');
    const cancelBadge = document.getElementById('cancelCount');

    cancelInput.addEventListener('keyup', function() {
        const filter = this.value.toLowerCase().trim();
        let visibleCount = 0;

        cancelItems.forEach(item => {
            const searchContent = item.getAttribute('data-search');
            if (searchContent.includes(filter)) {
                item.style.display = ""; 
                visibleCount++;
            } else {
                item.style.display = "none"; 
            }
        });

        cancelBadge.textContent = visibleCount + ' Records';
        noCancelResults.classList.toggle('d-none', visibleCount > 0);
    });
});
</script>
@endpush