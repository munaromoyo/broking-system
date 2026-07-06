@extends('layouts.app') {{-- Assuming you have a base layout --}}

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="card shadow-sm mb-4 d-print-none">
        <div class="card-body bg-light">
            <form method="GET" action="{{ route('finance.income') }}" class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Fiscal Year</label>
                    <select name="year" class="form-select">
                        @for($y = date('Y'); $y >= 2023; $y--)
                            <option value="{{ $y }}" {{ $y == $targetYear ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small">Rate (1 USD to ZMW)</label>
                    <input type="number" name="rate" step="0.01" class="form-control" value="{{ $conversionRate }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Update Report</button>
                </div>
                <div class="col-md-6 text-end">
                    <button type="button" onclick="window.print()" class="btn btn-outline-danger">Print PDF</button>
                    <button type="button" onclick="exportToCSV()" class="btn btn-outline-success ms-2">Export Excel</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white p-3">
            <h5 class="mb-0">Quarterly Income Statement (ZMW) - {{ $targetYear }}</h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover table-bordered mb-0" id="incomeStatementTable">
                <thead class="table-secondary text-center">
                    <tr>
                        <th class="text-start">Description</th>
                        <th>Q1</th><th>Q2</th><th>Q3</th><th>Q4</th>
                        <th class="bg-dark text-white">Annual Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-warning">
                        <td class="fw-bold ps-3">Balance Brought Forward (Opening)</td>
                        <td class="text-end fw-bold">{{ number_format($report['BBF'], 2) }}</td>
                        <td colspan="3" class="bg-light"></td>
                        <td class="text-end fw-bold">{{ number_format($report['BBF'], 2) }}</td>
                    </tr>

                    <tr><td colspan="6" class="bg-light fw-bold small text-primary">REVENUE (COMMISSIONS)</td></tr>
                    @php $tot_inv = 0; $tot_rec = 0; $tot_exp = 0; @endphp
                    
                    <tr>
                        <td class="ps-4 italic text-muted">Commissions Invoiced (Accrual)</td>
                        @foreach($report['ZMW_TOTAL'] as $q)
                            @php $tot_inv += $q['invoiced_comm']; @endphp
                            <td class="text-end text-muted small">{{ number_format($q['invoiced_comm'], 2) }}</td>
                        @endforeach
                        <td class="text-end fw-bold text-muted small">{{ number_format($tot_inv, 2) }}</td>
                    </tr>

                    <tr>
                        <td class="ps-4">Commissions Received (Actual Cash)</td>
                        @foreach($report['ZMW_TOTAL'] as $q)
                            @php $tot_rec += $q['received_comm']; @endphp
                            <td class="text-end">{{ number_format($q['received_comm'], 2) }}</td>
                        @endforeach
                        <td class="text-end fw-bold">{{ number_format($tot_rec, 2) }}</td>
                    </tr>

                    <tr><td colspan="6" class="bg-light fw-bold small text-danger">OPERATING EXPENSES</td></tr>
                    <tr>
                        <td class="ps-4 text-danger">Payment Vouchers (Expenses)</td>
                        @foreach($report['ZMW_TOTAL'] as $q)
                            @php $tot_exp += $q['expenses']; @endphp
                            <td class="text-end text-danger">({{ number_format($q['expenses'], 2) }})</td>
                        @endforeach
                        <td class="text-end fw-bold text-danger">({{ number_format($tot_exp, 2) }})</td>
                    </tr>

                    <tr class="table-info">
                        <td class="fw-bold ps-3">Advance Receipts (Future Periods)</td>
                        <td colspan="3"></td>
                        <td class="text-end fw-bold">{{ number_format($report['ADVANCES'], 2) }}</td>
                        <td class="text-end fw-bold">{{ number_format($report['ADVANCES'], 2) }}</td>
                    </tr>

                    <tr class="table-dark">
                        <th class="ps-3">NET OPERATING SURPLUS</th>
                        @foreach($report['ZMW_TOTAL'] as $q)
                            @php $net = $q['received_comm'] - $q['expenses']; @endphp
                            <th class="text-end {{ $net >= 0 ? 'text-success' : 'text-warning' }}">
                                {{ number_format($net, 2) }}
                            </th>
                        @endforeach
                        <th class="text-end">{{ number_format($tot_rec - $tot_exp, 2) }}</th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function exportToCSV() {
    let csv = [];
    const rows = document.querySelectorAll("#incomeStatementTable tr");
    rows.forEach(row => {
        let cols = row.querySelectorAll("td, th");
        let rowData = Array.from(cols).map(col => `"${col.innerText.replace(/,/g, '')}"`);
        csv.push(rowData.join(","));
    });
    const csvFile = new Blob([csv.join("\n")], {type: "text/csv"});
    const link = document.createElement("a");
    link.download = "Income_Statement_{{ $targetYear }}.csv";
    link.href = window.URL.createObjectURL(csvFile);
    link.click();
}
</script>
@endsection