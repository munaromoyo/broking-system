<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Credit Note - {{ $note->slip_id }}</title>
    <style>
        @page {
            margin: 30px 40px;
        }
        body { 
            font-family: sans-serif; 
            color: #333; 
            font-size: 11px;
            line-height: 1.4;
        }
        .content-table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-bottom: 20px; 
        }
        .tableheader3 { 
            text-align: left; 
            width: 35%; 
            padding: 8px; 
            border-bottom: 1px solid #eee; 
            font-size: 11px; 
            font-weight: bold; 
        }
        .tabledata3 { 
            padding: 8px; 
            border-bottom: 1px solid #eee; 
            font-size: 11px; 
        }
        .invoice-label { 
            font-size: 22px; 
            font-weight: bold; 
            color: #d9534f; 
        }
        .text-right { 
            text-align: right; 
        }
    </style>
</head>
<body>

    <header>
        <table style="width: 100%; border-collapse: collapse; font-family: sans-serif; border-bottom: 2px solid #333; padding-bottom: 10px;">
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

    <table style="width: 100%; margin-top: 15px; margin-bottom: 15px;">
        <tr>
            <td class="invoice-label" style="vertical-align: bottom;">CREDIT NOTE</td>
            <td class="text-right" style="font-size: 11px; vertical-align: bottom; line-height: 1.5;">
                <b>CN No:</b> CN{{ $note->id }}<br>
                <b>Slip ID:</b> {{ $note->slip_id }}<br>
                <b>Date:</b> {{ $current_date }}
            </td>
        </tr>
    </table>

    <table class="content-table">
        <tr>
            <th class="tableheader3">INSURED</th>
            <td class="tabledata3">{{ $note->slipCancellation->insured_name ?? $note->insured_name }}</td>
        </tr>
        <tr>
            <th class="tableheader3">POLICY</th>
            <td class="tabledata3">{{ $note->slipCancellation->insurance_policy ?? $note->insurance_policy }}</td>
        </tr>
        <tr>
            <th class="tableheader3">CANCEL DATE</th>
            <td class="tabledata3">
                {{ \Carbon\Carbon::parse($note->slipCancellation->cancellation_date ?? $note->cancellation_date)->format('d M Y') }}
            </td>
        </tr>
        <tr>
            <th class="tableheader3">REMARKS</th>
            <td class="tabledata3">{{ $note->slipCancellation->remarks ?? $note->remarks ?? 'N/A' }}</td>
        </tr>
    </table>

    <table class="content-table" style="border: 1px solid #eee;">
        <thead>
            <tr style="background-color: #f2f2f2;">
                <th class="tableheader3">Description</th>
                <th class="tabledata3 text-right" style="font-weight: bold;">
                    Amount ({{ $note->slipCancellation->policy_currency ?? $note->policy_currency }})
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="tabledata3">Original Premium (Before Levy)</td>
                <td class="tabledata3 text-right">
                    {{ number_format((float)($note->slipCancellation->basic_premium ?? $note->basic_premium), 2) }}
                </td>
            </tr>
            <tr style="font-weight: bold; background: #f9f9f9;">
                <td class="tabledata3">TOTAL REFUND AMOUNT</td>
                <td class="tabledata3 text-right" style="color: #d9534f; border-top: 2px solid #333;">
                    {{ number_format((float)($note->slipCancellation->premium_refund ?? $note->premium_refund), 2) }}
                </td>
            </tr>
        </tbody>
    </table>

</body>
</html>