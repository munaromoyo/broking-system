@extends('layouts.app')

@section('title', $pageTitle ?? 'Edit Cancellation Advice')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

<style>
    @page { margin-top: 55mm; margin-footer: 10mm; header: html_MyHeader; }
    body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #333; }
    .title-box { text-align: center; margin-bottom: 30px; }
    .title-box h1 { font-size: 24px; text-transform: uppercase; color: #1a1a1a; border-bottom: 2px solid #e20613; display: inline-block; padding-bottom: 5px; }
    .ref-number { text-align: right; font-weight: bold; font-size: 14px; margin-bottom: 10px; }
    .content-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; table-layout: fixed; }
    .section-header { background-color: #f8f9fa; color: #1a1a1a; padding: 12px; font-weight: bold; font-size: 15px; border-bottom: 2px solid #dee2e6; }
    .tableheader3 { text-align: left; width: 35%; padding: 10px 15px; background-color: #fcfcfc; border-bottom: 1px solid #eee; font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; vertical-align: middle; }
    .tabledata3 { padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 13px; color: #000; font-weight: 500; word-wrap: break-word; vertical-align: middle; }
    .summary-title { font-size: 18px; color: #e20613; margin: 20px 0 10px 0; font-weight: bold; }
    .total-row { background-color: #fff5f5; }
    .total-row th, .total-row td { border-bottom: 2px solid #e20613; font-size: 14px; color: #e20613; font-weight: bold; }
    
    /* Form Control Enhancements matching template palette */
    .form-control-custom { width: 100%; padding: 6px 12px; font-size: 13px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; background-color: #fff; }
    .form-control-custom:focus { border-color: #e20613; outline: 0; box-shadow: inset 0 1px 1px rgba(0,0,0,.075), 0 0 8px rgba(226, 6, 19, 0.2); }
    
    /* Date range layout wrapper */
    .date-range-container { display: flex; gap: 15px; align-items: center; }
    .date-range-field { flex: 1; }
    .date-range-label { font-size: 11px; color: #777; margin-bottom: 3px; font-weight: bold; }

    /* Flatpickr override to match your brand theme */
    .flatpickr-day.selected, .flatpickr-day.selected:hover { background: #e20613 !important; border-color: #e20613 !important; }
    
    button.btn, a.btn { padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 12px; display: inline-block; margin-right: 8px; border: none; cursor: pointer; }
    .btn-save { background-color: #1cc88a; color: white; }
    .btn-save:hover { background-color: #17a673; }
    .btn-secondary { background-color: #858796; color: white; }
    .btn-secondary:hover { background-color: #717384; }
    .text-danger-msg { color: #e20613; font-size: 11px; margin-top: 4px; display: block; }
</style>
@endpush

@section('content')
<div style="padding: 0 50px;">

    {{-- Flash Success Alert Banner Display Section --}}
    @if(session('success'))
        <div style="background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; padding: 12px 20px; margin-bottom: 20px; border-radius: 4px; font-size: 14px; font-weight: bold; display: flex; align-items: center; gap: 10px;">
            <span style="font-size: 18px;">✓</span>
            {{ session('success') }}
        </div>
    @endif

    @if($cancellations)
        
        <form action="{{ route('insurance_broking.cancelled_slips.update', $cancellations->slip_id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="ref-number">CANCELLATION REF: CAN-{{ $cancellations->slip_id }}</div>
            
            <div class="title-box">
                <h1 style="color: #e20613;">Edit Cancellation Advice</h1>
            </div>

            <table class="content-table">
                <tr><th colspan="2" class="section-header">Original Policy Information (Read Only)</th></tr>
                <tr><th class="tableheader3">Insured</th><td class="tabledata3">{{ $cancellations->insured_name }}</td></tr>
                <tr><th class="tableheader3">Policy Number/Name</th><td class="tabledata3">{{ $cancellations->insurance_policy }}</td></tr>
                <tr><th class="tableheader3">Original Slip ID</th><td class="tabledata3"><strong>#{{ $cancellations->slip_id }}</strong></td></tr>
            </table>

            <table class="content-table">
                <tr><th colspan="2" class="section-header">Cancellation Details</th></tr>
                <tr>
                    <th class="tableheader3">Effective Date</th>
                    <td class="tabledata3">
                        <input type="text" id="cancellation_date" name="cancellation_date" class="form-control-custom" 
                               value="{{ old('cancellation_date', \Carbon\Carbon::parse($cancellations->cancellation_date)->format('Y-m-d')) }}"
                               placeholder="Select Effective Date..">
                        @error('cancellation_date') <span class="text-danger-msg">{{ $message }}</span> @enderror
                    </td>
                </tr>
                <tr>
                    <th class="tableheader3">Cancellation Period</th>
                    <td class="tabledata3">
                        <div class="date-range-container">
                            <div class="date-range-field">
                                <div class="date-range-label">FROM:</div>
                                <input type="text" id="cancellation_date_from" name="cancellation_date_from" class="form-control-custom" 
                                       value="{{ old('cancellation_date_from', $cancellations->cancellation_date_from ? \Carbon\Carbon::parse($cancellations->cancellation_date_from)->format('Y-m-d') : '') }}"
                                       placeholder="Start Date">
                                @error('cancellation_date_from') <span class="text-danger-msg">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="date-range-field">
                                <div class="date-range-label">TO:</div>
                                <input type="text" id="cancellation_date_to" name="cancellation_date_to" class="form-control-custom" 
                                       value="{{ old('cancellation_date_to', $cancellations->cancellation_date_to ? \Carbon\Carbon::parse($cancellations->cancellation_date_to)->format('Y-m-d') : '') }}"
                                       placeholder="End Date">
                                @error('cancellation_date_to') <span class="text-danger-msg">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th class="tableheader3">Cancelled By</th>
                    <td class="tabledata3">
                        <input type="text" name="cancelled_by" class="form-control-custom" 
                               value="{{ old('cancelled_by', $cancellations->cancelled_by) }}">
                        @error('cancelled_by') <span class="text-danger-msg">{{ $message }}</span> @enderror
                    </td>
                </tr>
                <tr>
                    <th class="tableheader3">Remarks / Reason</th>
                    <td class="tabledata3">
                        <textarea name="remarks" class="form-control-custom" rows="4">{{ old('remarks', $cancellations->remarks) }}</textarea>
                        @error('remarks') <span class="text-danger-msg">{{ $message }}</span> @enderror
                    </td>
                </tr>
            </table>

            <div class="summary-title">Financial Adjustment</div>
            <table class="content-table">
                <tr>
                    <th class="tableheader3">Currency</th>
                    <td class="tabledata3">
                        <input type="text" name="policy_currency" class="form-control-custom" style="max-width: 120px;" 
                               value="{{ old('policy_currency', $cancellations->policy_currency) }}">
                        @error('policy_currency') <span class="text-danger-msg">{{ $message }}</span> @enderror
                    </td>
                </tr>
                <tr>
                    <th class="tableheader3">Original Basic Premium</th>
                    <td class="tabledata3">
                        <input type="number" step="0.01" name="basic_premium" class="form-control-custom" 
                               value="{{ old('basic_premium', $cancellations->basic_premium) }}">
                        @error('basic_premium') <span class="text-danger-msg">{{ $message }}</span> @enderror
                    </td>
                </tr>
                
                <tr class="total-row" style="background: #fdf2f2;">
                    <th class="tableheader3">Prorated Refund to Client</th>
                    <td class="tabledata3">
                        <input type="number" step="0.01" name="premium_refund" class="form-control-custom" style="font-weight: bold; color: #e20613;"
                               value="{{ old('premium_refund', $cancellations->premium_refund) }}">
                        @error('premium_refund') <span class="text-danger-msg">{{ $message }}</span> @enderror
                    </td>
                </tr>        
            </table>

            <div class="actions" style="margin-top: 30px; text-align: center;">
                <button type="submit" class="btn btn-save">Save Updates</button>
                <a href="{{ route('insurance_broking.view_list.index', ['action' => 'view_cancelled_slip_list']) }}" class="btn btn-secondary">Back to List</a>
            </div>
        </form>

    @else
        <div style="padding: 50px; text-align: center; color: red;">
            @if($isEmpty)
                <h3>Error: No cancellation data found in the database.</h3>
            @else
                <h3>Error: Record with ID {{ $idFromUrl }} not found in the list.</h3>
            @endif
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const config = {
            dateFormat: "Y-m-d",
            allowInput: true,
            altInput: true,
            altFormat: "F j, Y",
        };

        // Initialize Primary Date
        flatpickr("#cancellation_date", config);

        // Initialize Range Dates with Interlinked Logic
        const fpFrom = flatpickr("#cancellation_date_from", {
            ...config,
            onChange: function(selectedDates, dateStr, instance) {
                fpTo.set('minDate', dateStr);
            }
        });

        const fpTo = flatpickr("#cancellation_date_to", {
            ...config,
            onChange: function(selectedDates, dateStr, instance) {
                fpFrom.set('maxDate', dateStr);
            }
        });
    });
</script>
@endpush