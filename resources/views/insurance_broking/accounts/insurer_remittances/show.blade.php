@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">

    <h4 class="mb-4">All Insurers Remittance Statement
        @if($fromDate && $toDate)
            ({{ \Carbon\Carbon::parse($fromDate)->format('d M, Y') }} to {{ \Carbon\Carbon::parse($toDate)->format('d M, Y') }})
        @endif
    </h4>

    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <form method="GET" action="{{ url()->current() }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label fw-bold">From Date:</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-bold">To Date:</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate }}" required>
                </div>

                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">
                        <i class="bi bi-play-fill"></i> Generate Report
                    </button>

                    <a href="#" class="btn btn-outline-info">
                        <i class="bi bi-arrow-repeat"></i> PIA Returns
                    </a>
                        
                    @if($records->isNotEmpty())
                        <a href="{{ route('insurance_broking.accounts.insurer_remittances.summary', [
                            'from_date' => request('from_date'),
                            'to_date'   => request('to_date')
                        ]) }}" class="btn btn-success">
                            <i class="bi bi-file-earmark-excel"></i> Export
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Date</th>
                        <th>Insurer</th>
                        <th>Reference</th>
                        <th>Receipt #</th>
                        <th>Invoice #</th>
                        <th class="text-end">Amount Paid</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalZMW = 0; $totalUSD = 0; @endphp

                    @forelse($records as $row)
                        @php
                            $currency = strtoupper($row->policy_currency ?? '');
                            $amount   = (float) ($row->insurer_premium_received ?? 0);

                            if ($currency === 'USD') {
                                $totalUSD += $amount;
                            } else {
                                $totalZMW += $amount;
                            }
                        @endphp
                        <tr>
                            <td>
                                @if($row->remittance_date)
                                    {{ \Carbon\Carbon::parse($row->remittance_date)->format('d M, Y') }}
                                @endif
                            </td>
                            <td class="fw-bold">{{ $row->insurer_name }}</td>
                            <td><code class="text-primary fw-bold">{{ $row->remittance_reference }}</code></td>
                            <td>{{ $row->receipt_number }}</td>
                            <td>{{ $row->invoice_number }}</td>
                            <td class="text-end fw-bold">
                                <small class="text-muted">{{ $row->policy_currency }}</small>
                                {{ number_format($amount, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success">Remitted</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Select a date range to see remittance records.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($records->isNotEmpty())
        <div class="card-footer bg-white border-top-0 py-4">
            <div class="row justify-content-end">
                <div class="col-md-4">
                    <div class="p-3 border rounded bg-light">
                        <h6 class="fw-bold text-uppercase small text-muted mb-3">Remittance Breakdown</h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fw-bold">Total ZMW:</span>
                            <span class="text-dark fw-bold">K {{ number_format($totalZMW, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="fw-bold">Total USD:</span>
                            <span class="text-dark fw-bold">$ {{ number_format($totalUSD, 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

</div>
@endsection