<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - INV{{ str_pad($invoice->invoice_number, 4, '0', STR_PAD_LEFT) }}</title>
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
        .logo-img {
            width: 110px;
            height: auto;
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

        /* Invoice Meta Header */
        .invoice-meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 35px;
        }
        .invoice-title {
            font-size: 26px;
            font-weight: 800;
            color: #1a202c;
            letter-spacing: -0.5px;
            vertical-align: bottom;
            text-transform: uppercase;
        }
        .invoice-details-box {
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

        /* Remittance Banking Block Wrapper */
        .banking-container {
            page-break-inside: avoid;
            margin-top: 10px;
        }
        .banking-title {
            font-size: 11px;
            font-weight: 700;
            color: #dc3545;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .banking-box {
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 16px;
            background-color: #fdfdfd;
            line-height: 1.6;
            color: #4a5568;
        }
    </style>
</head>
<body>

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
                <td style="text-align: right; vertical-align: top;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($qrString) }}" style="width: 70px; height: 70px;" alt="Header Verification QR">
                </td>
            </tr>
        </table>
    </header>

    <hr class="horizontal-rule">

    <table class="invoice-meta-table">
        <tr>
            <td class="invoice-title">Tax Invoice</td>
            <td class="invoice-details-box">
                <div class="details-badge">
                    <span style="color: #718096; font-size: 10px; text-transform: uppercase; font-weight: 600;">Invoice Number</span><br>
                    <strong style="font-size: 14px; color: #1a202c; font-family: monospace;">INV{{ str_pad($invoice->invoice_number, 4, '0', STR_PAD_LEFT) }}</strong><br style="line-height: 1.8;">
                    <span style="color: #718096; font-size: 10px; text-transform: uppercase; font-weight: 600;">Date of Issue</span><br>
                    <strong style="color: #2d3748;">{{ $dateFormatted }}</strong>
                </div>
            </td>
        </tr>
    </table>

    <table class="ledger-table">
        <thead>
            <tr>
                <th style="border-top-left-radius: 6px; border-bottom-left-radius: 6px;">Description Parameter</th>
                <th class="text-right" style="border-top-right-radius: 6px; border-bottom-right-radius: 6px;">Details / Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="label-column">Client Name</td>
                <td class="value-column">{{ $invoice->client_name }}</td>
            </tr>
            <tr>
                <td class="label-column">Policy Description</td>
                <td class="value-column">{{ $invoice->policy_name ?? 'Motor Comprehensive Insurance' }}</td>
            </tr>
            <tr>
                <td class="label-column">Account Currency</td>
                <td class="value-column"><span style="background-color: #edf2f7; padding: 2px 6px; border-radius: 4px; font-weight: 600; font-size: 10px;">{{ $invoice->policy_currency }}</span></td>
            </tr>
            <tr>
                <td class="label-column">Basic Premium</td>
                <td class="value-column text-right">{{ number_format($invoice->basic_premium ?? ($invoice->gross_premium / 1.05), 2) }}</td>
            </tr>
            <tr>
                <td class="label-column">Premium Levy (5%)</td>
                <td class="value-column text-right" style="color: #718096;">{{ number_format($invoice->premium_levy ?? (($invoice->gross_premium / 1.05) * 0.05), 2) }}</td>
            </tr>
            <tr class="total-row">
                <td style="text-transform: uppercase; letter-spacing: 0.5px;">Gross Premium Due</td>
                <td class="text-right" style="font-size: 14px;">
                    {{ $invoice->policy_currency }} {{ number_format($invoice->gross_premium, 2) }}
                </td>
            </tr>
        </tbody>
    </table>

    <div class="banking-container">
        <div class="banking-title">Banking Details for Remittance</div>
        <div class="banking-box">
            <strong style="color: #2d3748;">Bank:</strong> {{ $bankDetails['bank'] }}<br>
            <strong style="color: #2d3748;">Account Number:</strong> {{ $bankDetails['acc_no'] }}
        </div>
    </div>

</body>
</html>