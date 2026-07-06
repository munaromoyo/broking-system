@extends('layouts.app')

@section('content')
<div class="card shadow-sm border-0 mx-auto" style="max-width: 800px; border-radius: 12px; overflow: hidden;">
    <div class="card-header bg-primary text-white py-4 text-center">
        <h4 class="mb-1 fw-bold">Update Client Details</h4>
        <p class="small mb-0 opacity-75">Select a client and modify information below</p>
    </div>

    <div class="card-body p-4 p-md-5">
        {{-- Display Success Message --}}
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        {{-- Dropdown to choose client - changes url on selection --}}
        <div class="mb-4 p-3 bg-light border rounded">
            <label class="form-label fw-bold">Select Client to Edit</label>
            <select class="form-select form-select-lg" id="client_select">
               <option value="">-- Choose Client --</option>
               @foreach($allClients as $c)
                   <option value="{{ $c->id }}" {{ $c->id == $client->id ? 'selected' : '' }}>
                       {{ $c->client_name }}
                   </option>
               @endforeach
            </select>
        </div>

        {{-- Form action points to the specific client's update route --}}
        <form id="updateForm" method="POST" action="{{ route('clients.update', $client->id) }}">
            @csrf
            @method('PUT')
            
            <input type="hidden" name="client_id" id="client_id" value="{{ $client->id }}">

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label">Client Name*</label>
                    <input type="text" name="client_name" id="client_name" class="form-control" value="{{ old('client_name', $client->client_name) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Client Type*</label>
                    <select name="client_type" id="client_type" class="form-select">
                        <option value="Individual" {{ old('client_type', $client->client_type) == 'Individual' ? 'selected' : '' }}>Individual</option>
                        <option value="Corporate" {{ old('client_type', $client->client_type) == 'Corporate' ? 'selected' : '' }}>Corporate</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Contact Number*</label>
                    <input type="text" name="contact_number" id="contact_number" class="form-control" value="{{ old('contact_number', $client->contact_number) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address*</label>
                    <input type="email" name="email_address" id="email_address" class="form-control" value="{{ old('email_address', $client->email_address) }}">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nature of Business*</label>
                <textarea name="nature_of_business" id="nature_of_business" class="form-control" rows="2">{{ old('nature_of_business', $client->nature_of_business) }}</textarea>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Physical Address*</label>
                    <input type="text" name="physical_address" id="physical_address" class="form-control" value="{{ old('physical_address', $client->physical_address) }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Postal Address*</label>
                    <input type="text" name="postal_address" id="postal_address" class="form-control" value="{{ old('postal_address', $client->postal_address) }}">
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <button type="submit" class="btn btn-success px-5 fw-bold">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('client_select').addEventListener('change', function() {
    const selectedId = this.value;
    if (selectedId) {
        // Dynamically changes URL to point to the freshly selected client's edit route
        window.location.href = `/clients/${selectedId}/edit`;
    }
});
</script>
@endsection