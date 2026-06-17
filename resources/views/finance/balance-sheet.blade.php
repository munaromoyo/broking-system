@extends('layouts.app')

@section('content')
<div class="container-fluid px-4 py-4">
    {{-- Message Handling --}}
    @foreach(['success', 'danger'] as $msg)
        @if(session($msg))
            <div class="alert alert-{{ $msg }} alert-dismissible fade show shadow-sm">
                <i class="bi {{ $msg == 'success' ? 'bi-check-circle-fill' : 'bi-exclamation-triangle-fill' }} me-2"></i>
                {{ session($msg) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach
    
    <ul class="nav nav-pills mb-4 d-print-none" id="bsTabs">
        <li class="nav-item">
            <button class="nav-link active fw-bold" id="report-tab" data-bs-toggle="pill" data-bs-target="#report-content">Report</button>
        </li>
        <li class="nav-item">
            <button class="nav-link fw-bold" id="forms-tab" data-bs-toggle="pill" data-bs-target="#forms-content">Management</button>
        </li>
    </ul>

    <div class="tab-content">
        {{-- TAB 1: REPORT --}}
        <div class="tab-pane fade show active" id="report-content">
            <div class="card shadow-sm mb-4 d-print-none">
                <div class="card-body bg-light">
                    <form method="GET" action="{{ route('finance.balance-sheet') }}" class="row g-3 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Fiscal Year End</label>
                            <select name="year" class="form-select">
                                @for($y=date('Y'); $y>=2023; $y--)
                                    <option value="{{ $y }}" {{ $y == $targetYear ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label fw-bold small">Rate (USD to ZMW)</label>
                            <input type="number" name="rate" step="0.01" class="form-control" value="{{ $conversionRate }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Refresh Sheet</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-dark text-white p-3">
                    <h5 class="mb-0">Balance Sheet - Dec 31, {{ $targetYear }}</h5>
                </div>
                <table class="table table-hover table-bordered mb-0">
                    <thead class="table-secondary">
                        <tr><th>Description</th><th class="text-end">Amount (ZMW)</th></tr>
                    </thead>
                    <tbody>
                        <tr><td colspan="2" class="bg-light fw-bold text-primary">1. ASSETS</td></tr>
                        {{-- Non-Current Assets --}}
                        @foreach($bs['ASSETS']['FIXED'] as $name => $val)
                            <tr><td class="ps-5">{{ $name }}</td><td class="text-end">{{ number_format($val, 2) }}</td></tr>
                        @endforeach
                        {{-- Current Assets --}}
                        @foreach($bs['ASSETS']['CURRENT'] as $name => $val)
                            <tr><td class="ps-5">{{ $name }}</td><td class="text-end">{{ number_format($val, 2) }}</td></tr>
                        @endforeach
                        <tr class="table-info fw-bold">
                            <td class="ps-3">TOTAL ASSETS</td>
                            <td class="text-end border-bottom border-dark border-3">{{ number_format($grandTotalAssets, 2) }}</td>
                        </tr>
                        {{-- Liabilities & Equity Section follows same pattern... --}}
                    </tbody>
                </table>
            </div>
        </div>

        {{-- TAB 2: FORMS --}}
        <div class="tab-pane fade" id="forms-content">
            <div class="row">
                <div class="col-xl-6 mb-4">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">Chart of Accounts</div>
                        <div class="card-body">
                            <form action="{{ route('finance.balance-sheet.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="save_account">
                                <div class="mb-3">
                                    <label class="small fw-bold">Account Name</label>
                                    <input type="text" name="account_name" class="form-control" required>
                                </div>
                                {{-- Other fields --}}
                                <button type="submit" class="btn btn-primary btn-sm">Add Account</button>
                            </form>
                        </div>
                    </div>
                </div>
                {{-- Add Asset/Liability cards similarly --}}
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Tab persistence logic
    document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(t => {
        t.addEventListener('shown.bs.tab', (e) => localStorage.setItem('activeBSTab', e.target.id));
    });
    const activeTabId = localStorage.getItem('activeBSTab');
    if (activeTabId) {
        const el = document.querySelector(`#${activeTabId}`);
        if (el) new bootstrap.Tab(el).show();
    }
</script>
@endpush