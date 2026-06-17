@extends('layouts.app') {{-- Assuming you have a base layout --}}

@section('content')
<style>
    /* ... Keep your CSS here or move to a CSS file ... */
    .invoice-table { width: 100%; border-collapse: collapse; background: #fff; }
    .slpn-badge { background: #eef0ff; color: #0004FF; padding: 3px 7px; border-radius: 4px; font-weight: bold; }
    .btn-generate { background-color: #0004FF; color: white; border: none; padding: 6px 12px; border-radius: 4px; cursor: pointer; }
</style>

<div class="container py-5">
    <h6 class="text-center mb-4" style="font-size: 20px;">Generate Invoices</h6>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="search-container position-relative">
            <input type="text" id="invoiceSearch" onkeyup="filterTable()" placeholder="Search Slip No..." class="form-control rounded-pill ps-5">
        </div>

        <a class="btn btn-link fw-bold" href="{{ route('insurance_broking.accounts.invoices.view_list') }}">
            <i class="fa fa-list"></i> View Invoices
        </a>
    </div>

    @if($placements->isNotEmpty())
        <table class="invoice-table table" id="invoiceTable">
            <thead>
                <tr>
                    <th>Slip Number</th>
                    <th>Insured Name</th>
                    <th>Currency</th>
                    <th>Gross Premium</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($placements as $placement)
                    <tr>
                        <td><span class="slpn-badge">SLPN{{ $placement->id }}</span></td>
                        <td>{{ Str::title($placement->insured) }}</td>
                        <td>{{ $placement->policy_currency }}</td>
                        <td>{{ number_format($placement->gross_premium, 2) }}</td>
                        <td class="text-center">
                            <form method="POST" action="{{ route('insurance.create_invoice') }}">
                                @csrf
                                <input type="hidden" name="slip_number" value="{{ $placement->id }}">
                                <button type="submit" class="btn-generate">Generate Invoice</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="text-center py-5 text-muted">
            <p>No pending invoices found.</p>
        </div>
    @endif
</div>

<script>
function filterTable() {
    let filter = document.getElementById("invoiceSearch").value.toUpperCase();
    let rows = document.querySelectorAll("#invoiceTable tbody tr");

    rows.forEach(row => {
        let text = row.innerText.toUpperCase();
        row.style.display = text.includes(filter) ? "" : "none";
    });
}
</script>
@endsection