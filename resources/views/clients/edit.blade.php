@extends('layouts.app') {{-- Assuming you have a base layout --}}

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

        {{-- Form action points to the update route --}}
        <form id="updateForm" method="POST" action="{{ route('clients.update') }}">
            @csrf
            @method('PUT') {{-- Browsers don't support PUT, Laravel simulates it --}}
            
            {{-- We use ID now instead of name for better reliability --}}
            <input type="hidden" name="client_id" id="client_id">

            <div class="mb-4 p-3 bg-light border rounded">
                <label class="form-label fw-bold">Select Client to Edit</label>
                <select class="form-select form-select-lg" id="client_select">
                    <option value="">-- Choose Client --</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}">{{ $client->client_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-8">
                    <label class="form-label">Client Name*</label>
                    <input type="text" name="client_name" id="client_name" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Client Type*</label>
                    <select name="client_type" id="client_type" class="form-select">
                        <option value="Individual">Individual</option>
                        <option value="Corporate">Corporate</option>
                    </select>
                </div>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label">Contact Number*</label>
                    <input type="text" name="contact_number" id="contact_number" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address*</label>
                    <input type="email" name="email_address" id="email_address" class="form-control">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Nature of Business*</label>
                <textarea name="nature_of_business" id="nature_of_business" class="form-control" rows="2"></textarea>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label">Physical Address*</label>
                    <input type="text" name="physical_address" id="physical_address" class="form-control">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Postal Address*</label>
                    <input type="text" name="postal_address" id="postal_address" class="form-control">
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
// Pass Laravel collection to JS safely
const clients = @json($clients);

document.getElementById('client_select').addEventListener('change', function() {
    const selectedId = this.value;
    // Find client by ID (more accurate than name)
    const client = clients.find(c => c.id == selectedId);

    if (client) {
        setValue('client_id', client.id);
        setValue('client_name', client.client_name);
        setValue('client_type', client.client_type);
        setValue('contact_number', client.contact_number);
        setValue('email_address', client.email_address);
        setValue('nature_of_business', client.nature_of_business);
        setValue('physical_address', client.physical_address);
        setValue('postal_address', client.postal_address);
    } else {
        document.getElementById('updateForm').reset();
    }
});

function setValue(id, value) {
    const element = document.getElementById(id);
    if (element) element.value = value || '';
}
</script>
@endsection