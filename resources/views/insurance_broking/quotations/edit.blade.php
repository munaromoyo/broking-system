@extends('layouts.app')

@section('title', 'Edit Quotation - #' . $quotation->id)

@push('styles')
<style>
    :root {
        --brand-primary: var(--rib-red, #e20613);
        --brand-dark: var(--rib-dark, #1e293b);
    }
    .edit-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        overflow: hidden;
        margin-bottom: 2rem;
    }
    .edit-header {
        background-color: var(--brand-dark);
        color: #ffffff;
        padding: 1.5rem;
    }
    .form-section-title {
        font-size: 0.8rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: #475569;
        background-color: #f8fafc;
        padding: 0.75rem 1rem;
        border-radius: 0.375rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        border-left: 4px solid var(--brand-primary);
    }
    .form-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #64748b;
    }
    .form-control:focus, .form-select:focus {
        border-color: #cbd5e1;
        box-shadow: 0 0 0 0.15rem rgba(226, 6, 19, 0.1);
    }
    .calculation-block {
        background-color: #f1f5f9;
        border-radius: 0.5rem;
        padding: 1.25rem;
    }
    .readonly-display {
        background-color: #e2e8f0 !important;
        font-weight: 600;
        color: #0f172a;
    }
</style>
@endpush

@section('content')
<div class="row justify-content-center my-4">
    <div class="col-1xl col-xl-10">
        
        <div class="edit-card">
            <!-- Header Section -->
            <div class="edit-header d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h4 font-weight-bold text-white mb-1">Modify Quotation Registry Entry</h1>
                    <p class="small text-white-50 mb-0">Updating Reference Code: #{{ $quotation->id }}</p>
                </div>
                <a href="{{ route('insurance_broking.quotations.list') }}" class="btn btn-sm btn-outline-light">
                    Cancel & Exit
                </a>
            </div>

            <!-- Form Wrapper -->
            <form action="{{ route('insurance_broking.quotations.update', $quotation->id) }}" method="POST" id="quotationEditForm">
                @csrf
                @method('PUT')

                <div class="p-4 p-md-5">
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 small">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <!-- SECTION 1: Client & Insurer Identification -->
                    <div class="mb-5">
                        <div class="form-section-title">
                            <span>1. Client & Underwriter Identification</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="insured" class="form-label">Insured Legal Entity <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="insured" name="insured" value="{{ old('insured', $quotation->insured) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="insurer" class="form-label">Underwriting Insurer <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="insurer" name="insurer" value="{{ old('insurer', $quotation->insurer) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label for="principal_address" class="form-label">Principal Corporate Address</label>
                                <textarea class="form-control" id="principal_address" name="principal_address" rows="2">{{ old('principal_address', $quotation->principal_address) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="nature_of_business" class="form-label">Nature of Business Operation</label>
                                <textarea class="form-control" id="nature_of_business" name="nature_of_business" rows="2">{{ old('nature_of_business', $quotation->nature_of_business) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="policy_start_date" class="form-label">Risk Inception Date</label>
                                <input type="date" class="form-control" id="policy_start_date" name="policy_start_date" value="{{ old('policy_start_date', $quotation->policy_start_date ? \Carbon\Carbon::parse($quotation->policy_start_date)->format('Y-m-get_class') : '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="policy_expiry_date" class="form-label">Risk Term Expiry Date</label>
                                <input type="date" class="form-control" id="policy_expiry_date" name="policy_expiry_date" value="{{ old('policy_expiry_date', $quotation->policy_expiry_date ? \Carbon\Carbon::parse($quotation->policy_expiry_date)->format('Y-m-get_class') : '') }}">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 2: Placement Parameters & Risk Architecture -->
                    <div class="mb-5">
                        <div class="form-section-title">
                            <span>2. Risk Profile & Slips clauses</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="insurance_policy" class="form-label">Insurance Policy Architecture</label>
                                <input type="text" class="form-control" id="insurance_policy" name="insurance_policy" value="{{ old('insurance_policy', $quotation->insurance_policy) }}" placeholder="e.g., Fire & Allied Perils">
                            </div>
                            <div class="col-md-6">
                                <label for="location_of_risk" class="form-label">Geographic Location of Risk</label>
                                <input type="text" class="form-control" id="location_of_risk" name="location_of_risk" value="{{ old('location_of_risk', $quotation->location_of_risk) }}">
                            </div>
                            <div class="col-12">
                                <label for="property_insured" class="form-label">Property Assets Insured</label>
                                <textarea class="form-control" id="property_insured" name="property_insured" rows="2">{{ old('property_insured', $quotation->property_insured) }}</textarea>
                            </div>
                            <div class="col-12">
                                <label for="scope_of_cover" class="form-label">Scope of Underwritten Cover</label>
                                <textarea class="form-control" id="scope_of_cover" name="scope_of_cover" rows="3">{{ old('scope_of_cover', $quotation->scope_of_cover) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="extensions" class="form-label">Policy Extensions</label>
                                <textarea class="form-control" id="extensions" name="extensions" rows="2">{{ old('extensions', $quotation->extensions) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="excess_deductible" class="form-label">Excess / Deductible Parameters</label>
                                <textarea class="form-control" id="excess_deductible" name="excess_deductible" rows="2">{{ old('excess_deductible', $quotation->excess_deductible) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="specific_warranties" class="form-label">Specific Warranties</label>
                                <textarea class="form-control" id="specific_warranties" name="specific_warranties" rows="2">{{ old('specific_warranties', $quotation->specific_warranties) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="specific_conditions" class="form-label">Specific Conditions</label>
                                <textarea class="form-control" id="specific_conditions" name="specific_conditions" rows="2">{{ old('specific_conditions', $quotation->specific_conditions) }}</textarea>
                            </div>
                            <div class="col-md-6">
                                <label for="cancellation_clause" class="form-label">Cancellation Clause Terms</label>
                                <input type="text" class="form-control" id="cancellation_clause" name="cancellation_clause" value="{{ old('cancellation_clause', $quotation->cancellation_clause) }}">
                            </div>
                            <div class="col-md-6">
                                <label for="placing_slip_clause" class="form-label">Placing Slip Provision</label>
                                <input type="text" class="form-control" id="placing_slip_clause" name="placing_slip_clause" value="{{ old('placing_slip_clause', $quotation->placing_slip_clause) }}">
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 3: Financial Framework Matrix -->
                    <div class="mb-5">
                        <div class="form-section-title">
                            <span>3. Premium Ledger Breakdown Matrix</span>
                        </div>
                        <div class="calculation-block">
                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <label for="policy_currency" class="form-label">Policy Base Currency</label>
                                    <input type="text" class="form-control font-weight-bold" id="policy_currency" name="policy_currency" value="{{ old('policy_currency', $quotation->policy_currency ?? 'USD') }}">
                                </div>
                                <div class="col-md-8">
                                    <label for="total_sum_insured" class="form-label">Total Sum Insured (TSI)</label>
                                    <input type="number" step="0.01" class="form-control" id="total_sum_insured" name="total_sum_insured" value="{{ old('total_sum_insured', $quotation->total_sum_insured) }}">
                                </div>
                            </div>

                            <div class="row g-3 border-top pt-3">
                                <div class="col-md-6 col-lg-3">
                                    <label for="basic_rate" class="form-label">Basic Premium Rate (%)</label>
                                    <input type="number" step="0.0001" class="form-control calc-trigger" id="basic_rate" name="basic_rate" value="{{ old('basic_rate', $quotation->basic_rate) }}">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="basic_premium" class="form-label">Basic Premium Owed</label>
                                    <input type="number" step="0.01" class="form-control calc-trigger" id="basic_premium" name="basic_premium" value="{{ old('basic_premium', $quotation->basic_premium) }}">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="discount_rate" class="form-label">Discount Percentage Rate (%)</label>
                                    <input type="number" step="0.0001" class="form-control calc-trigger" id="discount_rate" name="discount_rate" value="{{ old('discount_rate', $quotation->discount_rate) }}">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="discount" class="form-label">Discount Reduction Value</label>
                                    <input type="number" step="0.01" class="form-control calc-trigger" id="discount" name="discount" value="{{ old('discount', $quotation->discount) }}">
                                </div>
                            </div>

                            <div class="row g-3 mt-1">
                                <div class="col-md-6 col-lg-3">
                                    <label for="premium_levy_rate" class="form-label">Premium Levy Rate (%)</label>
                                    <input type="number" step="0.0001" class="form-control calc-trigger" id="premium_levy_rate" name="premium_levy_rate" value="{{ old('premium_levy_rate', $quotation->premium_levy_rate) }}">
                                </div>
                                <div class="col-md-6 col-lg-3">
                                    <label for="premium_levy" class="form-label">Statutory Premium Levy</label>
                                    <input type="number" step="0.01" class="form-control calc-trigger" id="premium_levy" name="premium_levy" value="{{ old('premium_levy', $quotation->premium_levy) }}">
                                </div>
                                <div class="col-md-6 col-lg-6">
                                    <label for="gross_premium" class="form-label text-dark font-weight-bold">Gross Client Premium Summary</label>
                                    <input type="number" step="0.01" class="form-control readonly-display" id="gross_premium" name="gross_premium" value="{{ old('gross_premium', $quotation->gross_premium) }}" readonly>
                                </div>
                            </div>

                            <div class="row g-3 mt-1 border-top pt-3">
                                <div class="col-md-4">
                                    <label for="commission_rate" class="form-label">Broker Commission Yield (%)</label>
                                    <input type="number" step="0.0001" class="form-control calc-trigger" id="commission_rate" name="commission_rate" value="{{ old('commission_rate', $quotation->commission_rate) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="commission_amount" class="form-label">Broker Commission Amount</label>
                                    <input type="number" step="0.01" class="form-control calc-trigger" id="commission_amount" name="commission_amount" value="{{ old('commission_amount', $quotation->commission_amount) }}">
                                </div>
                                <div class="col-md-4">
                                    <label for="insurer_premium" class="form-label text-dark font-weight-bold">Net Insurer Remittance Allocation</label>
                                    <input type="number" step="0.01" class="form-control readonly-display" id="insurer_premium" name="insurer_premium" value="{{ old('insurer_premium', $quotation->insurer_premium) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION 4: Settlement Configuration -->
                    <div>
                        <div class="form-section-title">
                            <span>4. Capital Settlement Profile</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="payment_method" class="form-label">Settlement Routing Channel</label>
                                <select class="form-select" id="payment_method" name="payment_method">
                                    <option value="" disabled {{ old('payment_method', $quotation->payment_method) == '' ? 'selected' : '' }}>Select Channel</option>
                                    <option value="Bank Wire Transfer" {{ old('payment_method', $quotation->payment_method) == 'Bank Wire Transfer' ? 'selected' : '' }}>Bank Wire Transfer</option>
                                    <option value="Corporate Check" {{ old('payment_method', $quotation->payment_method) == 'Corporate Check' ? 'selected' : '' }}>Corporate Check</option>
                                    <option value="Direct Debit Ledger" {{ old('payment_method', $quotation->payment_method) == 'Direct Debit Ledger' ? 'selected' : '' }}>Direct Debit Ledger</option>
                                    <option value="Cash Deposit Account" {{ old('payment_method', $quotation->payment_method) == 'Cash Deposit Account' ? 'selected' : '' }}>Cash Deposit Account</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="payment_made" class="form-label">Cleared Capital Logged</label>
                                <input type="number" step="0.01" class="form-control font-weight-semibold text-success" id="payment_made" name="payment_made" value="{{ old('payment_made', $quotation->payment_made ?? 0.00) }}">
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Footer Operations Panel -->
                <div class="bg-light p-4 border-top d-flex justify-content-between align-items-center">
                    <a href="{{ route('insurance_broking.quotations.list') }}" class="text-decoration-none text-muted fw-medium small">
                        <i class="bi bi-arrow-left me-1"></i> Discard Variations
                    </a>
                    <button type="submit" class="btn btn-danger px-4 shadow-sm" style="background-color: var(--brand-primary); border-color: var(--brand-primary);">
                        <i class="bi bi-save me-2"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('quotationEditForm');
        
        // Target trigger inputs
        const totalSumInsuredInp = document.getElementById('total_sum_insured');
        const basicRateInp = document.getElementById('basic_rate');
        const basicPremiumInp = document.getElementById('basic_premium');
        const discountRateInp = document.getElementById('discount_rate');
        const discountInp = document.getElementById('discount');
        const premiumLevyRateInp = document.getElementById('premium_levy_rate');
        const premiumLevyInp = document.getElementById('premium_levy');
        const grossPremiumInp = document.getElementById('gross_premium');
        const commissionRateInp = document.getElementById('commission_rate');
        const commissionAmountInp = document.getElementById('commission_amount');
        const insurerPremiumInp = document.getElementById('insurer_premium');

        function calculateFinanceMatrix(event) {
            let tsi = parseFloat(totalSumInsuredInp.value) || 0;
            let basicRate = parseFloat(basicRateInp.value) || 0;
            let basicPremium = parseFloat(basicPremiumInp.value) || 0;
            let discountRate = parseFloat(discountRateInp.value) || 0;
            let discount = parseFloat(discountInp.value) || 0;
            let levyRate = parseFloat(premiumLevyRateInp.value) || 0;
            let levy = parseFloat(premiumLevyInp.value) || 0;
            let commRate = parseFloat(commissionRateInp.value) || 0;
            let commAmount = parseFloat(commissionAmountInp.value) || 0;

            // Handle calculation relationships depending on what input changed
            if (event && event.target) {
                const targetId = event.target.id;
                
                if (targetId === 'total_sum_insured' || targetId === 'basic_rate') {
                    basicPremium = (tsi * basicRate) / 100;
                    basicPremiumInp.value = basicPremium.toFixed(2);
                } else if (targetId === 'basic_premium' && tsi > 0) {
                    basicRate = (basicPremium / tsi) * 100;
                    basicRateInp.value = basicRate.toFixed(4);
                }

                if (targetId === 'discount_rate') {
                    discount = (basicPremium * discountRate) / 100;
                    discountInp.value = discount.toFixed(2);
                } else if (targetId === 'discount' && basicPremium > 0) {
                    discountRate = (discount / basicPremium) * 100;
                    discountRateInp.value = discountRate.toFixed(4);
                }

                if (targetId === 'premium_levy_rate') {
                    levy = (basicPremium * levyRate) / 100;
                    premiumLevyInp.value = levy.toFixed(2);
                } else if (targetId === 'premium_levy' && basicPremium > 0) {
                    levyRate = (levy / basicPremium) * 100;
                    premiumLevyRateInp.value = levyRate.toFixed(4);
                }
            }

            // Calculations for compound metrics
            let grossPremium = basicPremium - discount + levy;
            grossPremiumInp.value = grossPremium.toFixed(2);

            if (event && event.target && event.target.id === 'commission_rate') {
                commAmount = (basicPremium * commRate) / 100;
                commissionAmountInp.value = commAmount.toFixed(2);
            } else if (event && event.target && event.target.id === 'commission_amount' && basicPremium > 0) {
                commRate = (commAmount / basicPremium) * 100;
                commissionRateInp.value = commRate.toFixed(4);
            }

            let insurerPremium = grossPremium - commAmount;
            insurerPremiumInp.value = insurerPremium.toFixed(2);
        }

        // Bind interactive event listeners
        form.querySelectorAll('.calc-trigger, #total_sum_insured').forEach(element => {
            element.addEventListener('input', calculateFinanceMatrix);
        });
    });
</script>
@endpush