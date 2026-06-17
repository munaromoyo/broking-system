@extends('layouts.app')

@section('content')
<div class="container mt-4">
    <h6 class="text-center mb-4" style="font-size: 20px; font-weight: bold;">Credit Notes</h6>

    <div class="mb-3">
        <input type="text" id="creditNoteSearch" class="form-control" placeholder="Search by insured, insurer, slip no, or currency...">
    </div>

    <div class="table-responsive">
        <table class="table table-hover border align-middle">
            <thead class="table-dark">
                <tr>
                    <th style="width: 120px;">Cancel Date</th>
                    <th>Slip ID</th>
                    <th>Insured / Policy</th>
                    <th>Currency</th>
                    <th class="text-end">Refund (Gross)</th>
                    <th class="text-end">Basic Premium</th>
                    <th class="text-end">Action</th>
                </tr>
            </thead>
            <tbody id="creditNoteTable">
                @if($creditNotes->isNotEmpty())
                    @foreach($creditNotes as $note)
                    <tr>
                        <td class="text-muted">
                            <i class="fa fa-calendar-o me-1"></i>
                            {{-- Accessing attributes securely via model relationships --}}
                            {{ $note->slipCancellation->cancellation_date ?? $note->cancellation_date }}
                        </td>

                        <td>
                            <span class="badge bg-secondary">
                                {{ $note->slip_id }}
                            </span>
                        </td>

                        <td>
                            <div class="fw-bold text-primary">
                                {{ $note->slipCancellation->insured_name ?? $note->insured_name }}
                            </div>
                            <small class="text-muted">
                                <i class="fa fa-shield me-1"></i>{{ $note->slipCancellation->insurance_policy ?? $note->insurance_policy }}
                            </small>
                        </td>

                        <td>
                            <span class="fw-bold">{{ $note->slipCancellation->policy_currency ?? $note->policy_currency }}</span>
                        </td>

                        <td class="text-end text-danger fw-bold">
                            {{ number_format((float)($note->slipCancellation->premium_refund ?? $note->premium_refund), 2) }}
                        </td>

                        <td class="text-end">
                            {{ number_format((float)($note->slipCancellation->basic_premium ?? $note->basic_premium), 2) }}
                        </td>

                        <td class="text-end">
                            <div class="btn-group">
                                {{-- Updated link to point to a dynamic secure Laravel download route or action --}}
                                <a class="btn btn-sm btn-outline-danger" 
                                   href="{{ route('insurance_broking.accounts.credit_notes.pdf', ['slip_id' => $note->slip_id]) }}"
                                   title="View PDF">
                                    <i class="fa fa-file-pdf-o"></i> PDF
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="7" class="py-5 text-center text-muted">
                            <i class="fa fa-info-circle fa-2x mb-3 d-block"></i>
                            No credit notes found in the system.
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('creditNoteSearch').addEventListener('keyup', function() {
    const value = this.value.toLowerCase();
    const rows = document.querySelectorAll('#creditNoteTable tr');

    rows.forEach(row => {
        // Keeps search functionality clean and dynamic
        if(row.cells.length > 1) { 
            row.style.display = row.innerText.toLowerCase().includes(value) ? '' : 'none';
        }
    });
});
</script>
@endpush