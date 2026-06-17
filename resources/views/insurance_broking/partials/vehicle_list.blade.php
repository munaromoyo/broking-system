<!-- Vehicle Registry Management -->
<div class="card shadow-sm border-0 m-4">
    <!-- Header with Live Search -->
    <div class="card-header bg-white py-3 px-4 border-bottom-0">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="mb-0 fw-bold text-primary">
                <i class="bi bi-truck me-2"></i> Vehicle Registry
            </h6>
            <span class="badge rounded-pill bg-light text-primary border" id="resultCount">
                {{ count($vehicles) }} Vehicles
            </span>
        </div>
        
        <!-- Live Search Input -->
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted small"></i></span>
            <input type="text" id="liveSearchInput" class="form-control bg-light border-0" 
                   placeholder="Search Reg #, Client, Insurer, or Make..." autocomplete="off">
        </div>
    </div>

    <!-- Scrollable List Body -->
    <div class="list-group list-group-flush border-top searchable-list" id="vehicleList">
        @forelse ($vehicles as $row)
            @php 
                // Search string includes all key vehicle and policy identifiers
                $searchContent = strtolower(
                    ($row['client_name'] ?? '') . ' ' . 
                    ($row['reg_number'] ?? '') . ' ' . 
                    ($row['insurer_name'] ?? '') . ' ' . 
                    ($row['vehicle_make'] ?? '') . ' ' . 
                    ($row['slip_number'] ?? '')
                );
            @endphp
            <div class="vehicle-item list-group-item list-group-item-action py-2 px-4" 
                 data-search="{{ $searchContent }}">
                
                <div class="row align-items-center">
                    <!-- Vehicle & Client Info -->
                    <div class="col-md-5">
                        <div class="d-flex align-items-center">
                            <span class="badge bg-secondary me-2 font-monospace" style="font-size: 0.75rem;">
                                {{ $row['reg_number'] ?? 'N/A' }}
                            </span>
                            <h6 class="mb-0 fw-bold text-dark text-truncate" style="font-size: 0.9rem;">
                                {{ $row['client_name'] ?? 'Unknown Client' }}
                            </h6>
                        </div>
                        <div class="text-muted small mt-1">
                            <span class="text-primary fw-medium">{{ $row['vehicle_make'] ?? 'Make' }}</span>
                            <span class="mx-1 text-silver">|</span>
                            <span>{{ $row['insurer_name'] ?? 'No Insurer' }}</span>
                            <span class="mx-1 text-silver">|</span>
                            <span class="font-monospace small">#{{ $row['slip_number'] ?? '000' }}</span>
                        </div>
                    </div>

                    <!-- Policy Dates -->
                    <div class="col-md-4 d-none d-md-block border-start py-1">
                        <div class="row g-0 text-center">
                            <div class="col-6">
                                <small class="text-muted d-block font-xxs">EXPIRY DATE</small>
                                <span class="small fw-semibold text-danger">
                                    @php
                                        $rawDate = trim($row['policy_expiry_date'] ?? '');
                                        $date = null;

                                        if (!empty($rawDate)) {
                                            // Try standard Carbon parsing first
                                            try {
                                                $date = \Carbon\Carbon::createFromFormat('d/m/Y', $rawDate);
                                            } catch (\Exception $e) {
                                                try {
                                                    $date = \Carbon\Carbon::parse($rawDate);
                                                } catch (\Exception $e2) {
                                                    $date = null;
                                                }
                                            }
                                        }
                                    @endphp
                                    {{ $date ? $date->format('d M Y') : 'N/A' }}
                                </span>
                            </div>
                            <div class="col-6 border-start">
                                <small class="text-muted d-block font-xxs">POLICY TYPE</small>
                                <span class="small fw-medium text-truncate d-block">
                                    {{ $row['policy_type'] ?? 'General' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Financials -->
                    <div class="col-md-3 text-end border-start py-1">
                        <small class="text-muted d-block font-xxs">TOTAL PREMIUM</small>
                        <span class="fw-bold text-success">
                            {{ $row['policy_currency'] ?? 'ZMW' }} 
                            {{ number_format((float)($row['total_premium'] ?? 0), 2) }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="p-5 text-center text-muted">
                <i class="bi bi-truck-flatbed display-4 d-block mb-3"></i>
                No vehicles found in registry.
            </div>
        @endforelse

        <!-- Empty Search Result -->
        <div id="noResults" class="p-4 text-center d-none border-top">
            <span class="text-muted small"><i class="bi bi-info-circle me-1"></i> No matching vehicles found.</span>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('liveSearchInput');
    const vehicleItems = document.querySelectorAll('.vehicle-item');
    const noResults = document.getElementById('noResults');
    const badge = document.getElementById('resultCount');

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            let visibleCount = 0;

            vehicleItems.forEach(item => {
                const content = item.getAttribute('data-search');
                if (content.includes(query)) {
                    item.classList.remove('d-none');
                    visibleCount++;
                } else {
                    item.classList.add('d-none');
                }
            });

            badge.textContent = visibleCount + ' Vehicles';
            noResults.classList.toggle('d-none', visibleCount > 0);
        });
    }
});
</script>
@endpush

@push('styles')
<style>
    .font-xxs { font-size: 0.62rem; text-transform: uppercase; font-weight: 700; color: #adb5bd; letter-spacing: 0.3px; }
    .text-silver { color: #dee2e6; }
    .vehicle-item { border-bottom: 1px solid #f1f3f5 !important; transition: all 0.15s ease; cursor: pointer; }
    .vehicle-item:hover { background-color: #f8faff !important; border-left: 3px solid #0d6efd !important; padding-left: calc(1.5rem - 3px) !important; }
    .vehicle-item:last-child { border-bottom: 0 !important; }
</style>
@endpush