<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Key Fact Statement - #{{ $placement->id }}</title>
    <style>
        /* CSS Paged Media Configuration for Dompdf */
        @page { 
            margin: 140px 50px 80px 50px; 
        }
        
        header {
            position: fixed;
            top: -110px;
            left: 0px;
            right: 0px;
            height: 100px;
            width: 100%;
        }

        .section-wrapper { page-break-inside: avoid; margin-bottom: 15px; width: 100%; }
        .content-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
        .tableheader3 { text-align: left; width: 35%; padding: 7px; border-bottom: 1px solid #eee; font-family: sans-serif; font-size: 11px; vertical-align: top; font-weight: bold; color: #444; }
        .tabledata3 { padding: 7px; border-bottom: 1px solid #eee; font-family: sans-serif; font-size: 11px; vertical-align: top; }
        
        .data-title { font-size: 11px; font-weight: bold; background-color: #f7f7f7; padding: 8px 10px; border-left: 4px solid red; text-transform: uppercase; font-family: sans-serif; color: #333; }
        .list-body { font-size: 11px; padding: 10px 10px 10px 30px; border: 1px solid #eee; border-top: none; line-height: 1.6; font-family: sans-serif; }
        .list-body ul { margin: 0; padding: 0; list-style-type: disc; }
        
        .plain-body { font-size: 11px; padding: 10px; white-space: pre-wrap; border: 1px solid #eee; border-top: none; line-height: 1.6; font-family: sans-serif; }
        
        h1 { font-family: sans-serif; font-size: 22px; text-align: center; margin-bottom: 5px; color: #333; text-transform: uppercase; }
        h2 { font-family: sans-serif; font-size: 14px; border-bottom: 2px solid red; padding-bottom: 5px; margin-top: 25px; color: #333; }
        .kfs-sub { text-align: center; font-family: sans-serif; font-size: 12px; margin-bottom: 20px; color: #666; }
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
                <td style="font-size: 11px; line-height: 1.4; vertical-align: top; padding-top: 5px;">
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
        <div style="text-align: right; font-size: 10px; font-weight: bold; color: #666;">REF: {{ $placement->id }}</div>
        <h1>Key Fact Statement</h1>
        <div class="kfs-sub">Summary of Insurance Cover & Important Information</div>

        <table class="content-table">
            <tr><th class="tableheader3">CLIENT NAME:</th><td class="tabledata3">{{ $placement->insured }}</td></tr>
            <tr><th class="tableheader3">NATURE OF BUSINESS:</th><td class="tabledata3">{{ $placement->nature_of_business }}</td></tr>
            <tr><th class="tableheader3">INSURANCE PERIOD:</th><td class="tabledata3">{{ $placement->policy_start_date }} TO {{ $placement->policy_expiry_date }}</td></tr>
            <tr><th class="tableheader3">UNDERWRITER:</th><td class="tabledata3">{{ $placement->insurer }}</td></tr>
            <tr><th class="tableheader3">CORE PROTECTION:</th><td class="tabledata3">{{ $placement->scope_of_cover }}</td></tr>
            <tr><th class="tableheader3">EXCESS:</th><td class="tabledata3">{{ $placement->excess_deductible }}</td></tr>
        </table>

        @if(!empty($extensionsList))
            <div class="section-wrapper">
                <div class="data-title">Included Extensions (Additional Benefits)</div>
                <div class="list-body">
                    <ul>
                        @foreach($extensionsList as $extension)
                            @if($extension['is_header'])
                                <div style="margin-top:10px; margin-bottom:5px; font-weight:bold; list-style-type:none; margin-left:-20px; color:red;">
                                    {{ $extension['text'] }}
                                </div>
                            @else
                                <li style="margin-bottom: 4px;">{{ $extension['text'] }}</li>
                            @endif
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="section-wrapper">
            <div class="data-title">Key Warranties (Your Obligations)</div>
            <div class="plain-body">{{ $placement->specific_warranties }}</div>
        </div>

        <div class="section-wrapper">
            <div class="data-title">Special Conditions</div>
            <div class="plain-body">{{ $placement->specific_conditions }}</div>
        </div>
         
        <h2>Premium Calculation</h2>
        <table class="content-table">
            <tr><th class="tableheader3">Currency:</th><td class="tabledata3">{{ $placement->policy_currency }}</td></tr>
            <tr><th class="tableheader3">Total Value Insured:</th><td class="tabledata3">{{ $sumInsured }}</td></tr>
            <tr><th class="tableheader3" style="color: red; font-size: 13px;">TOTAL PAYABLE:</th><td class="tabledata3" style="font-weight: bold; font-size: 13px;">{{ $grossPremium }}</td></tr>
            <tr><th class="tableheader3">Payment Terms:</th><td class="tabledata3">{{ $placement->payment_method }}</td></tr>
        </table>

        <div class="section-wrapper" style="margin-top: 30px; border: 1px solid #333; padding: 10px; page-break-inside: avoid;">
            <div style="font-size: 10px; font-weight: bold; margin-bottom: 5px;">CLIENT ACKNOWLEDGEMENT:</div>
            <div style="font-size: 9px; line-height: 1.3;">
                I/We hereby confirm that the key features, benefits, and obligations of this insurance policy have been explained to me/us. 
                I/We understand the warranties and conditions required to maintain valid cover.
            </div>
            <table style="width: 100%; margin-top: 20px;">
                <tr>
                    <td style="font-size: 10px; width: 50%;">Signature: __________________________</td>
                    <td style="font-size: 10px; width: 50%;">Date: __________________________</td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>