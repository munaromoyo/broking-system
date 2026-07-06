@extends('layouts.app')

@section('title', $section)
@section('content')
<div class="container-fluid custom_container">
    {{-- Global Messaging --}}
    <div class="mt-3">
        {{-- Look for 'success' or the generic 'msg' just in case --}}
        @if(session('success') || session('msg'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                <strong>Success!</strong> {{ session('success') ?? session('msg') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Look for custom try/catch 'error' messages --}}
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                <strong>Error!</strong> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Look for request layer validation array errors --}}
        @if($errors->any())
            <div class="alert alert-danger shadow-sm">
                <strong>Validation Errors:</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- --- 1. MODULE: SLIP REGISTRATION --- --}}
    @if($action == "register_slip")

    @push('styles')
<style>
    /* Professional Dashboard UI */

    .form-section-header {
    display: block;
    font-size: 16px;
    font-weight: 700;
    color: #e20613;
    margin: 30px 0 15px 0;
    padding-bottom: 5px;
    border-bottom: 1px solid #e2e8f0;
}

textarea.form_control {
    min-height: 100px;
    resize: vertical;
}

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
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .form-title { 
        font-size: 22px; 
        font-weight: 700; 
        color: #1e293b;
        border-left: 5px solid #e20613; /* Revolution Corporate Red */
        padding-left: 15px;
    }

    /* Grid System */
    .form_grid { 
        display: grid; 
        grid-template-columns: 1fr 1fr 1fr; 
        gap: 20px 25px; 
    }
    .full-width { grid-column: span 3; }
    .half-width { grid-column: span 2; }

    .form_group label { 
        display: block; 
        font-size: 11px; 
        font-weight: 800; 
        color: #64748b; 
        margin-bottom: 8px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .form_control { 
        width: 100%; 
        padding: 10px 14px; 
        border: 1px solid #cbd5e1; 
        border-radius: 8px; 
        font-size: 14px; 
        transition: all 0.2s;
        background-color: #fcfcfc;
    }

    .form_control:focus { 
        border-color: #e20613; 
        outline: none; 
        box-shadow: 0 0 0 4px rgba(226, 6, 19, 0.08);
    }

    /* Financial Inputs */
    .financial-input { border-left: 3px solid #e20613; font-weight: 600; }
    
    /* Buttons */
    .btn-register {
        background: #e20613;
        color: white;
        border: none;
        padding: 14px 40px;
        border-radius: 8px;
        font-weight: 700;
        transition: all 0.3s;
    }
    .btn-register:hover { background: #b90510; }

    .btn-dashboard-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.875rem;
        color: #64748b;
        background-color: #fff;
        border: 1px solid #e2e8f0;
        text-decoration: none;
    }
    
    .custom_container { background-color: #f8fafc; padding: 30px; min-height: 100vh; }
    .form_body { background: #fff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); padding: 40px; max-width: 1100px; margin: 0 auto; }
    .form-title { font-size: 22px; font-weight: 700; color: #1e293b; }
    .form-section-header { font-size: 13px; font-weight: 800; text-transform: uppercase; color: #64748b; margin: 30px 0 15px 0; display: block; border-bottom: 1px solid #f1f5f9; }
    .form_grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; }
    .full-width { grid-column: span 3; }
    .half-width { grid-column: span 2; }
    .form_control { width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; }
    .btn-register { background: #e20613; color: white; border: none; padding: 15px 40px; border-radius: 8px; font-weight: 700; cursor: pointer; }
    .btn-dashboard-back { text-decoration: none; color: #64748b; font-size: 14px; }

</style>
@endpush

    <div id="signup_form" class="form_body">
        <div class="form-heading d-flex justify-content-between align-items-center flex-wrap gap-3 pb-3 mb-4 border-bottom">
            <div class="form-title">
                Slip Registration 
                @if(request('clone_id'))
                    <small class="text-primary" style="font-size: 13px; margin-left: 10px;">(Template #{{ request('clone_id') }})</small>
                @endif
            </div>       
        </div>

        <form id="signupform" method="POST" action="{{ route('insurance_broking.store') }}">        
            @csrf
            <!-- Crucial Hidden Action Field for Controller Routing -->
            <input type="hidden" name="action" value="register_slip">

            <span class="form-section-header">1. Client & Basic Policy Info</span>
            <div class="form_grid">
                
                <div class="form_group full-width">
                    <label>Insured Party*</label>
                    <select class="form_control" id="client_select" name="insured" required>
                        <option value="">-- Select Client --</option>
                        @foreach($clients as $client)
                            @php $cName = $client->client_name; @endphp
                            <option value="{{ $cName }}" {{ old('insured', $cloneData['insured'] ?? '') == $cName ? 'selected' : '' }}>
                                {{ $cName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group">
                    <label>Lead Insurer*</label>
                    <select class="form_control" name="insurer" required>
                        <option value="">-- Select Insurer --</option>
                        @foreach($insurers as $ins)
                            @php $insName = $ins->insurer_name; @endphp
                            <option value="{{ $insName }}" {{ old('insurer', $cloneData['insurer'] ?? '') == $insName ? 'selected' : '' }}>
                                {{ $insName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group full-width">
                    <label>Nature of Business</label>
                    <textarea class="form_control" id="nature_of_business" name="nature_of_business" placeholder="Nature of Business...">{{ old('nature_of_business', $cloneData['nature_of_business'] ?? '') }}</textarea>
                </div>

                <div class="form_group half-width">
                    <label>Principal Address*</label>
                    <input type="text" class="form_control" id="principal_address" name="principal_address" value="{{ old('principal_address', $cloneData['principal_address'] ?? '') }}" required>
                </div>
                
                <div class="form_group">
                    <label>Insurance Policy Type*</label>
                    <select class="form_control" id="insurance_policy" name="insurance_policy" required>
                        <option value="">-- Select Policy --</option>
                        @foreach($policies as $pol)
                            @php $pName = $pol->policy_name; @endphp
                            <option value="{{ $pName }}" {{ old('insurance_policy', $cloneData['insurance_policy'] ?? '') == $pName ? 'selected' : '' }}>
                                {{ $pName }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group">
                    <label>Policy Start Date*</label>
                    <input type="date" class="form_control" name="policy_start_date" value="{{ old('policy_start_date') }}" required>
                </div>

                <div class="form_group">
                    <label>Policy Expiry Date*</label>
                    <input type="date" class="form_control" name="policy_expiry_date" value="{{ old('policy_expiry_date') }}" required>
                </div>
                
                <div class="form_group">
                    <label>Location of Risk*</label>
                    <input type="text" class="form_control" name="location_of_risk" value="{{ old('location_of_risk', $cloneData['location_of_risk'] ?? '') }}" required>
                </div>
            </div>

            <span class="form-section-header">2. Premium Calculations</span>
            <div class="form_grid">
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
                    <input type="number" step="0.01" class="form_control financial-input" name="total_sum_insured" value="{{ old('total_sum_insured', $cloneData['total_sum_insured'] ?? '') }}" required>
                </div>
                <div class="form_group">
                    <label>Basic Rate (%)*</label>
                    <input type="number" step="0.0001" class="form_control" name="basic_rate" value="{{ old('basic_rate', $cloneData['basic_rate'] ?? '') }}" required>
                </div>

                <div class="form_group">
                    <label>Basic Premium*</label>
                    <input type="number" step="0.01" class="form_control" id="basic_premium" name="basic_premium" value="{{ old('basic_premium', $cloneData['basic_premium'] ?? '') }}" required>
                </div>

                <div class="form_group">
                    <label>Discount Rate</label>
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
                    <input type="number" step="0.01" class="form_control" id="gross_premium_slip" name="gross_premium" value="{{ old('gross_premium', $cloneData['gross_premium'] ?? '') }}" readonly>
                </div>
            </div>

            <span class="form-section-header">3. Commission & Settlement</span>
            <div class="form_grid">
                <div class="form_group">
                    <label>Commission Rate (%)*</label>
                    <input type="number" step="0.0001" class="form_control" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', $cloneData['commission_rate'] ?? '') }}" required>
                </div>
                <div class="form_group">
                    <label>Commission Amount*</label>
                    <input type="number" step="0.01" class="form_control financial-input" id="commission_amount" name="commission_amount" value="{{ old('commission_amount', $cloneData['commission_amount'] ?? '') }}" required readonly>
                </div>
                <div class="form_group">
                    <label>Insurer Premium</label>
                    <input type="number" step="0.01" class="form_control" id="insurer_premium" name="insurer_premium" value="{{ old('insurer_premium', $cloneData['insurer_premium'] ?? '') }}" readonly>
                </div>
                <div class="form_group">
                    <label>Total Payment Made*</label>
                    <input type="number" step="0.01" class="form_control" name="payment_made" value="{{ old('payment_made') }}" required>
                </div>
                <div class="form_group full-width">
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

            <span class="form-section-header">4. Coverage Details & Specific Clauses</span>
            <div class="form_grid">
                <textarea class="form_control" name="scope_of_cover" id="scope_of_cover" placeholder="Scope of Cover*" required>{{ old('scope_of_cover', $cloneData['scope_of_cover'] ?? '') }}</textarea>
                <textarea class="form_control" name="property_insured" placeholder="Property Insured*" required>{{ old('property_insured', $cloneData['property_insured'] ?? '') }}</textarea>
                <textarea class="form_control" name="extensions" placeholder="Extended Coverage">{{ old('extensions', $cloneData['extensions'] ?? '') }}</textarea>
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

    @push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Data Injection from Laravel
    const policyData = @json($policy_lookup);
    const clientData = @json($client_lookup);

    // 2. Element Mapping
    const fields = {
        basic: document.getElementById('basic_premium'),
        discRate: document.getElementById('discount_rate'),
        commRate: document.getElementById('commission_rate'),
        discOut: document.getElementById('discount'),
        commOut: document.getElementById('commission_amount'),
        levyOut: document.getElementById('premium_levy'),
        insOut: document.getElementById('insurer_premium'),
        grossOut: document.getElementById('gross_premium_slip'),
        policySelect: document.getElementById('insurance_policy'),
        scopeField: document.getElementById('scope_of_cover'),
        clientSelect: document.getElementById('client_select'),
        natureField: document.getElementById('nature_of_business'),
        addressField: document.getElementById('principal_address')
    };

    

    // 3. Calculation Logic
    function calculatePremium() {
        // 1. Debug what the function is reading
    console.log("Fields object:", fields);
    console.log("Basic Value:", fields.basic?.value);

        const basic = parseFloat(fields.basic.value) || 0;
        const dRate = parseFloat(fields.discRate.value) || 0;
        const cRate = parseFloat(fields.commRate.value) || 0;

        // Normalize rates dynamically (converts 15 to 0.15, handles 0.15 gracefully)
        const disRATE = normalizeRate(dRate);
        const comRate = normalizeRate(cRate);

        const discountAmt = basic * disRATE;
        const netPremium  = basic - discountAmt;
        const commAmt     = netPremium * comRate;
        const levyAmt     = netPremium * 0.05; 
        
        const insurerAmt  = netPremium - commAmt; 
        const grossAmt    = netPremium + levyAmt;

    

        if(fields.discOut)  fields.discOut.value  = discountAmt.toFixed(2);
        if(fields.commOut)  fields.commOut.value  = commAmt.toFixed(2);
        if(fields.levyOut)  fields.levyOut.value  = levyAmt.toFixed(2);
        if(fields.insOut)   fields.insOut.value   = insurerAmt.toFixed(2);
        if(fields.grossOut) fields.grossOut.value = grossAmt.toFixed(2);
    }

    function normalizeRate(rate) {
    // If the user typed something like 15, convert it to 0.15
    // If they typed 0.15, leave it as 0.15
    if (rate > 1) {
        return rate / 100;
    }
    return rate;
}

    // 4. Event Listeners
    // Premium Inputs
    [fields.basic, fields.discRate, fields.commRate].forEach(el => {
        if(el) el.addEventListener('input', calculatePremium);
    });

    // Policy Selection
    if (fields.policySelect && fields.scopeField) {
        fields.policySelect.addEventListener('change', function() {
            fields.scopeField.value = policyData[this.value] || "";
        });
    }

    // Client Selection
    if (fields.clientSelect) {
        fields.clientSelect.addEventListener('change', function() {
            const selected = clientData[this.value];
            fields.natureField.value = selected ? selected.nature : "";
            fields.addressField.value = selected ? selected.address : "";
        });
    }
});
</script>
@endpush


    {{-- --- 2. MODULE: CLIENT REGISTRATION --- --}}
    @elseif($action == "register_client")
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px;">
        <!-- <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <div><h5 class="mb-0"><i class="fa fa-user-plus me-2"></i>New Client Registration</h5></div>
            <div class="d-flex gap-2">
                <a href="{{ route('clients.list') }}" class="btn-back-header">
                    <i class="fa fa-list"></i> Registry
                </a>
            </div>
        </div> -->
        <div class="card-header bg-primary text-white py-4 px-4 position-relative d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1 fw-bold text-start">
                    <i class="fa fa-user-plus me-2"></i>New Client Registration
                </h4>
                <p class="small mb-0 opacity-75 text-start">Please fill in all required fields marked with (*)</p>
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('clients.list') }}" class="btn btn-sm btn-outline-light bg-transparent text-white border border-white opacity-90 btn-back-header">
                    <i class="fa fa-list me-1"></i> Registry
                </a>
            </div>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('insurance_broking.store') }}" method="POST" id="signupform" class="needs-validation">
                @csrf
                
                <!-- Crucial Hidden Action Field for Controller Routing -->
                <input type="hidden" name="action" value="register_client">

                <!-- Client Name and Type -->
                <div class="row g-3 mb-4">
                    <div class="col-md-8">
                        <label for="client_name" class="form-label fw-semibold text-secondary">Client Name*</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="client_name" name="client_name" 
                               placeholder="Enter Full Name" 
                               value="{{ old('client_name') }}" required>
                    </div>

                    <div class="col-md-4">
                        <label for="client_type" class="form-label fw-semibold text-secondary">Client Type*</label>
                        <select class="form-select form-select-lg fs-6" id="client_type" name="client_type" required>
                            <option value="" disabled {{ !old('client_type') ? 'selected' : '' }}>Choose type...</option>
                            <option value="Individual" {{ old('client_type') == 'Individual' ? 'selected' : '' }}>Individual Client</option>
                            <option value="Corporate" {{ old('client_type') == 'Corporate' ? 'selected' : '' }}>Corporate Client</option>
                        </select>
                    </div>
                </div>

                <!-- Nature of Business -->
                <div class="mb-4">
                    <label for="nature_of_business" class="form-label fw-semibold text-secondary">Nature of Business*</label>
                    <textarea class="form-control" id="nature_of_business" name="nature_of_business" rows="3" 
                              placeholder="Describe business activities..." required>{{ old('nature_of_business') }}</textarea>
                </div>

                <!-- Address Information -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="physical_address" class="form-label fw-semibold text-secondary">Physical Address*</label>
                        <input type="text" class="form-control" id="physical_address" name="physical_address" 
                               placeholder="Street / Plot Number"
                               value="{{ old('physical_address') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="postal_address" class="form-label fw-semibold text-secondary">Postal Address*</label>
                        <input type="text" class="form-control" id="postal_address" name="postal_address" 
                               placeholder="P.O. Box"
                               value="{{ old('postal_address') }}" required>
                    </div>
                </div>
                
                <!-- Contact Details -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="contact_number" class="form-label fw-semibold text-secondary">Contact Number*</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa fa-phone text-muted"></i></span>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number" 
                                   placeholder="+260 9xx xxx xxx"
                                   value="{{ old('contact_number') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="email_address" class="form-label fw-semibold text-secondary">Email Address*</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fa fa-envelope text-muted"></i></span>
                            <input type="email" class="form-control" id="email_address" name="email_address" 
                                   placeholder="client@example.com"
                                   value="{{ old('email_address') }}" required>
                        </div>
                    </div>
                </div>

                <hr class="my-4 opacity-25">

                <!-- Form Actions -->
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <button type="reset" class="btn btn-light border px-4 py-2 text-secondary">Clear Form</button>
                    <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm" 
                            onclick="return confirm('Are you sure you want to complete the registration?');">
                        Confirm Registration
                    </button>
                </div>
            </form>
        </div>
    </div>

   {{-- --- 3. MODULE: POLICY REGISTRATION --- --}}
    @elseif($action == "register_policy")
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px;">
        <div class="card-header bg-primary text-white py-3 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0"><i class="fa fa-shield-halved me-2"></i>New Policy Registration</h5>
                <small class="opacity-75">Enter details below to initialize a new insurance policy.</small>
            </div>
        </div>
        <div class="card-body p-4 p-md-5">
            <form action="{{ route('insurance_broking.store') }}" method="POST" id="signupform" class="row g-4">
                @csrf
    
                <!-- Crucial Hidden Action Field for Controller Routing -->
                <input type="hidden" name="action" value="register_policy">
                
                <!-- Policy Name & Class -->
                <div class="col-md-7">
                    <label for="policy_name" class="form-label fw-semibold text-secondary">Name of Policy*</label>
                    <input type="text" class="form-control" id="policy_name" name="policy_name" 
                           placeholder="e.g. Comprehensive Executive Cover" 
                           value="{{ old('policy_name') }}" required>
                </div>

                <div class="col-md-5">
                    <label for="class_of_policy" class="form-label fw-semibold text-secondary">Class of Policy*</label>
                    <select class="form-select" id="class_of_policy" name="class_of_policy" required>
                        <option value="" selected disabled>Select Category</option>
                        @php
                            $classes = ["Fire Insurance", "Miscellaneous Accident Insurance", "Motor Insurance", "Engineering Insurance", "Marine Insurance", "Agriculture and Livestock Insurance", "Bonds and Guarantees", "Life Assurance"];
                        @endphp
                        @foreach ($classes as $class)
                            <option value="{{ $class }}" {{ old('class_of_policy') == $class ? 'selected' : '' }}>
                                {{ $class }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Scope of Cover -->
                <div class="col-12">
                    <label for="scope_of_cover_policy" class="form-label fw-semibold text-secondary">Scope of Cover*</label>
                    <textarea class="form-control" id="scope_of_cover_policy" name="scope_of_cover_policy" 
                              placeholder="Describe the coverage limits and inclusions..." 
                              rows="4" required>{{ old('scope_of_cover_policy') }}</textarea>
                </div>

                <!-- Remarks -->
                <div class="col-12">
                    <label for="remarks_policy" class="form-label fw-semibold text-secondary">Remarks</label>
                    <textarea class="form-control" id="remarks_policy" name="remarks_policy" 
                              placeholder="Internal notes or additional information..." 
                              rows="2">{{ old('remarks_policy') }}</textarea>
                </div>
                

                <!-- Action Buttons -->
                <div class="col-12 pt-3 border-top mt-4">
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <button type="reset" class="btn btn-light border px-4 py-2 me-md-2">Reset Form</button>
                        <button type="submit" class="btn btn-primary px-5 py-2 fw-bold shadow-sm" 
                                onclick="return confirm('Are you sure you want to register this Policy?');">
                            Register Policy
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- --- 4. MODULE: INSURER REGISTRATION --- --}}
        @elseif($action == "register_insurer")
        <div class="form-container">
            <div class="card shadow-sm border-0 mx-auto" style="max-width: 600px;">
                <!-- Card Header with Back Link -->
                <div class="card-header bg-primary text-white text-center position-relative">
                    <h4 class="mb-0">New Insurer Registration</h4>
                    <small class="opacity-75">Register a new insurance partner</small>
                </div>
                
                <div class="card-body p-4">
                    {{-- Validation Errors Alert --}}
                    @if ($errors->any())
                        <div class="alert alert-danger mb-4">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('insurance_broking.store') }}" method="POST" id="signupform">
                        @csrf
                        <!-- Crucial Hidden Action Field for Controller Routing -->
                        <input type="hidden" name="action" value="register_insurer">

                        <!-- Insurer Name -->
                        <div class="mb-3">
                            <label for="insurer_name" class="form-label">Insurer Name <span class="text-danger">*</span></label>
                            <input type="text" 
                                class="form-control shadow-sm" 
                                id="insurer_name" 
                                name="insurer_name" 
                                placeholder="e.g. Global Assurance Ltd" 
                                value="{{ old('insurer_name') }}" 
                                required>
                        </div>

                        <!-- Insurer Type -->
                        <div class="mb-3">
                            <label for="insurer_type" class="form-label">Insurer Type <span class="text-danger">*</span></label>
                            <select class="form-select shadow-sm" id="insurer_type" name="insurer_type" required>
                                <option value="" disabled {{ old('insurer_type') ? '' : 'selected' }}>Select Type</option>
                                <option value="General" {{ old('insurer_type') == 'General' ? 'selected' : '' }}>General</option>
                                <option value="Life" {{ old('insurer_type') == 'Life' ? 'selected' : '' }}>Life</option>
                            </select>
                        </div>

                        <!-- Physical Address -->
                        <div class="mb-3">
                            <label for="physical_address" class="form-label">Physical Address <span class="text-danger">*</span></label>
                            <textarea class="form-control shadow-sm" 
                                    id="physical_address" 
                                    name="physical_address" 
                                    rows="2" 
                                    placeholder="123 Business Plaza, City Center" 
                                    required>{{ old('physical_address') }}</textarea>
                        </div>

                        <!-- Postal Address (Added to match your original) -->
                        <div class="mb-4">
                            <label for="postal_address" class="form-label">Postal Address</label>
                            <input type="text" 
                                class="form-control shadow-sm" 
                                id="postal_address" 
                                name="postal_address" 
                                placeholder="P.O. Box 4567, City" 
                                value="{{ old('postal_address') }}">
                        </div>

                        <!-- Submit Button with Confirmation -->
                        <div class="d-grid gap-2">
                            <button type="submit" 
                                    class="btn btn-primary btn-register" 
                                    onclick="return confirm('Are you sure you want to register Insurer?');">
                                Register Insurer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    {{-- --- 5. MODULE: VEHICLE REGISTRATION --- --}}
@elseif($action == "register_vehicle")
    <!-- HEADER LINKS & STATUS -->
    <div class="alert alert-success border-0 shadow-sm mb-4 d-flex justify-content-between align-items-center">
        <a href="{{ route('insurance_broking.register', ['action' => 'view_vehicle_list']) }}" class="alert-link" target="_blank">View Vehicle List here.</a>
        <span class="badge bg-success">System Active</span>
    </div>

    <!-- TABS NAVIGATION -->
    <ul class="nav nav-tabs mb-3" id="vehicleTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single" type="button" role="tab">Single Registration</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk" type="button" role="tab">Bulk CSV Import</button>
        </li>
    </ul>

    <div class="tab-content" id="vehicleTabContent">
        <!-- SINGLE REGISTRATION TAB -->
        <div class="tab-pane fade show active" id="single" role="tabpanel">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">New Vehicle Registration</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('insurance_broking.store') }}" method="POST" class="needs-validation" novalidate>
                        @csrf
                        <!-- Crucial Hidden Action Field for Controller Routing -->
                        <input type="hidden" name="action" value="register_vehicle">

                        <div class="row g-3">
                            <!-- Reference Section -->
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Slip Number*</label>
                                <input type="text" class="form-control" name="slip_number" value="{{ old('slip_number') }}" required>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Insurer*</label>
                                <select class="form-select" name="insurer_name" required>
                                    <option value="" disabled selected>Select Insurer</option>
                                    @foreach($insurers as $insurer)
                                        <option value="{{ $insurer->insurer_name }}">{{ $insurer->insurer_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label fw-bold">Client Name*</label>
                                <select class="form-select" name="client_name" required>
                                    <option value="" disabled selected>Select Client</option>
                                    @foreach($clients as $client)
                                        <option value="{{ $client->client_name }}">{{ $client->client_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Vehicle Specification -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Registration No.*</label>
                                <input type="text" class="form-control" name="reg_number" placeholder="e.g. ABC 1234" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Make & Model*</label>
                                <input type="text" class="form-control" name="vehicle_make" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Chassis Number*</label>
                                <input type="text" class="form-control" name="chassis_number" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Engine Number*</label>
                                <input type="text" class="form-control" name="engine_number" required>
                            </div>

                            <!-- Policy Details -->
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Policy Type*</label>
                                <select class="form-select" name="policy_type" required>
                                    <option value="" disabled selected>Select Coverage Type</option>
                                    @foreach(["Road Traffic Act", "Motor Full Third Party Policy", "Fleet Motor Full Third Party Policy", "Motor Full Third Party, Fire and Theft Policy", "Motor Comprehensive Policy"] as $type)
                                        <option value="{{ $type }}">{{ $type }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Start Date*</label>
                                <input type="date" class="form-control" name="policy_start_date" required>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label fw-bold">Expiry Date*</label>
                                <input type="date" class="form-control" name="policy_expiry_date" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Sum Insured*</label>
                                <div class="input-group">
                                    <select class="form-select flex-grow-0 w-25" name="policy_currency" required>
                                        <option value="ZMW">ZMW</option>
                                        <option value="USD">USD</option>
                                    </select>
                                    <input type="number" step="0.01" class="form-control" name="sum_insured" placeholder="0.00" required>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-bold">Total Premium*</label>
                                <input type="number" step="0.01" min="0.01" class="form-control" name="total_premium" placeholder="0.00" required>
                            </div>
                            

                            <div class="col-12 mt-4 text-end">
                                <button class="btn btn-secondary me-2" type="reset">Clear Form</button>
                                <button type="submit" class="btn btn-primary px-5" onclick="return confirm('Are you sure you want to register Vehicle?');">
                                    Register Vehicle
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- BULK IMPORT TAB -->
        <div class="tab-pane fade" id="bulk" role="tabpanel">
            <div class="card shadow-sm border-primary text-center py-5">
                <div class="card-body">
                    <i class="fa fa-file-csv text-primary mb-3" style="font-size: 3.5rem;"></i>
                    <h4 class="fw-bold">Bulk Upload Vehicles</h4>
                    <p class="text-muted mb-4">Process large lists quickly using a CSV file.</p>
                    
                    <a href="{{ route('insurance_broking.vehicle_upload.download_template') }}" class="btn btn-outline-primary mb-4">
                        <i class="fa fa-download me-2"></i>Download CSV Template
                    </a>

                    <form action="{{ route('insurance_broking.store') }}" method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width: 450px;">
                        @csrf
                        <div class="input-group mb-3">
                            <input type="file" name="vehicle_csv" class="form-control" id="csvUpload" accept=".csv" required>
                        </div>
                        <!-- Crucial Hidden Action Field for Controller Routing -->
                        <input type="hidden" name="action" value="bulk_import_vehicle">
                        <button type="submit" class="btn btn-success btn-lg w-100 shadow-sm" onclick="return confirm('Are you sure you want to Start Bulk Upload?');">
                            <i class="fa fa-upload me-2"></i>Start Bulk Processing
                        </button>
                    </form>
                    
                    <div class="mt-4 small text-start mx-auto" style="max-width: 450px;">
                        <div class="alert alert-warning py-2 border-0">
                            <strong>Note:</strong> Ensure dates are in YYYY-MM-DD format and amounts do not contain commas.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- --- 6. MODULE: CLAIM REGISTRATION --- --}}
@elseif($action == "register_claim")
    <div class="card shadow-sm border-0 mx-auto" style="max-width: 900px;">
        <div class="card-header bg-primary text-white py-3 position-relative d-flex justify-content-between align-items-center">
            <!-- Centered Title -->
            <h4 class="mb-0 fw-bold">New Claim Registration</h4>
            
            <!-- Professional Glass-Style Back Link -->
        </div>
        
        <div class="card-body p-4 bg-light-subtle">
            {{-- Validation Errors --}}
            @if($errors->any())
                <div class="alert alert-danger mb-4 shadow-sm">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('insurance_broking.store') }}" method="POST" class="needs-validation" novalidate>
                @csrf
                
                <!-- Crucial Hidden Action Field for Controller Routing -->
                <input type="hidden" name="action" value="register_claim">

                <div class="row g-4">
                    <!-- Dates Section -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Claim Intimation Date*</label>
                        <input type="date" class="form-control" name="claim_intimation_date" 
                               value="{{ old('claim_intimation_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date of Loss*</label>
                        <input type="date" class="form-control" name="date_of_loss" 
                               value="{{ old('date_of_loss') }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Date of Notification*</label>
                        <input type="date" class="form-control" name="date_of_notification" 
                               value="{{ old('date_of_notification') }}" required>
                    </div>

                    <!-- Selection Section -->
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Insurer*</label>
                        <select class="form-select" name="insurer_name" required>
                            <option value="">Select Insurer</option>
                            @foreach($insurers as $insurer)
                                <option value="{{ $insurer->insurer_name }}" {{ old('insurer_name') == $insurer->insurer_name ? 'selected' : '' }}>
                                    {{ $insurer->insurer_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-bold">Client Name*</label>
                        <select class="form-select" name="client_name" required>
                            <option value="">Select Client</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->client_name }}" {{ old('client_name') == $client->client_name ? 'selected' : '' }}>
                                    {{ $client->client_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Financials & Status -->
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Type of Claim*</label>
                        <select class="form-select" name="type_of_claim" required>
                            <option value="">Select Policy</option>
                            @foreach($policies ?? [] as $policy)
                                <option value="{{ $policy->policy_name }}" {{ old('type_of_claim') == $policy->policy_name ? 'selected' : '' }}>
                                    {{ $policy->policy_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Claim Amount*</label>
                        <div class="input-group">
                            <select class="form-select flex-grow-0 w-auto" name="claim_currency" style="min-width: 85px;">
                                <option value="ZMW" {{ old('claim_currency', 'ZMW') == 'ZMW' ? 'selected' : '' }}>ZMW</option>
                                <option value="USD" {{ old('claim_currency') == 'USD' ? 'selected' : '' }}>USD</option>
                            </select>
                            <input type="number" step="0.01" class="form-control" name="claim_amount" 
                                   placeholder="0.00" value="{{ old('claim_amount') }}" required>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Claim Status*</label>
                        <select class="form-select" name="claim_status" required>
                            @foreach(["Pending Documents", "Pending Inspection", "Pending Discharge", "Pending Payment", "Settled", "Pending Loss Adjuster Report"] as $status)
                                <option value="{{ $status }}" {{ old('claim_status') == $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Text Areas -->
                    <div class="col-12">
                        <label class="form-label fw-bold">Details of Loss/Accident*</label>
                        <textarea class="form-control" name="details_of_loss" rows="3" required>{{ old('details_of_loss') }}</textarea>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Documents Received*</label>
                        <textarea class="form-control" name="documents_received" rows="2" required>{{ old('documents_received') }}</textarea>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top d-flex gap-2">
                    <button class="btn btn-primary px-5 py-2 shadow-sm" type="submit" onclick="return confirm('Are you sure you want to register Claim?');">
                        Register Claim
                    </button>
                    <button class="btn btn-light border px-4 py-2" type="reset">Reset</button>
                </div>
            </form>
        </div>
    </div>
{{-- --- 7. MODULE: QUOTATION REGISTRATION --- --}}
@elseif($action == "register_quote")

<style>
    .custom_container { background-color: #f8fafc; padding: 40px; font-family: 'Inter', sans-serif; min-height: 100vh; }
    .form_body { background: #ffffff; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.05); padding: 40px; max-width: 1100px; margin: 0 auto; }
    .form-title { font-size: 22px; font-weight: 700; color: #1e293b; border-left: 5px solid #0d6efd; padding-left: 15px; }
    .form-section-header { display: block; padding: 10px 0; margin: 20px 0; border-bottom: 2px solid #e2e8f0; color: #0d6efd; font-weight: 800; text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }
    
    .form_grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px 25px; }
    .full-width { grid-column: span 3; }
    .form_group label { display: block; font-size: 12px; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; }
    .form_control { width: 100%; padding: 10px 14px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 14px; background-color: #fcfcfc; }
    .form_control:focus { border-color: #0d6efd; outline: none; box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.08); background-color: #fff; }
    textarea.form_control { min-height: 95px; resize: vertical; }

    .btn-register { background: #0d6efd; color: white; border: none; padding: 16px 60px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(13, 110, 253, 0.25); }
    .btn-register:hover { background: #0b5ed7; transform: translateY(-1px); }
    .btn-dashboard-back { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 8px; color: #64748b; background-color: #f8fafc; border: 1px solid #e2e8f0; text-decoration: none; }
</style>

<div class="container-fluid custom_container">

    <div id="signup_form" class="form_body mt-4">
        <div class="form-heading d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            <div class="form-title">
                Quote Registration 
                @if(request('clone_id'))
                    <small class="text-primary" style="font-size: 13px;">(Template #{{ request('clone_id') }})</small>
                @endif
            </div>     
        </div>

        {{-- Laravel Action Route --}}
        <form id="signupform" method="POST" action="{{ route('broking.store') }}"> 
            @csrf {{-- CRITICAL: Protection for Laravel forms --}}
            
            @if (session('msg'))
                <div class="alert alert-success alert-dismissible fade show">
                    <strong>Success!</strong> {{ session('msg') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <span class="form-section-header">1. Client & Basic Policy Info</span>
            <div class="form_grid">
                <div class="form_group full-width">
                    <label for="client_select">Insured Party*</label>
                    <select class="form_control" id="client_select" name="insured" required>
                        <option value="">-- Select Client --</option>
                        @foreach ($all_clients as $client)
                            <option value="{{ $client }}" {{ (old('insured', $cloneData['insured'] ?? '') == $client) ? 'selected' : '' }}>
                                {{ $client }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group">
                    <label>Lead Insurer*</label>
                    <select class="form_control" name="insurer" required>
                        <option value="">-- Select Insurer --</option>
                        @foreach ($insurer_names as $ins)
                            <option value="{{ $ins['insurer_name'] }}" {{ (old('insurer', $cloneData['insurer'] ?? '') == $ins['insurer_name']) ? 'selected' : '' }}>
                                {{ $ins['insurer_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group">
                    <label>Insurance Policy Type*</label>
                    <select class="form_control" id="insurance_policy" name="insurance_policy" required>
                        <option value="">-- Select Policy --</option>
                        @foreach ($policy_names as $pol)
                            <option value="{{ $pol['policy_name'] }}" {{ (old('insurance_policy', $cloneData['insurance_policy'] ?? '') == $pol['policy_name']) ? 'selected' : '' }}>
                                {{ $pol['policy_name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form_group full-width">
                    <label>Nature of Business</label>
                    <textarea class="form_control" id="nature_of_business" name="nature_of_business" readonly>{{ old('nature_of_business', $cloneData['nature_of_business'] ?? '') }}</textarea>
                </div>

                <div class="form_group full-width">
                    <label>Principal Address*</label>
                    <input type="text" class="form_control" id="principal_address" name="principal_address" value="{{ old('principal_address', $cloneData['principal_address'] ?? '') }}" readonly>
                </div>
            </div>

            <span class="form-section-header">2. Premium Calculations</span>
            <div class="form_grid">
                <div class="form_group">
                    <label>Basic Premium*</label>
                    <input type="number" step="0.01" class="form_control" id="basic_premium" name="basic_premium" value="{{ old('basic_premium', $cloneData['basic_premium'] ?? '') }}" required>
                </div>
                <div class="form_group">
                    <label>Discount Rate (%)</label>
                    <input type="number" step="0.01" class="form_control" id="discount_rate" name="discount_rate" value="{{ old('discount_rate', $cloneData['discount_rate'] ?? '0') }}">
                </div>
                <div class="form_group">
                    <label>Discount Amount</label>
                    <input type="number" class="form_control" id="discount" name="discount" readonly tabindex="-1">
                </div>
                <div class="form_group">
                    <label>Premium Levy (5%)</label>
                    <input type="number" class="form_control" id="premium_levy" name="premium_levy" readonly tabindex="-1">
                </div>
                <div class="form_group full-width">
                    <label>Gross Premium (Client Payable)*</label>
                    <input type="number" class="form_control" id="gross_premium" name="gross_premium" readonly style="background-color: #f0f7ff; font-weight: bold; border: 2px solid #0d6efd;">
                </div>
            </div>

            <span class="form-section-header">3. Coverage Details & Specific Clauses</span>
            <div class="form_grid">
                <div class="form_group full-width">
                    <label>Scope of Cover*</label>
                    <textarea class="form_control" id="scope_of_cover" name="scope_of_cover" required>{{ old('scope_of_cover', $cloneData['scope_of_cover'] ?? '') }}</textarea>
                </div>
                <div class="form_group full-width">
                    <label>Property Insured*</label>
                    <textarea class="form_control" name="property_insured" required>{{ old('property_insured', $cloneData['property_insured'] ?? '') }}</textarea>
                </div>
                <div class="form_group full-width">
                    <label>Policy Excess/Deductible*</label>
                    <textarea class="form_control" name="excess_deductible" required>{{ old('excess_deductible', $cloneData['excess_deductible'] ?? '') }}</textarea>
                </div>
                <div class="form_group full-width">
                    <label>Cancellation Clause*</label>
                    <textarea class="form_control" name="cancellation_clause" readonly>{{ old('cancellation_clause', $cloneData['cancellation_clause'] ?? 'The Insured can cancel the policy at any time by notifying the Insurer...') }}</textarea>
                </div>
                <div class="form_group full-width">
                    <label>Specific Warranties*</label>
                    <textarea class="form_control" name="specific_warranties" required>{{ old('specific_warranties', $cloneData['specific_warranties'] ?? '') }}</textarea>
                </div>
            </div>

            <div class="mt-5 text-end">
                <button type="submit" name="register_quote" class="btn-register">
                    Complete Registration
                </button>
            </div>
        </form>
    </div>
</div>
@else
<h2>Please select an action from the menu.</h2>
@endif

</div>
@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Calculations logic for Slips
    const basicField = document.getElementById('basic_premium');
    const discField = document.getElementById('discount_rate');
    const grossField = document.getElementById('gross_premium');

    function calculateSlip() {
        const basic = parseFloat(basicField?.value) || 0;
        const rate = parseFloat(discField?.value) || 0;
        const discount = basic * rate;
        const net = basic - discount;
        const levy = net * 0.05;
        if(grossField) grossField.value = (net + levy).toFixed(2);
    }

    [basicField, discField].forEach(el => el?.addEventListener('input', calculateSlip));

    // 2. Lookup Logic (Laravel data)
    const policyData = @json($policy_lookup ?? []);
    const clientData = @json($client_lookup ?? []);

    document.getElementById('client_select')?.addEventListener('change', function() {
        const selected = this.value;
        if (clientData[selected]) {
            if(document.getElementById('nature_of_business')) document.getElementById('nature_of_business').value = clientData[selected].nature;
            if(document.getElementById('principal_address')) document.getElementById('principal_address').value = clientData[selected].address;
        }
    });
});
</script>
@endpush