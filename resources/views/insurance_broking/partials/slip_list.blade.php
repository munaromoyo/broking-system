@extends('layouts.app')

@section('title', 'Placing Slips Registry')

@push('styles')
<style>
    /* Prevent row design explosions on smaller viewports */
    .font-xxs { font-size: 0.65rem !important; }
    .policy-link-item { transition: background-color 0.2s ease; }
    .policy-link-item:hover { background-color: #f8f9fa !important; }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-xl-10">

            {{-- Placing Slips Card Wrapper Component --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 px-4 border-bottom-0">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0 fw-bold text-primary">
                            <i class="bi bi-file-earmark-text me-1"></i> Placing Slips
                        </h6>
                        <span class="badge rounded-pill bg-light text-primary border" id="resultCount">
                            {{ $placements->count() }} Total Records
                        </span>
                    </div>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted small"></i></span>
                        {{-- input ID matches live search JS targets below --}}
                        <input type="text" id="slipSearchInput" class="form-control bg-light border-0" placeholder="Type to search by insured, insurer, or status...">
                    </div>
                </div>

                <div class="list-group list-group-flush border-top" id="slipList">
                    @forelse($placements as $placement)
                        @php 
                            // Added status string to search indices matching live javascript filters
                            $searchString = strtolower($placement->insured . ' ' . $placement->insurer . ' ' . $placement->status . ' SLPN-' . $placement->id); 
                        @endphp
                        {{-- policy-link-item maps directly to live search query --}}
                        <a href="{{ route('insurance_broking.placement_slips.show', $placement->id) }}" 
                           class="policy-link-item list-group-item list-group-item-action py-2 px-4"
                           data-search="{{ $searchString }}">
                           
                            <div class="row align-items-center">
                                <div class="col-md-4">
                                    <div class="fw-bold text-dark text-truncate mb-0" style="font-size: 0.9rem;">
                                        {{ $placement->insured }}
                                    </div>
                                    <div class="text-muted small">
                                        <span class="text-primary fw-medium">{{ $placement->insurer }}</span> | SLPN-{{ $placement->id }}
                                    </div>
                                </div>
                                
                                {{-- Status Column --}}
                                <div class="col-md-3 text-center border-start py-1">
                                    <small class="text-muted d-block font-xxs text-uppercase tracking-wider">Status</small>
                                    <span class="badge {{ $placement->status === 'Active' ? 'bg-success' : 'bg-info text-dark' }} font-xxs tracking-wider px-2 py-1 uppercase">
                                        {{ $placement->status }}
                                    </span>
                                </div>

                                <div class="col-md-2 text-center border-start py-1">
                                    <small class="text-muted d-block font-xxs text-uppercase tracking-wider">Expiry Date</small>
                                    <span class="small fw-semibold text-nowrap text-danger">
                                        {{ \Carbon\Carbon::parse($placement->policy_expiry_date)->format('d M y') }}
                                    </span>
                                </div>
                                
                                <div class="col-md-3 text-end border-start py-1">
                                    <small class="text-muted d-block font-xxs text-uppercase tracking-wider">Gross Premium</small>
                                    <span class="fw-bold text-success">
                                        {{ $placement->policy_currency }} {{ number_format($placement->gross_premium, 2) }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-4 text-center text-muted small">No records found.</div>
                    @endforelse

                    {{-- No Results Match Placeholder Alert --}}
                    <div id="noResults" class="p-4 text-center d-none">
                        <span class="text-muted small"><i class="bi bi-exclamation-circle me-1"></i>No items match your search.</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('slipSearchInput');
    const targetItems = document.querySelectorAll('.policy-link-item');
    const badgeEl = document.getElementById('resultCount');
    const noResultsEl = document.getElementById('noResults');

    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            const searchTerm = e.target.value.toLowerCase().trim();
            let totalMatchCount = 0;

            targetItems.forEach(item => {
                const searchableDataString = item.getAttribute('data-search') || '';
                
                // Native index filtering skips nested layout bugs entirely
                if (searchableDataString.indexOf(searchTerm) !== -1) {
                    item.setAttribute('style', 'display: block !important;');
                    totalMatchCount++;
                } else {
                    item.setAttribute('style', 'display: none !important;');
                }
            });

            // Update data matches total badge count dynamically
            if (badgeEl) {
                badgeEl.textContent = `${totalMatchCount} Records Match`;
            }

            // Manage empty search context layout warning triggers
            if (noResultsEl) {
                if (totalMatchCount === 0 && searchTerm !== "") {
                    noResultsEl.setAttribute('style', 'display: block !important;');
                } else {
                    noResultsEl.setAttribute('style', 'display: none !important;');
                }
            }
        });
    }
});
</script>
@endpush