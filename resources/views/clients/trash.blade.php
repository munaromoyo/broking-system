@extends('layouts.app')

@section('content')
<div class="container-fluid max-width-xl py-4" style="max-width: 1200px; margin: 0 auto;">
    
    {{-- Top Heading & Actions --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h2 class="mb-1 fw-bold text-dark"><i class="bi bi-trash3 me-2 text-danger"></i>Trash Bin: Clients</h2>
            <p class="text-muted mb-0">Review, restore, or permanently remove soft-deleted client profiles.</p>
        </div>
        <div>
            <a href="{{ route('clients.list') }}" class="btn btn-outline-secondary px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-2" style="border-radius: 8px;">
                <i class="bi bi-arrow-left"></i> Back to Registry
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

    {{-- Table Card --}}
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; overflow: hidden;">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light text-muted uppercase fs-7">
                    <tr>
                        <th class="ps-4 py-3" style="width: 80px;">ID</th>
                        <th class="py-3">Client Details</th>
                        <th class="py-3">Client Type</th>
                        <th class="py-3">Contact Information</th>
                        <th class="pe-4 py-3 text-end" style="width: 250px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($deletedClients as $client)
                        <tr>
                            <td class="ps-4 fw-semibold text-muted">#{{ $client->id }}</td>
                            <td>
                                <div class="fw-bold text-dark fs-6">{{ $client->client_name }}</div>
                            </td>
                            <td>
                                <span class="badge rounded-pill px-2.5 py-1.5 fw-semibold {{ $client->client_type === 'Corporate' ? 'bg-success bg-opacity-10 text-success' : 'bg-info bg-opacity-10 text-info' }}">
                                    {{ $client->client_type }}
                                </span>
                            </td>
                            <td>
                                <div class="mb-0"><i class="bi bi-envelope me-1 text-muted"></i>{{ $client->email_address }}</div>
                            </td>
                            <td class="pe-4 text-end">
                                <div class="d-inline-flex gap-2">
                                    {{-- Restore Button --}}
                                    <form action="{{ route('clients.restore', $client->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success d-flex align-items-center gap-1">
                                            <i class="bi bi-arrow-counterclockwise"></i> Restore
                                        </button>
                                    </form>

                                    {{-- Force Delete Button --}}
                                    <form action="{{ route('clients.force-delete', $client->id) }}" method="POST" onsubmit="return confirm('CRITICAL WARNING: This will permanently wipe this client from the database. This cannot be undone. Proceed?');" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger d-flex align-items-center gap-1">
                                            <i class="bi bi-exclamation-triangle"></i> Purge
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted mb-2">
                                    <i class="bi bi-trash-text fs-1 opacity-50"></i>
                                </div>
                                <h5 class="fw-bold text-secondary">Trash Bin is Empty</h5>
                                <p class="text-muted small mb-0">No soft-deleted client records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection