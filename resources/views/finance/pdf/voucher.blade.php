<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: sans-serif; color: #333; font-size: 13px; }
        .header-table { width: 100%; border-bottom: 2px solid #4e73df; padding-bottom: 15px; margin-bottom: 20px; }
        .company-info { font-size: 11px; color: #555; vertical-align: middle; }
        .doc-title { text-align: right; vertical-align: middle; }
        .doc-title h2 { margin: 0; color: #4e73df; font-size: 20px; }
        .info-box { background: #f8f9fc; padding: 15px; border: 1px solid #e3e6f0; margin-bottom: 20px; }
        .label { font-weight: bold; color: #4e73df; font-size: 10px; text-transform: uppercase; display: block; }
        .value { font-size: 14px; }
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table-data th { background-color: #4e73df; color: white; padding: 10px; text-align: left; font-size: 11px; }
        .table-data td { padding: 12px; border-bottom: 1px solid #e3e6f0; vertical-align: top; }
        .amount-large { font-size: 18px; font-weight: bold; color: #4e73df; text-align: right; }
        .qr-container { text-align: center; vertical-align: top; padding-top: 10px; }
        .sig-section { margin-top: 60px; width: 100%; table-layout: fixed; }
        .sig-line { border-top: 1px solid #999; width: 160px; text-align: center; font-size: 11px; padding-top: 5px; }
        .footer-note { font-size: 10px; color: #aaa; text-align: center; margin-top: 40px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="90" style="vertical-align: middle;">
                @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" style="width: 80px; max-height: 80px;">
                @endif
            </td>
            <td class="company-info">
                <strong style="font-size: 14px; color: #4e73df;">{{ $companyName }}</strong><br>
                {!! nl2br(e($address)) !!}<br>
                C: {{ $phone }}<br>
                E: {{ $email }}
            </td>
            <td class="doc-title">
                <h2>PAYMENT VOUCHER</h2>
                <span style="color: #888; font-weight: bold;">#{{ $voucherNumber }}</span>
            </td>
        </tr>
    </table>

    <div class="info-box">
        <table width="100%">
            <tr>
                <td width="50%">
                    <span class="label">Pay To:</span>
                    <span class="value">{{ $row->client_name ?? $row->insured_name ?? 'Client' }}</span>
                </td>
                <td width="25%">
                    <span class="label">Date:</span>
                    <span class="value">{{ $formattedDate }}</span>
                </td>
                <td width="25%">
                    <span class="label">Status:</span>
                    @php
                        $statusColor = (strtolower($row->status ?? 'pending') === 'approved' || strtolower($row->status ?? 'pending') === 'paid') ? '#1cc88a' : '#f6c23e';
                    @endphp
                    <span class="value" style="color: {{ $statusColor }}; font-weight: bold;">
                        {{ strtoupper($row->status ?? 'PENDING') }}
                    </span>
                </td>
            </tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>Description / Purpose</th>
                <th style="text-align: center; width: 130px;">System Verification</th>
                <th style="text-align: right; width: 140px;">Amount ({{ $row->policy_currency ?? $row->currency ?? 'ZMW' }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <div style="font-weight: bold; margin-bottom: 5px;">{{ $row->policy_name ?? 'Insurance Premium Remittance Settlement' }}</div>
                    <div style="color: #555; line-height: 1.4;">{{ $row->description ?? 'Settlement voucher generated for insurance policy matching internal transaction registries.' }}</div>
                    <div style="margin-top: 10px;">
                        <small style="color: #777; display: block;">Internal Ref: PV-{{ $row->id }}</small>
                        @if(!empty($row->receipt_number))
                            <small style="color: #777; display: block;">Receipt Association: {{ $row->receipt_number }}</small>
                        @endif
                    </div>
                </td>
                <td class="qr-container">
                    @if(!empty($qrString))
                        {{-- Correctly configured to bind the offline base64 vector data URI passed from your controller --}}
                        <img src="{{ $qrString }}" style="width: 70px; height: 70px; display: block; margin-left: auto;" alt="Header Verification QR">
                    @else
                        <div style="width: 70px; height: 70px;"></div>
                    @endif
                </td>
                                <td class="amount-large">
                    {{ $amount }}
                </td>
            </tr>
        </tbody>
    </table>

    <table class="sig-section">
        <tr>
            <td><div class="sig-line">Prepared By</div></td>
            <td><div class="sig-line">Authorized By</div></td>
            <td align="right"><div class="sig-line" style="margin-left: auto;">Payee Signature</div></td>
        </tr>
    </table>

    <div class="footer-note">
        System Generated Document • {{ $companyName }} Management System • {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>