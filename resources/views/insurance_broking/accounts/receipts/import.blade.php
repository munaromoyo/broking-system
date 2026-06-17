@extends('layouts.app')

@section('title', 'Bulk Import Transactions')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-9">
            
            {{-- Unified Session Alert Processing Notifications --}}
            @if(session('success'))
                <div class='alert alert-success border-0 shadow-sm alert-dismissible fade show' role="alert">
                    <i class='bi bi-check-circle-fill me-2'></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error') || $errors->any())
                <div class='alert alert-danger border-0 shadow-sm alert-dismissible fade show' role="alert">
                    <i class='bi bi-exclamation-triangle-fill me-2'></i> 
                    {{ session('error') ?? 'Please check the uploaded document constraints.' }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 border-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark">Bulk Statement Upload</h5>
                        {{-- Points securely back to your central Accounts dashboard view index --}}
                        <a href="{{ route('insurance_broking.accounts.receipts.show') ?? '#' }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            Back to Dashboard
                        </a>
                    </div>
                </div>
                
                <div class="card-body p-4">
                    <form action="{{ route('insurance_broking.accounts.receipts.import.store') }}" method="POST" enctype="multipart/form-data" onsubmit="this.querySelector('button[type=submit]').disabled=true; return true;">
                        @csrf
                        
                        <div class="p-5 border-2 border-dashed rounded-3 bg-light text-center mb-4" style="border-style: dashed !important;">
                            <i class="bi bi-file-earmark-spreadsheet display-4 text-success"></i>
                            <p class="mt-2 text-muted">Upload your bank statement in <strong>.CSV</strong> format</p>
                            <input type="file" name="csv_file" class="form-control mt-3 mx-auto" style="max-width: 400px;" accept=".csv" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold shadow-sm">
                            <i class="bi bi-cloud-arrow-up-fill me-2"></i> Start Bulk Import
                        </button>
                    </form>

                    <div class="mt-5">
                        <h6 class="fw-bold small text-uppercase text-muted mb-3">Expected CSV Column Order</h6>
                        <div class="table-responsive">
                            <table class="table table-hover table-sm table-bordered small text-center mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>1. Trans Date</th>
                                        <th>2. Value Date</th>
                                        <th>3. Description</th>
                                        <th>4. Reference</th>
                                        <th>5. Currency</th>
                                        <th>6. Debit</th>
                                        <th>7. Credit</th>
                                        <th>8. Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-muted bg-white">
                                        <td>10/04/2026</td>
                                        <td>11/04/2026</td>
                                        <td>Premium Pymt</td>
                                        <td>REF-123</td>
                                        <td>ZMW</td>
                                        <td>0.00</td>
                                        <td>2500.00</td>
                                        <td>15000.00</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="alert alert-warning mt-2 py-2 border-0 small">
                            <i class="bi bi-info-circle me-1"></i> Ensure your CSV file does not have a footer, subtotal summary blocks, or duplicate header entries.
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection