@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4 d-print-none bg-light">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Ledger Type</label>
                    <select class="form-select" id="typeFilter" onchange="toggleLedgerView()">
                        <option value="clients">Client Ledgers (Debtors)</option>
                        <option value="insurers">Insurer Ledgers (Creditors)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Currency</label>
                    <select class="form-select" id="currencyFilter" onchange="toggleLedgerView()">
                        <option value="ZMW">ZMW Only</option>
                        <option value="USD">USD Only</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small">Search Name</label>
                    <input type="text" id="nameSearch" class="form-control" placeholder="Search..." onkeyup="filterByName()">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button onclick="window.print()" class="btn btn-outline-dark flex-fill">Print</button>
                    <button onclick="exportFilteredToCSV()" class="btn btn-success flex-fill">Export Excel</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Tables -->
    <div id="ledgerWrapper">
        @foreach (['clients', 'insurers'] as $type)
            @foreach (['ZMW', 'USD'] as $curr)
                <div class="ledger-group" data-type="{{ $type }}" data-curr="{{ $curr }}" 
                     style="{{ ($type == 'clients' && $curr == 'ZMW') ? '' : 'display:none;' }}">
                    
                    @foreach ($ledgers[$type][$curr] as $name => $txns)
                        @php $balance = 0; @endphp
                        <div class="card mb-4 border-0 shadow-sm account-card" data-name="{{ strtolower($name) }}">
                            <div class="card-header {{ $type == 'clients' ? 'bg-primary' : 'bg-dark' }} text-white py-2">
                                <h6 class="mb-0 small fw-bold text-uppercase">{{ $name }} ({{ $curr }})</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped mb-0 small ledger-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th><th>Ref</th><th>Description</th>
                                            <th class="text-end">DR</th><th class="text-end">CR</th>
                                            <th class="text-end">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($txns as $t)
                                            @php 
                                                // Debtors (Clients): DR increases balance. Creditors (Insurers): CR increases balance.
                                                $balance += ($type == 'clients') ? ($t['dr'] - $t['cr']) : ($t['cr'] - $t['dr']); 
                                            @endphp
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($t['date'])->format('d-M-y') }}</td>
                                                <td>{{ $t['ref'] }}</td>
                                                <td>{{ $t['desc'] }}</td>
                                                <td class="text-end">{{ number_format($t['dr'], 2) }}</td>
                                                <td class="text-end">{{ number_format($t['cr'], 2) }}</td>
                                                <td class="text-end fw-bold">{{ number_format($balance, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endforeach
    </div>
</div>

@push('scripts')
<script>
// Existing JS Logic for toggleLedgerView, filterByName, and exportFilteredToCSV remains identical
function toggleLedgerView() {
    const selectedType = document.getElementById('typeFilter').value;
    const selectedCurr = document.getElementById('currencyFilter').value;
    document.getElementById('nameSearch').value = "";

    document.querySelectorAll('.ledger-group').forEach(group => {
        group.style.display = (group.dataset.type === selectedType && group.dataset.curr === selectedCurr) ? 'block' : 'none';
    });
    document.querySelectorAll('.account-card').forEach(card => card.style.display = 'block');
}

function filterByName() {
    const searchTerm = document.getElementById('nameSearch').value.toLowerCase();
    const activeGroup = document.querySelector('.ledger-group[style*="display: block"]');
    if (!activeGroup) return;

    activeGroup.querySelectorAll('.account-card').forEach(card => {
        card.style.display = card.dataset.name.includes(searchTerm) ? 'block' : 'none';
    });
}
</script>
@endpush
@endsection