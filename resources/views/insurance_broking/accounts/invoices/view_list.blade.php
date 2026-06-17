@extends('layouts.app') {{-- This inherits your global app wrapper layout --}}

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h6 class="mb-0 text-uppercase fw-bold text-secondary">Invoices</h6>
        <a href="{{ route('insurance_broking.accounts.invoices.monthly_business_done') }}" class="btn btn-outline-primary btn-sm shadow-sm">
            <i class="fa fa-chart-bar me-1"></i> Monthly Business Done
        </a>
    </div>

    <div class="mb-4">
        <div class="input-group shadow-sm">
            <span class="input-group-text bg-white border-end-0 text-muted">
                <i class="fa fa-search"></i>
            </span>
            <input type="text" id="invoiceSearch" class="form-control border-start-0 ps-0" placeholder="Search by client, ID, currency, or date...">
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive rounded">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-uppercase fs-7 border-bottom">
                    <tr>
                        <th class="ps-3" style="width: 15%;">Date</th>
                        <th style="width: 15%;">Invoice #</th>
                        <th style="width: 35%;">Client Name</th>
                        <th style="width: 10%;">Currency</th>
                        <th class="text-end" style="width: 15%;">Gross Premium</th>
                        <th class="text-end pe-3" style="width: 10%;">Action</th>
                    </tr>
                </thead>
                <tbody id="invoiceTable">
                    @forelse($invoice_infor as $invoice)
                        <tr>
                            <td class="ps-3 text-muted">
                                {{-- Checks if created_at is an instance of Carbon to format cleanly --}}
                                {{ is_string($invoice->created_at) ? $invoice->created_at : $invoice->created_at->format('Y-m-d H:i') }}
                            </td>
                            <td>
                                <span class="font-monospace fw-bold text-dark">
                                    INV{{ str_pad($invoice->invoice_number, 4, "0", STR_PAD_LEFT) }}
                                </span>
                            </td>
                            <td class="fw-semibold text-secondary">
                                {{ Str::title(Str::lower($invoice->client_name)) }}
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1.5">
                                    {{ $invoice->policy_currency }}
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark">
                                {{ number_format((float)$invoice->gross_premium, 2, '.', ',') }}
                            </td>
                            <td class="text-end pe-3">          
                                {{-- Update this route helper to point to your download endpoint --}}
                                <a href="{{ route('insurance_broking.accounts.invoices.generate_pdf', $invoice->invoice_number) }}" class="btn btn-danger btn-sm px-3 shadow-sm">
                                    <i class="fa fa-file-pdf me-1"></i> PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr id="noResultsRow">
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa fa-folder-open d-block mb-2 fs-3 text-opacity-25 text-secondary"></i>
                                No invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.getElementById('invoiceSearch').addEventListener('keyup', function() {
    const query = this.value.toLowerCase().trim();
    const rows = document.querySelectorAll('#invoiceTable tr:not(#noResultsRow)');
    let entriesFound = false;

    rows.forEach(row => {
        const matches = row.innerText.toLowerCase().includes(query);
        row.style.display = matches ? '' : 'none';
        if (matches) entriesFound = true;
    });

    let fallbackRow = document.getElementById('dynamicNoResults');
    if (!entriesFound && query !== '') {
        if (!fallbackRow) {
            fallbackRow = document.createElement('tr');
            fallbackRow.id = 'dynamicNoResults';
            fallbackRow.innerHTML = '<td colspan="6" class="text-center py-4 text-muted">No items match your search criteria.</td>';
            document.getElementById('invoiceTable').appendChild(fallbackRow);
        }
    } else if (fallbackRow) {
        fallbackRow.remove();
    }
});
</script>
@endsection