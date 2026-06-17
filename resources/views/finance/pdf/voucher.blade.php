<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; color: #333; font-size: 13px; }
        .header-table { width: 100%; border-bottom: 2px solid #4e73df; padding-bottom: 15px; margin-bottom: 20px; }
        .company-info { font-size: 11px; color: #555; }
        .doc-title { text-align: right; }
        .doc-title h2 { margin: 0; color: #4e73df; font-size: 20px; }
        .info-box { background: #f8f9fc; padding: 15px; border: 1px solid #e3e6f0; margin-bottom: 20px; }
        .label { font-weight: bold; color: #4e73df; font-size: 10px; text-transform: uppercase; display: block; }
        .value { font-size: 14px; }
        .table-data { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        .table-data th { background-color: #4e73df; color: white; padding: 10px; text-align: left; font-size: 11px; }
        .table-data td { padding: 12px; border-bottom: 1px solid #e3e6f0; }
        .amount-large { font-size: 18px; font-weight: bold; color: #4e73df; text-align: right; }
        .sig-section { margin-top: 50px; width: 100%; }
        .sig-line { border-top: 1px solid #999; width: 180px; text-align: center; font-size: 11px; padding-top: 5px; }
        .footer-note { font-size: 10px; color: #aaa; text-align: center; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>
    <table class="header-table">
        <tr>
            <td width="100">
                {{-- Use public_path for PDF images --}}
                <img src="{{ public_path('img/rib_logo.jpg') }}" style="width: 80px;">
            </td>
            <td class="company-info">
                <strong>Revolution Insurance Brokers Ltd</strong><br>
                5833 Mwange Close, Lusaka<br>
                C: +26 (0) 777 780 882
            </td>
            <td class="doc-title">
                <h2>PAYMENT VOUCHER</h2>
                <span style="color: #888;">#{{ $voucherNumber }}</span>
            </td>
        </tr>
    </table>

    <div class="info-box">
        <table width="100%">
            <tr>
                <td width="50%">
                    <span class="label">Pay To:</span>
                    <span class="value">{{ $row->client_name }}</span>
                </td>
                <td width="25%">
                    <span class="label">Date:</span>
                    <span class="value">{{ $formattedDate }}</span>
                </td>
                <td width="25%">
                    <span class="label">Status:</span>
                    <span class="value" style="color: #1cc88a;">{{ strtoupper($row->status) }}</span>
                </td>
            </tr>
        </table>
    </div>

    <table class="table-data">
        <thead>
            <tr>
                <th>Description / Purpose</th>
                <th style="text-align: right; width: 150px;">Amount ({{ $row->currency }})</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    {{ $row->description }}<br>
                    <small style="color: #777;">Internal Ref: PV-{{ $row->id }}</small>
                </td>
                <td class="amount-large">{{ $amount }}</td>
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
        System Generated Document • RIB Management System • {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>