<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Voucher - {{ $voucherNumber }}</title>
    <style>
        @page { 
            margin-top: 15mm; 
            margin-bottom: 15mm; 
            margin-left: 15mm; 
            margin-right: 15mm;
        }
        body { 
            font-family: sans-serif; 
            color: #333; 
            font-size: 13px; 
            line-height: 1.4;
        }
        .voucher-wrapper { width: 100%; }
        
        /* Main Document Info Layout */
        .info-box { 
            background: #f8f9fc; 
            padding: 15px; 
            border: 1px solid #e3e6f0; 
            margin-top: 20px; 
            margin-bottom: 20px; 
        }
        .label { 
            font-weight: bold; 
            color: #4e73df; 
            font-size: 10px; 
            text-transform: uppercase; 
            display: block; 
            margin-bottom: 2px;
        }
        .value { font-size: 14px; font-weight: bold; }
        .doc-title-text { font-size: 20px; margin: 0; color: #4e73df; font-weight: bold; }

        /* Particulars Ledger Grid */
        .table-data { width: 100%; border-collapse: collapse; margin-top: 20px; margin-bottom: 40px; }
        .table-data th { background-color: #4e73df; color: white; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase; }
        .table-data td { padding: 12px; border-bottom: 1px solid #e3e6f0; vertical-align: top; }
        .amount-large { font-size: 18px; font-weight: bold; color: #4e73df; text-align: right; }
        .category-badge { font-size: 11px; color: #666; font-weight: bold; display: inline-block; margin-top: 5px; }

        /* Corporate Signatures Footing */
        .sig-section { margin-top: 60px; width: 100%; }
        .sig-line { border-top: 1px solid #999; width: 180px; text-align: center; font-size: 11px; padding-top: 6px; color: #555; }
        .footer-note { font-size: 10px; color: #aaa; text-align: center; margin-top: 40px; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

    {{-- MANDATORY INJECTED HEADER METRICS --}}
    <header>
        <table style="width: 100%; border-collapse: collapse; font-family: sans-serif; border-bottom: 2px solid #333; padding-bottom: 10px;">
            <tr>
                <td style="width: 130px; vertical-align: middle;">
                    @if(isset($logoUrl) && !empty($logoUrl))
                        <img src="{{ $logoUrl }}" style="width: 110px; max-height: 80px; display: block;">
                    @else
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
                        <img src="{{ $qrString }}" style="width: 75px; height: 75px; display: block; margin-left: auto;">
                    @else
                        <div style="width: 75px; height: 75px;"></div>
                    @endif
                </td>
            </tr>
        </table>
    </header>

   {{-- DOCUMENT DATA FIELDS LAYER --}}
        <div class="voucher-wrapper">
            
            <table width="100%" style="margin-top: 15px;">
                <tr>
                    <td><h1 class="doc-title-text">PAYMENT VOUCHER</h1></td>
                    <td align="right" style="font-size: 16px; font-weight: bold; color: #888;">#{{ $voucherNumber }}</td>
                </tr>
            </table>

            <div class="info-box">
                <table width="100%">
                    <tr>
                        <td width="40%">
                            <span class="label">Pay To / Client Name:</span>
                            <span class="value">{{ ucwords(strtolower(trim($voucher->client_name))) }}</span>
                        </td>
                        <td width="20%">
                            <span class="label">Date Created:</span>
                            <span class="value">{{ \Carbon\Carbon::parse($voucher->created_at)->format('d M Y') }}</span>
                        </td>
                        <td width="20%">
                            <span class="label">Payment Method:</span>
                            <span class="value" style="font-size: 13px;">{{ $voucher->payment_method }}</span>
                        </td>
                        <td width="20%">
                            <span class="label">Status:</span>
                            <span class="value" style="{{ $voucher->status === 'Approved' ? 'color: #1cc88a;' : 'color: #f6c23e;' }}">
                                {{ strtoupper($voucher->status ?? 'PENDING') }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>

            <table class="table-data">
                <thead>
                    <tr>
                        <th>Description / Purpose</th>
                        <th style="text-align: right; width: 160px;">Amount ({{ $voucher->currency }})</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <strong style="font-size: 14px; display: block; margin-bottom: 5px;">{{ $voucher->description }}</strong>
                            <span class="category-badge">Category: {{ $voucher->expense_category ?? 'General Expense' }}</span><br>
                            
                            {{-- Multi-tenant meta audit fields block --}}
                            <div style="margin-top: 10px; font-size: 10px; color: #888; line-height: 1.3;">
                                <span>Internal Tracking ID: PV-{{ $voucher->id }}</span><br>
                                <span>Prepared By: {{ $voucher->created_by }}</span>
                                @if(!empty($voucher->approved_by))
                                    <br><span>Approved By: {{ $voucher->approved_by }} on {{ \Carbon\Carbon::parse($voucher->approved_at)->format('d M Y H:i') }}</span>
                                @endif
                                @if(!empty($voucher->updated_by) && $voucher->updated_by !== $voucher->created_by)
                                    <br><span>Last Modified By: {{ $voucher->updated_by }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="amount-large">{{ number_format($voucher->amount, 2) }}</td>
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
            System Generated Document • RIB Management System • {{ $currentTime }}
        </div>
    </div>

</body>
</html>