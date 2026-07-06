@extends('layouts.app') {{-- Assuming you have a base layout --}}

@section('content')
<div class="container-fluid max-width-xl py-4" style="max-width: 1200px; margin: 0 auto;">
    
    {{-- Top Heading & Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="mb-1 fw-bold text-dark">Client Registry</h2>
            <p class="text-muted mb-0">Manage, monitor, and update your registered client database.</p>
        </div>
        <div>
            {{-- 1. Add the Trash Bin button here --}}
            <a href="{{ route('clients.trash') }}" class="btn btn-outline-danger px-3 fw-bold shadow-sm d-inline-flex align-items-center gap-2 me-2" style="border-radius: 8px;">
                <i class="bi bi-trash3"></i> Trash Bin
            </a>
            {{-- Assuming you have a route to create a new client --}}
            <a href="{{ route('insurance_broking.register', ['action' => 'register_client']) }}" class="btn btn-primary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="border-radius: 8px;">
                <i class="bi bi-plus-lg"></i> Register New Client
            </a>
        </div>
    </div>

    {{-- Success/Status Alerts --}}
    @if (session('status'))
        <div class="alert alert-success alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="border-radius: 8px;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Registry Quick Metrics --}}
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0 p-3" style="border-radius: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-3 d-inline-flex">
                        <i class="bi bi-people fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small uppercase fw-bold">Total Clients</h6>
                        <h4 class="mb-0 fw-bold">{{ $clients->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0 p-3" style="border-radius: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle p-3 d-inline-flex">
                        <i class="bi bi-building fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small uppercase fw-bold">Corporate Accounts</h6>
                        <h4 class="mb-0 fw-bold">{{ $clients->where('client_type', 'Corporate')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-md-4">
            <div class="card shadow-sm border-0 p-3" style="border-radius: 12px;">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 text-info rounded-circle p-3 d-inline-flex">
                        <i class="bi bi-person fs-4"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0 small uppercase fw-bold">Individual Accounts</h6>
                        <h4 class="mb-0 fw-bold">{{ $clients->where('client_type', 'Individual')->count() }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Search and Table Card --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
        
        {{-- Search / Filter Header Bar --}}
        <div class="card-header bg-white border-bottom py-3">
            <div class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted" id="search-addon">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="registrySearch" class="form-control bg-light border-start-0 ps-0" placeholder="Search by name, email or number..." aria-describedby="search-addon">
                    </div>
                </div>
                <div class="col-md-3 col-lg-2 ms-auto">
                    <select class="form-select bg-light" id="filterType">
                        <option value="">All Types</option>
                        <option value="Individual">Individual</option>
                        <option value="Corporate">Corporate</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Table Container --}}
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="registryTable">
                <thead class="table-light text-muted uppercase fs-7">
                    <tr>
                        <th class="ps-4 py-3" style="width: 80px;">ID</th>
                        <th class="py-3">Client Details</th>
                        <th class="py-3">Client Type</th>
                        <th class="py-3">Contact Information</th>
                        <th class="py-3">Nature of Business</th>
                        <th class="pe-4 py-3 text-end" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($clients as $client)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">#{{ $client->id }}</td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $client->client_name }}</div>
                                <small class="text-muted d-block text-truncate" style="max-width: 200px;">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $client->physical_address ?? 'No address listed' }}
                                </small>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2.5 py-1.5 fw-semibold {{ $client->client_type === 'Corporate' ? 'bg-success bg-opacity-10 text-success' : 'bg-info bg-opacity-10 text-info' }}">
                                    {{ $client->client_type }}
                                </span>
                            </td>
                            <td>
                                <div class="mb-0"><i class="bi bi-envelope me-1 text-muted"></i>{{ $client->email_address }}</div>
                                <small class="text-muted"><i class="bi bi-telephone me-1"></i>{{ $client->contact_number }}</small>
                            </td>
                            <td>
                                <span class="d-inline-block text-truncate text-muted" style="max-width: 180px;">
                                    {{ $client->nature_of_business ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-2">
                                    {{-- Edit Button --}}
                                    <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" title="Edit Client">
                                        <i class="bi bi-pencil-square"></i> Edit
                                    </a>

                                    {{-- Delete Form Action --}}
                                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this client? This action cannot be undone.');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1" title="Delete Client">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="bi bi-folder-x fs-1 opacity-50"></i>
                                </div>
                                <h5 class="fw-bold text-secondary">No Clients Registered</h5>
                                <p class="text-muted small mb-0">Get started by onboarding your very first client profile.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination (If using Eloquent dynamic pagination) --}}
        @if(method_exists($clients, 'links') && $clients->hasPages())
            <div class="card-footer bg-white border-top py-3">
                {{ $clients->links() }}
            </div>
        @endif
    </div>
</div>

{{-- Frontend Filter Script --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('registrySearch');
    const typeFilter = document.getElementById('filterType');
    const tableRows = document.querySelectorAll('#registryTable tbody tr');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const selectedType = typeFilter.value;

        tableRows.forEach(row => {
            // Skip empty-state rows
            if (row.cells.length < 5) return;

            const textContent = row.textContent.toLowerCase();
            const clientType = row.cells[2].textContent.trim();

            const matchesSearch = textContent.includes(searchTerm);
            const matchesType = !selectedType || clientType === selectedType;

            if (matchesSearch && matchesType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    if (searchInput && typeFilter) {
        searchInput.addEventListener('input', filterTable);
        typeFilter.addEventListener('change', filterTable);
    }
});
</script>
@endsection