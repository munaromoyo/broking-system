@extends('layouts.app')

@section('title', $pageTitle ?? 'View Cancellation')

@push('styles')
<style>
    @page { margin-top: 55mm; margin-footer: 10mm; header: html_MyHeader; }
    body { font-family: "Helvetica Neue", Helvetica, Arial, sans-serif; color: #333; }
    .title-box { text-align: center; margin-bottom: 30px; }
    .title-box h1 { font-size: 24px; text-transform: uppercase; color: #1a1a1a; border-bottom: 2px solid #e20613; display: inline-block; padding-bottom: 5px; }
    .ref-number { text-align: right; font-weight: bold; font-size: 14px; margin-bottom: 10px; }
    .content-table { width: 100%; border-collapse: collapse; margin-bottom: 25px; table-layout: fixed; }
    .section-header { background-color: #f8f9fa; color: #1a1a1a; padding: 12px; font-weight: bold; font-size: 15px; border-bottom: 2px solid #dee2e6; }
    .tableheader3 { text-align: left; width: 35%; padding: 10px 15px; background-color: #fcfcfc; border-bottom: 1px solid #eee; font-size: 11px; color: #666; text-transform: uppercase; letter-spacing: 0.5px; }
    .tabledata3 { padding: 10px 15px; border-bottom: 1px solid #eee; font-size: 13px; color: #000; font-weight: 500; word-wrap: break-word; }
    .summary-title { font-size: 18px; color: #e20613; margin: 20px 0 10px 0; font-weight: bold; }
    .total-row { background-color: #fff5f5; }
    .total-row th, .total-row td { border-bottom: 2px solid #e20613; font-size: 14px; color: #e20613; font-weight: bold; }
    .btn { padding: 10px 20px; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 12px; display: inline-block; margin-right: 8px; }
    .btn-edit { background-color: #4e73df; color: white; }
    .btn-pdf { background-color: #1cc88a; color: white; }
    .btn-secondary { background-color: #858796; color: white; }
</style>
@endpush

@section('content')
<div style="padding: 0 50px;">
    @if($cancellations)
        <div class="ref-number">CANCELLATION REF: CAN-{{ $cancellations->slip_id }}</div>
        
        <div class="title-box">
            <h1 style="color: #e20613;">Cancellation Advice</h1>
        </div>

        <table class="content-table">
            <tr><th colspan="2" class="section-header">Original Policy Information</th></tr>
            <tr><th class="tableheader3">Insured</th><td class="tabledata3">{{ $cancellations->insured_name }}</td></tr>
            <tr><th class="tableheader3">Policy Number/Name</th><td class="tabledata3">{{ $cancellations->insurance_policy }}</td></tr>
            <tr><th class="tableheader3">Original Slip ID</th><td class="tabledata3"><strong>#{{ $cancellations->slip_id }}</strong></td></tr>
        </table>

        <table class="content-table">
            <tr><th colspan="2" class="section-header">Cancellation Details</th></tr>
            <tr>
                <th class="tableheader3">Effective Date</th>
                <td class="tabledata3" style="color: #e20613; font-weight: bold;">
                    {{ \Carbon\Carbon::parse($cancellations->cancellation_date)->format('d M Y') }}
                </td>
            </tr>
            <tr><th class="tableheader3">Cancelled By</th><td class="tabledata3">{{ $cancellations->cancelled_by }}</td></tr>
            <tr><th class="tableheader3">Remarks / Reason</th><td class="tabledata3">{!! nl2br(e($cancellations->remarks)) !!}</td></tr>
        </table>

        <div class="summary-title">Financial Adjustment</div>
        <table class="content-table">
            <tr><th class="tableheader3">Currency</th><td class="tabledata3">{{ $cancellations->policy_currency }}</td></tr>
            <tr><th class="tableheader3">Original Basic Premium</th><td class="tabledata3">{{ number_format($cancellations->basic_premium, 2) }}</td></tr>
            
            <tr class="total-row" style="background: #fdf2f2;">
                <th class="tableheader3">Prorated Refund to Client</th>
                <td class="tabledata3">
                    <strong>{{ number_format($cancellations->premium_refund, 2) }}</strong>
                </td>
            </tr>        
        </table>

        <div class="actions" style="margin-top: 30px; text-align: center;">
            <a href="{{ route('insurance_broking.cancelled_slips.edit', $cancellations->slip_id) }}" class="btn btn-edit">Edit</a>
            <a href="{{ route('insurance_broking.view_list.index', ['action' => 'view_cancelled_slip_list']) }}" class="btn btn-secondary">Back</a>
        </div>
    @else
        {{-- Debugging error fallback box --}}
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