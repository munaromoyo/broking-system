<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Placing Slip</title>
    <style>
        /* 1. Adjusted document margins to prevent content clipping */
        @page { 
            margin: 140px 50px 80px 50px; 
        }
        
        /* 2. Fixed positioning allows the header to repeat natively on multi-page PDF documents */
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
        .tableheader3 { text-align: left; width: 35%; padding: 7px; border-bottom: 1px solid #eee; font-family: sans-serif; font-size: 11px; vertical-align: top; font-weight: bold; }
        .tabledata3 { padding: 7px; border-bottom: 1px solid #eee; font-family: sans-serif; font-size: 11px; vertical-align: top; }
        
        .data-title { font-size: 11px; font-weight: bold; background-color: #f7f7f7; padding: 6px 10px; border-left: 3px solid red; text-transform: uppercase; font-family: sans-serif; }
        .list-body { font-size: 11px; padding: 10px 10px 10px 30px; border: 1px solid #eee; border-top: none; line-height: 1.5; font-family: sans-serif; }
        .list-body ul { margin: 0; padding: 0; list-style-type: disc; }
        
        .plain-body { font-size: 11px; padding: 10px; white-space: pre-wrap; border: 1px solid #eee; border-top: none; line-height: 1.5; font-family: sans-serif; }
        h1 { font-family: sans-serif; font-size: 20px; text-align: center; margin-bottom: 10px; }
        h2 { font-family: sans-serif; font-size: 14px; border-bottom: 1px solid #333; padding-bottom: 5px; margin-top: 25px; }

        .signature-section { margin-top: 30px; width: 100%; border-collapse: collapse; }
        .sig-box { width: 48%; vertical-align: top; }
        .sig-content { padding: 15px; border: 1px solid #eee; border-top: none; height: 120px; position: relative; }
        .sig-line { border-bottom: 1px solid #333; margin-top: 80px; width: 90%; }
        .sig-label { font-family: sans-serif; font-size: 11px; font-weight: bold; margin-top: 8px; }
        .sig-subtext { font-family: sans-serif; font-size: 10px; color: #666; margin-top: 4px; }
    </style>
</head>
<body>

    <!-- <header>
        <table style="width: 100%; border-collapse: collapse; font-family: sans-serif;">
            <tr>
                <td style="width: 120px;">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" style="width: 110px;">
                    @endif
                </td>
                <td style="font-size: 11px; line-height: 1.4;">
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
    </header> -->

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

    <div style="font-family: sans-serif;">
        <div style="text-align: right; font-size: 10px;">REF: {{ $placement->id }}</div>
        <h1>Placing Slip</h1>

        <table class="content-table">
            <tr><th class="tableheader3">INSURED:</th><td class="tabledata3">{{ $placement->insured }}</td></tr>
            <tr><th class="tableheader3">NATURE OF BUSINESS:</th><td class="tabledata3">{{ $placement->nature_of_business }}</td></tr>
            <tr><th class="tableheader3">PERIOD OF INSURANCE:</th><td class="tabledata3">{{ $placement->policy_start_date }} TO {{ $placement->policy_expiry_date }}</td></tr>
            <tr><th class="tableheader3">INSURER:</th><td class="tabledata3">{{ $placement->insurer }}</td></tr>
            <tr><th class="tableheader3">INSURANCE POLICY:</th><td class="tabledata3">{{ $placement->insurance_policy }}</td></tr>
            <tr><th class="tableheader3">SCOPE OF COVER:</th><td class="tabledata3">{{ $placement->scope_of_cover }}</td></tr>
            <tr><th class="tableheader3">EXCESS:</th><td class="tabledata3">{{ $placement->excess_deductible }}</td></tr>
        </table>

        @if(!empty($extensionsList))
            <div class="section-wrapper">
                <div class="data-title">Policy Extensions</div>
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
            <div class="data-title">Property Details</div>
            <div class="plain-body">{{ $placement->property_insured }}</div>
        </div>

        <div class="section-wrapper">
            <div class="data-title">Specific Warranties</div>
            <div class="plain-body">{{ $placement->specific_warranties }}</div>
        </div>

        <div class="section-wrapper">
            <div class="data-title">Specific Conditions</div>
            <div class="plain-body">{{ $placement->specific_conditions }}</div>
        </div>
        
        <h2>Premium Summary</h2>
        <table class="content-table">
            <tr><th class="tableheader3">Policy Currency:</th><td class="tabledata3">{{ $placement->policy_currency }}</td></tr>
            <tr><th class="tableheader3">Total Sum Insured:</th><td class="tabledata3">{{ $numbers['sumInsured'] }}</td></tr>
            <tr><th class="tableheader3">Basic Rate:</th><td class="tabledata3">{{ $numbers['basicRate'] }}</td></tr>
            <tr><th class="tableheader3">Basic Premium:</th><td class="tabledata3">{{ $numbers['basicPremium'] }}</td></tr>
            <tr><th class="tableheader3">Discount Rate:</th><td class="tabledata3">{{ $numbers['discountRate'] }}</td></tr>
            <tr><th class="tableheader3">Discount:</th><td class="tabledata3">{{ $numbers['discount'] }}</td></tr>
            <tr><th class="tableheader3">Premium Levy Rate:</th><td class="tabledata3">{{ $numbers['levyRate'] }}</td></tr>
            <tr><th class="tableheader3">Premium Levy:</th><td class="tabledata3">{{ $numbers['levyAmount'] }}</td></tr>
            <tr><th class="tableheader3">Gross Premium:</th><td class="tabledata3">{{ $numbers['grossPremium'] }}</td></tr>
            <tr><th class="tableheader3">Insurer Premium:</th><td class="tabledata3">{{ $numbers['insurerPremium'] }}</td></tr>
            <tr><th class="tableheader3">Payment Method:</th><td class="tabledata3">{{ $placement->payment_method }}</td></tr>
        </table>

        <div class="section-wrapper">
            <table class="signature-section">
                <tr>
                    <td class="sig-box">
                        <div class="data-title">Broker Confirmation</div>
                        <div class="sig-content">
                            <div class="sig-line"></div>
                            <div class="sig-label">For and on behalf of {{ $companyName }}</div>
                            <div class="sig-subtext">Authorised Signature & Official Stamp</div>
                            <div class="sig-subtext">Date: ____/____/20____</div>
                        </div>
                    </td>
                    <td style="width: 4%;"></td>
                    <td class="sig-box">
                        <div class="data-title">Insurer Acceptance</div>
                        <div class="sig-content">
                            <div class="sig-line"></div>
                            <div class="sig-label">For and on behalf of {{ $placement->insurer }}</div>
                            <div class="sig-subtext">Authorised Signature & Official Stamp</div>
                            <div class="sig-subtext">Date: ____/____/20____</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
    </div>

</body>
</html>