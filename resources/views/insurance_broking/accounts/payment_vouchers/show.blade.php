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
                            <form id="voucherForm" method="POST" action="{{ route('insurance_broking.accounts.payment_vouchers.store') }}" class="row g-4">
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
                                                <span class="text-muted small"><em>Under Review</em></span>
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
                                        <th>Expense Category</th> {{-- Added Column Header --}}
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
                                            
                                            {{-- Added Expense Category Display Column --}}
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
                                                            data-category="{{ $row->expense_category }}" {{-- Added data attribute for Modal JS mapping --}}
                                                            data-amount="{{ $row->amount }}"
                                                            data-currency="{{ $row->currency }}"
                                                            data-method="{{ $row->payment_method }}"
                                                            data-description="{{ $row->description }}"
                                                            data-date="{{ \Carbon\Carbon::parse($row->created_at)->format('Y-m-d\TH:i') }}">
                                                        <i class="bi bi-pencil-square me-1"></i> Edit
                                                    </button>
                                                    
                                                    <a href="{{ route('insurance_broking.accounts.payment_vouchers.print', $row->id) }}" class="btn btn-sm btn-outline-primary shadow-sm">
                                                        <i class="bi bi-printer me-1"></i> Print / View
                                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        {{-- Incremented colspan to 6 to balance out the newly added column layout --}}
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

{{-- MODAL FOR EDITING APPROVED VOUCHER --}}
<div class="modal fade" id="editVoucherModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="editVoucherModalLabel">
                    <i class="bi bi-pencil-square me-2"></i>Edit Approved Voucher
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('insurance_broking.accounts.payment_vouchers.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <input type="hidden" name="id" id="modal_voucher_id">

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

            // Map data attributes to variables
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const amount = button.getAttribute('data-amount');
            const currency = button.getAttribute('data-currency');
            const method = button.getAttribute('data-method');
            const description = button.getAttribute('data-description');
            const date = button.getAttribute('data-date');

            // Populate Modal Fields
            document.getElementById('modal_voucher_id').value = id;
            document.getElementById('modal_client_name').value = name;
            document.getElementById('modal_amount').value = amount;
            document.getElementById('modal_currency').value = currency;
            document.getElementById('modal_payment_method').value = method;
            document.getElementById('modal_description').value = description;
            document.getElementById('modal_created_at').value = date;
            
            document.getElementById('editVoucherModalLabel').innerHTML = `<i class="bi bi-pencil-square me-2"></i>Edit Approved Voucher #${id}`;
        });
    }

    // Confirmation Interception
    document.getElementById('voucherForm').addEventListener('submit', function(e) {
        if(!confirm("Are you sure you want to submit this voucher for approval?")) {
            e.preventDefault();
        }
    });
});
</script>
@endpush