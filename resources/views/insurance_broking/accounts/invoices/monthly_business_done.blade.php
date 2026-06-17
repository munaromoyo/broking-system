@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="m-0 fw-bold text-uppercase text-secondary">Monthly Business Done</h6>
        <button id="exportCsvBtn" class="btn btn-success shadow-sm">
            <i class="fa fa-file-excel me-1"></i> Export CSV
        </button>
    </div>

    <div class="row mb-3 g-2">
        <div class="col-md-8">
            <form method="GET" action="{{ url()->current() }}" id="filterForm" class="row g-2 align-items-center">
                <div class="col-auto">
                    <div class="input-group">
                        <span class="input-group-text bg-white">Period:</span>
                        <select name="period" class="form-select" onchange="toggleCustomDates(this.value)">
                            <option value="current" {{ $period == 'current' ? 'selected' : '' }}>Current Month</option>
                            <option value="last" {{ $period == 'last' ? 'selected' : '' }}>Last Month</option>
                            <option value="custom" {{ $period == 'custom' ? 'selected' : '' }}>Custom Range</option>
                            <option value="all" {{ $period == 'all' ? 'selected' : '' }}>All Time</option>
                        </select>
                    </div>
                </div>
    
                <div class="col-auto d-flex gap-2 {{ $period != 'custom' ? 'd-none' : '' }}" id="customDateInputs">
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}">
                    <span class="align-self-center">to</span>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </form>
        </div>
        <div class="col-md-4 text-end">
            <input type="text" id="invoiceSearch" class="form-control" placeholder="Search filtered records...">
        </div>
    </div>

    <div class="table-responsive shadow-sm rounded">
        <table class="table table-hover border align-middle" id="businessTable">
            <thead class="table-light">
                <tr>
                    <th>Invoice #</th>
                    <th>Client Name</th>
                    <th class="d-none">Insurer</th> 
                    <th class="d-none">Policy Name</th> 
                    <th>Start Date</th>
                    <th class="d-none">Expiry Date</th> 
                    <th>Currency</th>
                    <th class="d-none">Basic Premium</th> 
                    <th class="d-none">Discount Rate</th> 
                    <th class="d-none">Levy Rate</th>
                    <th class="d-none">Premium Levy</th> 
                    <th class="d-none">Commission Amount</th>
                    <th class="text-end">Gross Premium</th>
                </tr>
            </thead>
            <tbody id="invoiceTable">
                @forelse($invoices as $invoice)
                    <tr>
                        <td><strong>INV{{ str_pad($invoice->invoice_number, 4, '0', STR_PAD_LEFT) }}</strong></td>
                        <td>{{ ucwords(strtolower($invoice->client_name)) }}</td>
                        <td class="d-none">{{ $invoice->insurer ?? 'N/A' }}</td>
                        <td class="d-none">{{ $invoice->policy_name }}</td>
                        <td>{{ $invoice->policy_start_date }}</td>
                        <td class="d-none">{{ $invoice->policy_expiry_date }}</td>
                        <td><span class="badge bg-light text-dark border">{{ $invoice->policy_currency }}</span></td>
                        <td class="d-none">{{ number_format((float)$invoice->basic_premium, 2, '.', '') }}</td>
                        <td class="d-none">{{ $invoice->discount_rate }}%</td>
                        <td class="d-none">{{ $invoice->premium_levy_rate }}</td>
                        <td class="d-none">{{ number_format((float)$invoice->premium_levy, 2, '.', '') }}</td>
                        <td class="d-none">{{ number_format((float)($invoice->commission_amount ?? 0), 2, '.', '') }}</td>
                        <td class="text-end fw-bold">{{ number_format((float)$invoice->gross_premium, 2, '.', ',') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="13" class="text-center py-4 text-muted">No records found for this period.</td>
                    </tr>
                @endforelse
            </tbody>
            
            @if($invoices->isNotEmpty())
            <tfoot class="table-light">
                <tr class="fw-bold">
                    <td colspan="12" class="text-end">Total USD Business Done:</td>
                    <td class="text-end text-primary">
                        {{ number_format($totalUSD, 2, '.', ',') }}
                    </td>
                </tr>
                <tr class="fw-bold">
                    <td colspan="12" class="text-end">Total ZMW Business Done:</td>
                    <td class="text-end text-success">
                        {{ number_format($totalZMW, 2, '.', ',') }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
function toggleCustomDates(value) {
    const customDiv = document.getElementById('customDateInputs');
    if (value === 'custom') {
        customDiv.classList.remove('d-none');
        customDiv.classList.add('d-flex');
    } else {
        customDiv.classList.add('d-none');
        customDiv.classList.remove('d-flex');
        document.getElementById('filterForm').submit();
    }
}

/**
 * Live Search Filter
 */
document.getElementById('invoiceSearch').addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('#invoiceTable tr');

    rows.forEach(row => {
        if(row.cells.length > 1) {
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        }
    });
});

/**
 * CSV Export Logic (Maintains structural context natively)
 */
document.getElementById('exportCsvBtn').addEventListener('click', function() {
    const table = document.getElementById('businessTable');
    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    for (const row of rows) {
        if (row.style.display === 'none') continue;

        const rowData = [];
        const cols = row.querySelectorAll('td, th');
        
        for (let j = 0; j < cols.length; j++) {
            let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, "").replace(/(\s\s+)/g, ' ').trim();
            data = data.replace(/"/g, '""'); 
            rowData.push('"' + data + '"');
        }
        csv.push(rowData.join(","));
    }

    const csvContent = csv.join("\n");
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement("a");
    
    const timestamp = new Date().toISOString().split('T')[0];
    link.setAttribute("href", url);
    link.setAttribute("download", `Business_Report_${timestamp}.csv`);
    link.style.visibility = 'hidden';
    
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
});
</script>
@endpush