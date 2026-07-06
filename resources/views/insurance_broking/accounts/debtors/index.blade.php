@extends('layouts.app')

@section('content')
<div class="container-fluid mt-4">

    <div class="card shadow border-0">
        <div class="card-header bg-dark text-white p-3">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="mb-0">Debtors Age Analysis Schedule</h4>
                    <span class="badge bg-primary">Benchmark: Policy Inception</span>
                </div>
                <div class="col-md-6 text-end">
                    <input type="text" id="debtorSearch" class="form-control d-inline-block w-75" placeholder="Live search Clients or Insurers...">
                    <a href="{{ route('insurance_broking.accounts.debtors.download', ['download' => 'debtors']) }}" class="btn btn-danger btn-sm">
                        <i class="fa fa-file-pdf"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-bordered mb-0 align-middle" id="debtorsTable">
                    <thead class="table-secondary text-center small text-uppercase">
                        <tr>
                            <th class="text-start">Client Name</th>
                            <th class="text-start">Insurer</th>
                            <th>Curr</th>
                            <th>Current</th>
                            <th>31-60</th>
                            <th>61-90</th>
                            <th>91+ Days</th>
                            <th class="table-primary">Total Due</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($debtors_master as $debtor)
                            <tr class="debtor-row">
                                <td class="fw-bold">{{ ucwords(strtolower($debtor['client'])) }}</td>
                                <td class="small text-muted">{{ $debtor['insurer'] }}</td>
                                <td class="text-center">{{ $debtor['currency'] }}</td>
                                <td class="text-end">{{ number_format($debtor['aging']['current'], 2) }}</td>
                                <td class="text-end">{{ number_format($debtor['aging']['31_60'], 2) }}</td>
                                <td class="text-end">{{ number_format($debtor['aging']['61_90'], 2) }}</td>
                                <td class="text-end text-danger fw-bold">{{ number_format($debtor['aging']['91_plus'], 2) }}</td>
                                <td class="text-end fw-bold table-primary">{{ number_format($debtor['balance_due'], 2) }}</td>
                                <td class="text-center">
                                    <form method="POST" action="{{ route('insurance_broking.accounts.debtors.view_statements') }}">
                                        @csrf
                                        <input type="hidden" name="client_name" value="{{ $debtor['client'] }}">
                                        <input type="hidden" name="currency" value="{{ $debtor['currency'] }}">
                                        <button type="submit" name="view_statement" class="btn btn-sm btn-outline-primary">View Statement</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        <tr id="noResults" style="display: none;">
                            <td colspan="9" class="text-center py-5 text-muted">No debtors found matching your criteria.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card-footer bg-light p-4">
            <div class="row">
                @foreach ($grand_totals as $curr => $t)
                <div class="col-md-4 mb-2">
                    <div class="card card-body shadow-sm border-0">
                        <h6 class="fw-bold text-muted border-bottom pb-2">{{ $curr }} CONSOLIDATED</h6>
                        <div class="d-flex justify-content-between small"><span>Current:</span> <strong>{{ number_format($t['current'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between small text-danger"><span>Total Aged:</span> <strong>{{ number_format($t['aged'], 2) }}</strong></div>
                        <div class="d-flex justify-content-between h6 mt-2 text-primary pt-2 border-top">
                            <span>Grand Total:</span> 
                            <strong>{{ number_format($t['total'], 2) }}</strong>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
    $("#debtorSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        var rows = $("#debtorsTable tbody .debtor-row");
        rows.each(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
        $("#noResults").toggle(rows.filter(':visible').length === 0);
    });
});
</script>
@endpush