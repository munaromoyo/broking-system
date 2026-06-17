@extends('layouts.app')

@section('title', 'Quotation Registry')

@push('styles')
<style>
    .registry-card {
        background-color: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        overflow: hidden;
    }
    .table-registry {
        margin-bottom: 0;
        vertical-align: middle;
    }
    .table-registry thead th {
        background-color: #f8fafc;
        color: #475569;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 1rem 1.25rem;
        border-bottom: 1px solid #e2e8f0;
    }
    .table-registry tbody td {
        padding: 1rem 1.25rem;
        color: #334155;
        font-size: 0.875rem;
        border-bottom: 1px solid #f1f5f9;
    }
    .table-registry tbody tr:last-child td {
        border-bottom: none;
    }
    .table-registry tbody tr {
        transition: background-color 0.15s ease;
    }
    .table-registry tbody tr:hover {
        background-color: #f8fafc;
    }
    .truncated-cell {
        max-width: 180px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>
@endpush

@section('content')
<div class="my-4">
    
    <!-- Header Section -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
        <div>
            <h1 class="h3 font-weight-bold text-dark mb-1">Insurance Quotations</h1>
            <p class="text-muted small mb-0">Manage, compare, and track client insurance quotes within the registry engine.</p>
        </div>
        <div>
            <a href="{{ route('insurance_broking.quotations.create') }}" class="btn btn-signup-rib d-inline-flex align-items-center gap-2 shadow-sm">
                <i class="bi bi-plus-lg"></i>
                <span>New Quotation</span>
            </a>
        </div>
    </div>

    <!-- Quotations Table Container -->
    <div class="registry-card">
        <div class="table-responsive">
            <table class="table table-registry text-start">
                <thead>
                    <tr>
                        <th scope="col" style="width: 80px;">ID</th>
                        <th scope="col">Insured Name</th>
                        <th scope="col">Nature of Business</th>
                        <th scope="col">Insurer</th>
                        <th scope="col">Sum Insured</th>
                        <th scope="col">Gross Premium</th>
                        <th scope="col">Expiry Date</th>
                        <th scope="col" class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($quotations as $quote)
                        <tr>
                            <td class="fw-semibold text-dark">#{{ $quote->id }}</td>
                            <td class="fw-bold text-dark">{{ $quote->insured }}</td>
                            <td>
                                <div class="truncated-cell text-muted small" title="{{ $quote->nature_of_business }}">
                                    {{ $quote->nature_of_business ?? '—' }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border px-2 py-1.5 fw-medium">
                                    {{ $quote->insurer }}
                                </span>
                            </td>
                            <td class="font-monospace text-secondary">
                                {{ $quote->policy_currency }} {{ number_format($quote->total_sum_insured, 2) }}
                            </td>
                            <td class="font-monospace fw-bold text-success">
                                {{ $quote->policy_currency }} {{ number_format($quote->gross_premium, 2) }}
                            </td>
                            <td>
                                <span class="small text-muted">
                                    <i class="bi bi-calendar3 me-1 text-black-50"></i>
                                    {{ $quote->policy_expiry_date ? \Carbon\Carbon::parse($quote->policy_expiry_date)->format('M d, Y') : 'N/A' }}
                                </span>
                            </td>
                            <td class="text-end whitespace-nowrap">
                                <a href="{{ route('insurance_broking.quotations.show', $quote->id) }}" class="btn btn-sm btn-outline-dark fw-medium px-3">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="bi bi-folder-x fs-2 text-black-50"></i></div>
                                <span class="small">No records found in the engine database.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination Context Layout -->
    @if(method_exists($quotations, 'links'))
        <div class="d-flex justify-content-center mt-4">
            {{ $quotations->links() }}
        </div>
    @endif
</div>
@endsection