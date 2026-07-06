@extends('layouts.app') {{-- Inherits your main app layout/header/footer --}}

@section('content')
<div class="container py-5">
    
    {{-- Display Status Alerts --}}
@if(session('status') == 'created')
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div>
            <strong>Success!</strong> Payment voucher generated and submitted for approval successfully.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@elseif(session('status') == 'updated')
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
        <i class="bi bi-pencil-square me-2 fs-5"></i>
        <div>
            <strong>Updated!</strong> Voucher configuration records saved successfully.
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@elseif(session('status') == 'error' || $errors->any())
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm d-flex align-items-center" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div>
            <strong>Error!</strong> {{ session('status') == 'error' ? 'An error occurred while processing the voucher.' : 'Please correct the invalid input fields below.' }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

    <div class="row justify-content-center">
        <div class="col-lg-11">
            
            <h2 class="mb-4 fw-bold">Payment Voucher Dashboard</h2>

            {{-- Tabs --}}
            <ul class="nav nav-pills mb-4 bg-white p-2 rounded shadow-sm" id="voucherTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="register-tab" data-bs-toggle="tab" data-bs-target="#register" type="button" role="tab">
                        <i class="bi bi-plus-lg me-2"></i>Register Voucher
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold position-relative" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                        <i class="bi bi-clock-history me-2"></i>Pending Approval
                        @if($pendingItems->count() > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                {{ $pendingItems->count() }}
                            </span>
                        @endif
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                        <i class="bi bi-check2-all me-2"></i>Approved History
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="voucherTabContent">
                
                {{-- Tab 1: Create Voucher Form --}}
                <div class="tab-pane fade show active" id="register" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white p-3">
                            <h5 class="mb-0">Create New Payment Voucher</h5>
                        </div>
                        <div class="card-body p-4 p-md-5">
                            <form id="voucherForm" method="POST" action="{{ route('insurance_broking.accounts.payment_vouchers.store') }}" enctype="multipart/form-data" class="row g-4">
                                @csrf
                                <div class="col-md-6">
                                    <label for="payee_name" class="form-label fw-bold">Payee / Client Name*</label>
                                    <input list="client_list" class="form-control" id="payee_name" name="payee_name" placeholder="Type or select a name..." required>
                                    <datalist id="client_list">
                                        @foreach($finalClientList as $name)
                                            <option value="{{ $name }}">{{ $name }}</option>
                                        @endforeach
                                    </datalist>
                                </div> 
                
                                <div class="col-md-3">
                                    <label for="currency" class="form-label fw-bold">Currency*</label>
                                    <select class="form-select" id="currency" name="currency" required>
                                        <option value="ZMW" selected>ZMW</option>
                                        <option value="USD">USD</option>
                                    </select>
                                </div>
                
                                <div class="col-md-3">
                                    <label for="amount" class="form-label fw-bold">Amount*</label>
                                    <input type="number" step="0.01" class="form-control" name="amount" id="amount" placeholder="0.00" required>
                                </div>
                
                                <div class="col-md-6">
                                    <label for="expense_category" class="form-label fw-bold">Expense Category*</label>
                                    <select class="form-select" name="expense_category" id="expense_category" required>
                                        <option value="" selected disabled>Choose category...</option>
                                        <option value="Advertising/Promotions">Advertising/Promotions</option>
                                        <option value="Automobile Expenses">Automobile Expenses</option>
                                        <option value="Bad Debts">Bad Debts</option>
                                        <option value="Dues/Subscriptions/Contributions">Dues/Subscriptions/Contributions</option>
                                        <option value="Education/ Training">Education/ Training</option>
                                        <option value="Insurer Remittances">Insurer Remittances</option>
                                        <option value="IT Expenses">IT Expenses</option>
                                        <option value="Miscellaneous">Miscellaneous</option>
                                        <option value="Occupancy Expenses">Occupancy Expenses</option>
                                        <option value="Office Equipment">Office Equipment</option>
                                        <option value="Outside Service">Outside Service</option>
                                        <option value="P&C Insurance">P&C Insurance</option>
                                        <option value="Postage">Postage</option>
                                        <option value="Professional Fees">Professional Fees</option>
                                        <option value="Supplies/Printing">Supplies/Printing</option>
                                        <option value="Taxes/Licenses">Taxes/Licenses</option>
                                        <option value="Telephone">Telephone</option>
                                        <option value="Travel/Entertainment/Convention">Travel/Entertainment/Convention</option>
                                    </select>
                                </div>
                
                                <div class="col-md-6">
                                    <label class="form-label fw-bold">Payment Method*</label>
                                    <select class="form-select" name="payment_method" required>
                                        <option value="Bank Transfer">Bank Transfer</option>
                                        <option value="Cheque">Cheque</option>
                                        <option value="Cash">Cash</option>
                                    </select>
                                </div>

                                {{-- Modified Field: Array input and multiple attribute allowed --}}
                                <div class="col-md-12">
                                    <label for="supporting_documents" class="form-label fw-bold">Supporting Documents</label>
                                    <input type="file" class="form-control" name="supporting_documents[]" id="supporting_documents" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                                    <div class="form-text">You can select multiple files at once. Accepted Formats: PDF, Word, Images (Max: 5MB per file)</div>
                                </div>
                
                                <div class="col-12">
                                    <label for="description" class="form-label fw-bold">Description / Particulars*</label>
                                    <textarea class="form-control" name="description" rows="3" placeholder="State reason for payment..." required></textarea>
                                </div>
                
                                <div class="col-12 pt-3 border-top text-end">
                                    <button type="reset" class="btn btn-outline-secondary px-4 me-2">Clear Form</button>
                                    <button type="submit" class="btn btn-primary px-5">Generate & Submit for Approval</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Tab 2: Pending Approval Vouchers --}}
                <div class="tab-pane fade" id="pending" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-warning p-3">
                            <h5 class="mb-0 text-dark"><i class="bi bi-hourglass-split me-2"></i>Pending Accountant Review</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Payee</th>
                                        <th>Amount</th>
                                        <th>Submitted Date</th>
                                        <th>Status</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($pendingItems as $row)
                                        <tr>
                                            <td class="ps-3 fw-bold text-primary">#{{ $row->id }}</td>
                                            <td>{{ ucwords(strtolower($row->client_name)) }}</td>
                                            <td class="fw-bold">{{ $row->currency }} {{ number_format($row->amount, 2) }}</td>
                                            <td class="text-muted small">{{ \Carbon\Carbon::parse($row->created_at)->format('d M, Y') }}</td>
                                            <td><span class="badge rounded-pill bg-warning text-dark">Awaiting Approval</span></td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning shadow-sm edit-voucher-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editVoucherModal"
                                                            data-id="{{ $row->id }}"
                                                            data-name="{{ ucwords(strtolower(trim($row->client_name))) }}"
                                                            data-category="{{ $row->expense_category }}" 
                                                            data-amount="{{ $row->amount }}"
                                                            data-currency="{{ $row->currency }}"
                                                            data-method="{{ $row->payment_method }}"
                                                            data-description="{{ $row->description }}"
                                                            {{-- Passes database column value directly (expects a JSON string or comma-separated string) --}}
                                                            data-documents="{{ is_array($row->supporting_documents) ? json_encode($row->supporting_documents) : $row->supporting_documents }}"
                                                            data-date="{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d\TH:i') }}">
                                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                                    </button>
                                                    
                                                    {{-- Document View dropdown for multi-files --}}
                                                    @if(!empty($row->supporting_documents))
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="bi bi-file-earmark-text me-1"></i> Docs
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                                @php
                                                                    $docs = is_string($row->supporting_documents) ? json_decode($row->supporting_documents, true) ?? explode(',', $row->supporting_documents) : $row->supporting_documents;
                                                                @endphp
                                                                @foreach((array)$docs as $index => $doc)
                                                                    <li>
                                                                        <a href="{{ asset('storage/' . trim($doc)) }}" target="_blank" class="dropdown-item small py-2">
                                                                            <i class="bi bi-file-earmark me-2 text-primary"></i>Document #{{ $index + 1 }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm" disabled>
                                                            <i class="bi bi-file-earmark-x me-1"></i> No Doc
                                                        </button>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-5 text-muted italic">No pending vouchers found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                {{-- Tab 3: Approved History Vouchers --}}
                <div class="tab-pane fade" id="approved" role="tabpanel">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-success text-white p-3">
                            <h5 class="mb-0"><i class="bi bi-check-circle-fill me-2"></i>Approved Vouchers</h5>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">ID</th>
                                        <th>Payee</th>
                                        <th>Expense Category</th>
                                        <th>Amount</th>
                                        <th>Approval Date</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($approvedItems as $row)
                                        <tr>
                                            <td class="ps-3 fw-bold text-success">#{{ $row->id }}</td>
                                            <td>{{ ucwords(strtolower($row->client_name)) }}</td>
                                            <td>
                                                <span class="badge bg-light text-dark border shadow-sm px-2.5 py-1.5 small font-monospace">
                                                    <i class="bi bi-tag-fill me-1 text-secondary"></i>{{ $row->expense_category ?? 'Uncategorized' }}
                                                </span>
                                            </td>
                                            <td class="fw-bold text-success">{{ $row->currency }} {{ number_format($row->amount, 2) }}</td>
                                            <td>
                                                <div class="small fw-bold text-dark">{{ \Carbon\Carbon::parse($row->updated_at)->format('M d, Y') }}</div>
                                                <div class="text-muted small">{{ \Carbon\Carbon::parse($row->updated_at)->format('H:i A') }}</div>
                                            </td>
                                            <td class="text-center">
                                                <div class="btn-group" role="group">
                                                    <button type="button" 
                                                            class="btn btn-sm btn-outline-warning shadow-sm edit-voucher-btn" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#editVoucherModal"
                                                            data-id="{{ $row->id }}"
                                                            data-name="{{ ucwords(strtolower(trim($row->client_name))) }}"
                                                            data-category="{{ $row->expense_category }}" 
                                                            data-amount="{{ $row->amount }}"
                                                            data-currency="{{ $row->currency }}"
                                                            data-method="{{ $row->payment_method }}"
                                                            data-description="{{ $row->description }}"
                                                            data-documents="{{ is_array($row->supporting_documents) ? json_encode($row->supporting_documents) : $row->supporting_documents }}"
                                                            data-date="{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d\TH:i') }}">
                                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                                    </button>
                                                    
                                                    {{-- Document View dropdown for multi-files --}}
                                                    @if(!empty($row->supporting_documents))
                                                        <div class="btn-group" role="group">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                                <i class="bi bi-file-earmark-text me-1"></i> Docs
                                                            </button>
                                                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                                @php
                                                                    $docs = is_string($row->supporting_documents) ? json_decode($row->supporting_documents, true) ?? explode(',', $row->supporting_documents) : $row->supporting_documents;
                                                                @endphp
                                                                @foreach((array)$docs as $index => $doc)
                                                                    <li>
                                                                        <a href="{{ asset('storage/' . trim($doc)) }}" target="_blank" class="dropdown-item small py-2">
                                                                            <i class="bi bi-file-earmark me-2 text-primary"></i>Document #{{ $index + 1 }}
                                                                        </a>
                                                                    </li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @else
                                                        <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm" disabled>
                                                            <i class="bi bi-file-earmark-x me-1"></i> No Doc
                                                        </button>
                                                    @endif
                                                    
                                                    <a href="{{ route('insurance_broking.accounts.payment_vouchers.print', $row->id) }}" class="btn btn-sm btn-outline-primary shadow-sm">
                                                        <i class="bi bi-printer me-1"></i> Print / View
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center py-5 text-muted">No approved history found.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div> 
        </div>
    </div>
</div>

{{-- MODAL FOR EDITING VOUCHER --}}
<div class="modal fade" id="editVoucherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editVoucherModalLabel"><i class="bi bi-pencil-square me-2"></i>Edit Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('insurance_broking.accounts.payment_vouchers.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="modal_voucher_id">
                    
                    {{-- Tracking hidden input for deleted files arrays --}}
                    <input type="hidden" name="deleted_documents" id="modal_deleted_documents" value="[]">

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Payee / Client Name</label>
                            <input type="text" name="client_name" id="modal_client_name" class="form-control" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label fw-bold">Date Created</label>
                            <input type="datetime-local" name="created_at" id="modal_created_at" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-bold">Expense Category</label>
                            <select name="expense_category" id="modal_expense_category" class="form-select" required>
                                <option value="" selected disabled>Choose category...</option>
                                <option value="Advertising/Promotions">Advertising/Promotions</option>
                                <option value="Automobile Expenses">Automobile Expenses</option>
                                <option value="Bad Debts">Bad Debts</option>
                                <option value="Dues/Subscriptions/Contributions">Dues/Subscriptions/Contributions</option>
                                <option value="Education/ Training">Education/ Training</option>
                                <option value="Insurer Remittances">Insurer Remittances</option>
                                <option value="IT Expenses">IT Expenses</option>
                                <option value="Miscellaneous">Miscellaneous</option>
                                <option value="Occupancy Expenses">Occupancy Expenses</option>
                                <option value="Office Equipment">Office Equipment</option>
                                <option value="Outside Service">Outside Service</option>
                                <option value="P&C Insurance">P&C Insurance</option>
                                <option value="Postage">Postage</option>
                                <option value="Professional Fees">Professional Fees</option>
                                <option value="Supplies/Printing">Supplies/Printing</option>
                                <option value="Taxes/Licenses">Taxes/Licenses</option>
                                <option value="Telephone">Telephone</option>
                                <option value="Travel/Entertainment/Convention">Travel/Entertainment/Convention</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Amount</label>
                            <input type="number" step="0.01" name="amount" id="modal_amount" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold">Currency</label>
                            <select name="currency" id="modal_currency" class="form-select">
                                <option value="ZMW">ZMW</option>
                                <option value="USD">USD</option>
                            </select>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Payment Method</label>
                            <select name="payment_method" id="modal_payment_method" class="form-select">
                                <option value="Cash">Cash</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="EFT/Transfer">EFT/Transfer</option>
                                <option value="Mobile Money">Mobile Money</option>
                            </select>
                        </div>

                        {{-- Multi-upload update setup --}}
                        <!-- <div class="col-md-12">
                            <label for="modal_supporting_documents" class="form-label fw-bold">Add Additional Documents</label>
                            <input type="file" class="form-control mb-2" name="supporting_documents[]" id="modal_supporting_documents" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>
                            
                            <label class="form-label d-block text-secondary small fw-bold">Current Files Attached (Click X to remove):</label>
                            <div id="modal_docs_preview_container" class="d-flex flex-wrap gap-2 p-2 bg-light rounded border">
                                {{-- Loaded via JS loop context --}}
                            </div>
                        </div> -->
                       <div class="mb-4">
                        <label class="form-label fw-bold mb-2">Supporting Documents</label>
                        
                        <input type="file" class="form-control mb-3" name="supporting_documents[]" id="modal_supporting_documents" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" multiple>

                        <label class="form-label d-block text-secondary small fw-bold mb-2">Current Files Attached (Click Delete to remove):</label>
                        
                        <div id="modal_docs_preview_container">
                            @if(!empty($voucher->supporting_documents))
                                @php
                                    $documents = is_array($voucher->supporting_documents) 
                                        ? $voucher->supporting_documents 
                                        : json_decode($voucher->supporting_documents, true) ?? [];
                                @endphp

                                @if(count($documents) > 0)
                                    <div class="list-group mb-3 shadow-sm rounded">
                                        @foreach($documents as $index => $file)
                                            <div class="list-group-item d-flex justify-content-between align-items-center p-3 file-row" id="voucher-file-{{ $index }}">
                                                <div class="d-flex align-items-center text-truncate me-3">
                                                    <i class="bi bi-file-earmark-text text-primary fs-4 me-3"></i>
                                                    <div class="text-truncate">
                                                        <a href="{{ asset('storage/' . $file) }}" target="_blank" class="text-decoration-none fw-semibold text-dark text-truncate d-block">
                                                            {{ basename($file) }}
                                                        </a>
                                                    </div>
                                                </div>
                                                
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger rounded-pill px-3 flex-shrink-0"
                                                        onclick="deleteFile('{{ route('insurance_broking.accounts.payment_vouchers.delete_file', $voucher->id) }}', '{{ $file }}', 'voucher-file-{{ $index }}')">
                                                    <i class="bi bi-trash me-1"></i> Delete
                                                </button>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="p-3 text-center text-muted bg-light rounded small border mb-3">No attachments found.</div>
                                @endif
                            @else
                                <div class="p-3 text-center text-muted bg-light rounded small border mb-3">No attachments found.</div>
                            @endif
                        </div>
                    </div>

                        <div class="col-12">
                            <label class="form-label fw-bold">Description / Particulars</label>
                            <textarea name="description" id="modal_description" class="form-control" rows="3" required></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editVoucherModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;

            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const category = button.getAttribute('data-category');
            const amount = button.getAttribute('data-amount');
            const currency = button.getAttribute('data-currency');
            const method = button.getAttribute('data-method');
            const description = button.getAttribute('data-description');
            const rawDocuments = button.getAttribute('data-documents');
            const date = button.getAttribute('data-date');

            // Populate text targets
            document.getElementById('modal_voucher_id').value = id;
            document.getElementById('modal_client_name').value = name;
            document.getElementById('modal_expense_category').value = category;
            document.getElementById('modal_amount').value = amount;
            document.getElementById('modal_currency').value = currency;
            document.getElementById('modal_payment_method').value = method;
            document.getElementById('modal_description').value = description;
            document.getElementById('modal_created_at').value = date;
            
            // Re-render multi-document view nodes dynamically
            const previewContainer = document.getElementById('modal_docs_preview_container');
            previewContainer.innerHTML = ''; 

            if (rawDocuments && rawDocuments.trim() !== "" && rawDocuments !== "[]") {
                let parsedDocs = [];
                try {
                    // Try parsing JSON format first
                    parsedDocs = JSON.parse(rawDocuments);
                } catch(e) {
                    // Fall back to comma separation logic strings
                    parsedDocs = rawDocuments.split(',');
                }

                if (Array.isArray(parsedDocs) && parsedDocs.length > 0) {
                    parsedDocs.forEach((docPath, idx) => {
                        if (docPath.trim() !== "") {
                            const cleanPath = docPath.trim();
                            const fileName = cleanPath.split('/').pop();
                            const elementId = `voucher-file-${idx}`;
                            
                            // Generate the exact named route endpoint url structure using JavaScript string interpolation
                            const deleteRoute = `/insurance-broking/accounts/payment-vouchers/${id}/delete-file`;

                            // Create the outer layout list row container item matching your design format
                            const rowDiv = document.createElement('div');
                            rowDiv.className = 'list-group-item d-flex justify-content-between align-items-center p-3 file-row';
                            rowDiv.id = elementId;

                            // Build the internal display structural markup layout string
                            rowDiv.innerHTML = `
                                <div class="d-flex align-items-center text-truncate me-3">
                                    <i class="bi bi-file-earmark-text text-primary fs-4 me-3"></i>
                                    <div class="text-truncate">
                                        <a href="/storage/${cleanPath}" target="_blank" class="text-decoration-none fw-semibold text-dark text-truncate d-block">
                                            ${fileName}
                                        </a>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 flex-shrink-0">
                                    <i class="bi bi-trash me-1"></i> Delete
                                </button>
                            `;

                            // Connect your custom direct asynchronous deletion logic hook right onto the action click event
                            rowDiv.querySelector('button').onclick = function(e) {
                                e.preventDefault();
                                deleteFile(deleteRoute, cleanPath, elementId);
                            };

                            previewContainer.appendChild(rowDiv);
                        }
                    });
                } else {
                    showNoAttachmentsMessage(previewContainer);
                }
            } else {
                showNoAttachmentsMessage(previewContainer);
            }

            document.getElementById('editVoucherModalLabel').innerHTML = `<i class="bi bi-pencil-square me-2"></i>Edit Voucher #${id}`;
        });
    }

    // Fix: Updated fallback form validation reference safely if voucherForm uses dynamic targeting properties
    const voucherForm = document.getElementById('voucherForm') || document.querySelector('form[action*="payment_vouchers.update"]');
    if (voucherForm) {
        voucherForm.addEventListener('submit', function(e) {
            if (!confirm("Are you sure you want to save changes to this voucher?")) {
                e.preventDefault();
            }
        });
    }
});

// Universal helper clean fallbacks wrapper notice
function showNoAttachmentsMessage(container) {
    container.innerHTML = `
        <div class="list-group-item text-center text-muted small p-3 bg-light italic">
            <i class="bi bi-file-earmark-x me-1"></i> No current files attached.
        </div>
    `;
}

// DIRECT AJAX DELETION MECHANISM
function deleteFile(routeUrl, filePath, elementId) {
    if (!confirm('Are you sure you want to permanently delete this file?')) return;

    // Fetch CSRF Token directly from the active form context markup definition
    const token = document.querySelector('input[name="_token"]').value;

    fetch(routeUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ 
            file_path: filePath 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Smoothly remove the file element from the DOM mapping grid
            const element = document.getElementById(elementId);
            if (element) {
                element.style.transition = 'all 0.3s ease';
                element.style.opacity = '0';
                setTimeout(() => {
                    element.remove();
                    
                    // If everything is deleted, render the empty attachments banner notice fallback block
                    const previewContainer = document.getElementById('modal_docs_preview_container');
                    if (previewContainer && previewContainer.children.length === 0) {
                        showNoAttachmentsMessage(previewContainer);
                    }
                }, 300);
            }
        } else {
            alert(data.message || 'Failed to delete the file.');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while trying to delete the file.');
    });
}
</script>
@endpush