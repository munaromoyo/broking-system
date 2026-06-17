@extends('layouts.app')

@section('title', 'Quotation Details - #' . $quotation->id)

@push('styles')
<style>
    :root {
        --brand-primary: var(--rib-red, #e20613);
        --brand-dark: var(--rib-dark, #1e293b);
    }
    .quote-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .quote-header {
        background-color: var(--brand-dark);
        color: #ffffff;
        padding: 1.5rem;
        border-bottom: 1px solid rgba(255,255,255,0.1);
    }
    .section-divider {
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 0.5rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .step-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 1.25rem;
        height: 1.25rem;
        border-radius: 50%;
        background-color: #f1f5f9;
        color: #475569;
        font-size: 0.65rem;
    }
    .data-box {
        background-color: rgba(248, 250, 252, 0.8);
        border: 1px solid #f1f5f9;
        border-radius: 0.5rem;
        padding: 1rem;
    }
    .data-label {
        font-size: 0.7rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #94a3b8;
        margin-bottom: 0.25rem;
    }
    .data-value {
        color: #0f172a;
        font-weight: 600;
        margin-bottom: 0;
    }
    .text-block-display {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.5rem;
        padding: 0.75rem;
        font-size: 0.875rem;
        color: #334155;
        min-height: 70px;
        line-height: 1.6;
    }
    /* Financial Matrix Premium Summary Panel */
    .premium-panel {
        background-color: #0f172a;
        color: #ffffff;
        border-radius: 0.75rem;
        padding: 1.5rem;
    }
    .premium-panel .data-label {
        color: #94a3b8;
    }
    .net-remittance-card {
        background-color: var(--brand-primary);
        color: #ffffff;
        border-radius: 0.5rem;
        padding: 1rem;
        height: 100%;
    }
    .net-remittance-card .data-label {
        color: rgba(255,255,255,0.8);
    }
    /* Pulse indicator customization */
    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: currentColor;
        display: inline-block;
        animation: pulseAnimation 2s infinite;
    }
    @keyframes pulseAnimation {
        0% { opacity: 0.4; }
        50% { opacity: 1; }
        100% { opacity: 0.4; }
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-1xl col-xl-10">
        
        <div class="quote-card">
            <!-- Header Section -->
            <div class="quote-header d-flex flex-column sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-danger bg-opacity-10 text-danger text-uppercase tracking-wider" style="font-size: 0.7rem; background-color: rgba(226,6,19,0.15) !important; color: var(--brand-primary) !important;">
                            Broking Engine
                        </span>
                        <small class="text-white-50">| Registry Record</small>
                    </div>
                    <h1 class="h3 font-weight-bold text-white mt-2 mb-1">Quotation Reference #{{ $quotation->id }}</h1>
                    <p class="small text-white-50 mb-0">
                        Account Executive: <span class="text-white font-weight-medium">{{ $quotation->user?->name ?? $quotation->user }}</span>
                    </p>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2">
                    @php
                        $statusColors = [
                            'Pending' => 'bg-warning text-warning border-warning',
                            'Approved' => 'bg-success text-success border-success',
                            'Rejected' => 'bg-danger text-danger border-danger',
                            'Invoiced' => 'bg-info text-info border-info',
                        ];
                        $badgeStyle = $statusColors[$quotation->quote_status] ?? 'bg-secondary text-secondary border-secondary';
                    @endphp
                    <span class="badge border bg-opacity-10 d-inline-flex align-items-center gap-2 px-3 py-2 text-sm {{ $badgeStyle }}" style="font-size: 0.85rem;">
                        <span class="pulse-dot"></span>
                        {{ $quotation->quote_status ?? 'Draft' }}
                    </span>
                    
                    <!-- Quick Header Actions -->
                    <a href="{{ route('insurance_broking.quotations.edit', $quotation->id) }}" class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-1">
                        <i class="bi bi-pencil-square"></i> Edit
                    </a>
                    <a href="{{ route('insurance_broking.quotations.pdf', $quotation->id) }}" class="btn btn-sm btn-light text-dark d-inline-flex align-items-center gap-1">
                        <i class="bi bi-file-earmark-pdf text-danger"></i> PDF
                    </a>
                </div>
            </div>

            <!-- Main Content Form-Body -->
            <div class="p-4 p-md-5">
                
                <!-- Section 1: Identification -->
                <div class="mb-5">
                    <div class="section-divider">
                        <span class="step-badge">1</span>
                        Client & Insurer Identification
                    </div>
                    <div class="data-box">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="data-label d-block">Insured Legal Entity</label>
                                <p class="data-value fs-5">{{ $quotation->insured }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="data-label d-block">Underwriting Insurer</label>
                                <p class="data-value fs-5">{{ $quotation->insurer }}</p>
                            </div>
                            <div class="col-12 border-top pt-3 mt-3">
                                <label class="data-label d-block">Principal Corporate Address</label>
                                <p class="text-secondary small mb-0 style-support" style="white-space: pre-line; line-height: 1.6;">{{ $quotation->principal_address ?? 'Not Configured' }}</p>
                            </div>
                            <div class="col-12 border-top pt-3 mt-3">
                                <label class="data-label d-block">Nature of Business Operation</label>
                                <p class="text-secondary small mb-0 style-support" style="white-space: pre-line; line-height: 1.6;">{{ $quotation->nature_of_business ?? 'Not Configured' }}</p>
                            </div>
                            <div class="col-md-6 border-top pt-3 mt-3">
                                <label class="data-label d-block">Risk Inception Date</label>
                                <div class="text-dark font-weight-medium d-flex align-items-center gap-2 small">
                                    <i class="bi bi-calendar3 text-muted"></i>
                                    <span>{{ $quotation->policy_start_date ? \Carbon\Carbon::parse($quotation->policy_start_date)->format('d M, Y') : 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6 border-top pt-3 mt-3">
                                <label class="data-label d-block">Risk Term Expiry Date</label>
                                <div class="text-dark font-weight-medium d-flex align-items-center gap-2 small">
                                    <i class="bi bi-calendar3-wave text-muted"></i>
                                    <span>{{ $quotation->policy_expiry_date ? \Carbon\Carbon::parse($quotation->policy_expiry_date)->format('d M, Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Risk Profile -->
                <div class="mb-5">
                    <div class="section-divider">
                        <span class="step-badge">2</span>
                        Risk Profile & Placement Parameters
                    </div>
                    <div class="d-flex flex-column gap-3">
                        <div class="data-box">
                            <label class="data-label d-block">Insurance Policy Architecture</label>
                            <p class="data-value small">{{ $quotation->insurance_policy ?? 'N/A' }}</p>
                        </div>
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-muted fw-semibold small text-uppercase tracking-wider mb-2 d-block" style="font-size:0.7rem;">Property Assets Insured</label>
                                <div class="text-block-display">{{ $quotation->property_insured ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="text-muted fw-semibold small text-uppercase tracking-wider mb-2 d-block" style="font-size:0.7rem;">Geographic Location of Risk</label>
                                <div class="text-block-display">{{ $quotation->location_of_risk ?? 'N/A' }}</div>
                            </div>
                        </div>

                        <div class="border rounded p-3 bg-white">
                            <label class="data-label d-block">Scope of Underwritten Cover</label>
                            <p class="small text-secondary mb-0 mt-1" style="white-space: pre-line; line-height: 1.6;">{{ $quotation->scope_of_cover ?? 'N/A' }}</p>
                        </div>

                        <div class="data-box">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="data-label d-block">Cancellation Clause Terms</label>
                                    <p class="small text-muted fst-italic mb-0 mt-1">{{ $quotation->cancellation_clause ?? 'Standard market operational terms apply.' }}</p>
                                </div>
                                <div class="col-md-6">
                                    <label class="data-label d-block">Placing Slip Provision</label>
                                    <p class="small text-muted fst-italic mb-0 mt-1">{{ $quotation->placing_slip_clause ?? 'Standard market operational terms apply.' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Financial Calculations -->
                <div class="mb-5">
                    <div class="section-divider">
                        <span class="step-badge">3</span>
                        Premium Breakdown & Commission Calculations ({{ $quotation->policy_currency }})
                    </div>
                    
                    <div class="premium-panel">
                        <div class="row g-4 border-bottom border-secondary pb-4 mb-4">
                            <div class="col-6 col-md-3">
                                <span class="data-label d-block">Total Sum Insured</span>
                                <span class="h4 font-weight-bold text-white tracking-tight">{{ $quotation->total_sum_insured ? number_format((float)$quotation->total_sum_insured, 2) : '0.00' }}</span>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="data-label d-block">Basic Premium</span>
                                <span class="text-muted d-block small mb-1">Factor: {{ number_format($quotation->basic_rate, 4) }}%</span>
                                <span class="fw-semibold text-white-50">{{ number_format($quotation->basic_premium, 2) }}</span>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="data-label d-block">Discounts Retained</span>
                                <span class="text-muted d-block small mb-1">Rate: {{ number_format($quotation->discount_rate, 4) }}%</span>
                                <span class="fw-semibold text-danger">-{{ number_format($quotation->discount, 2) }}</span>
                            </div>
                            <div class="col-6 col-md-3">
                                <span class="data-label d-block">Statutory Premium Levy</span>
                                <span class="text-muted d-block small mb-1">Rate: {{ number_format($quotation->premium_levy_rate, 4) }}%</span>
                                <span class="fw-semibold text-white-50">{{ number_format($quotation->premium_levy, 2) }}</span>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="bg-secondary bg-opacity-20 border border-secondary border-opacity-20 p-3 rounded">
                                    <span class="data-label d-block">Gross Client Premium</span>
                                    <span class="h4 font-weight-bold text-white mb-0">{{ number_format($quotation->gross_premium, 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="bg-secondary bg-opacity-20 border border-secondary border-opacity-20 p-3 rounded">
                                    <span class="data-label d-block">Brokerage Commission Matrix</span>
                                    <span class="text-muted d-block small">Yield: {{ number_format($quotation->commission_rate, 4) }}%</span>
                                    <span class="h5 font-weight-bold text-success mb-0">+{{ number_format($quotation->commission_amount, 2) }}</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="net-remittance-card">
                                    <span class="data-label d-block text-uppercase">Net Insurer Remittance</span>
                                    <span class="h3 font-weight-black tracking-tight text-white mb-0 d-block mt-1">{{ number_format($quotation->insurer_premium, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Settlement & Audit -->
                <div>
                    <div class="section-divider">
                        <span class="step-badge">4</span>
                        Settlement Status & Audit Lifecycles
                    </div>
                    <div class="data-box">
                        <div class="row g-4">
                            <div class="col-sm-4">
                                <label class="data-label d-block">Settlement Routing Channel</label>
                                <p class="data-value small d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-primary rounded-circle p-1" style="width:8px; height:8px;"></span>
                                    {{ $quotation->payment_method ?? 'Awaiting Instructions' }}
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <label class="data-label d-block">Cleared Capital Logged</label>
                                <p class="text-success font-weight-bold fs-5 mb-0">
                                    {{ $quotation->policy_currency }} {{ number_format($quotation->payment_made, 2) }}
                                </p>
                            </div>
                            <div class="col-sm-4">
                                <label class="data-label d-block">System Database Creation Date</label>
                                <p class="text-muted small mb-0 mt-1">
                                    {{ $quotation->created_at ? $quotation->created_at->format('d M, Y \a\t H:i A') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Action Panel -->
            <div class="bg-light p-4 border-top d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <a href="{{ route('insurance_broking.quotations.list') }}" class="text-decoration-none text-muted fw-medium small d-inline-flex align-items-center gap-2 group-link">
                    <i class="bi bi-arrow-left border-end pe-2"></i>
                    <span>Back to Registry Index</span>
                </a>

                <div class="d-flex flex-wrap gap-2">
                    <!-- Edit Link Button -->
                    <a href="{{ route('insurance_broking.quotations.edit', $quotation->id) }}" class="btn btn-outline-secondary d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 small font-weight-semibold shadow-sm">
                        <i class="bi bi-pencil-square"></i>
                        <span>Edit Quote</span>
                    </a>

                    <!-- Download PDF Link Button -->
                    <a href="{{ route('insurance_broking.quotations.pdf', $quotation->id) }}" class="btn btn-danger d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 small font-weight-semibold shadow-sm" style="background-color: var(--brand-primary); border-color: var(--brand-primary);">
                        <i class="bi bi-file-earmark-pdf"></i>
                        <span>Download PDF</span>
                    </a>

                    <!-- Print Fallback Button -->
                    <button onclick="window.print()" class="btn btn-dark d-inline-flex align-items-center justify-content-center gap-2 px-3 py-2 small font-weight-semibold shadow-sm">
                        <i class="bi bi-printer"></i>
                        <span>Print</span>
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection