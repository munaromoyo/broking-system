@extends('layouts.app') {{-- Assuming you have a base layout --}}

@section('content')

<style>
    :root {
        --primary-blue: #0004FF;
        --text-main: #1e293b;
        --text-muted: #64748b;
        --border-color: #e2e8f0;
        --card-bg: #ffffff;
    }
    body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: var(--text-main); }
    .dashboard-card { border: 1px solid var(--border-color); border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); background: var(--card-bg); }
    .card-header-custom { background-color: #fff; border-bottom: 1px solid var(--border-color); padding: 1rem 1.25rem !important; }
    .card-header-custom h5 { font-size: 0.95rem; font-weight: 700; color: var(--text-main); text-transform: uppercase; }
    .search-container { position: relative; max-width: 450px; }
    .search-input { padding: 10px 15px 10px 40px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 14px; }
    .search-icon { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 14px; }
    .policy-link-item { padding: 10px 15px; border-bottom: 1px solid #f1f5f9; transition: all 0.2s; text-decoration: none; color: var(--text-main); display: flex; justify-content: space-between; align-items: center; font-size: 13.5px; }
    .policy-link-item:hover { background-color: #f8fafc; color: var(--primary-blue); }
    .claim-status-badge { font-size: 11px; padding: 4px 8px; border-radius: 6px; font-weight: 600; }
    .hidden { display: none !important; }
    .text-sm-muted { font-size: 12px; color: var(--text-muted); }
</style>

<div class="container-fluid px-4 py-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 mt-2">
        <div class="d-flex align-items-center gap-3">
            <h4 class="fw-bold m-0">Broking Dashboard</h4>
            <button class="btn btn-link p-0 text-decoration-none" onclick="toggleTheme()">
                <i class="fas fa-moon text-muted" id="themeIcon"></i>
            </button>
        </div>
        
        <div class="search-container flex-grow-1 ms-lg-4 mt-3 mt-lg-0">
            <i class="fa fa-search search-icon"></i>
            <input type="text" id="dashboardSearch" class="form-control search-input" placeholder="Search client, ID, or claim type...">
        </div>
    </div>

    <div class="row g-3">
        <!-- Placing Slips Section -->
        <div class="col-xl-6">
            <div class="card h-100 dashboard-card">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-file-lines text-primary me-2"></i> Placing Slips</h5>
                    <div class="btn-group">
                        <a href="{{ route('insurance_broking.view_list.index', ['action' => 'view_slip_list']) }}" class="btn btn-outline-secondary btn-sm">All</a>
                        <a href="{{ route('insurance_broking.view_list.index', ['action' => 'view_cancelled_slip_list']) }}" class="btn btn-outline-danger btn-sm">Cancelled</a>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="list-group list-group-flush searchable-list" id="slipList">
                        @forelse($placements as $placement)
                            <div class="list-group-item p-0 search-item"> 
                                <div class="d-flex align-items-center">
                                    <a href="{{ route('insurance_broking.placement_slips.show', $placement->id) }}" class="policy-link-item flex-grow-1 border-0">
                                        <div>
                                            <strong>SLPN{{ $placement->id }}</strong>
                                            <span class="ms-2">{{ $placement->insured }}</span>
                                            <div class="text-sm-muted mt-1">
                                                Expires: {{ $placement->policy_expiry_date ? \Carbon\Carbon::parse($placement->policy_expiry_date)->format('d M Y') : 'N/A' }}
                                                <strong>{{ $placement->policy_currency }} {{ number_format((float)($placement->gross_premium ?? 0), 2) }}</strong>
                                            </div>
                                        </div>
                                    </a>
                                    <div class="pe-3">
                                        <button type="button" class="renew-trigger btn btn-sm btn-outline-primary" data-id="{{ $placement->id }}">
                                            <i class="fa fa-sync-alt"></i> Renew
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">No active policies expiring within 3 months.</div>
                        @endforelse
                        <div class="p-4 text-center text-muted hidden no-results small">No matching records found.</div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Active Claims Section -->
        <div class="col-xl-6">
            <div class="card h-100 dashboard-card">
                <div class="card-header card-header-custom d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fa fa-triangle-exclamation text-warning me-2"></i> Active Claims</h5>
                    <a href="{{ route('insurance_broking.view_list.index', ['action' => 'view_claim_list']) }}" class="btn btn-outline-secondary btn-sm">View Registry</a>
                </div>

                <div class="card-body p-0">
                    <div class="list-group list-group-flush searchable-list" id="claimList">
                        @forelse($activeClaims as $claim)
                            @php
                                $isPending = $claim->claim_status == 'Pending';
                                $badgeStyle = $isPending 
                                    ? 'background: #fffbeb; color: #92400e; border: 1px solid #fde68a;' 
                                    : 'background: #eff6ff; color: #1e40af; border: 1px solid #bfdbfe;';
                            @endphp
                            <div class="list-group-item p-0 search-item">
                                <a href="{{ route('insurance_broking.claims.show', $claim->id) }}" class="policy-link-item border-0">
                                    <div>
                                        <strong>CLM{{ $claim->id }}</strong> 
                                        <span class="ms-2">{{ $claim->client_name }}</span>
                                        <div class="text-sm-muted mt-1">{{ $claim->type_of_claim }}</div>
                                    </div>
                                    <span class="claim-status-badge" style="{{ $badgeStyle }}">{{ $claim->claim_status }}</span>
                                </a>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">No active claims found.</div>
                        @endforelse
                        <div class="p-4 text-center text-muted hidden no-results small">No matching records found.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Renewal Modal --}}
<div class="modal fade" id="renewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Renewal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                Are you sure you want to renew <span id="modalPolicyId"></span>?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmRenewBtn" class="btn btn-primary">Process Renewal</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    let selectedId = null;

    // Search Logic
    $('#dashboardSearch').on('input', function() {
        const filter = $(this).val().toLowerCase();
        $('.searchable-list').each(function() {
            const items = $(this).find('.search-item');
            const noResults = $(this).find('.no-results');
            let visibleCount = 0;

            items.each(function() {
                const match = $(this).text().toLowerCase().includes(filter);
                $(this).toggleClass('hidden', !match);
                if(match) visibleCount++;
            });
            noResults.toggleClass('hidden', visibleCount > 0);
        });
    });

    // Renewal Modal
    $('.renew-trigger').on('click', function() {
        selectedId = $(this).data('id');
        $('#modalPolicyId').text('SLPN' + selectedId);
        new bootstrap.Modal('#renewModal').show();
    });

    $('#confirmRenewBtn').on('click', function() {
        const btn = $(this);
        btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

        $.ajax({
            url: "{{ route('insurance_broking.placement_slips.renew') }}", // Corrected named route
            type: 'POST',
            data: { 
                id: selectedId,
                _token: "{{ csrf_token() }}" 
            },
            success: function(res) {
                if(res.success) {
                    // Redirects to the show page for the newly created slip
                    window.location.href = "{{ url('insurance-broking/placement-slips') }}/" + res.new_id;
                } else {
                    alert('Renewal failed: ' + res.message);
                }
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                alert('An error occurred while processing the renewal.');
            }
        });
    });
});
</script>
@endsection

