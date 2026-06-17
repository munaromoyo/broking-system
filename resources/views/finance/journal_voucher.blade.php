@extends('layouts.app') {{-- Assuming you have a main layout --}}

@section('content')
<div class="card shadow border-0">
    <div class="card-header bg-dark text-white p-3 d-flex justify-content-between align-items-center">
        <h5 class="mb-0">New Journal Voucher (JV)</h5>
        <span class="badge bg-secondary">RIB Ledger Module</span>
    </div>
    <div class="card-body">
        {{-- Laravel Action and CSRF --}}
        <form id="jvForm" method="POST" action="{{ route('finance.journal-voucher.store') }}">
            @csrf
            <div class="row mb-3">
                <div class="col-md-2">
                    <label class="fw-bold small">JV Number</label>
                    <input type="text" name="jv_number" class="form-control" value="{{ $jv_number }}" readonly>
                </div>
                <div class="col-md-2">
                    <label class="fw-bold small">Date</label>
                    <input type="date" name="jv_date" class="form-control" value="{{ $current_date }}" required>
                </div>
                <div class="col-md-2">
                    <label class="fw-bold small text-primary">Currency</label>
                    <select name="currency" id="jvCurrency" class="form-select border-primary" onchange="updateCurrencyLabels()" required>
                        <option value="ZMW">ZMW (Kwacha)</option>
                        <option value="USD">USD (Dollar)</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="fw-bold small">General Narration</label>
                    <input type="text" name="main_narration" class="form-control" placeholder="Purpose of this adjustment..." required>
                </div>
            </div>

            <table class="table table-bordered" id="jvTable">
                <thead class="table-light small">
                    <tr>
                        <th style="width: 40%;">Account Name / Client / Insurer</th>
                        <th style="width: 25%;">Debit (DR)</th>
                        <th style="width: 25%;">Credit (CR)</th>
                        <th style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><input type="text" name="account[]" class="form-control form-control-sm" placeholder="Search account..." required></td>
                        <td><input type="number" name="dr[]" class="form-control form-control-sm dr-input text-end" step="0.01" value="0.00" onchange="calculateTotals()"></td>
                        <td><input type="number" name="cr[]" class="form-control form-control-sm cr-input text-end" step="0.01" value="0.00" onchange="calculateTotals()"></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                </tbody>
                <tfoot class="table-secondary fw-bold">
                    <tr>
                        <td class="text-end">Total <span class="curr-label">ZMW</span>:</td>
                        <td id="totalDr" class="text-end">0.00</td>
                        <td id="totalCr" class="text-end">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>

            <div class="alert alert-warning py-2 small d-none" id="balanceWarning">
                <i class="bi bi-exclamation-triangle"></i> Journal is out of balance. Debits must equal Credits.
            </div>

            <div class="d-flex justify-content-between align-items-center">
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="addRow()">
                    <i class="bi bi-plus-circle"></i> Add Entry Row
                </button>
                <div class="d-flex align-items-center gap-3">
                    <div id="statusIndicator" class="small fw-bold text-danger">● Unbalanced</div>
                    <button type="submit" id="submitBtn" class="btn btn-success px-5" disabled>Post Journal</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Note: I fixed the ID mismatch in your original code (jvCurrency vs currency)
    function updateCurrencyLabels() {
        const curr = document.getElementById('jvCurrency').value;
        document.querySelectorAll('.curr-label').forEach(el => el.innerText = curr);
    }

    function addRow() {
        const tbody = document.querySelector('#jvTable tbody');
        const row = `<tr>
            <td><input type="text" name="account[]" class="form-control form-control-sm" required></td>
            <td><input type="number" name="dr[]" class="form-control form-control-sm dr-input text-end" step="0.01" value="0.00" onchange="calculateTotals()"></td>
            <td><input type="number" name="cr[]" class="form-control form-control-sm cr-input text-end" step="0.01" value="0.00" onchange="calculateTotals()"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeRow(this)"><i class="bi bi-trash"></i></button></td>
        </tr>`;
        tbody.insertAdjacentHTML('beforeend', row);
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('#jvTable tbody tr');
        if(rows.length > 1) {
            btn.closest('tr').remove();
            calculateTotals();
        }
    }

    function calculateTotals() {
        let dr = 0; let cr = 0;
        document.querySelectorAll('.dr-input').forEach(i => dr += parseFloat(i.value || 0));
        document.querySelectorAll('.cr-input').forEach(i => cr += parseFloat(i.value || 0));
        
        document.getElementById('totalDr').innerText = dr.toLocaleString(undefined, {minimumFractionDigits: 2});
        document.getElementById('totalCr').innerText = cr.toLocaleString(undefined, {minimumFractionDigits: 2});
        
        const isBalanced = (dr.toFixed(2) === cr.toFixed(2) && dr > 0);
        const btn = document.getElementById('submitBtn');
        const warning = document.getElementById('balanceWarning');
        const indicator = document.getElementById('statusIndicator');

        if (isBalanced) {
            btn.disabled = false;
            warning.classList.add('d-none');
            indicator.innerText = "● Balanced";
            indicator.classList.replace('text-danger', 'text-success');
        } else {
            btn.disabled = true;
            if (dr > 0 || cr > 0) warning.classList.remove('d-none');
            indicator.innerText = "● Unbalanced";
            indicator.classList.replace('text-success', 'text-danger');
        }
    }
</script>
@endsection