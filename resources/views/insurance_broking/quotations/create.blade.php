@extends('layouts.app')

@section('title', $pageTitle)

@push('styles')
<style>
    .custom_container { 
        background-color: #f8fafc; 
        padding: 40px; 
        font-family: 'Inter', 'Segoe UI', sans-serif; 
        min-height: 100vh;
    }
    .form_body { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        padding: 40px;
        max-width: 1100px;
        margin: 0 auto;
    }
    .form-heading { 
        border-bottom: 2px solid #f1f5f9;
        margin-bottom: 30px;
        padding-bottom: 15px;
    }
    .form-title { 
        font-size: 22px; 
        font-weight: 700; 
        color: #1e293b;
        border-left: 5px solid #0d6efd; 
        padding-left: 15px;
    }
    .form-section-header {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #64748b;
        margin: 35px 0 15px 0;
        display: block;
        border-bottom: 1px solid #f1f5f9;
        padding-bottom: 5px;
    }
    .form_grid { 
        display: grid; 
        grid-template-columns: repeat(3, 1fr); 
        gap: 20px 25px; 
    }
    .full-width { grid-column: span 3; }
    .form_group label { 
        display: block; 
        font-size: 12px; 
        font-weight: 700; 
        color: #475569; 
        margin-bottom: 8px;
        text-transform: uppercase;
    }
    .form_control { 
        width: 100%; 
        padding: 10px 14px; 
        border: 1px solid #cbd5e1; 
        border-radius: 8px; 
        font-size: 14px; 
        background-color: #fcfcfc;
    }
    .form_control:focus { 
        border-color: #0d6efd; 
        outline: none; 
        box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08);
        background-color: #fff;
    }
    textarea.form_control { min-height: 95px; resize: vertical; line-height: 1.5; }
    .btn-register {
        background: #0d6efd;
        color: white;
        border: none;
        padding: 16px 60px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25);
        transition: all 0.3s;
    }
    .btn-register:hover { background: #0b5ed7; transform: translateY(-1px); }
    .btn-dashboard-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 500;
        color: #64748b;
        background-color: #f8fafc;
        border: 1px solid #e2e8f0;
        text-decoration: none;
        transition: all 0.2s;
    }
    .btn-dashboard-back:hover {
        background-color: #f1f5f9;
        color: #0d6efd;
    }
    .btn-back-header {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.4);
        color: white !important;
        padding: 6px 14px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }
    .btn-back-header:hover {
        background: rgba(255, 255, 255, 0.3);
    }
</style>
@endpush

@section('content')
<div class="container-fluid custom_container">
    
    {{-- Laravel Alert Feedback Messages --}}
    @if(session('msg'))
        <div class="alert alert-success alert-dismissible fade show mx-auto" style="max-width: 1100px;" role="alert">
            <strong>Success!</strong> {{ session('msg') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger mx-auto" style="max-width: 1100px;">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- MODULE 1: SLIP REGISTRATION --}}
    @if($action === "register_quote")
    <div id="signup_form" class="form_body">
        <div class="form-heading d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div class="form-title">
                Quote Registration 
                @if(request()->filled('clone_id'))
                    <small class="text-primary" style="font-size: 13px; margin-left: 10px;">(Template #{{ request('clone_id') }})</small>
                @endif
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('insurance_broking.quotations.list') }}" class="btn-dashboard-back text-primary border-primary-subtle">
                    <i class="fa fa-list"></i> <span>Quotations Registry</span>
                </a>
                <a href="{{ url('insurance-broking/dashboard') }}" class="btn-dashboard-back">
                    <i class="fa fa-arrow-left"></i> <span>Dashboard</span>
                </a>        
            </div>
        </div>

        <form id="signupform" method="POST" action="{{ route('insurance_broking.quotations.store') }}">
            @csrf
            <input type="hidden" name="register_slip" value="register_slip">

            <span class="form-section-header">1. Client & Basic Policy Info</span>
            <div class="form_grid mb-4">
                <div class="form_group full-width">
                    <label>Insured Party*</label>
                    <select class="form_control" id="client_select" name="insured" required>
                        <option value="">-- Select Client --</option>
                        @foreach($allClients as $client)
                            <option value="{{ $client }}" {{ old('insured', $cloneData['insured'] ?? '') == $client ? 'selected' : '' }}>
                                {{ $client }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group">
                    <label>Lead Insurer*</label>
                    <select class="form_control" name="insurer" required>
                        <option value="">-- Select Insurer --</option>
                        @foreach($insurerNames as $insName)
                            <option value="{{ $insName }}" {{ old('insurer', $cloneData['insurer'] ?? '') == $insName ? 'selected' : '' }}>
                                {{ $insName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group">
                    <label>Insurance Policy Type*</label>
                    <select class="form_control" id="insurance_policy" name="insurance_policy" required>
                        <option value="">-- Select Policy --</option>
                        @foreach($policyTypes as $pol)
                            <option value="{{ $pol->policy_name }}" {{ old('insurance_policy', $cloneData['insurance_policy'] ?? '') == $pol->policy_name ? 'selected' : '' }}>
                                {{ $pol->policy_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group">
                    <label>Location of Risk*</label>
                    <input type="text" class="form_control" name="location_of_risk" value="{{ old('location_of_risk', $cloneData['location_of_risk'] ?? '') }}" required>
                </div>

                <div class="form_group">
                    <label>Policy Start Date*</label>
                    <input type="date" class="form_control" name="policy_start_date" value="{{ old('policy_start_date', $cloneData['policy_start_date'] ?? '') }}" required>
                </div>

                <div class="form_group">
                    <label>Policy Expiry Date*</label>
                    <input type="date" class="form_control" name="policy_expiry_date" value="{{ old('policy_expiry_date', $cloneData['policy_expiry_date'] ?? '') }}" required>
                </div>

                <div class="form_group half-width">
                    <label>Principal Address*</label>
                    <input type="text" class="form_control" id="principal_address" name="principal_address" value="{{ old('principal_address', $cloneData['principal_address'] ?? '') }}" required>
                </div>

                <div class="form_group full-width">
                    <label>Nature of Business</label>
                    <textarea class="form_control" id="nature_of_business" name="nature_of_business" placeholder="Nature of Business...">{{ old('nature_of_business', $cloneData['nature_of_business'] ?? '') }}</textarea>
                </div>
            </div>

            <span class="form-section-header">2. Premium Calculations</span>
            <div class="form_grid mb-4">
                <div class="form_group">
                    <label>Policy Currency*</label>
                    <select class="form_control" name="policy_currency" required>
                        @foreach(["", "ZMW", "USD"] as $curr)
                            <option value="{{ $curr }}" {{ old('policy_currency', $cloneData['policy_currency'] ?? '') == $curr ? 'selected' : '' }}>
                                {{ $curr ?: 'Select' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group">
                    <label>Total Sum Insured*</label>
                    <input type="number" step="0.01" class="form_control financial-input" id="total_sum_insured" name="total_sum_insured" value="{{ old('total_sum_insured', $cloneData['total_sum_insured'] ?? '') }}" required>
                </div>

                <div class="form_group">
                    <label>Basic Rate (%)*</label>
                    <input type="number" step="0.0001" class="form_control" id="basic_rate" name="basic_rate" value="{{ old('basic_rate', $cloneData['basic_rate'] ?? '') }}" required>
                </div>

                <div class="form_group">
                    <label>Basic Premium*</label>
                    <input type="number" step="0.01" class="form_control" id="basic_premium" name="basic_premium" value="{{ old('basic_premium', $cloneData['basic_premium'] ?? '') }}" required>
                </div>

                <div class="form_group">
                    <label>Discount Rate (%)</label>
                    <input type="number" step="0.0001" class="form_control" id="discount_rate" name="discount_rate" value="{{ old('discount_rate', $cloneData['discount_rate'] ?? '') }}">
                </div>

                <div class="form_group">
                    <label>Discount Amount</label>
                    <input type="number" step="0.01" class="form_control" id="discount" name="discount" value="{{ old('discount', $cloneData['discount'] ?? '') }}" readonly>
                </div>

                <div class="form_group">
                    <label>Premium Levy Rate (%)</label>
                    <input type="number" step="0.01" class="form_control" name="premium_levy_rate" value="0.05" readonly>
                </div>

                <div class="form_group">
                    <label>Premium Levy Amount</label>
                    <input type="number" step="0.01" class="form_control" id="premium_levy" name="premium_levy" value="{{ old('premium_levy', $cloneData['premium_levy'] ?? '') }}" readonly>
                </div>

                <div class="form_group">
                    <label>Gross Premium (Client Payable)*</label>
                    <input type="number" step="0.01" class="form_control" id="gross_premium" name="gross_premium" value="{{ old('gross_premium', $cloneData['gross_premium'] ?? '') }}" readonly style="background-color: #fcfdfa; border-left: 3px solid #198754; font-weight: bold;">
                </div>
            </div>

            <span class="form-section-header">3. Policy Extensions & Loading</span>
            <div id="extensions_container" class="mb-4 bg-light p-3 rounded border" style="display: none;">
                <p class="text-muted small fw-bold mb-3 text-uppercase">Select matching structural risk extensions:</p>
                <div id="extensions_checkbox_grid" class="row g-3"></div>
            </div>

            <span class="form-section-header">4. Commission & Broker Settlement Breakdown</span>
            <div class="form_grid mb-4">
                <div class="form_group">
                    <label>Commission Rate (%)*</label>
                    <input type="number" step="0.0001" class="form_control" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', $cloneData['commission_rate'] ?? '') }}" required>
                </div>

                <div class="form_group">
                    <label>Commission Amount*</label>
                    <input type="number" step="0.01" class="form_control financial-input" id="commission_amount" name="commission_amount" value="{{ old('commission_amount', $cloneData['commission_amount'] ?? '') }}" required readonly>
                </div>

                <div class="form_group">
                    <label>Insurer Net Premium Remittance</label>
                    <input type="number" step="0.01" class="form_control" id="insurer_premium" name="insurer_premium" value="{{ old('insurer_premium', $cloneData['insurer_premium'] ?? '') }}" readonly>
                </div>

                <div class="form_group">
                    <label>Total Payment Made*</label>
                    <input type="number" step="0.01" class="form_control" name="payment_made" value="{{ old('payment_made', $cloneData['payment_made'] ?? '0.00') }}" required>
                </div>

                <div class="form_group">
                    <label>Payment Method / Terms*</label>
                    <select class="form_control" name="payment_method" required>
                        @foreach(["", "Upfront", "2 Instalments", "3 Instalments", "4 Instalments"] as $method)
                            <option value="{{ $method }}" {{ old('payment_method', $cloneData['payment_method'] ?? '') == $method ? 'selected' : '' }}>
                                {{ $method ?: 'Select' }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <span class="form-section-header">5. Coverage Details & Legal Provisions</span>
            <div class="form_grid mb-4">
                <textarea class="form_control" name="scope_of_cover" id="scope_of_cover" placeholder="Scope of Cover*" required>{{ old('scope_of_cover', $cloneData['scope_of_cover'] ?? '') }}</textarea>
                <textarea class="form_control" name="property_insured" placeholder="Property Insured*" required>{{ old('property_insured', $cloneData['property_insured'] ?? '') }}</textarea>
                <textarea class="form_control" name="extensions" placeholder="Extended Coverage Miscellaneous Details">{{ old('extensions', $cloneData['extensions'] ?? '') }}</textarea>
                <textarea class="form_control" name="excess_deductible" placeholder="Policy Excess/Deductible*" required>{{ old('excess_deductible', $cloneData['excess_deductible'] ?? '') }}</textarea>
                
                <textarea class="form_control" name="cancellation_clause" placeholder="Cancellation Clause*" readonly>{{ old('cancellation_clause', $cloneData['cancellation_clause'] ?? "The Insured can cancel the policy at any time by notifying the Insurer. If the Insurer wishes to cancel the Insured’s policy, the Insurer will give the Insured 30 days’ notice in writing. If the Insured pays the premium annually, the Insured will be allowed a pro-rata refund of the premium. If the premium is paid monthly, the Insured will not be allowed a refund of any premium") }}</textarea>
                <textarea class="form_control" name="placing_slip_clause" placeholder="Placing Slip Clause" readonly>{{ old('placing_slip_clause', $cloneData['placing_slip_clause'] ?? "The Placing slip has full force and effect of the insurance Contract before the policy is issued and remains in force until receipt of the signed policy document. The Placing slip shall be the Insurance Contract unless and until replaced by Policy Documents") }}</textarea>
                
                <textarea class="form_control" name="specific_warranties" placeholder="Specific Warranties*" required>{{ old('specific_warranties', $cloneData['specific_warranties'] ?? '') }}</textarea>
                <textarea class="form_control" name="specific_conditions" placeholder="Specific Conditions*" required>{{ old('specific_conditions', $cloneData['specific_conditions'] ?? '') }}</textarea>
            </div>

            <div style="margin-top: 50px; text-align: right; border-top: 2px solid #f1f5f9; padding-top: 30px;">
                <button id="btn_signup" type="submit" class="btn-register" onclick="return confirm('Are you sure you want to complete the registration?');">
                    Complete Registration
                </button>
            </div>
        </form>
    </div>

    {{-- MODULE 2: POTENTIAL CLIENT REGISTRATION --}}
    @elseif ($action === "register_potential_client")
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 800px; border-radius: 12px; overflow: hidden;">
        <div class="card-header bg-primary text-white py-4 px-4 position-relative d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1 fw-bold text-start">Potential Client Registration</h4>
                <p class="small mb-0 opacity-75 text-start">Please fill in all required fields marked with (*)</p>
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('clients.list') }}" class="btn-back-header">
                    <i class="fa fa-list"></i> Registry
                </a>
                <a href="{{ url('insurance-broking/dashboard') }}" class="btn-back-header">
                    <i class="fa fa-arrow-left"></i> Dashboard
                </a> 
            </div>
        </div>
        
        <div class="card-body p-4 p-md-5">
            <form method="POST" action="{{ route('potential_client.store') }}" class="needs-validation">
                @csrf
                
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label for="client_name" class="form-label fw-semibold text-secondary">Client Name*</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="client_name" name="client_name" value="{{ old('client_name') }}" placeholder="Enter Full Name" required>
                    </div>
    
                    <div class="col-md-4">
                        <label for="client_type" class="form-label fw-semibold text-secondary">Client Type*</label>
                        <select class="form-select form-select-lg fs-6" id="client_type" name="client_type" required>
                            <option value="" disabled {{ !old('client_type') ? 'selected' : '' }}>Choose type...</option>
                            <option value="Individual" {{ old('client_type') === 'Individual' ? 'selected' : '' }}>Individual Client</option>
                            <option value="Corporate" {{ old('client_type') === 'Corporate' ? 'selected' : '' }}>Corporate Client</option>
                        </select>
                    </div>
                </div>
    
                <div class="mb-4">
                    <label for="nature_of_business" class="form-label fw-semibold text-secondary">Nature of Business*</label>
                    <textarea class="form-control" id="nature_of_business" name="nature_of_business" rows="3" placeholder="Describe business activities..." required>{{ old('nature_of_business') }}</textarea>
                </div>
    
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="physical_address" class="form-label fw-semibold text-secondary">Physical Address*</label>
                        <input type="text" class="form-control" id="physical_address" name="physical_address" value="{{ old('physical_address') }}" placeholder="Street / Plot Number" required>
                    </div>
                    <div class="col-md-6">
                        <label for="postal_address" class="form-label fw-semibold text-secondary">Postal Address*</label>
                        <input type="text" class="form-control" id="postal_address" name="postal_address" value="{{ old('postal_address') }}" placeholder="P.O. Box" required>
                    </div>
                </div>
    
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="contact_number" class="form-label fw-semibold text-secondary">Contact Number*</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa fa-phone text-muted"></i></span>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" value="{{ old('contact_number') }}" placeholder="+260 9xx xxx xxx" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="email_address" class="form-label fw-semibold text-secondary">Email Address*</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control" id="email_address" name="email_address" value="{{ old('email_address') }}" placeholder="client@example.com" required>
                        </div>
                    </div>
                </div>
    
                <hr class="my-4 opacity-25">
    
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-light border px-4 py-2 text-secondary">Clear Form</button>
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm" onclick="return confirm('Are you sure you want to complete the registration?');">
                        Confirm Registration
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const clientLookup = @json($clientLookup ?? []);
    const policyLookup = @json($policyLookup ?? []);

    const clientSelect = document.getElementById('client_select');
    const policySelect = document.getElementById('insurance_policy');

    if (clientSelect) {
        clientSelect.addEventListener('change', function() {
            const data = clientLookup[this.value];
            document.getElementById('nature_of_business').value = data ? data.nature : "";
            document.getElementById('principal_address').value = data ? data.address : "";
        });
    }

    if (policySelect) {
        policySelect.addEventListener('change', function() {
            const scope = policyLookup[this.value];
            document.getElementById('scope_of_cover').value = scope || "";
        });
    }

    const sumInsuredIn = document.getElementById('total_sum_insured');
    const basicRateIn   = document.getElementById('basic_rate');
    const basicPremiumIn= document.getElementById('basic_premium');
    const discRateIn    = document.getElementById('discount_rate');
    const discAmtOut    = document.getElementById('discount');
    const levyOut       = document.getElementById('premium_levy');
    const grossOut      = document.getElementById('gross_premium');
    const commRateIn    = document.getElementById('commission_rate');
    const commAmtOut    = document.getElementById('commission_amount');
    const insPremOut    = document.getElementById('insurer_premium');

    function calculateInsuranceFinance() {
        let sumInsured = parseFloat(sumInsuredIn.value) || 0;
        let ratePercent = parseFloat(basicRateIn.value) || 0;
        
        if(sumInsured > 0 && ratePercent > 0 && document.activeElement !== basicPremiumIn) {
            basicPremiumIn.value = ((ratePercent / 100) * sumInsured).toFixed(2);
        }

        let basicPremium = parseFloat(basicPremiumIn.value) || 0;
        let discountRate = parseFloat(discRateIn.value) || 0;

        let discountAmount = (discountRate / 100) * basicPremium;
        let netPremiumBeforeExtensions = basicPremium - discountAmount;

        let extensionLoadingTotal = 0;
        document.querySelectorAll('.extension-card').forEach(card => {
            const checkbox = card.querySelector('.extension-toggle');
            const premiumInput = card.querySelector('.extension-premium-input');
            if (checkbox && (checkbox.checked || checkbox.disabled)) {
                extensionLoadingTotal += parseFloat(premiumInput.value) || 0;
            }
        });

        let totalNetPremium = netPremiumBeforeExtensions + extensionLoadingTotal;

        let premiumLevyAmount = totalNetPremium * 0.05;
        let grossPremiumTotal = totalNetPremium + premiumLevyAmount;

        let commissionRate = parseFloat(commRateIn.value) || 0;
        let commissionAmount = (commissionRate / 100) * totalNetPremium;
        
        let insurerNetRemittance = totalNetPremium - commissionAmount;

        discAmtOut.value = discountAmount.toFixed(2);
        levyOut.value    = premiumLevyAmount.toFixed(2);
        grossOut.value   = grossPremiumTotal.toFixed(2);
        commAmtOut.value = commissionAmount.toFixed(2);
        insPremOut.value = insurerNetRemittance.toFixed(2);
    }

    const triggerFields = [sumInsuredIn, basicRateIn, basicPremiumIn, discRateIn, commRateIn];
    triggerFields.forEach(element => {
        if (element) {
            element.addEventListener('input', calculateInsuranceFinance);
        }
    });
});
</script>
@endpush