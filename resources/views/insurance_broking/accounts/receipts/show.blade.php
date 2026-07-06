@extends('layouts.app')

@section('title', 'Accounts Dashboard')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .transition-all { transition: all 0.3s ease; }
    .nav-tabs .nav-link { color: #6c757d; font-weight: 600; border: none; padding: 1rem 1.5rem; }
    .nav-tabs .nav-link.active { color: #0d6efd; border-bottom: 3px solid #0d6efd; background: none; }
    
    /* Allocation Styling */
    .bank-item { border: 1px solid #eee; border-radius: 12px; margin-bottom: 8px; cursor: pointer; transition: all 0.2s; }
    .bank-item:hover { background-color: #f8f9fa; border-color: #dee2e6; }
    .match-glow { border-color: #198754 !important; background-color: #f0fff4; box-shadow: 0 0 10px rgba(25, 135, 84, 0.1); }
    .btn-xs { padding: 0.1rem 0.4rem; font-size: 0.75rem; }
</style>
@endpush

@section('content')
<div class="container-fluid px-4 mt-4">
    
    {{-- Dynamic Session Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-calculator me-2"></i>Accounts Management</h5>
            
            <ul class="nav nav-tabs border-bottom" id="accountsTab" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">
                        <i class="bi bi-file-earmark-text me-2"></i>Invoices
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="records-tab" data-bs-toggle="tab" data-bs-target="#records" type="button" role="tab">
                        <i class="bi bi-journal-text me-2"></i>Receipt Records
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link" id="allocations-tab" data-bs-toggle="tab" data-bs-target="#allocations" type="button" role="tab">
                        <i class="bi bi-bank me-2"></i>Bank Allocations
                    </button>
                </li>
            </ul>
        </div>
        
        <div class="card-body p-0">
            <div class="tab-content" id="accountsTabContent">
                
                {{-- Tab 1: Invoices --}}
                <div class="tab-pane fade show active" id="invoices" role="tabpanel">
                    <div class="p-3 bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="small text-muted fw-bold text-uppercase">Pending Invoices</span>
                        <input type="text" id="invoiceSearch" class="form-control form-control-sm w-100 w-md-25" style="min-width:200px;" placeholder="Search invoices...">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small fw-bold text-muted text-uppercase">
                                    <th class="ps-3">Date</th>
                                    <th>Invoice #</th>
                                    <th>Client Name</th>
                                    <th>Currency</th>
                                    <th class="text-end">Gross Premium</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="invoiceTable">
                                @forelse ($invoice_infor as $invoice)
                                    {{-- Robust check handling both case sensitivity and object/array syntax --}}
                                    @php
                                        $status = is_array($invoice) ? ($invoice['status'] ?? '') : ($invoice->status ?? '');
                                    @endphp

                                    @if (strcasecmp(trim($status), 'Fully Paid') === 0)
                                        @continue
                                    @endif
                                    <tr>
                                        <td class="ps-3">{{ isset($invoice['created_at']) ? \Carbon\Carbon::parse($invoice['created_at'])->format('d M Y') : '' }}</td>
                                        <td><span class="badge bg-secondary">{{ $invoice['invoice_number'] ?? '' }}</span></td>
                                        <td>{{ Str::title(Str::lower($invoice['client_name'] ?? '')) }}</td>
                                        <td>{{ $invoice['policy_currency'] ?? '' }}</td>
                                        <td class="text-end fw-bold">{{ number_format((float)($invoice['gross_premium'] ?? 0), 2) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-success btn-sm rounded-pill px-3" 
                                                onclick="openReceiptModal('{{ addslashes($invoice['invoice_number'] ?? '') }}', '{{ addslashes($invoice['client_name'] ?? '') }}', '{{ addslashes($invoice['policy_name'] ?? '') }}', '{{ $invoice['policy_currency'] ?? '' }}', '{{ $invoice['gross_premium'] ?? 0 }}', '{{ addslashes($invoice['insurer'] ?? '') }}')">
                                                Receipt
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4">No pending invoices found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 2: Receipt Records --}}
                <div class="tab-pane fade" id="records" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3 p-3">
                        <h6 class="text-muted mb-0">Cash Book Entries</h6>
                        <input type="text" id="receiptSearch" class="form-control w-100 w-md-25" style="min-width: 200px;" placeholder="Search records...">
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover border align-middle table-custom">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-nowrap">Receipt #</th> 
                                    <th class="text-nowrap">Date</th>
                                    <th class="text-nowrap">Invoice #</th>
                                    <th>Client Name</th>
                                    <th>Policy</th>
                                    <th>Reference</th>
                                    <th class="text-end text-nowrap">Amount Rec.</th>
                                    <th class="text-end text-nowrap">Insurer Prem.</th>
                                    <th class="text-nowrap">Receipt Date</th> 
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody id="receiptTable">
                                @forelse ($cash_book_infor as $record)
                                    @php $isCancelled = (($record['status'] ?? '') === 'Cancelled'); @endphp
                                    
                                    @if ($isCancelled)
                                        @continue
                                    @endif

                                    <tr>
                                        <td class="fw-bold text-primary text-nowrap">{{ $record['receipt_number'] ?? '' }}</td>
                                        <td class="text-nowrap">{{ $record['receipt_date'] ?? '' }}</td>
                                        <td class="text-nowrap"><strong>{{ $record['invoice_number'] ?? '' }}</strong></td>
                                        <td style="max-width: 150px;" class="text-truncate" title="{{ $record['client_name'] ?? '' }}">
                                            {{ $record['client_name'] ?? '' }}
                                        </td>
                                        <td style="max-width: 150px;" class="text-truncate" title="{{ $record['policy_name'] ?? '' }}">
                                            {{ $record['policy_name'] ?? '' }}
                                        </td>
                                        <td style="max-width: 120px;" class="text-truncate" title="{{ $record['payment_ref'] ?? '' }}">
                                            {{ $record['payment_ref'] ?? '' }}
                                        </td>
                                        <td class="text-end text-success fw-bold text-nowrap">
                                            <small>{{ $record['policy_currency'] ?? '' }}</small> 
                                            {{ number_format((float)($record['gross_amount_received'] ?? 0), 2) }}
                                        </td>
                                        <td class="text-end fw-bold text-nowrap">
                                            <small>{{ $record['policy_currency'] ?? '' }}</small> 
                                            {{ number_format((float)($record['insurer_premium_received'] ?? 0), 2) }}
                                        </td>
                                        <td class="text-nowrap">{{ $record['receipt_date'] ?? '' }}</td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center align-items-center flex-nowrap gap-1">
                                                @if (($record['remittance_status'] ?? '') === 'Remitted')
                                                    <button type="button" class="btn btn-xs btn-secondary py-0 px-2 text-nowrap" disabled title="Already Remitted">
                                                        <i class="bi bi-check-circle-fill"></i> <small>Done</small>
                                                    </button>
                                                @else
                                                    <button type="button" 
                                                        class="btn btn-xs btn-info text-white remit-btn py-0 px-2 text-nowrap" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#remittanceModal"
                                                        data-receipt="{{ $record['receipt_number'] ?? '' }}" 
                                                        data-cur="{{ $record['policy_currency'] ?? '' }}" 
                                                        data-net="{{ $record['insurer_premium_received'] ?? '0' }}"
                                                        data-inv="{{ $record['invoice_number'] ?? '' }}"
                                                        data-insurer="{{ $record['insurer'] ?? '' }}">
                                                        <small>Remit</small>
                                                    </button>
                                                @endif
                                        
                                                <a href="{{ route('insurance_broking.accounts.receipts.generate_pdf', ['id' => $record['receipt_number'] ?? '']) }}" 
                                                   class="btn btn-xs btn-outline-danger py-0 px-2 text-nowrap"
                                                   target="_blank" 
                                                   title="Download Receipt">
                                                     <small>PDF</small>
                                                </a>
                                        
                                                @if (session('role') === 'Admin')
                                                    <button type="button" 
                                                        class="btn btn-xs btn-danger py-0 px-2 cancel-btn text-nowrap" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#cancelReceiptModal"
                                                        data-receipt="{{ $record['receipt_number'] ?? '' }}"
                                                        data-client="{{ $record['client_name'] ?? '' }}"
                                                        data-insurer="{{ $record['insurer'] ?? '' }}"
                                                        data-policy="{{ $record['policy_name'] ?? '' }}"
                                                        data-date="{{ $record['receipt_date'] ?? '' }}"
                                                        data-currency="{{ $record['policy_currency'] ?? '' }}"
                                                        data-amount="{{ number_format((float)($record['gross_amount_received'] ?? 0), 2) }}"
                                                        data-method="{{ $record['payment_method'] ?? '' }}">
                                                        <small>Cancel</small>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="10" class="text-center py-4">No records in cash book.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- Tab 3: Bank Allocations --}}
                <div class="tab-pane fade" id="allocations" role="tabpanel">
                    <div class="p-3 bg-light d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <span class="small text-muted fw-bold text-uppercase">Bank Matching</span>
                        <div class="d-flex gap-2 flex-wrap align-items-center">
                            <a href="{{ route('insurance_broking.accounts.receipts.bank_recon_template') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm">
                                <i class="bi bi-download me-1"></i> Template
                            </a>
                            <a href="{{ route('insurance_broking.accounts.receipts.import') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm">
                                <i class="bi bi-file-earmark-arrow-up me-1"></i> Import CSV
                            </a>
                            <input type="text" id="bankAllocationSearch" class="form-control form-control-sm" style="max-width:200px;" placeholder="Search references...">
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light small fw-bold text-muted">
                                <tr>
                                    <th class="ps-3">Date</th>
                                    <th>Client</th>
                                    <th>Ref</th>
                                    <th>Currency</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody id="bankAllocationTable">
                                @forelse ($cash_book_infor as $receipt)
                                    @php $isAllocated = (($receipt['allocation_status'] == 'allocated') || ($receipt['allocation_status'] == 1)); @endphp
                                    <tr class="{{ $isAllocated ? 'opacity-75' : '' }}">
                                        <td class="ps-3">{{ $receipt['receipt_date'] ?? '' }}</td>
                                        <td>{{ $receipt['client_name'] ?? '' }}</td>
                                        <td><code>{{ $receipt['payment_ref'] ?? '' }}</code></td>
                                        <td><span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-10">{{ $receipt['policy_currency'] ?? '' }}</span></td>
                                        <td class="text-end fw-bold">{{ number_format(($receipt['gross_amount_received'] ?? 0), 2) }}</td>
                                        <td class="text-center">
                                            {!! $isAllocated ? '<span class="badge rounded-pill bg-success-subtle text-success">Allocated</span>' : '<span class="badge rounded-pill bg-warning-subtle text-warning">Pending</span>' !!}
                                        </td>
                                        <td class="text-end pe-3">
                                            <button class="btn {{ $isAllocated ? 'btn-outline-secondary' : 'btn-success' }} btn-sm rounded-pill px-3" 
                                                {{ $isAllocated ? 'disabled' : '' }} onclick='openAllocationModal(@json($receipt))'>
                                                {{ $isAllocated ? 'Locked' : 'Allocate' }}
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-4">No entries available for bank processing.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div> 

            </div>
        </div>
    </div>
</div>

{{-- Modal Module 1: Post Payment Receipt --}}
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content border-0 shadow-lg">
            <form method="POST" action="{{ route('insurance_broking.accounts.receipts.store-payment') }}" onsubmit="this.querySelector('button[type=submit]').disabled=true; return true;">
                @csrf
                <div class="modal-header text-white" style="background-color: #15803d; border-bottom: none;">
                    <h5 class="modal-title" id="receiptModalLabel"><i class="bi bi-cash-coin me-2"></i>Post Payment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1 small">Invoice Number</label>
                            <input type="text" name="invoice_number" id="m_invoice_no" class="form-control bg-light border-secondary-subtle" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1 small">Receipt Date</label>
                            <div class="input-group">
                                <input type="text" name="receipt_date" id="receipt_date_picker" class="form-control border-secondary-subtle border-end-0" 
                                       value="{{ now()->format('d-M-Y') }}" required placeholder="DD-Mmm-YYYY">
                                <span class="input-group-text bg-white border-secondary-subtle border-start-0 text-muted" id="open_calendar" style="cursor: pointer;">
                                    <i class="bi bi-calendar3"></i>
                                </span>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-1 small">Client Name</label>
                            <input type="text" name="client_name" id="m_client_name" class="form-control bg-light border-secondary-subtle" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1 small">Payment Method</label>
                            <select name="payment_method" class="form-select border-secondary-subtle" required>
                                <option value="Transfer" selected>Transfer</option>
                                <option value="Cash">Cash</option>
                                <option value="Check">Check</option>
                                <option value="Mobile Money">Mobile Money</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1 small">Currency</label>
                            <input type="text" name="policy_currency" id="m_currency" class="form-control bg-light border-secondary-subtle" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-success mb-1 small">Amount Received</label>
                            <input type="number" step="0.01" name="gross_amount_received" id="m_amount" class="form-control border-success fw-bold text-dark fs-5" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1 small">Payment Ref (e.g. Chq No)</label>
                            <input type="text" name="payment_ref" class="form-control border-secondary-subtle">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark mb-1 small">Internal Ref No</label>
                            <input type="text" name="reference_no" class="form-control border-secondary-subtle">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold text-dark mb-1 small">Description / Remarks</label>
                            <textarea name="description" class="form-control border-secondary-subtle" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                
                <input type="hidden" name="receipt_payment" value="1">
                <input type="hidden" name="insurer_name" id="m_insurer">
                
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success px-4 fw-bold">Post Receipt</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Module 2: Process Insurer Remittance --}}
<div class="modal fade" id="remittanceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-bank"></i> Process Insurer Remittance</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="remittanceForm" method="POST" action="{{ route('insurance_broking.accounts.receipts.remit') }}">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="policy_currency" id="hidden_currency">
                    <input type="hidden" name="remit_payment" value="1">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Receipt Number</label>
                            <input type="text" name="receipt_number" id="remit_receipt_no" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Invoice Number</label>
                            <input type="text" name="invoice_number" id="remit_invoice_no" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Insurer</label>
                            <input type="text" id="remit_insurer_name" class="form-control bg-light" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Amount to Remit</label>
                            <div class="input-group">
                                <span class="input-group-text" id="remit_currency_display">ZMW</span>
                                <input type="number" step="0.01" name="remittance_amount" id="remit_amount" class="form-control border-primary" required>
                            </div>
                            <small class="text-muted">Net Due: <span id="net_due_display" class="fw-bold">0.00</span></small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Remittance Date</label>
                            <input type="date" name="remittance_date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label fw-bold">Bank Reference</label>
                            <input type="text" name="remittance_ref" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Remittance</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Module 3: Match Bank Transaction Allocation --}}
<div class="modal fade" id="allocationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header border-0 pb-0">
                <h5 class="fw-bold mb-0">Match Bank Transaction</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('insurance_broking.accounts.receipts.allocate') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-4 border d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted fw-bold d-block" style="font-size: 0.65rem;">Selected Receipt</span>
                            <span id="m_client_display" class="fw-bold h6"></span><br>
                            <small id="m_ref_display" class="text-primary"></small>
                        </div>
                        <div class="text-end">
                            <span class="text-muted fw-bold d-block" style="font-size: 0.65rem;">Amount to Match</span>
                            <span id="m_amount_display" class="h5 fw-bold text-dark"></span>
                        </div>
                    </div>
                    <input type="hidden" name="receipt_id" id="m_hidden_id">
                    <div id="bankSelectionList" class="overflow-auto" style="max-height: 350px;"></div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="confirmBtn" class="btn btn-primary rounded-pill px-4 shadow-sm" disabled>Confirm & Link</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Module 4: Cancel Receipt Confirmation --}}
<div class="modal fade" id="cancelReceiptModal" tabindex="-1" aria-labelledby="cancelReceiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white py-2">
                <h5 class="modal-title fs-6" id="cancelReceiptModalLabel">Confirm Receipt Cancellation</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('insurance_broking.accounts.receipts.cancel') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted small mb-3">You are about to cancel the following receipt entry. Please review details carefully.</p>
                    
                    <input type="hidden" name="receipt_number" id="modal_receipt_number_input">
                    
                    <table class="table table-sm table-bordered custom-table mb-3 small">
                        <tbody>
                            <tr>
                                <th class="bg-light w-40">Receipt Number</th>
                                <td id="modal_receipt_number" class="fw-bold text-danger"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Client Name</th>
                                <td id="modal_client_name"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Insurer</th>
                                <td id="modal_insurer"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Policy Name</th>
                                <td id="modal_policy_name"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Receipt Date</th>
                                <td id="modal_receipt_date"></td>
                            </tr>
                            <tr>
                                <th class="bg-light">Amount Received</th>
                                <td class="fw-bold">
                                    <span id="modal_policy_currency"></span> 
                                    <span id="modal_gross_amount_received"></span>
                                </td>
                            </tr>
                            <tr>
                                <th class="bg-light">Payment Method</th>
                                <td id="modal_payment_method"></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mb-0">
                        <label for="modal_remarks" class="form-label fw-bold small text-secondary">Cancellation Remarks / Reasons <span class="text-danger">*</span></label>
                        <textarea class="form-control form-control-sm" id="modal_remarks" name="remarks" rows="3" placeholder="Provide clear reasons for cancelling this receipt..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer py-1">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-danger">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
const bankDataRaw = @json($bank_transactions ?? []);

// Global Table Search Logic 
function setupSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    if (!input) return;

    input.addEventListener('input', function() {
        const val = this.value.toLowerCase().trim();
        const rows = document.querySelectorAll(`#${tableId} > tr`);
        
        rows.forEach(row => {
            if (row.querySelector('td[colspan]')) return;

            let matchFound = false;
            const cells = row.querySelectorAll('td');
            
            for (let cell of cells) {
                const textContent = cell.textContent.toLowerCase();
                const titleContent = cell.getAttribute('title') ? cell.getAttribute('title').toLowerCase() : '';
                
                if (textContent.includes(val) || titleContent.includes(val)) {
                    matchFound = true;
                    break;
                }
            }
            row.style.display = matchFound ? '' : 'none';
        });
    });
}

document.addEventListener("DOMContentLoaded", function() {
    // Run Search Listeners Setup
    setupSearch('invoiceSearch', 'invoiceTable');
    setupSearch('receiptSearch', 'receiptTable');
    setupSearch('bankAllocationSearch', 'bankAllocationTable');

    flatpickr("#receipt_date_picker", {
        dateFormat: "d-M-Y",
        allowInput: true
    });

    // Cancellation dynamic data injection
    const cancelButtons = document.querySelectorAll('.cancel-btn');
    cancelButtons.forEach(button => {
        button.addEventListener('click', function() {
            const receipt = this.getAttribute('data-receipt');
            document.getElementById('modal_receipt_number_input').value = receipt;
            document.getElementById('modal_receipt_number').textContent = receipt;
            document.getElementById('modal_client_name').textContent = this.getAttribute('data-client') || 'N/A';
            document.getElementById('modal_insurer').textContent = this.getAttribute('data-insurer') || 'N/A';
            document.getElementById('modal_policy_name').textContent = this.getAttribute('data-policy') || 'N/A';
            document.getElementById('modal_receipt_date').textContent = this.getAttribute('data-date') || 'N/A';
            document.getElementById('modal_policy_currency').textContent = this.getAttribute('data-currency');
            document.getElementById('modal_gross_amount_received').textContent = this.getAttribute('data-amount');
            document.getElementById('modal_payment_method').textContent = this.getAttribute('data-method') || 'N/A';
            document.getElementById('modal_remarks').value = '';
        });
    });

    // Remittance mapping
    const remitModal = document.getElementById('remittanceModal');
    if (remitModal) {
        remitModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget.closest('.remit-btn') || event.relatedTarget;
            const currency = button.getAttribute('data-cur') || 'ZMW';
            const netAmount = button.getAttribute('data-net') || '0.00';

            document.getElementById('remit_currency_display').textContent = currency;
            document.getElementById('hidden_currency').value = currency;
            document.getElementById('remit_receipt_no').value = button.getAttribute('data-receipt') || '';
            document.getElementById('remit_invoice_no').value = button.getAttribute('data-inv') || '';
            document.getElementById('remit_insurer_name').value = button.getAttribute('data-insurer') || '';
            document.getElementById('remit_amount').value = netAmount;
            document.getElementById('net_due_display').textContent = netAmount;
        });
    }
});

function openReceiptModal(invNo, client, policy, policy_currency, amount, insurer) {
    document.getElementById('m_invoice_no').value = invNo;
    document.getElementById('m_client_name').value = client;
    document.getElementById('m_currency').value = policy_currency;
    document.getElementById('m_amount').value = amount;
    if (document.getElementById('m_insurer')) document.getElementById('m_insurer').value = insurer;

    const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
    modal.show();
}

function openAllocationModal(receipt) {
    document.getElementById('m_client_display').innerText = receipt.client_name;
    document.getElementById('m_ref_display').innerText = 'Ref: ' + receipt.payment_ref;
    document.getElementById('m_amount_display').innerText = (receipt.policy_currency || 'ZMW') + ' ' + parseFloat(receipt.gross_amount_received).toLocaleString();
    document.getElementById('m_hidden_id').value = receipt.receipt_number;
    
    const listContainer = document.getElementById('bankSelectionList');
    listContainer.innerHTML = '';
    
    bankDataRaw.forEach(bank => {
        const amt = parseFloat(bank.credits) > 0 ? parseFloat(bank.credits) : (parseFloat(bank.debits) * -1);
        const isMatch = Math.abs(amt).toFixed(2) === Math.abs(parseFloat(receipt.gross_amount_received)).toFixed(2);
        
        const item = document.createElement('div');
        item.className = `bank-item p-3 ${isMatch ? 'match-glow' : ''}`;
        item.innerHTML = `
            <div class="form-check d-flex align-items-center w-100">
                <input class="form-check-input me-3" type="radio" name="bank_id" value="${bank.id}" id="bank_${bank.id}" onchange="document.getElementById('confirmBtn').disabled=false">
                <label class="form-check-label w-100" for="bank_${bank.id}">
                    <div class="d-flex justify-content-between">
                        <div><span class="fw-bold d-block">${bank.description}</span><small class="text-muted">${bank.value_date}</small></div>
                        <div class="text-end">
                            <span class="fw-bold ${amt >= 0 ? 'text-success' : 'text-danger'}">${Math.abs(amt).toLocaleString()}</span>
                            ${isMatch ? '<br><span class="badge bg-success-subtle text-success">Match Found</span>' : ''}
                        </div>
                    </div>
                </label>
            </div>`;
        listContainer.appendChild(item);
    });
    new bootstrap.Modal(document.getElementById('allocationModal')).show();
}
</script>
@endpush