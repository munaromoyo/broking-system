<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Insurance Quotation Summary - #{{ $quotation->id }}</title>
    <style>
        /* 1. Structural Page Media Context Configured for Dompdf */
        @page { 
            margin: 140px 50px 80px 50px; 
        }
        
        /* 2. Fixed positioning handles clean recurring header drawing rules */
        header {
            position: fixed;
            top: -110px;
            left: 0px;
            right: 0px;
            height: 100px;
            width: 100%;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }
        .section-title {
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            background-color: #f1f5f9;
            color: #334155;
            padding: 6px 10px;
            margin-top: 20px;
            margin-bottom: 10px;
            border-left: 3px solid #e20613;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table td {
            padding: 6px 8px;
            vertical-align: top;
            border-bottom: 1px solid #f1f5f9;
        }
        .label {
            font-weight: bold;
            color: #64748b;
            width: 30%;
            font-size: 11px;
            text-transform: uppercase;
        }
        .value {
            color: #0f172a;
            width: 70%;
        }
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .financial-table th {
            background-color: #1e293b;
            color: #ffffff;
            font-size: 10px;
            text-transform: uppercase;
            padding: 8px;
            text-align: left;
        }
        .financial-table td {
            padding: 8px;
            border-bottom: 1px solid #e2e8f0;
        }
        .text-right {
            text-align: right !important;
        }
        .gross-row {
            background-color: #f8fafc;
            font-weight: bold;
        }
        .gross-price {
            color: #e20613;
            font-size: 14px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <header>
        <table style="width: 100%; border-collapse: collapse; font-family: sans-serif;">
            <tr>
                <td style="width: 120px;">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" style="width: 110px;">
                    @endif
                </td>
                <td style="font-size: 11px; line-height: 1.4; vertical-align: top;">
                    <strong style="color: red; font-size: 12px;">{{ $companyName }}</strong><br>
                    <span style="color: red; font-weight: bold;">Add</span>: {{ $address }}<br>
                    <span style="color: red; font-weight: bold;">C</span>: {{ $phone }}<br>
                    <span style="color: red; font-weight: bold;">E</span>: {{ $email }}
                </td>
                <td style="text-align: right; vertical-align: top;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode($qrString) }}" style="width: 70px; height: 70px;">
                </td>
            </tr>
        </table>
    </header>

    <div style="font-family: sans-serif;">
        <div style="text-align: right; font-size: 10px; color: #64748b;">
            <strong>Quote Reference:</strong> #{{ $quotation->id }}<br>
            <strong>Generated Date:</strong> {{ now()->format('d M, Y H:i A') }}
        </div>
        
        <h1 style="font-size: 20px; text-align: center; margin-bottom: 10px; margin-top: 5px; color: #1e293b; text-transform: uppercase;">Insurance Quotation</h1>

        <div class="section-title">1. Client & Underwriter Identification</div>
        <table class="data-table">
            <tr>
                <td class="label">Insured Legal Entity</td>
                <td class="value" style="font-size: 14px; font-weight: bold;">{{ $quotation->insured }}</td>
            </tr>
            <tr>
                <td class="label">Underwriting Insurer</td>
                <td class="value">{{ $quotation->insurer }}</td>
            </tr>
            @if($quotation->principal_address)
            <tr>
                <td class="label">Principal Address</td>
                <td class="value" style="white-space: pre-line;">{{ $quotation->principal_address }}</td>
            </tr>
            @endif
            @if($quotation->nature_of_business)
            <tr>
                <td class="label">Nature of Business</td>
                <td class="value">{{ $quotation->nature_of_business }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Coverage Period</td>
                <td class="value">
                    <strong>Inception:</strong> {{ $quotation->policy_start_date ? \Carbon\Carbon::parse($quotation->policy_start_date)->format('d M, Y') : 'N/A' }} 
                    <span style="margin-left: 15px; margin-right: 15px; color: #cbd5e1;">|</span>
                    <strong>Expiry:</strong> {{ $quotation->policy_expiry_date ? \Carbon\Carbon::parse($quotation->policy_expiry_date)->format('d M, Y') : 'N/A' }}
                </td>
            </tr>
        </table>

        <div class="section-title">2. Placement Parameters & Architecture</div>
        <table class="data-table">
            <tr>
                <td class="label">Policy Type</td>
                <td class="value"><strong>{{ $quotation->insurance_policy ?? 'N/A' }}</strong></td>
            </tr>
            <tr>
                <td class="label">Location of Risk</td>
                <td class="value">{{ $quotation->location_of_risk ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Property Assets Insured</td>
                <td class="value" style="white-space: pre-line;">{{ $quotation->property_insured ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Scope of Cover</td>
                <td class="value" style="white-space: pre-line;">{{ $quotation->scope_of_cover ?? 'N/A' }}</td>
            </tr>
            @if($quotation->extensions)
            <tr>
                <td class="label">Policy Extensions</td>
                <td class="value" style="white-space: pre-line;">{{ $quotation->extensions }}</td>
            </tr>
            @endif
            @if($quotation->excess_deductible)
            <tr>
                <td class="label">Excess / Deductible</td>
                <td class="value" style="white-space: pre-line;">{{ $quotation->excess_deductible }}</td>
            </tr>
            @endif
            @if($quotation->specific_warranties)
            <tr>
                <td class="label">Specific Warranties</td>
                <td class="value" style="white-space: pre-line;">{{ $quotation->specific_warranties }}</td>
            </tr>
            @endif
            @if($quotation->specific_conditions)
            <tr>
                <td class="label">Specific Conditions</td>
                <td class="value" style="white-space: pre-line;">{{ $quotation->specific_conditions }}</td>
            </tr>
            @endif
            <tr>
                <td class="label">Cancellation Terms</td>
                <td class="value">{{ $quotation->cancellation_clause ?? 'Standard market operational terms apply.' }}</td>
            </tr>
        </table>

        <div class="section-title">3. Premium Summary Ledger Matrix</div>
        <table class="financial-table">
            <thead>
                <tr>
                    <th>Financial Item Description</th>
                    <th class="text-right" style="width: 25%;">Rating Metrics</th>
                    <th class="text-right" style="width: 30%;">Amount ({{ $quotation->policy_currency }})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong>Total Sum Insured (TSI)</strong></td>
                    <td class="text-right">-</td>
                    <td class="text-right"><strong>{{ number_format($quotation->total_sum_insured, 2) }}</strong></td>
                </tr>
                <tr>
                    <td>Basic Premium Owed</td>
                    <td class="text-right">{{ number_format($quotation->basic_rate, 4) }} %</td>
                    <td class="text-right">{{ number_format($quotation->basic_premium, 2) }}</td>
                </tr>
                <tr>
                    <td>Discounts Retained</td>
                    <td class="text-right">{{ number_format($quotation->discount_rate, 4) }} %</td>
                    <td class="text-right" style="color: #dc2626;">-{{ number_format($quotation->discount, 2) }}</td>
                </tr>
                <tr>
                    <td>Statutory Premium Levy</td>
                    <td class="text-right">{{ number_format($quotation->premium_levy_rate, 4) }} %</td>
                    <td class="text-right">+{{ number_format($quotation->premium_levy, 2) }}</td>
                </tr>
                <tr class="gross-row">
                    <td>Gross Client Premium Summary</td>
                    <td class="text-right">-</td>
                    <td class="text-right gross-price">{{ number_format($quotation->gross_premium, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="footer">
        Confidential Document Issued via the Registry Engine. Generated dynamically in standard secure architecture format.
    </div>

</body>
</html>