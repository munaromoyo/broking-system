@extends('layouts.app')

@push('styles')
<style>
    body { font-family: 'Inter', sans-serif; background-color: #f8f9fa; }
    .form-card { border: none; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); }
    .statement-card { border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); }
    .form-label { font-weight: 600; color: #495057; font-size: 0.9rem; margin-bottom: 0.5rem; }
    .form-control, .form-select { 
        border-radius: 8px; border: 1px solid #dee2e6; padding: 12px 15px; transition: all 0.2s; 
    }
    .btn-register { 
        padding: 12px; border-radius: 8px; font-weight: 600; letter-spacing: 0.5px;
        background: linear-gradient(45deg, #0d6efd, #0043a8); border: none;
    }
    .section-title { position: relative; padding-bottom: 8px; margin-bottom: 20px; border-bottom: 2px solid #f1f5f9; font-weight: 700; color: #1e293b; }
    .table-section-title { font-size: 0.8rem; letter-spacing: 0.05em; margin-top: 1.5rem; margin-bottom: 0.75rem; color: #64748b; }
</style>
@endpush

@section('content')
<div class="container py-5">

    <div class="row justify-content-center g-4">
        
        <div class="col-lg-10">            
            <div class="card form-card mb-4">
                <div class="card-body p-4">
                    <h5 class="section-title"><i class="bi bi-search me-2"></i>Statement Search Criteria</h5>
                    
                    {{-- Form posts back to the exact same route to render tables in-place downstream --}}
                    <form id="statementForm" method="POST" action="{{ route('insurance_broking.accounts.client_statements.index') }}" class="row g-3 align-items-end">
                        @csrf

                        <div class="col-md-5">
                            <label for="client_name" class="form-label">Client Name*</label>
                            <select class="form-select" id="client_name" name="client_name" required>
                                <option value="" disabled {{ !isset($selectedClient) ? 'selected' : '' }}>Select Client</option>
                                @foreach ($clients as $name)
                                    <option value="{{ $name }}" {{ (isset($selectedClient) && $selectedClient == $name) ? 'selected' : '' }}>
                                        {{ ucwords(strtolower($name)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="currency" class="form-label">Currency*</label>
                            <select class="form-select" id="currency" name="currency" required>
                                <option value="ZMW" {{ (isset($selectedCurr) && $selectedCurr == 'ZMW') ? 'selected' : '' }}>ZMW</option>
                                <option value="USD" {{ (isset($selectedCurr) && $selectedCurr == 'USD') ? 'selected' : '' }}>USD</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="{{ route('insurance_broking.accounts.client_statements.index') }}" class="btn btn-light px-3 py-2 border w-100">Reset</a>
                                <button type="submit" id="view_statement" class="btn btn-primary btn-register px-4 w-100">
                                    Generate Statement
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(isset($selectedClient))
        <div class="col-lg-10">
            <div class="card statement-card animate__animated animate__fadeIn">
                <div class="card-header bg-white py-4 px-4 border-0">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <span class="badge bg-primary-subtle text-primary mb-2 px-3 py-2 rounded-pill fw-semibold">Official Document</span>
                            <h3 class="mb-1 text-dark fw-bold">Statement of Account</h3>
                            <p class="text-muted mb-0">Client Account: <strong class="text-secondary">{{ ucwords(strtolower($selectedClient)) }}</strong></p>
                        </div>
                        <div class="col-md-6 text-md-end mt-3 mt-md-0">
                            <h4 class="text-primary fw-bold mb-0">{{ $selectedCurr }}</h4>
                            <p class="small text-muted mb-0">Statement Date: {{ $date ?? now()->format('Y-m-d') }}</p>
                        </div>
                    </div>
                </div>
                
                <div class="card-body px-4 pt-0">
                    <hr class="text-muted opacity-25 my-3">

                    <h6 class="text-uppercase fw-bold table-section-title">Invoices / Debits</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-4">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th style="width: 25%">Invoice Number</th>
                                    <th style="width: 50%">Description</th>
                                    <th style="width: 25%" class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($filteredInvoices as $inv)
                                <tr>
                                    <td><span class="fw-semibold text-dark">INV#{{ str_pad($inv->invoice_number, 4, "0", STR_PAD_LEFT) }}</span></td>
                                    <td class="text-muted">Insurance Policy Premium</td>
                                    <td class="text-end fw-semibold text-dark">{{ number_format($inv->gross_premium, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No active debit statements recorded.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h6 class="text-uppercase fw-bold table-section-title">Receipts / Credits</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-4">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th style="width: 25%">Receipt Number</th>
                                    <th style="width: 50%">Description</th>
                                    <th style="width: 25%" class="text-end">Amount Paid</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($filteredReceipts as $rec)
                                <tr>
                                    <td><span class="fw-semibold text-dark">REC#{{ str_pad($rec->receipt_number, 4, "0", STR_PAD_LEFT) }}</span></td>
                                    <td class="text-muted">Payment Received</td>
                                    <td class="text-end text-success fw-semibold">- {{ number_format($rec->gross_amount_received, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No matching transaction credits verified.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h6 class="text-uppercase fw-bold table-section-title">Cancelled Slips / Credits</h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light text-secondary">
                                <tr>
                                    <th style="width: 25%">Slip No</th>
                                    <th style="width: 50%">Cancellation Date</th>
                                    <th style="width: 25%" class="text-end">Credit Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($filteredCancellations as $slip)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ $slip->slip_id }}</td>
                                    <td class="text-muted">{{ $slip->cancellation_date }}</td>
                                    <td class="text-end text-danger fw-semibold">- {{ number_format($slip->premium_refund ?? 0, 2) }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-3">No cancellations or credit revisions for this period.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light p-4 border-0 rounded-bottom-4">
                    <div class="row text-end g-2">
                        <div class="col-8 col-md-10 text-muted fw-semibold">Total Invoiced:</div>
                        <div class="col-4 col-md-2 fw-bold text-dark">{{ number_format($totalInvoiced, 2) }}</div>
                        
                        <div class="col-8 col-md-10 text-muted fw-semibold">Total Paid:</div>
                        <div class="col-4 col-md-2 text-success fw-bold">{{ number_format($totalPaid, 2) }}</div>

                        <div class="col-8 col-md-10 text-muted fw-semibold">Total Cancelled:</div>
                        <div class="col-4 col-md-2 text-danger fw-bold">{{ number_format($totalCancelled, 2) }}</div>
                        
                        <div class="col-12"><hr class="my-2 opacity-25"></div>

                        <div class="col-8 col-md-10 d-flex align-items-center justify-content-end">
                            <h5 class="mb-0 fw-bold text-dark me-2">Balance Due:</h5>
                        </div>
                        <div class="col-4 col-md-2 d-flex align-items-center justify-content-end">
                            <h4 class="mb-0 text-danger fw-bold">{{ $selectedCurr }} {{ number_format($balanceDue, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Context Action: PDF Generation & Printing Dispatch --}}
            <div class="mt-4 text-center d-print-none">
                <form method="POST" action="{{ route('insurance_broking.accounts.client_statements.pdf') }}" class="d-inline">
                    @csrf
                    <input type="hidden" name="client_name" value="{{ $selectedClient }}">
                    <input type="hidden" name="currency" value="{{ $selectedCurr }}">
                    <button type="submit" name="print_statement" class="btn btn-outline-dark px-4 py-2 fw-semibold shadow-sm">
                        <i class="bi bi-printer me-2"></i>Print Statement Report
                    </button>
                </form>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Handle structural document print triggers if requested via flash streams
    @if(session('download_pdf_id'))
        var downloadWin = window.open("/generate_pdf.php?id={{ session('download_pdf_id') }}", '_blank');
        if(!downloadWin || downloadWin.closed || typeof downloadWin.closed == 'undefined') { 
            alert('Download Blocked! Please allow browser popups for this ecosystem to pull down document records.'); 
        }
    @endif
</script>
@endpush