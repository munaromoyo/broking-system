@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    {{-- Alert Messages --}}
    @if(session('msg'))
    <div class="alert alert-{{ session('msg_type') }} alert-dismissible fade show shadow-sm" role="alert">
        <i class="fas {{ session('msg_type') == 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle' }} me-2"></i>
        {{ session('msg') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    @endif

    <div class="placement-container px-lg-5 position-relative">
        @if($placement->status == 'Cancelled')
            <div class="watermark">CANCELLED</div>
            <div class="status-banner">Placing Slip Status: CANCELLED</div>
        @endif

        <div class="d-flex justify-content-between align-items-end mb-4 position-relative" style="z-index: 2;">
            <div class="title-box">
                <h1 class="border-bottom border-danger border-2 pb-2">Placing Slip</h1>
            </div>
            <div class="ref-number fw-bold">REF NO: {{ $placement->id }}</div>
        </div>

        {{-- Section: Client & Period --}}
        <table class="table table-bordered content-table mb-4 position-relative" style="z-index: 2;">
            <thead>
                <tr><th colspan="2" class="section-header bg-light text-danger py-2 px-3">Client & Period Details</th></tr>
            </thead>
            <tbody>
                <tr><th class="tableheader3 w-25 text-uppercase small text-muted">Insured Name</th><td>{{ $placement->insured }}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Principal Address</th><td>{{ $placement->principal_address }}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Nature of Business</th><td>{{ $placement->nature_of_business }}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Insurer</th><td>{{ $placement->insurer }}</td></tr>
                <tr>
                    <th class="tableheader3 text-uppercase small text-muted">Period of Insurance</th>
                    <td>
                        <strong>From:</strong> {{ $placement->policy_start_date ? \Carbon\Carbon::parse($placement->policy_start_date)->format('d M Y') : 'N/A' }} 
                        <strong class="ms-2">To:</strong> {{ $placement->policy_expiry_date ? \Carbon\Carbon::parse($placement->policy_expiry_date)->format('d M Y') : 'N/A' }}
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Section: Risk Details --}}
        <table class="table table-bordered content-table mb-4 position-relative" style="z-index: 2;">
            <thead>
                <tr><th colspan="2" class="section-header bg-light text-danger py-2 px-3">Risk & Coverage Details</th></tr>
            </thead>
            <tbody>
                <tr><th class="tableheader3 w-25 text-uppercase small text-muted">Insurance Policy</th><td>{{ $placement->insurance_policy }}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Property Insured</th><td>{!! nl2br(e($placement->property_insured)) !!}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Location of Risk</th><td>{{ $placement->location_of_risk }}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Scope of Cover</th><td>{!! nl2br(e($placement->scope_of_cover)) !!}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Extensions</th><td>{!! nl2br(e($placement->extensions)) !!}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Excess / Deductible</th><td>{!! nl2br(e($placement->excess_deductible)) !!}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Specific Warranties</th><td>{!! nl2br(e($placement->specific_warranties)) !!}</td></tr>
                <tr><th class="tableheader3 text-uppercase small text-muted">Specific Conditions</th><td>{!! nl2br(e($placement->specific_conditions)) !!}</td></tr>
            </tbody>
        </table>

        {{-- Premium Summary --}}
        <div class="summary-title text-danger fw-bold text-uppercase mb-2 position-relative" style="z-index: 2;">Premium & Financial Summary</div>
        <table class="table table-sm table-striped border mb-4 position-relative" style="z-index: 2;">
            <tbody>
                <tr><th class="bg-light w-50">Currency</th><td><strong>{{ $placement->policy_currency }}</strong></td></tr>
                <tr><th class="bg-light">Total Sum Insured</th><td>{{ number_format((float)($placement->total_sum_insured ?? 0), 2) }}</td></tr>
                <tr><th class="bg-light">Basic Premium (Rate: {{ $placement->basic_rate ?? '0' }}%)</th><td>{{ number_format((float)($placement->basic_premium ?? 0), 2) }}</td></tr>
                <tr><th class="bg-light">Discount (Rate: {{ $placement->discount_rate ?? '0' }})</th><td>{{ number_format((float)($placement->discount ?? 0), 2) }}</td></tr>
                <tr><th class="bg-light">Premium Levy (Rate: {{ $placement->premium_levy_rate ?? '0' }})</th><td>{{ number_format((float)($placement->premium_levy ?? 0), 2) }}</td></tr>
                <tr class="table-danger">
                    <th class="bg-light fw-bold text-danger">Gross Premium (Payable)</th>
                    <td class="fw-bold text-danger">{{ number_format((float)($placement->gross_premium ?? 0), 2) }}</td>
                </tr>
                <tr><th class="bg-light">Commission (Rate: {{ $placement->commission_rate ?? '0' }}%)</th><td>{{ number_format((float)($placement->commission_amount ?? 0), 2) }}</td></tr>
                <tr><th class="bg-light fw-bold">Insurer Net Remittance</th><td class="fw-bold">{{ number_format((float)($placement->insurer_premium ?? 0), 2) }}</td></tr>
                <tr><th class="bg-light">Payment Method</th><td>{{ $placement->payment_method ?? 'N/A' }}</td></tr>
                <tr><th class="bg-light">Total Payment Made</th><td>{{ number_format((float)($placement->payment_made ?? 0), 2) }}</td></tr>
            </tbody>
        </table>

        {{-- Section: Statutory Clauses --}}
        <table class="table table-bordered content-table mb-4 position-relative" style="z-index: 2;">
            <thead>
                <tr><th colspan="2" class="section-header bg-light text-danger py-2 px-3">Statutory Clauses</th></tr>
            </thead>
            <tbody>
                <tr>
                    <th class="tableheader3 w-25 text-uppercase small text-muted">Cancellation Clause</th>
                    <td class="text-muted fs-7">{!! nl2br(e($placement->cancellation_clause)) !!}</td>
                </tr>
                <tr>
                    <th class="tableheader3 text-uppercase small text-muted">Placing Slip Clause</th>
                    <td class="text-muted fs-7">{!! nl2br(e($placement->placing_slip_clause)) !!}</td>
                </tr>
            </tbody>
        </table>

        {{-- Actions --}}
        <div class="actions border-top pt-4 text-center position-relative" style="z-index: 2;">
            @if($placement && isset($placement->id))
                
                {{-- 1. ACTIVE STATUS ONLY ACTIONS --}}
                @if($placement->status !== 'Cancelled')
                    {{-- Edit Slip --}}
                    <a href="{{ route('insurance_broking.placement_slips.edit', $placement->id) }}" class="btn btn-primary btn-edit me-1">
                        <i class="fa fa-edit"></i> Edit Slip
                    </a>
                    
                    {{-- Use as Template (Clone Slip) --}}
                    <a href="{{ route('insurance_broking.register', ['action' => 'register_slip', 'clone_id' => $placement->id]) }}" class="btn me-1" style="background-color: #6f42c1; color: white;">
                        <i class="fa fa-copy"></i> Use as Template
                    </a>
            
                    {{-- Cancel Slip Button --}}
                    <button type="button" class="btn btn-danger btn-cancel me-1" 
                        data-bs-toggle="modal" data-bs-target="#cancelModal{{ $placement->id }}"
                        data-id="{{ $placement->id }}"
                        data-premium="{{ $placement->basic_premium ?? 0 }}"
                        data-expiry="{{ $placement->policy_expiry_date }}"
                        data-currency="{{ $placement->policy_currency }}">
                        <i class="fa fa-times-circle"></i> Cancel Slip
                    </button>
                @endif
            
                {{-- 2. GLOBAL ACTIONS AVAILABLE REGARDLESS OF STATUS --}}
                {{-- Generate Quotation --}}
                <a href="{{ route('insurance_broking.quotations.create', ['action' => 'register_quote', 'clone_id' => $placement->id]) }}" class="btn me-1" target="_blank" style="background-color: #28a745; color: white;">
                     <i class="fa fa-file-invoice"></i> Generate Quotation
                </a>
                            
                {{-- Generate Slip PDF --}}
                <a href="{{ route('insurance_broking.placement_slips.pdf_slip', $placement->id) }}" class="btn btn-success me-1" target="_blank">
                    <i class="fa fa-file-pdf"></i> Generate PDF
                </a>
                
                {{-- Generate KFS PDF --}}
                <a href="{{ route('insurance_broking.placement_slips.pdf_kfs', $placement->id) }}" class="btn btn-info" target="_blank" style="background-color: #17a2b8; color: white; border-color: #17a2b8;">
                    <i class="fa fa-file-alt"></i> Generate KFS
                </a>
                
            @endif
        </div>
    </div>
</div>


{{-- Cancel Modal --}}
<div class="modal fade" id="cancelModal{{ $placement->id }}" tabindex="-1" aria-hidden="true" role="dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('insurance_broking.placement_slips.cancel') }}" method="POST">
                @csrf
                
                {{-- Hidden input payloads --}}
                <input type="hidden" name="action" value="cancel_slip">
                <input type="hidden" name="id" id="modal_placement_id_{{ $placement->id }}" value="{{ $placement->id }}">
                <input type="hidden" name="remaining_days" id="hiddenDays{{ $placement->id }}" value="0">

                <div class="modal-header bg-light">
                    <h5 class="modal-title">Cancel Slip #{{ $placement->id }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body text-start">
                    
                    {{-- Refund Warning Container --}}
                    <div id="refundSummary{{ $placement->id }}" class="alert alert-warning border border-warning-subtle mb-3">
                        <label class="fw-bold form-label">Refund Amount ({{ $placement->policy_currency }}):</label>
                        <input type="number" step="0.01" name="manual_refund" id="displayRefundInput{{ $placement->id }}" class="form-control fw-bold fs-5 mb-1 text-warning-dark bg-transparent" value="0.00">
                        <small class="text-muted d-block">Days remaining: <span id="displayDays{{ $placement->id }}">0</span></small>
                    </div>

                    {{-- Full Reversal Checkbox --}}
                    <div class="form-check p-3 bg-light rounded mb-3 ms-0">
                        <input class="form-check-input ms-1" type="checkbox" id="reversalCheck{{ $placement->id }}" name="is_reversal">
                        <label class="form-check-label fw-bold text-primary ms-2" for="reversalCheck{{ $placement->id }}">
                            Full Reversal (Ab Initio)
                        </label>
                        <small class="text-muted d-block ms-2 mt-1">Check this to refund the FULL premium amount.</small>
                    </div>

                    {{-- Date Ranges --}}
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="dateFrom{{ $placement->id }}" class="fw-bold form-label">Cancellation From:</label>
                            <input type="date" name="cancellation_date_from" id="dateFrom{{ $placement->id }}" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label for="dateTo{{ $placement->id }}" class="fw-bold form-label">Cancellation To:</label>
                            <input type="date" name="cancellation_date_to" id="dateTo{{ $placement->id }}" class="form-control" required>
                        </div>
                    </div>

                    {{-- Remarks --}}
                    <div class="mb-3">
                        <label class="fw-bold form-label">Reason for Cancellation:</label>
                        <textarea name="remarks" class="form-control mt-1" rows="3" required placeholder="Enter reason for cancellation..."></textarea>
                    </div>

                    {{-- Final Acknowledgment Confirmation Checkbox --}}
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="confirmCheck{{ $placement->id }}" required>
                        <label class="form-check-label text-muted fs-7" for="confirmCheck{{ $placement->id }}">
                            I confirm that I want to cancel this policy and the refund amount is understood.
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

<!-- @push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
    // Listen globally for any Bootstrap modal opening
    document.addEventListener('show.bs.modal', function (event) {
        const modal = event.target;
        
        // Ensure we are only dealing with a cancellation modal
        if (modal.id.startsWith('cancelModal')) {
            const button = event.relatedTarget; // Button that triggered the modal
            if (!button) return;

            // Extract values directly from the clicked button's data attributes
            const uniqueId   = button.getAttribute('data-id');
            const premium    = parseFloat(button.getAttribute('data-premium')) || 0;
            const expiryStr  = button.getAttribute('data-expiry');
            
            // Scope target elements specifically to this open modal instance
            const refundInput   = modal.querySelector(`[name="manual_refund"]`);
            const daysSpan      = modal.querySelector(`#displayDays${uniqueId}`);
            const hiddenDaysInput = modal.querySelector(`#hiddenDays${uniqueId}`);
            const dateFromInput = modal.querySelector(`#dateFrom${uniqueId}`);
            const dateToInput   = modal.querySelector(`#dateTo${uniqueId}`);
            const reversalCheck = modal.querySelector(`#reversalCheck${uniqueId}`);

            let calculatedRefund = 0;
            let diffDays = 0;

            // Pre-fill "Cancellation To" with the policy expiry date if available
            if (expiryStr && dateToInput) {
                dateToInput.value = expiryStr;
            }

            // Fallback proactive date calculation system (Using current date vs policy expiry)
            if (expiryStr && expiryStr.trim() !== "") {
                const expiryDate = new Date(expiryStr);
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                
                const diffTime = expiryDate - today;
                diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                diffDays = diffDays < 0 ? 0 : diffDays;
                
                calculatedRefund = (premium / 365) * diffDays;
                
                if (daysSpan) daysSpan.innerText = diffDays;
                if (hiddenDaysInput) hiddenDaysInput.value = diffDays;
                if (refundInput) refundInput.value = calculatedRefund.toFixed(2);
            }

            

            // Manage dynamic date selections changing the calculations reactively
            function recalculateProRata() {
                if (dateFromInput?.value && dateToInput?.value) {
                    const fromDate = new Date(dateFromInput.value);
                    const toDate = new Date(dateToInput.value);
                    
                    let activeDiffTime = toDate - fromDate;
                    let activeDiffDays = Math.ceil(activeDiffTime / (1000 * 60 * 60 * 24));
                    activeDiffDays = activeDiffDays < 0 ? 0 : activeDiffDays;
                    
                    calculatedRefund = (premium / 365) * activeDiffDays;
                    
                    if (daysSpan) daysSpan.innerText = activeDiffDays;
                    if (hiddenDaysInput) hiddenDaysInput.value = activeDiffDays;
                    
                    // Only update value if the full reversal toggle is off
                    if (refundInput && (!reversalCheck || !reversalCheck.checked)) {
                        refundInput.value = calculatedRefund.toFixed(2);
                    }
                }
            }

            // Bind change listeners to the newly added date windows
            dateFromInput?.addEventListener('change', recalculateProRata);
            dateToInput?.addEventListener('change', recalculateProRata);

            // Reversal Check Event Management
            if (reversalCheck) {
                // Wipe clean old event bindings using clone replacement structures
                const newReversalCheck = reversalCheck.cloneNode(true);
                reversalCheck.replaceWith(newReversalCheck);
                
                newReversalCheck.addEventListener('change', function() {
                    if (refundInput) {
                        if (this.checked) {
                            refundInput.value = premium.toFixed(2);
                            refundInput.classList.remove('text-warning-dark');
                            refundInput.style.color = '#004085'; // Full Reversal Blue
                        } else {
                            refundInput.value = calculatedRefund.toFixed(2);
                            refundInput.style.color = ''; 
                            refundInput.classList.add('text-warning-dark'); // Standard Warning Yellow
                        }
                    }
                });
            }
        }
    });
});
</script>
@endpush -->

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // Helper to get consistent date difference
        function calculateDaysInclusive(start, end) {
            const startDate = new Date(start);
            const endDate = new Date(end);
            
            // Normalize to midnight to avoid time-of-day offsets
            const startMidnight = new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate());
            const endMidnight = new Date(endDate.getFullYear(), endDate.getMonth(), endDate.getDate());
            
            const diffTime = endMidnight - startMidnight;
            const diffDays = Math.round(diffTime / (1000 * 60 * 60 * 24));
            
            // Add 1 for inclusive count (standard for insurance/policy duration)
            return Math.max(0, diffDays + 1);
        }

        document.addEventListener('show.bs.modal', function (event) {
            const modal = event.target;
            
            if (modal.id.startsWith('cancelModal')) {
                const button = event.relatedTarget;
                if (!button) return;

                const uniqueId     = button.getAttribute('data-id');
                const premium      = parseFloat(button.getAttribute('data-premium')) || 0;
                const expiryStr    = button.getAttribute('data-expiry');
                
                const refundInput   = modal.querySelector(`[name="manual_refund"]`);
                const daysSpan      = modal.querySelector(`#displayDays${uniqueId}`);
                const hiddenDaysInput = modal.querySelector(`#hiddenDays${uniqueId}`);
                const dateFromInput = modal.querySelector(`#dateFrom${uniqueId}`);
                const dateToInput   = modal.querySelector(`#dateTo${uniqueId}`);
                const reversalCheck = modal.querySelector(`#reversalCheck${uniqueId}`);

                // Initialize Inputs
                if (expiryStr && dateToInput) dateToInput.value = expiryStr;
                
                function updateCalculations() {
                    if (dateFromInput?.value && dateToInput?.value) {
                        const days = calculateDaysInclusive(dateFromInput.value, dateToInput.value);
                        const calculatedRefund = (premium / 365) * days;
                        
                        if (daysSpan) daysSpan.innerText = days;
                        if (hiddenDaysInput) hiddenDaysInput.value = days;
                        
                        // Update refund only if NOT in full reversal mode
                        if (refundInput && (!reversalCheck || !reversalCheck.checked)) {
                            refundInput.value = calculatedRefund.toFixed(2);
                        }
                    }
                }

                // Initial run using 'today' as start date
                const today = new Date().toISOString().split('T')[0];
                if (dateFromInput) dateFromInput.value = today;
                updateCalculations();

                // Listeners
                dateFromInput?.addEventListener('change', updateCalculations);
                dateToInput?.addEventListener('change', updateCalculations);

                if (reversalCheck) {
                    reversalCheck.addEventListener('change', function() {
                        if (this.checked) {
                            refundInput.value = premium.toFixed(2);
                            refundInput.style.color = '#004085';
                        } else {
                            updateCalculations();
                            refundInput.style.color = '';
                        }
                    });
                }
            }
        });
    });
</script>
@endpush

@push('styles')
<style>
    .watermark {
        position: absolute; top: 35%; left: 50%; transform: translate(-50%, -50%) rotate(-45deg);
        font-size: 80px; color: rgba(226, 6, 19, 0.05); font-weight: bold; z-index: 1; pointer-events: none;
        border: 10px solid rgba(226, 6, 19, 0.05); padding: 20px; text-align: center; width: auto;
    }
    .status-banner {
        background-color: #e74a3b; color: white; text-align: center; padding: 10px;
        font-weight: bold; margin-bottom: 20px; border-radius: 4px; position: relative; z-index: 2;
    }
    .tableheader3 { background-color: #fcfcfc; }
</style>
@endpush