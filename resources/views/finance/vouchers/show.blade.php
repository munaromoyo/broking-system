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
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                {{-- Document View dropdown for multi-files --}}
                                                @if(!empty($row->supporting_documents))
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-file-earmark-text me-1"></i> Docs
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                                            @php
                                                                $docs = is_string($row->supporting_documents) ? json_decode($row->supporting_documents, true) ?? explode(',', $row->supporting_documents) : $row->supporting_documents;
                                                            @endphp
                                                            @foreach((array)$docs as $index => $doc)
                                                                <li>
                                                                    <a href="{{ asset('storage/' . trim($doc)) }}" target="_blank" class="dropdown-item small py-2">
                                                                        <i class="bi bi-file-earmark me-2 text-primary"></i>Document #{{ $index + 1 }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-outline-secondary shadow-sm" disabled>
                                                        <i class="bi bi-file-earmark-x me-1"></i> No Doc
                                                    </button>
                                                @endif

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
                <div class="card shadow-sm border-0 rounded-3 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                            <thead class="table-light border-top border-3 border-success text-secondary uppercase-tracking small">
                                <tr>
                                    <th class="ps-3 py-3" style="width: 80px;">ID</th>
                                    <th class="py-3">Client / Payee</th>
                                    <th class="text-end py-3" style="width: 140px;">Amount</th>
                                    <th class="py-3">Description</th>
                                    <th class="py-3" style="width: 110px;">Status</th>
                                    <th class="py-3" style="width: 180px;">Approved By</th>
                                    <th class="text-center py-3" style="width: 180px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vouchers->where('status', 'Approved') as $row)
                                    <tr class="transition-all">
                                        <td class="ps-3 fw-semibold text-secondary">#{{ $row->id }}</td>
                                        
                                        <td class="fw-semibold text-dark">
                                            {{ ucwords(strtolower($row->client_name)) }}
                                        </td>
                                        
                                        <td class="fw-bold text-success text-end text-nowrap">
                                            <span class="text-secondary small fw-normal me-1">{{ $row->currency }}</span>{{ number_format($row->amount, 2) }}
                                        </td>
                                        
                                        <td class="text-secondary text-wrap" style="max-width: 250px;">
                                            {{ \Illuminate\Support\Str::limit($row->description, 60, '...') }}
                                        </td>
                                        
                                        <td>
                                            <span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill fw-medium border border-success-subtle">
                                                <i class="bi bi-check-circle-fill me-1"></i> Approved
                                            </span>
                                        </td>
                                        
                                        <td>
                                            <div class="d-flex flex-column line-height-sm">
                                                <span class="fw-semibold text-dark">{{ $row->approved_by ?? 'System User' }}</span>
                                                <span class="text-secondary mt-0.5 opacity-75" style="font-size: 0.75rem;">
                                                    <i class="bi bi-clock me-1"></i>{{ $row->approved_at ? $row->approved_at->format('M d, Y H:i') : '---' }}
                                                </span>
                                            </div>
                                        </td>
                                        
                                        <td class="text-center pe-3">
                                            <div class="d-flex justify-content-center align-items-center gap-2">
                                                {{-- Document View Dropdown --}}
                                                @if(!empty($row->supporting_documents))
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center dropdown-toggle shadow-sm px-2.5 py-1.5 fw-medium" data-bs-toggle="dropdown" aria-expanded="false">
                                                            <i class="bi bi-file-earmark-text text-primary me-1.5"></i> Docs
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 my-1 rounded-3">
                                                            @php
                                                                $docs = is_string($row->supporting_documents) ? json_decode($row->supporting_documents, true) ?? explode(',', $row->supporting_documents) : $row->supporting_documents;
                                                            @endphp
                                                            @foreach((array)$docs as $index => $doc)
                                                                <li>
                                                                    <a href="{{ asset('storage/' . trim($doc)) }}" target="_blank" class="dropdown-item d-flex align-items-center text-secondary small py-2 px-3 transition-all">
                                                                        <i class="bi bi-file-earmark-arrow-down text-primary me-2"></i> Document #{{ $index + 1 }}
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                @else
                                                    <button type="button" class="btn btn-sm btn-light text-muted d-inline-flex align-items-center border px-2.5 py-1.5 fw-medium" disabled>
                                                        <i class="bi bi-file-earmark-x me-1.5"></i> No Doc
                                                    </button>
                                                @endif

                                                {{-- Download Action Trigger Button --}}
                                                <a href="{{ route('finance.vouchers.download', $row->id) }}" class="btn btn-sm btn-outline-primary d-inline-flex align-items-center shadow-sm px-2.5 py-1.5 fw-medium transition-all">
                                                    <i class="bi bi-cloud-arrow-down-fill me-1.5"></i> Download
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-secondary bg-light-subtle">
                                            <div class="py-3">
                                                <i class="bi bi-folder-x text-muted display-6 d-block mb-3 opacity-50"></i>
                                                <span class="fw-medium">No approved vouchers match these filters contextually.</span>
                                            </div>
                                        </td>
                                    </tr>
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