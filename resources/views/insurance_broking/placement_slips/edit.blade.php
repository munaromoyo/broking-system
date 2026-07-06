@extends('layouts.app') {{-- Extends your master design layout --}}

@section('title', 'Edit Slip')

@section('content')
<style>
    /* Professional Dashboard UI */
    .custom_container { 
        background-color: #f8fafc; 
        padding: 40px; 
        font-family: 'Inter', 'Segoe UI', Roboto, sans-serif; 
        min-height: 100vh;
    }
    
    .slip-header { 
        background: #fff; 
        padding: 20px 30px; 
        border-radius: 10px; 
        box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
        margin-bottom: 30px;
        border-left: 6px solid #e20613; /* RIB Corporate Red */
    }
    .slip-header h3 { margin: 0; color: #1e293b; font-size: 22px; font-weight: 700; }
    
    .form-card { 
        background: #ffffff; 
        border-radius: 12px; 
        box-shadow: 0 10px 25px rgba(0,0,0,0.05); 
        padding: 40px;
        max-width: 1100px;
        margin: 0 auto;
    }

    .section-title {
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        color: #e20613;
        margin: 35px 0 20px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #f1f5f9;
    }

    /* Grid Layout */
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px 30px; }
    .full-width { grid-column: span 2; }

    .form-group { margin-bottom: 5px; }
    .form-group label { 
        display: block; 
        font-size: 13px; 
        font-weight: 600; 
        color: #475569; 
        margin-bottom: 6px; 
    }

    .form-control { 
        width: 100%; 
        padding: 10px 14px; 
        border: 1px solid #cbd5e1; 
        border-radius: 7px; 
        font-size: 14px; 
        color: #334155;
        transition: all 0.2s;
        background-color: #fcfcfc;
    }
    .form-control:focus { 
        border-color: #e20613; 
        outline: none; 
        box-shadow: 0 0 0 4px rgba(226, 6, 19, 0.08); 
        background-color: #fff;
    }
    
    textarea.form-control { min-height: 90px; line-height: 1.5; resize: vertical; }

    /* Financial Specifics */
    .premium-input { font-weight: 700; color: #1e293b; border-left: 3px solid #e20613; }

    .btn-submit {
        background: #e20613;
        color: white;
        border: none;
        padding: 15px 45px;
        border-radius: 8px;
        font-weight: 700;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s;
        box-shadow: 0 4px 10px rgba(226, 6, 19, 0.25);
    }
    .btn-submit:hover { background: #b90510; transform: translateY(-1px); }

    .alert-danger {
        background: #fff1f2;
        color: #991b1b;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #fecaca;
        margin-bottom: 25px;
    }
    .alert-success {
        background: #f0fdf4;
        color: #166534;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #bbf7d0;
        margin-bottom: 25px;
    }
</style>

<div class="custom_container">
    <div class="slip-header">
        <h3>Edit Placing Slip: {{ $placement->insured }}</h3>
    </div>

    <div class="form-card">
        <form method="POST" action="{{ route('insurance_broking.placement_slips.update', $placement->id) }}">
            @csrf

            {{-- Handle Flash System Messages --}}
            @if(session('error'))
                <div class="alert-danger">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert-success">{{ session('success') }}</div>
            @endif

            {{-- Handle Field Validation UI Errors Automatically --}}
            @if ($errors->any())
                <div class="alert-danger">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="section-title">1. Client & Policy Basics</div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Insured Party *</label>
                    <input type="text" class="form-control" name="insured" value="{{ old('insured', $placement->insured) }}">
                </div>
                <div class="form-group">
                    <label>Nature of Business</label>
                    <input type="text" class="form-control" name="nature_of_business" value="{{ old('nature_of_business', $placement->nature_of_business) }}">
                </div>
                <div class="form-group">
                    <label>Lead Insurer</label>
                    <input type="text" class="form-control" name="insurer" value="{{ old('insurer', $placement->insurer) }}">
                </div>
                <div class="form-group full-width">
                    <label>Principal Address *</label>
                    <input type="text" class="form-control" name="principal_address" value="{{ old('principal_address', $placement->principal_address) }}">
                </div>
                <div class="form-group">
                    <label>Policy Start Date *</label>
                    <input type="text" class="form-control" name="policy_start_date" value="{{ old('policy_start_date', $placement->policy_start_date) }}">
                </div>
                <div class="form-group">
                    <label>Policy Expiry Date</label>
                    <input type="text" class="form-control" name="policy_expiry_date" value="{{ old('policy_expiry_date', $placement->policy_expiry_date) }}">
                </div>
            </div>

            <div class="section-title">2. Coverage Clauses & Conditions</div>
            <div class="form-grid">
                <div class="form-group full-width">
                    <label>Cancellation Clause *</label>
                    <textarea class="form-control" name="cancellation_clause">{{ old('cancellation_clause', $placement->cancellation_clause) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Placing Slip Clause</label>
                    <textarea class="form-control" name="placing_slip_clause">{{ old('placing_slip_clause', $placement->placing_slip_clause) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Insurance Policy Type</label>
                    <textarea class="form-control" name="insurance_policy">{{ old('insurance_policy', $placement->insurance_policy) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Scope of Cover</label>
                    <textarea class="form-control" name="scope_of_cover">{{ old('scope_of_cover', $placement->scope_of_cover) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Policy Extensions</label>
                    <textarea class="form-control" name="extensions">{{ old('extensions', $placement->extensions) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Policy Excess / Deductible</label>
                    <textarea class="form-control" name="excess_deductible">{{ old('excess_deductible', $placement->excess_deductible) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Property Insured</label>
                    <textarea class="form-control" name="property_insured">{{ old('property_insured', $placement->property_insured) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Location of Risk</label>
                    <textarea class="form-control" name="location_of_risk">{{ old('location_of_risk', $placement->location_of_risk) }}</textarea>
                </div>
                <div class="form-group">
                    <label>Specific Warranties</label>
                    <textarea class="form-control" name="specific_warranties">{{ old('specific_warranties', $placement->specific_warranties) }}</textarea>
                </div>
                <div class="form-group full-width">
                    <label>Specific Conditions</label>
                    <textarea class="form-control" name="specific_conditions">{{ old('specific_conditions', $placement->specific_conditions) }}</textarea>
                </div>
            </div>

            <div class="section-title">3. Premium & Financials</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Policy Currency</label>
                    <input type="text" class="form-control" name="policy_currency" value="{{ old('policy_currency', $placement->policy_currency) }}"> 
                </div>
                <div class="form-group">
                    <label>Total Sum Insured</label>
                    <input type="number" step="any" class="form-control" name="total_sum_insured" value="{{ old('total_sum_insured', $placement->total_sum_insured) }}"> 
                </div>
                <div class="form-group">
                    <label>Basic Rate (%)</label>
                    <input type="number" step="0.0001" class="form-control" name="basic_rate" value="{{ old('basic_rate', $placement->basic_rate) }}"> 
                </div>
                <div class="form-group">
                    <label>Basic Premium</label>
                    <input type="number" step="any" class="form-control" name="basic_premium" value="{{ old('basic_premium', $placement->basic_premium) }}"> 
                </div>
                <div class="form-group">
                    <label>Discount Rate (%)</label>
                    <input type="number" step="any" class="form-control" name="discount_rate" value="{{ old('discount_rate', $placement->discount_rate) }}"> 
                </div>
                <div class="form-group">
                    <label>Discount Amount</label>
                    <input type="number" step="any" class="form-control" name="discount" value="{{ old('discount', $placement->discount) }}"> 
                </div>
                <div class="form-group">
                    <label>Premium Levy Rate (%)</label>
                    <input type="number" step="any" class="form-control" name="premium_levy_rate" value="{{ old('premium_levy_rate', $placement->premium_levy_rate) }}">
                </div>
                <div class="form-group">
                    <label>Premium Levy Amount</label>
                    <input type="number" step="any" class="form-control" name="premium_levy" value="{{ old('premium_levy', $placement->premium_levy) }}">
                </div>
                <div class="form-group">
                    <label>Broker Commission Rate (%)</label>
                    <input type="number" step="any" class="form-control percent-autoshift" name="commission_rate" value="{{ old('commission_rate', $placement->commission_rate) }}">
                </div>
                <div class="form-group">
                    <label>Broker Commission Amount</label>
                    <input type="number" step="any" class="form-control" name="commission_amount" value="{{ old('commission_amount', $placement->commission_amount) }}">
                </div>
                <div class="form-group">
                    <label>Insurer Premium</label>
                    <input type="number" step="any" class="form-control" name="insurer_premium" value="{{ old('insurer_premium', $placement->insurer_premium) }}">
                </div>
                <div class="form-group">
                    <label>Gross Premium (Payable)</label>
                    <input type="number" step="any" class="form-control premium-input" name="gross_premium" style="background-color: #fffafa;" value="{{ old('gross_premium', $placement->gross_premium) }}">
                </div>
            </div>

            <div class="section-title">4. Payment Information & Status</div>
            <div class="form-grid">
                <div class="form-group">
                    <label>Payment Method</label>
                    <input type="text" class="form-control" name="payment_method" value="{{ old('payment_method', $placement->payment_method) }}">
                </div>
                <div class="form-group">
                    <label>Payment Made</label>
                    <input type="number" step="any" min="0" class="form-control" name="payment_made" placeholder="0.00" value="{{ old('payment_made', $placement->payment_made) }}">
                </div>
                <div class="form-group">
                    <label>Premium Status</label>
                    <input type="text" class="form-control" name="premium_status" value="{{ old('premium_status', $placement->premium_status) }}">
                </div>
                <div class="form-group">
                    <label>Slip Status</label>
                    <input type="text" class="form-control" name="status" value="{{ old('status', $placement->status) }}">
                </div>
            </div>

            <div style="text-align: right; margin-top: 50px; padding-top: 30px; border-top: 2px solid #f1f5f9;">
                <button type="submit" class="btn-submit">Update Placing Slip Records</button>
            </div>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('input[type="number"]').forEach(input => {
    input.addEventListener('input', function() {
        if (this.value.includes(',')) {
            this.value = this.value.replace(/,/g, '');
        }
    });
});
</script>



@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Data Injection from Laravel
    const clientData = @json($client_lookup ?? []);

    // 2. Element Mapping (Using name attributes)
    const fields = {
        basicRate:   document.querySelector('input[name="basic_rate"]'),
        basic:       document.querySelector('input[name="basic_premium"]'),
        discRate:    document.querySelector('input[name="discount_rate"]'),
        discOut:     document.querySelector('input[name="discount"]'),
        levyRate:    document.querySelector('input[name="premium_levy_rate"]'),
        levyOut:     document.querySelector('input[name="premium_levy"]'),
        commRate:    document.querySelector('input[name="commission_rate"]'),
        commOut:     document.querySelector('input[name="commission_amount"]'),
        insOut:      document.querySelector('input[name="insurer_premium"]'),
        grossOut:    document.querySelector('input[name="gross_premium"]')
    };

    // // Helper: Safely converts whole percentage input values to decimal multipliers
    // function normalizeRate(rateValue) {
    //     // Since input labels explicitly specify Rate (%), always divide by 100
    //     return rateValue / 100;
    // }

    // Helper: Dynamically handles both whole percentages (15) and raw decimals (0.15)
    function normalizeRate(rateValue) {
        // If they typed a whole percentage (like 15 or 5), normalize it to a decimal (0.15 or 0.05)
        // If they typed a decimal (like 0.15), leave it alone.
        if (rateValue >= 1) {
            return rateValue / 100;
        }
        return rateValue;
    }

    // 3. Calculation Logic
    function calculatePremium() {
        const basic = parseFloat(fields.basic?.value) || 0;
        
        // Extract raw inputs
        const rawDiscRate = parseFloat(fields.discRate?.value) || 0;
        const rawLevyRate = parseFloat(fields.levyRate?.value) || 0;
        const rawCommRate = parseFloat(fields.commRate?.value) || 0;

        // Normalize rates dynamically
        const dMultiplier = normalizeRate(rawDiscRate);
        const lMultiplier = normalizeRate(rawLevyRate);
        const cMultiplier = normalizeRate(rawCommRate);



        // Financial formulas using our smart multipliers
        const discountAmt = basic * dMultiplier;
        const netPremium  = basic - discountAmt;
        const commAmt     = netPremium * cMultiplier;
        const levyAmt     = netPremium * lMultiplier; 
        
        const insurerAmt  = netPremium - commAmt; 
        const grossAmt    = netPremium + levyAmt;

        // Update output elements safely
        if(fields.discOut)  fields.discOut.value  = discountAmt.toFixed(2);
        if(fields.commOut)  fields.commOut.value  = commAmt.toFixed(2);
        if(fields.levyOut)  fields.levyOut.value  = levyAmt.toFixed(2);
        if(fields.insOut)   fields.insOut.value   = insurerAmt.toFixed(2);
        if(fields.grossOut) fields.grossOut.value = grossAmt.toFixed(2);
    }

    // 4. Event Listeners
    const inputTriggers = [
        fields.basicRate, 
        fields.basic, 
        fields.discRate, 
        fields.levyRate, 
        fields.commRate
    ];

    inputTriggers.forEach(el => {
        if(el) el.addEventListener('input', calculatePremium);
    });
});
</script>
@endpush




@endsection