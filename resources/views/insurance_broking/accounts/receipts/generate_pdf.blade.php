<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Official Receipt - REC{{ str_pad($receipt->receipt_number, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
        @page {
            margin: 40px 50px 50px 50px;
        }
        body {
            font-family: 'Helvetica Neue', 'Helvetica', Helvetica, Arial, sans-serif;
            color: #2c3e50;
            font-size: 11px;
            line-height: 1.5;
        }
        
        /* Corporate Letterhead Structure */
        .letterhead-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .logo-container {
            width: 120px;
            vertical-align: top;
        }
        .text-red {
            color: #dc3545;
            font-weight: bold;
        }

        /* Divider Rule */
        .horizontal-rule {
            border: none;
            border-top: 2px solid #e2e8f0;
            margin-bottom: 30px;
        }

        /* Receipt Meta Header */
        .receipt-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }
        .receipt-title {
            font-size: 26px;
            font-weight: 800;
            color: #1a202c;
            letter-spacing: -0.5px;
            vertical-align: bottom;
            text-transform: uppercase;
        }
        .receipt-details-box {
            text-align: right;
            vertical-align: bottom;
        }
        .details-badge {
            display: inline-block;
            background-color: #f7fafc;
            border: 1px solid #edf2f7;
            padding: 12px 18px;
            border-radius: 6px;
            text-align: right;
        }
        
        /* Itemized Calculation Ledger Grid */
        .ledger-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        .ledger-table th {
            background-color: #1a202c;
            color: #ffffff;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 10px;
            letter-spacing: 0.5px;
            padding: 12px 14px;
            border: none;
        }
        .ledger-table td {
            padding: 14px;
            border-bottom: 1px solid #edf2f7;
            color: #4a5568;
            font-size: 11.5px;
        }
        .label-column {
            font-weight: 600;
            color: #2d3748;
            width: 35%;
        }
        .value-column {
            width: 65%;
        }
        .text-right {
            text-align: right;
        }
        
        /* Total Highlight Row Layout */
        .total-row {
            background-color: #f8fafc;
        }
        .total-row td {
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px double #1a202c;
            font-weight: 700;
            color: #1a202c;
            font-size: 13px;
        }

        /* Footer System Note */
        .footer-note {
            font-size: 10px;
            color: #a0aec0;
            text-align: center;
            margin-top: 60px;
            border-top: 1px solid #edf2f7;
            padding-top: 15px;
        }
    </style>
</head>
<body>

    @php
        // Accessing variables passed dynamically from your clean Receipt model controller method
        $receiptNumber = "REC-" . str_pad($receipt->receipt_number, 5, "0", STR_PAD_LEFT);
        $formattedDate = \Carbon\Carbon::parse($receipt->receipt_date)->format('d M Y');
        $formattedAmount = number_format((float)$receipt->gross_amount_received, 2);
        
        $policyName = $receipt->policy_name ?? 'N/A';
        $invoiceNumber = $receipt->invoice_number ?? 'N/A';
    @endphp

    {{-- Inserted Corporate Header Component --}}
    <header>
        <table class="letterhead-table" style="font-family: sans-serif;">
            <tr>
                <td class="logo-container">
                    @if(!empty($logoUrl))
                        <img src="{{ $logoUrl }}" style="width: 110px;">
                    @else
                        <span style="color: #dc3545; font-weight: bold; font-size: 14px; text-transform: uppercase;">
                            {{ $companyName }}
                        </span>
                    @endif
                </td>
                <td style="font-size: 11px; line-height: 1.4; vertical-align: top; padding-left: 10px;">
                    <strong style="color: red; font-size: 12px;">{{ $companyName }}</strong><br>
                    <span class="text-red">Add</span>: {{ $address }}<br>
                    <span class="text-red">C</span>: {{ $phone }}<br>
                    <span class="text-red">E</span>: {{ $email }}
                </td>
                <td style="text-align: right; vertical-align: top; width: 80px;">
                    @if(!empty($qrString))
                        {{-- Correctly configured to bind the offline base64 vector data URI passed from your controller --}}
                        <img src="{{ $qrString }}" style="width: 70px; height: 70px; display: block; margin-left: auto;" alt="Header Verification QR">
                    @else
                        <div style="width: 70px; height: 70px;"></div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

    <hr class="horizontal-rule">

    {{-- Receipt Meta Header Info --}}
    <table class="receipt-meta-table">
        <tr>
            <td class="receipt-title">Official Receipt</td>
            <td class="receipt-details-box">
                <div class="details-badge">
                    <span style="color: #718096; font-size: 10px; text-transform: uppercase; font-weight: 600;">Receipt Number</span><br>
                    <strong style="font-size: 14px; color: #1a202c; font-family: monospace;">{{ $receiptNumber }}</strong><br style="line-height: 1.8;">
                    <span style="color: #718096; font-size: 10px; text-transform: uppercase; font-weight: 600;">Date of Payment</span><br>
                    <strong style="color: #2d3748;">{{ $formattedDate }}</strong>
                </div>
            </td>
        </tr>
    </table>

    {{-- Statement Details Ledger --}}
    <table class="ledger-table">
        <thead>
            <tr>
                <th style="border-top-left-radius: 6px; border-bottom-left-radius: 6px;">Description Parameter</th>
                <th class="text-right" style="border-top-right-radius: 6px; border-bottom-right-radius: 6px;">Details</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label-column">Received From (Client)</td>
                <td class="value-column">{{ $receipt->client_name }}</td>
            </tr>
            <tr>
                <td class="label-column">Insurance Policy Reference</td>
                <td class="value-column">{{ $policyName }}</td>
            </tr>
            <tr>
                <td class="label-column">Associated Invoice #</td>
                <td class="value-column"><span style="font-family: monospace; font-weight: 600;">{{ $invoiceNumber }}</span></td>
            </tr>
            <tr>
                <td class="label-column">Payment Method Reference</td>
                <td class="value-column">
                    {{ $receipt->payment_method ?? 'Transfer' }} 
                    @if(!empty($receipt->payment_ref)) (Ref: {{ $receipt->payment_ref }}) @endif
                </td>
            </tr>
            <tr>
                <td class="label-column">Account Currency</td>
                <td class="value-column">
                    <span style="background-color: #edf2f7; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 10px;">
                        {{ $receipt->policy_currency ?? 'ZMW' }}
                    </span>
                </td>
            </tr>
            <tr class="total-row">
                <td style="text-transform: uppercase; letter-spacing: 0.5px;">Gross Amount Received</td>
                <td class="text-right" style="font-size: 14px;">
                    {{ $receipt->policy_currency ?? 'ZMW' }} {{ $formattedAmount }}
                </td>
            </tr>
        </tbody>
    </table>

    {{-- System Disclaimer Footer --}}
    <div class="footer-note">
        <p>This is an official system-generated payment receipt from the <strong>{{ $companyName }} Management System (RIB)</strong>. No physical signature is required.</p>
    </div>

</body>
</html>