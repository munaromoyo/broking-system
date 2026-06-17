@extends('layouts.app')

@section('content')
<style>
    body { background-color: #f8f9fa; font-family: 'Inter', sans-serif; }
    .claim-card { border: none; border-radius: 1rem; box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08); }
    .section-title { border-left: 4px solid #0d6efd; padding-left: 1rem; margin-bottom: 1.5rem; font-weight: 700; color: #333; }
    .form-control:focus { border-color: #0d6efd; box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15); }
</style>

<div class="container py-5">
    <!-- Header Section -->
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <h3 class="fw-bold m-0 text-dark">Edit Claim Record</h3>
            <p class="text-muted small mb-0">Insured: {{ $claim->client_name }}</p>
        </div>
        <a href="{{ route('insurance_broking.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="bi bi-arrow-left me-2"></i>Back to List
        </a>
    </div>

    <div class="card claim-card">
        <div class="card-body p-4 p-md-5">
            {{-- Updated to Laravel form action --}}
            <form method="POST" action="{{ route('insurance_broking.claims.update', $claim->id) }}">
                @csrf
                @method('PUT') {{-- Using PUT for updates --}}

                {{-- Display Laravel Validation Errors --}}
                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm mb-4">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Primary Details -->
                <h5 class="section-title text-primary">Primary Details</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="insurer_name" value="{{ old('insurer_name', $claim->insurer_name) }}" placeholder="Insurer">
                            <label>Insurer</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="client_name" value="{{ old('client_name', $claim->client_name) }}" placeholder="Insured" required>
                            <label>Insured Party*</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="type_of_claim" value="{{ old('type_of_claim', $claim->type_of_claim) }}" placeholder="Type">
                            <label>Type of Claim*</label>
                        </div>
                    </div>
                </div>

                <!-- Dates Section -->
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="claim_intimation_date" value="{{ old('claim_intimation_date', $claim->claim_intimation_date) }}">
                            <label>Intimation Date</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="date_of_loss" value="{{ old('date_of_loss', $claim->date_of_loss) }}">
                            <label>Date of Loss</label>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="date_of_notification" value="{{ old('date_of_notification', $claim->date_of_notification) }}">
                            <label>Notification Date</label>
                        </div>
                    </div>
                </div>

                <!-- Documentation & Narrative -->
                <h5 class="section-title text-primary">Documentation & Narrative</h5>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="details_of_loss" style="height: 120px" placeholder="Loss Details" required>{{ old('details_of_loss', $claim->details_of_loss) }}</textarea>
                            <label>Details of Loss*</label>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-floating mb-3">
                            <textarea class="form-control" name="documents_received" style="height: 100px" placeholder="Documents">{{ old('documents_received', $claim->documents_received) }}</textarea>
                            <label>Documents Received (Checklist)</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <textarea class="form-control" name="claim_status" style="height: 100px" placeholder="Status">{{ old('claim_status', $claim->claim_status) }}</textarea>
                            <label>Claims Status Updates</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-floating">
                            <textarea class="form-control" name="remarks" style="height: 100px" placeholder="Remarks">{{ old('remarks', $claim->remarks) }}</textarea>
                            <label>Internal Remarks / Notes</label>
                        </div>
                    </div>
                </div>

                <!-- Financial Settlement -->
                <h5 class="section-title text-primary">Financial Settlement</h5>
                <div class="row g-3 mb-5">
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="text" class="form-control" name="policy_currency" value="{{ old('policy_currency', $claim->policy_currency) }}" placeholder="USD">
                            <label>Currency</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="number" step="0.01" class="form-control" name="claim_amount" value="{{ old('claim_amount', $claim->claim_amount) }}" placeholder="0.00">
                            <label>Claimed Amount</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="number" step="0.01" class="form-control" name="amount_settled" value="{{ old('amount_settled', $claim->amount_settled) }}" placeholder="0.00">
                            <label>Amount Settled</label>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-floating">
                            <input type="date" class="form-control" name="date_settled" value="{{ old('date_settled', $claim->date_settled) }}">
                            <label>Date Settled</label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="d-flex justify-content-end gap-3 pt-4 border-top">
                    <button type="submit" class="btn btn-primary px-5 rounded-pill shadow-sm">
                        <i class="bi bi-save me-2"></i>Save Claim Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection