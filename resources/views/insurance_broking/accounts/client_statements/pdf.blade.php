<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Statement of Account</title>
    <style>
        body { font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif; color: #333; font-size: 11px; line-height: 1.4; }
        
        /* Define document top, right, bottom, and left page container margins */
        @page { margin: 150px 40px 60px 40px; }
        
        /* Style the fixed header element for clean multi-page repetition inside DomPDF */
        header { 
            position: fixed; 
            top: -110px; 
            left: 0px; 
            right: 0px; 
            height: 100px; 
            border-bottom: 2px solid #ff0000; 
            padding-bottom: 10px;
        }
        
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .tableheader3 { text-align: left; width: 25%; padding: 8px; border-bottom: 1px solid #eee; font-weight: bold; background-color: #f9f9f9; }
        .tabledata3 { padding: 8px; border-bottom: 1px solid #eee; vertical-align: top; }
        
        .statement-label { font-size: 22px; font-weight: bold; color: #333; }
        .title-container { border-bottom: 1px solid #ddd; padding-bottom: 5px; margin-bottom: 15px; }
        
        .transaction-table { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 20px; }
        .transaction-table th { font-size: 10px; background-color: #333; color: white; padding: 8px; text-align: left; text-transform: uppercase; }
        .transaction-table td { padding: 8px; border-bottom: 1px solid #eee; }
        
        .total-row { font-weight: bold; font-size: 12px; background-color: #eee; }
        .text-success { color: green; }
        .text-danger { color: red; }
        .bank-details-box { border: 1px solid #eee; padding: 10px; margin-top: 30px; font-size: 10px; background-color: #fafafa; }
    </style>
</head>
<body>

    <header>
        <table style="width: 100%; border-collapse: collapse; font-family: sans-serif;">
            <tr>
                <td style="width: 130px; vertical-align: middle;">
                    @if(isset($logoUrl) && !empty($logoUrl))
                        <img src="{{ $logoUrl }}" style="width: 110px; max-height: 80px; display: block;">
                    @else
                        {{-- Clean, professional blank spacer keeping total privacy alignment when zero files exist --}}
                        <div style="width: 110px; height: 50px;"></div>
                    @endif
                </td>
                
                <td style="font-size: 11px; line-height: 1.4; vertical-align: top; padding-left: 10px;">
                    <strong style="color: #ff0000; font-size: 13px; text-transform: uppercase; letter-spacing: 0.5px;">{{ $companyName }}</strong><br>
                    <span style="color: #ff0000; font-weight: bold;">Add</span>: {{ $address }}<br>
                    <span style="color: #ff0000; font-weight: bold;">C</span>: {{ $phone }}<br>
                    <span style="color: #ff0000; font-weight: bold;">E</span>: {{ $email }}
                </td>
                
                <td style="text-align: right; vertical-align: top; width: 80px;">
                    @if(isset($qrString) && !empty($qrString))
                        {{-- Enforced clean visual metrics rendering base64 binary strings directly --}}
                        <img src="{{ $qrString }}" style="width: 75px; height: 75px; display: block; margin-left: auto;">
                    @else
                        {{-- Quietly render an empty container if the engine hits an execution bottleneck --}}
                        <div style="width: 75px; height: 75px;"></div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <div class="title-container">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td class="statement-label">STATEMENT OF ACCOUNT</td>
                <td style="text-align: right; font-size: 11px; vertical-align: bottom;">
                    <b>Date:</b> {{ $date }}<br>
                    <b>Currency:</b> {{ $selected_curr }}
                </td>
            </tr>
        </table>
    </div>

    <table class="content-table">
        <tr>
            <th class="tableheader3">CLIENT NAME</th>
            <td class="tabledata3"><strong>{{ $selected_client }}</strong></td>
        </tr>
    </table>

    <div style="margin-top: 15px;">
        <span style="font-weight: bold; color: red; text-transform: uppercase;">Invoices / Debits</span>
        <table class="transaction-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Invoice No.</th>
                    <th style="width: 15%;">Date</th>
                    <th style="width: 25%;">Policy Name</th>
                    <th>Description</th>
                    <th style="text-align: right; width: 15%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filtered_invoices as $inv)
                <tr>
                    <td>INV#{{ str_pad($inv->invoice_number ?? $inv['invoice_number'], 4, "0", STR_PAD_LEFT) }}</td>
                    <td>{{ isset($inv->created_at) ? (\Illuminate\Support\Carbon::parse($inv->created_at)->format('Y-m-d')) : ($inv['created_at'] ?? 'N/A') }}</td>
                    <td>{{ $inv->policy_name ?? $inv['policy_name'] }}</td>
                    <td>Premium Charge</td>
                    <td style="text-align: right;">{{ number_format($inv->gross_premium ?? $inv['gross_premium'], 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic; color: #666; padding: 12px;">No active invoice records or premium debits verified for this timeframe.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 10px;">
        <span style="font-weight: bold; color: green; text-transform: uppercase;">Receipts / Credits</span>
        <table class="transaction-table">
            <thead>
                <tr>
                    <th style="width: 15%;">Receipt No.</th>
                    <th style="width: 15%;">Date Received</th>
                    <th style="width: 25%;">Policy Name</th>
                    <th>Description</th>
                    <th style="text-align: right; width: 15%;">Amount Paid</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filtered_receipts as $rec)
                <tr>
                    <td>REC#{{ str_pad($rec['receipt_number'] ?? $rec->receipt_number, 4, "0", STR_PAD_LEFT) }}</td>
                    <td>{{ $rec['receipt_date'] ?? $rec->receipt_date }}</td>
                    <td>{{ $rec['policy_name'] ?? $rec->policy_name }}</td>
                    <td>Payment Received</td>
                    <td style="text-align: right;" class="text-success">- {{ number_format($rec['gross_amount_received'] ?? $rec->gross_amount_received, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; font-style: italic; color: #666; padding: 12px;">No verified payment credits recorded for this timeframe.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 10px;">
        <span style="font-weight: bold; color: #555; text-transform: uppercase;">Cancelled Slips / Credits</span>
        <table class="transaction-table">
            <thead>
                <tr>
                    <th style="width: 20%;">Reference</th>
                    <th style="width: 25%;">Policy Name</th>
                    <th>Date Cancelled</th>
                    <th style="text-align: right; width: 20%;">Credit Amount</th>
                </tr>
            </thead>
            <tbody>
                @forelse($filtered_cancellations as $slip)
                <tr>
                    <td>{{ $slip['slip_id'] ?? $slip->slip_id }}</td>
                    <td>{{ $slip['policy_name'] ?? $slip->policy_name }}</td>
                    <td>{{ $slip['cancellation_date'] ?? $slip->cancellation_date }}</td>
                    <td style="text-align: right;" class="text-danger">- {{ number_format($slip['premium_refund'] ?? $slip->premium_refund, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="text-align: center; font-style: italic; color: #666; padding: 12px;">No processed cancellations or premium offsets on record.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div style="margin-top: 25px; width: 50%; margin-left: 50%;">
        <table class="content-table">
            <tr>
                <td class="tabledata3" style="border: 0; padding: 4px 8px;">Total Invoiced:</td>
                <td class="tabledata3" style="text-align: right; border: 0; padding: 4px 8px;">{{ number_format($total_invoiced, 2) }}</td>
            </tr>
            <tr>
                <td class="tabledata3" style="border: 0; padding: 4px 8px;">Total Paid:</td>
                <td class="tabledata3" style="text-align: right; color: green; border: 0; padding: 4px 8px;">({{ number_format($total_paid, 2) }})</td>
            </tr>
            <tr>
                <td class="tabledata3" style="border: 0; padding: 4px 8px;">Total Cancelled:</td>
                <td class="tabledata3" style="text-align: right; color: red; border: 0; padding: 4px 8px;">({{ number_format($total_cancelled, 2) }})</td>
            </tr>
            <tr class="total-row">
                <td class="tabledata3" style="border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 6px 8px;">BALANCE DUE:</td>
                <td class="tabledata3" style="text-align: right; color: red; border-top: 1px solid #333; border-bottom: 1px solid #333; padding: 6px 8px;">
                    {{ $selected_curr }} {{ number_format($balance_due, 2) }}
                </td>
            </tr>
        </table>
    </div>

    <div class="bank-details-box">
        <strong style="color: red;">PAYMENT INSTRUCTIONS</strong><br>
        Please quote the specific Client Name or relevant system Invoice Numbers when transmitting electronic funds transfers. Settlement balances are requested in selected billing currency.
    </div>

</body>
</html>