@extends('layouts.app') {{-- Assuming you have a main Laravel layout --}}

@section('content')
<style>
    body { background-color: #f8f9fa; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
    .document-page {
        background: white;
        max-width: 900px;
        margin: 30px auto;
        padding: 50px;
        box-shadow: 0 0 20px rgba(0,0,0,0.1);
        border-radius: 8px;
    }
    .status-badge { padding: 5px 15px; border-radius: 20px; font-weight: 600; text-transform: uppercase; font-size: 0.8rem; }
    .label-col { color: #6c757d; font-weight: 600; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
    .value-col { color: #212529; font-weight: 500; border-bottom: 1px solid #f1f1f1; padding-bottom: 5px; }
    .claim-header { border-bottom: 2px solid #004085; margin-bottom: 30px; padding-bottom: 10px; }
    
    @media print {
        body { background: white; }
        .document-page { box-shadow: none; margin: 0; padding: 20px; }
        .no-print { display: none; }
    }
</style>

<div class="container">
    <div class="document-page">
        <!-- Header: Logo & Claim Number -->
        <div class="row align-items-center mb-4">
            <div class="col-6">
                {{-- Use asset() helper for images in Laravel --}}
                <img src="{{ asset('img/rib_logo.jpg') }}" alt="RIB Logo" style="max-height: 80px;">
            </div>
            <div class="col-6 text-end">
                <div class="text-muted small">Reference Number</div>
                <h4 class="fw-bold text-primary mb-0">#CLM-{{ str_pad($claim->id, 5, '0', STR_PAD_LEFT) }}</h4>
            </div>
        </div>

        <div class="claim-header d-flex justify-content-between align-items-end">
            <h2 class="mb-0 fw-bold">CLAIM DETAILS REPORT</h2>
            <span class="status-badge bg-primary text-white">
                <i class="fas fa-info-circle me-1"></i> {{ $claim->claim_status }}
            </span>

            {{-- Updated to use Laravel named route --}}
            <a href="{{ route('insurance_broking.view_list.index', ['action' => 'view_claim_list']) }}" class="btn btn-outline-secondary rounded-pill px-4 no-print">
                <i class="bi bi-arrow-left me-2"></i>Back to List
            </a>  
        </div>

        <!-- Section: Involved Parties -->
        <h5 class="text-primary mb-3"><i class="fas fa-users me-2"></i>Primary Information</h5>
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="label-col">Insured Party</div>
                <div class="value-col">{{ $claim->client_name }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="label-col">Underwriting Insurer</div>
                <div class="value-col">{{ $claim->insurer_name }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="label-col">Policy Currency</div>
                <div class="value-col">{{ $claim->policy_currency }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="label-col">Claim Type</div>
                <div class="value-col">{{ $claim->type_of_claim }}</div>
            </div>
        </div>

        <!-- Section: Loss Details -->
        <h5 class="text-primary mb-3"><i class="fas fa-exclamation-triangle me-2"></i>Loss & Incident Details</h5>
        <div class="row mb-4">
            <div class="col-md-4 mb-3">
                <div class="label-col">Date of Loss</div>
                <div class="value-col">{{ $claim->date_of_loss }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="label-col">Notification Date</div>
                <div class="value-col">{{ $claim->date_of_notification }}</div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="label-col">Registration Date</div>
                <div class="value-col">{{ $claim->date_registered }}</div>
            </div>
            <div class="col-12 mb-3">
                <div class="label-col">Narrative / Details of Loss</div>
                <div class="value-col p-2 bg-light rounded" style="border: none; min-height: 50px;">
                    {!! nl2br(e($claim->details_of_loss)) !!}
                </div>
            </div>
        </div>

        <!-- Section: Financial Summary -->
        <div class="card bg-light border-0 mb-4">
            <div class="card-body">
                <h5 class="text-primary mb-3"><i class="fas fa-file-invoice-dollar me-2"></i>Financial Summary</h5>
                <div class="row text-center">
                    <div class="col-md-4">
                        <div class="label-col">Claimed Amount</div>
                        <h4 class="fw-bold">{{ number_format($claim->claim_amount, 2) }}</h4>
                    </div>
                    <div class="col-md-4 border-start">
                        <div class="label-col">Amount Settled</div>
                        <h4 class="fw-bold text-success">{{ number_format($claim->amount_settled, 2) }}</h4>
                    </div>
                    <div class="col-md-4 border-start">
                        <div class="label-col">Settlement Date</div>
                        <div class="fw-bold">{{ $claim->date_settled ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section: Additional Notes -->
        <div class="row">
            <div class="col-md-6 mb-3">
                <div class="label-col">Documents Received</div>
                <div class="value-col">{{ $claim->documents_received }}</div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="label-col">Internal Remarks</div>
                <div class="value-col">{{ $claim->remarks }}</div>
            </div>
        </div>

        <!-- ATTACHMENTS -->

        <h5 class="text-primary mb-3 mt-4"><i class="fas fa-file-pdf me-2"></i>Attached Documents</h5>
        <div class="row">
            <div class="col-12">
                <div class="value-col p-3 border rounded bg-light">
                    <!-- @if(!empty($claim->claim_documents))
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-alt text-danger me-3 fs-4"></i>
                            <div>
                                <div class="fw-bold">Supporting Document</div>
                                <a href="{{ asset('storage/' . $claim->claim_documents) }}" 
                                target="_blank" 
                                class="btn btn-sm btn-outline-primary mt-1">
                                <i class="fas fa-eye me-1"></i> View Document
                                </a>
                            </div>
                        </div>
                    @else
                        <span class="text-muted italic">No documents uploaded for this claim.</span>
                    @endif -->

                    @if(!empty($claim->claim_documents))
                    @php
                        // Decode the JSON string array from the database into a real PHP array
                        $documentPaths = json_decode($claim->claim_documents, true);
                    @endphp

                    @if(is_array($documentPaths) && count($documentPaths) > 0)
                        <div class="mt-3">
                            <label class="font-weight-bold">Uploaded Documents:</label>
                            <ul class="list-group">
                                @foreach($documentPaths as $index => $path)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>Document #{{ $index + 1 }}</span>
                                        
                                        <a href="{{ Storage::disk('public')->url($path) }}" target="_blank" class="btn btn-sm btn-primary">
                                            View File
                                        </a>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @else
                        <span class="text-muted">No documents uploaded.</span>
                    @endif
                @else
                    <span class="text-muted">No documents uploaded.</span>
                @endif
                </div>
            </div>
        </div>

        <!-- Action Buttons (Hidden on Print) -->
        <div class="mt-5 pt-3 border-top no-print d-flex justify-content-between">
            <button onclick="window.print()" class="btn btn-outline-dark">
                <i class="fas fa-print me-2"></i> Print Report
            </button>
            <a href="{{ route('insurance_broking.claims.edit', $claim->id) }}" class="btn btn-primary px-4">
                <i class="fas fa-edit me-2"></i> Edit Claim Record
            </a>
        </div>
    </div>
</div>
@endsection