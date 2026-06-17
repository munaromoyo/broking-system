@extends('layouts.app') {{-- Replace with your global admin layout wrapper --}}

@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-md-10">
            
            @if(session('success'))
                <div class="alert alert-success border-0 shadow-sm mb-4">{{ session('success') }}</div>
            @endif

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-dark text-white p-3">
                    <h5 class="mb-0 class font-weight-bold">Tenant Profile & Branding Configuration</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-7 border-end pe-md-4">
                                <h6 class="text-uppercase text-muted font-weight-bold mb-3 small">Corporate Details (Appears on PDF headers)</h6>
                                
                                <div class="mb-3">
                                    <label class="form-label font-weight-bold">Company Name</label>
                                    <input type="text" name="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company', $user->company) }}" required>
                                    @error('company') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-weight-bold">Company TPIN</label>
                                    <input type="text" name="company_tpin" class="form-control @error('company_tpin') is-invalid @enderror" value="{{ old('company_tpin', $user->company_tpin) }}" placeholder="Enter Company TPIN...">
                                    @error('company_tpin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label font-weight-bold">Physical Office Address</label>
                                    <textarea name="physical_address" class="form-control @error('physical_address') is-invalid @enderror" rows="3">{{ old('physical_address', $user->physical_address) }}</textarea>
                                    @error('physical_address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">Contact Number</label>
                                        <input type="text" name="tel_number" class="form-control @error('tel_number') is-invalid @enderror" value="{{ old('tel_number', $user->tel_number) }}">
                                        @error('tel_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label font-weight-bold">Support Email Address</label>
                                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}">
                                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5 ps-md-4 mt-4 mt-md-0">
                                <h6 class="text-uppercase text-muted font-weight-bold mb-3 small">Branding & Logo Identity</h6>
                                
                                <div class="mb-4 text-center p-3 bg-light rounded-3 border border-dashed">
                                    <label class="form-label d-block text-start font-weight-bold">Company Logo File</label>
                                    
                                    @if($logoPreview)
                                        <div class="mb-3 p-2 bg-white rounded shadow-sm d-inline-block">
                                            <img src="{{ $logoPreview }}?v={{ time() }}" alt="Current Logo" style="max-height: 90px; width: auto;" class="img-fluid">
                                        </div>
                                        <p class="text-muted small mb-3">Current active branding asset image file.</p>
                                    @else
                                        <div class="my-3 text-muted">
                                            <i class="bi bi-image" style="font-size: 2.5rem;"></i>
                                            <p class="small text-danger font-weight-bold mb-3">No custom logo configured. Using central default layout.</p>
                                        </div>
                                    @endif

                                    <input type="file" name="logo" class="form-control @error('logo') is-invalid @enderror">
                                    <div class="form-text text-start mt-2 small text-muted">Supports JPEG, JPG, or PNG. Max size file limitation 2MB.</div>
                                    @error('logo') <div class="invalid-feedback text-start d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-dark px-4 py-2 font-weight-bold">Save Profiles Settings Changes</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection