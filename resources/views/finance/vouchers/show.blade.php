@extends('layouts.app') {{-- Assuming you have a master layout --}}

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif


@section('content')
<div class="container-fluid px-4 py-4">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>Voucher Management</h3>
            <span class="badge bg-info text-dark">
                {{ $pendingCount }} Pending Tasks
            </span>
        </div>

        <ul class="nav nav-tabs mb-3" id="voucherTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending" type="button" role="tab">
                    Pending Approval
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="approved-tab" data-bs-toggle="tab" data-bs-target="#approved" type="button" role="tab">
                    Approved Vouchers
                </button>
            </li>
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button">Financial Statements</a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="{{ route('finance.income') }}">Income Statement</a></li>
                    <li><a class="dropdown-item" href="{{ route('finance.ledger') }}">Ledger Accounts</a></li>
                    <li><a class="dropdown-item" href="{{ route('finance.journal-voucher') }}">Journal Voucher</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="{{ route('finance.balance-sheet') }}">Balance Sheet</a></li>
                </ul>
            </li>
        </ul>

        <div class="tab-content" id="voucherTabsContent">
            {{-- Pending Tab --}}
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <div class="card shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Client / Payee</th>
                                    <th class="text-end">Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vouchers->where('status', 'Pending') as $row)
                                    <tr>
                                        <td class="fw-bold">#{{ $row->id }}</td>
                                        <td>{{ ucwords(strtolower($row->client_name)) }}</td>
                                        <td class="fw-bold text-primary text-end">
                                            <span class="text-muted small fw-normal">{{ $row->currency }}</span> 
                                            {{ number_format($row->amount, 2) }}
                                        </td>
                                        <td><small class="text-muted">{{ $row->description }}</small></td>
                                        <td><span class="badge rounded-pill bg-warning text-dark">Pending</span></td>
                                        <td class="text-center">

                                           <div class="btn-group">
                                                <form action="{{ route('finance.vouchers.update-status', ['id' => $row->id, 'action' => 'approve']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Approve this payment?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-success btn-sm px-3">
                                                        Approve
                                                    </button>
                                                </form>

                                                <form action="{{ route('finance.vouchers.update-status', ['id' => $row->id, 'action' => 'reject']) }}" method="POST" style="display:inline;" onsubmit="return confirm('Reject this payment?')">
                                                    @csrf
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        Reject
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted">No pending vouchers found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Approved Tab --}}
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <div class="card shadow-sm border-success">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-success">
                                <tr>
                                    <th>ID</th>
                                    <th>Client / Payee</th>
                                    <th class="text-end">Amount</th>
                                    <th>Description</th>
                                    <th>Status</th>
                                    <th>Approved By</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vouchers->where('status', 'Approved') as $row)
                                    <tr>
                                        <td class="fw-bold">#{{ $row->id }}</td>
                                        <td>{{ ucwords(strtolower($row->client_name)) }}</td>
                                        <td class="fw-bold text-success text-end">
                                            <span class="text-muted small fw-normal">{{ $row->currency }}</span> 
                                            {{ number_format($row->amount, 2) }}
                                        </td>
                                        <td><small class="text-muted">{{ $row->description }}</small></td>
                                        <td><span class="badge rounded-pill bg-success">Approved</span></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold small text-dark">{{ $row->approved_by ?? 'System' }}</span>
                                                <span class="text-muted" style="font-size: 0.75rem;">
                                                    {{ $row->approved_at ? $row->approved_at->format('M d, Y H:i') : '---' }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('vouchers.download', $row->id) }}" class="btn btn-sm btn-outline-primary px-3">
                                                <i class="bi bi-download"></i> Download
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-4 text-muted">No approved vouchers found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection