<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use App\Models\Quotation;
use App\Models\PlacingSlip;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Client;          // Replace with your real Client model
use App\Models\PotentialClient; // Replace with your real PotentialClient model
use App\Models\Insurer;         // Replace with your real Insurer model
use App\Models\Policy;      // Replace with your real Policy model
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class QuotationController extends Controller
{
    /**
     * Display the registration forms based on action parameter.
     */
    public function index(Request $request)
    {
        $action = $request->query('action', 'register_quote');

        // Page metadata settings
        $actions = [
            'register_quote'            => 'Register Quote',
            'register_potential_client' => 'Register Potential Client',
        ];
        
        $pageTitle = $actions[$action] ?? 'Register';

        // Fetch User and underlying details 
        $user = Auth::user();

        // 1. Data lists needed for Quote Form
        $insurerNames = Insurer::pluck('insurer_name')->toArray();
        $policyTypes  = Policy::select('policy_name', 'scope_of_cover_policy')->get();

        // Pull and clean unique list of clients (Potential & Active)
        $potentialNames = PotentialClient::pluck('client_name')->toArray();
        $existingNames  = Client::pluck('client_name')->toArray();
        
        $allClients = collect(array_merge($potentialNames, $existingNames))
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->toArray();

        // 2. Clone / Template Logic
        $cloneData = null;

        if ($request->filled('clone_id')) {
            // Option A: Direct Eloquent query (Recommended for clear visibility)
            $cloneData = PlacingSlip::find($request->query('clone_id'));
            
            // Option B: Or scoped specifically to the logged-in user if agents can only clone their own slips
            // $cloneData = PlacingSlip::where('user_id', auth()->id())->find($request->query('clone_id'));
        }

        // 3. Build Lookup JS Tables
        $policyLookup = $policyTypes->pluck('scope_of_cover_policy', 'policy_name')->toArray();
        
        // Build Raw Client Data lookups
        // Merging collections or selecting from unified view
        $rawClients = Client::select('client_name', 'nature_of_business', 'physical_address')->get();
        $clientLookup = [];
        foreach ($rawClients as $row) {
            $name = trim($row->client_name ?? 'Unknown');
            if ($name !== 'Unknown') {
                $clientLookup[$name] = [
                    'nature'  => $row->nature_of_business ?? '',
                    'address' => $row->physical_address ?? ''
                ];
            }
        }

        return view('insurance_broking.quotations.create', compact(
            'action', 
            'pageTitle', 
            'allClients', 
            'insurerNames', 
            'policyTypes', 
            'cloneData', 
            'policyLookup', 
            'clientLookup'
        ));
    }

//     /**
//      * Handle the Quote submission
//      */
//    public function storeQuote(Request $request)
// {
//     // 1. Comprehensive Validation matching all visible Blade Form inputs
//     $validated = $request->validate([
//         // Section 1: Client & Basic Policy Info
//         'insured'             => 'required|string|max:255',
//         'insurer'             => 'required|string|max:255',
//         'insurance_policy'    => 'required|string|max:255',
//         'location_of_risk'    => 'required|string|max:255',
//         'policy_start_date'   => 'required|date',
//         'policy_expiry_date'  => 'required|date|after_or_equal:policy_start_date',
//         'principal_address'   => 'required|string|max:500',
//         'nature_of_business'  => 'nullable|string',

//         // Section 2: Premium Calculations
//         'policy_currency'     => 'required|string|in:ZMW,USD',
//         'total_sum_insured'   => 'required|numeric|min:0',
//         'basic_rate'          => 'required|numeric|min:0',
//         'basic_premium'       => 'required|numeric|min:0',
//         'discount_rate'       => 'nullable|numeric|min:0|max:100',

//         // Section 4: Commission & Broker Settlement
//         'commission_rate'     => 'required|numeric|min:0|max:100',
//         'payment_made'        => 'required|numeric|min:0',
//         'payment_method'      => 'required|string|in:Upfront,2 Instalments,3 Instalments,4 Instalments',

//         // Section 5: Coverage Details & Legal Clauses
//         'scope_of_cover'      => 'required|string',
//         'property_insured'    => 'required|string',
//         'extensions'          => 'nullable|string', // Plain text notes box from form
//         'excess_deductible'   => 'required|string',
//         'cancellation_clause' => 'required|string',
//         'placing_slip_clause' => 'required|string',
//         'specific_warranties' => 'required|string',
//         'specific_conditions' => 'required|string',

//         // Optional Section 3: Dynamic Checkbox Extensions Array (if implemented)
//         'dynamic_extensions'  => 'nullable|array',
//     ]);


//     // 2. Append the authenticated user ID to the validated array
//     $validated['user'] = auth()->user()->id;

//     // 2. Strict Server-Side Financial Safety Calculations
//     $basicPremium = (float) $validated['basic_premium'];
//     $discountRate = (float) ($validated['discount_rate'] ?? 0);
    
//     // Calculate Base Discounted Premium
//     $discountAmount = ($discountRate / 100) * $basicPremium;
//     $netBasePremium = $basicPremium - $discountAmount;

//     // Sum up dynamic checked checkbox loaded premiums if present
//     $extensionLoadingTotal = 0;
//     if ($request->has('dynamic_extensions')) {
//         foreach ($request->input('dynamic_extensions') as $ext) {
//             if (isset($ext['included']) || !empty($ext['premium'])) {
//                 $extensionLoadingTotal += (float) ($ext['premium'] ?? 0);
//             }
//         }
//     }

//     // Complete Taxable Premium Base
//     $totalNetPremium = $netBasePremium + $extensionLoadingTotal;
    
//     // Zambian Statutory PIA Premium Levy (5%)
//     $premiumLevyAmount = $totalNetPremium * 0.05;
//     $grossPremiumTotal = $totalNetPremium + $premiumLevyAmount;

//     // Broker Commission calculations
//     $commissionRate = (float) $validated['commission_rate'];
//     $commissionAmount = ($commissionRate / 100) * $totalNetPremium;
    
//     // Final corporate remittance owed back to risk carrier underwriting panel
//     $insurerNetRemittance = $totalNetPremium - $commissionAmount;

//     // 3. Database Transaction Persistence Strategy
//     DB::beginTransaction();
//     try {
//         // Merge derived calculated values safely into model save profile array
//         $quotationData = array_merge($validated, [
//             'discount_amount'    => $discountAmount,
//             'premium_levy'       => $premiumLevyAmount,
//             'gross_premium'      => $grossPremiumTotal,
//             'commission_amount'  => $commissionAmount,
//             'insurer_premium'    => $insurerNetRemittance,
//             'user_id'            => auth()->id(), // Associate recording account session audit trace
//         ]);

//         // Create main quote record profile entry row
//         // $quote = Quotation::create($quotationData);

//         // Optional: Save separate related dynamic checkboxes breakdown rows if active
//         // if ($request->has('dynamic_extensions') && isset($quote)) {
//         //     foreach ($request->input('dynamic_extensions') as $ext) {
//         //         if (isset($ext['included'])) {
//         //             $quote->extensions()->create([
//         //                 'extension_name'     => $ext['name'],
//         //                 'additional_premium' => (float) ($ext['premium'] ?? 0),
//         //             ]);
//         //         }
//         //     }
//         // }

//             // Create the record row entry
//             \App\Models\Quotation::create($quotationData); 

//             DB::commit();
//             return redirect()->back()->with('msg', 'Quotation registration completed successfully.');

//         } catch (\Exception $e) {
//             DB::rollBack();
            
//             return redirect()->back()
//                 ->withInput()
//                 ->withErrors(['error' => 'System storage failed to process transaction: ' . $e->getMessage()]);
//         }
// }


/**
     * Handle the Quote submission
     */
  /**
     * Handle the Quote submission
     */
   /**
     * Handle the Quote submission with strict field restrictions
     */
    public function storeQuote(Request $request)
    {
        // 1. Comprehensive Validation matching all visible Blade Form inputs
        $validated = $request->validate([
            // Section 1: Client & Basic Policy Info
            'insured'                 => 'required|string|max:255',
            'insurer'                 => 'required|string|max:255',
            'insurance_policy'        => 'required|string|max:255',
            'location_of_risk'        => 'required|string|max:255',
            'policy_start_date'       => 'required|date',
            'policy_expiry_date'      => 'required|date|after_or_equal:policy_start_date',
            'principal_address'       => 'required|string|max:500',
            'nature_of_business'      => 'nullable|string',

            // Section 2: Premium Calculations
            'policy_currency'         => 'required|string|in:ZMW,USD',
            'total_sum_insured'       => 'required|numeric|min:0',
            'basic_rate'              => 'required|numeric|min:0',
            'basic_premium'           => 'required|numeric|min:0',
            'discount_rate'           => 'nullable|numeric|min:0|max:100',
            'expected_annual_premium' => 'nullable|numeric|min:0',

            // Section 4: Commission & Broker Settlement
            'commission_rate'         => 'required|numeric|min:0|max:100',
            'payment_made'            => 'required|numeric|min:0',
            'payment_method'          => 'required|string|in:Upfront,2 Instalments,3 Instalments,4 Instalments',

            // Section 5: Coverage Details & Legal Clauses
            'scope_of_cover'          => 'required|string',
            'property_insured'        => 'required|string',
            'extensions'              => 'nullable|string', 
            'excess_deductible'       => 'required|string',
            'cancellation_clause'     => 'required|string',
            'placing_slip_clause'     => 'required|string',
            'specific_warranties'     => 'required|string',
            'specific_conditions'     => 'required|string',

            // Optional Section 3: Dynamic Checkbox Extensions Array
            'dynamic_extensions'      => 'nullable|array',
        ]);

        // 2. Strict Server-Side Financial Safety Calculations
        $basicPremium = (float) $validated['basic_premium'];
        $discountRate = (float) ($validated['discount_rate'] ?? 0);
        
        // Calculate Base Discounted Premium
        $discountAmount = ($discountRate / 100) * $basicPremium;
        $netBasePremium = $basicPremium - $discountAmount;

        // Sum up dynamic checked checkbox loaded premiums if present
        $extensionLoadingTotal = 0;
        if ($request->has('dynamic_extensions')) {
            foreach ($request->input('dynamic_extensions') as $ext) {
                if (isset($ext['included']) || !empty($ext['premium'])) {
                    $extensionLoadingTotal += (float) ($ext['premium'] ?? 0);
                }
            }
        }

        // Complete Taxable Premium Base
        $totalNetPremium = $netBasePremium + $extensionLoadingTotal;
        
        // Zambian Statutory PIA Premium Levy (5%)
        $premiumLevyAmount = $totalNetPremium * 0.05;
        $grossPremiumTotal = $totalNetPremium + $premiumLevyAmount;

        // Broker Commission calculations
        $commissionRate = (float) $validated['commission_rate'];
        $commissionAmount = ($commissionRate / 100) * $totalNetPremium;
        
        // Final corporate remittance owed back to risk carrier underwriting panel
        $insurerNetRemittance = $totalNetPremium - $commissionAmount;

        // 3. Define the strict explicit whitelist allowed in the database
        $allowedFields = [
            'insured', 'nature_of_business', 'principal_address', 
            'policy_start_date', 'policy_expiry_date', 'insurer', 
            'cancellation_clause', 'insurance_policy', 
            'scope_of_cover', 'extensions', 'excess_deductible', 
            'property_insured', 'location_of_risk', 'specific_warranties', 
            'specific_conditions', 'policy_currency', 'total_sum_insured', 
            'basic_rate', 'basic_premium', 'discount_rate', 'discount', 
            'premium_levy_rate', 'premium_levy', 'gross_premium', 
            'commission_rate', 'commission_amount', 'payment_method', 'user'
        ];

        // 4. Merge validation data with calculated variables
        $allData = array_merge($validated, [
            'user'              => auth()->id(),
            'discount'          => $discountAmount,
            'premium_levy_rate' => 5.0000,
            'premium_levy'      => $premiumLevyAmount,
            'gross_premium'     => $grossPremiumTotal,
            'commission_amount' => $commissionAmount,
            'insurer_premium'   => $insurerNetRemittance,
        ]);

        // 5. Intersect keys to filter out everything EXCEPT your exact whitelisted fields
        $filteredQuotationData = array_intersect_key($allData, array_flip($allowedFields));

        // 6. Force append system tracking fields if required (e.g. user_id tracking)
        $filteredQuotationData['user_id'] = auth()->id();

        // 7. Database Transaction Persistence Strategy
        \DB::beginTransaction();
        try {
            // Create the record row entry using only the strictly filtered dataset
            \App\Models\Quotation::create($filteredQuotationData); 

            \DB::commit();
            return redirect()->back()->with('msg', 'Quotation registration completed successfully.');

        } catch (\Exception $e) {
            \DB::rollBack();
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'System storage failed to process transaction: ' . $e->getMessage()]);
        }
    }


    
    /**
     * Handle Potential Client submission
     */
    // public function storePotentialClient(Request $request)
    // {
    //     $validated = $request->validate([
    //         'user' => Auth::user()->name ?? auth()->user()->name ?? 'System Process',
    //         'client_name'        => 'required|string|max:255',
    //         'client_type'        => 'required|in:Individual,Corporate',
    //         'nature_of_business' => 'required|string',
    //         'physical_address'   => 'required|string',
    //         'postal_address'     => 'required|string',
    //         'contact_number'     => 'required|string',
    //         'email_address'      => 'required|email',
    //     ]);

    //     // Save potential client logic
    //     PotentialClient::create($validated);

    //     return redirect()->back()->with('msg', 'Potential Client registered successfully!');
    // }

    public function storePotentialClient(Request $request)
{
    // 1. Validate ONLY fields coming from the user's form inputs
    $validated = $request->validate([
        'client_name'        => 'required|string|max:255',
        'client_type'        => 'required|in:Individual,Corporate',
        'nature_of_business' => 'required|string',
        'physical_address'   => 'required|string',
        'postal_address'     => 'required|string',
        'contact_number'     => 'required|string',
        'email_address'      => 'required|email',
    ]);

    // 2. Inject the backend authenticated user session name into the save array
    PotentialClient::create(array_merge($validated, [
        'user' => Auth::user()->name ?? auth()->user()->name ?? 'System Process'
    ]));

    // 3. Clear the cache and redirect back with your global confirmation flag
    return redirect()->back()->with('msg', 'Potential Client registered successfully!');
}

    // QUOTATION SCHEDULE
    /**
     * Display the isolated insurance broking quotation registry.
     */
    public function quotationRegistry()
    {

        $pageTitle = 'Quotation Registry';

        // Fetches data cleanly from the 'register_quote' table with its user relationship
        $quotations = Quotation::with('user')->latest()->paginate(15);

        return view('insurance_broking.quotations.list', compact('quotations', 'pageTitle'));
    }

    // SHOW QUOTATION
    public function showQuote($id)
    {
        $pageTitle = 'View Quotation';

        // Fetch the quotation with its related user if configured
        $quotation = Quotation::findOrFail($id);

        return view('insurance_broking.quotations.show', compact('quotation', 'pageTitle'));
    }

    /**
     * Display the quotation edit matrix form layout.
     */
    public function editQuote(string $id): View
    {
        $pageTitle = 'Edit Quotation';

        $quotation = Quotation::findOrFail($id);
        
        return view('insurance_broking.quotations.edit', compact('quotation', 'pageTitle'));
    }

    /**
     * Process and commit premium variations to the database storage.
     */
    // public function updateQuote(Request $request, string $id): RedirectResponse
    // {
    //     $quotation = Quotation::findOrFail($id);

    //     // Comprehensive validation matching your view framework requirements
    //     $validatedData = $request->validate([
    //         // Section 1 Parameters
    //         'insured'              => 'required|string|max:255',
    //         'insurer'              => 'required|string|max:255',
    //         'principal_address'    => 'nullable|string',
    //         'nature_of_business'   => 'nullable|string',
    //         'policy_start_date'    => 'nullable|date',
    //         'policy_expiry_date'   => 'nullable|date|after_or_equal:policy_start_date',

    //         // Section 2 Parameters
    //         'insurance_policy'     => 'nullable|string',
    //         'location_of_risk'     => 'nullable|string',
    //         'property_insured'     => 'nullable|string',
    //         'scope_of_cover'       => 'nullable|string',
    //         'extensions'           => 'nullable|string',
    //         'excess_deductible'    => 'nullable|string',
    //         'specific_warranties'  => 'nullable|string',
    //         'specific_conditions'  => 'nullable|string',
    //         'cancellation_clause'  => 'nullable|string',
    //         'placing_slip_clause'  => 'nullable|string',

    //         // Section 3 Premium & Ledger Metrics
    //         'policy_currency'      => 'required|string|max:10',
    //         'total_sum_insured'    => 'nullable|numeric|min:0',
    //         'basic_rate'           => 'nullable|numeric|min:0|max:100',
    //         'basic_premium'        => 'nullable|numeric|min:0',
    //         'discount_rate'        => 'nullable|numeric|min:0|max:100',
    //         'discount'             => 'nullable|numeric|min:0',
    //         'premium_levy_rate'    => 'nullable|numeric|min:0|max:100',
    //         'premium_levy'         => 'nullable|numeric|min:0',
    //         'gross_premium'        => 'required|numeric|min:0',
    //         'commission_rate'      => 'nullable|numeric|min:0|max:100',
    //         'commission_amount'    => 'nullable|numeric|min:0',
    //         'insurer_premium'      => 'required|numeric|min:0',

    //         // Section 4 Settlement Setup
    //         'payment_method'       => 'nullable|string',
    //         'payment_made'         => 'nullable|numeric|min:0',
    //     ]);

    //     // Persist database changes smoothly
    //     $quotation->update($validatedData);

    //     // Bounce back to standard registry stream with confirmation payload
    //     return redirect()
    //         ->route('insurance_broking.quotations.list')
    //         ->with('success', "Quotation entry #{$quotation->id} updated successfully.");
    // }

    public function updateQuote(Request $request, string $id): RedirectResponse
{
    $quotation = Quotation::findOrFail($id);

    $allowedFields = [
        'insured', 'nature_of_business', 'principal_address', 
        'policy_start_date', 'policy_expiry_date', 'insurer', 
        'cancellation_clause', 'insurance_policy', 
        'scope_of_cover', 'extensions', 'excess_deductible', 
        'property_insured', 'location_of_risk', 'specific_warranties', 
        'specific_conditions', 'policy_currency', 'total_sum_insured', 
        'basic_rate', 'basic_premium', 'discount_rate', 'discount', 
        'premium_levy_rate', 'premium_levy', 'gross_premium', 
        'commission_rate', 'commission_amount', 'payment_method', 'user'
    ];

    // Comprehensive validation matching your permitted fields list
    $validatedData = $request->validate([
        // Section 1 Parameters
        'insured'              => 'required|string|max:255',
        'insurer'              => 'required|string|max:255',
        'principal_address'    => 'nullable|string',
        'nature_of_business'   => 'nullable|string',
        'policy_start_date'    => 'nullable|date',
        'policy_expiry_date'   => 'nullable|date|after_or_equal:policy_start_date',

        // Section 2 Parameters
        'insurance_policy'     => 'nullable|string',
        'location_of_risk'     => 'nullable|string',
        'property_insured'     => 'nullable|string',
        'scope_of_cover'       => 'nullable|string',
        'extensions'           => 'nullable|string',
        'excess_deductible'    => 'nullable|string',
        'specific_warranties'  => 'nullable|string',
        'specific_conditions'  => 'nullable|string',
        'cancellation_clause'  => 'nullable|string',

        // Section 3 Premium & Ledger Metrics
        'policy_currency'      => 'required|string|max:10',
        'total_sum_insured'    => 'nullable|numeric|min:0',
        'basic_rate'           => 'nullable|numeric|min:0|max:100',
        'basic_premium'        => 'nullable|numeric|min:0',
        'discount_rate'        => 'nullable|numeric|min:0|max:100',
        'discount'             => 'nullable|numeric|min:0',
        'premium_levy_rate'    => 'nullable|numeric|min:0|max:100',
        'premium_levy'         => 'nullable|numeric|min:0',
        'gross_premium'        => 'required|numeric|min:0',
        'commission_rate'      => 'nullable|numeric|min:0|max:100',
        'commission_amount'    => 'nullable|numeric|min:0',

        // Section 4 Settlement Setup & Context
        'payment_method'       => 'nullable|string',
        'user'                 => 'nullable|string|max:255', // Added to match allowed fields
    ]);

    // Explicitly filter to only allow the predefined fields array
    $filteredData = array_intersect_key($validatedData, array_flip($allowedFields));

    // Persist database changes smoothly
    $quotation->update($filteredData);

    // Bounce back to standard registry stream with confirmation payload
    return redirect()
        ->route('insurance_broking.quotations.list')
        ->with('success', "Quotation entry #{$quotation->id} updated successfully.");
}



    /**
 * Generate and stream/download a formal PDF document containing filtered metrics.
 */
// public function downloadPdf(string $id)
// {
//     $quotation = Quotation::findOrFail($id);

//     // Load view and pass the quotation record
//     $pdf = Pdf::loadView('insurance_broking.quotations.pdf', compact('quotation'));
    
//     // Set format to standard A4 portrait
//     $pdf->setPaper('a4', 'portrait');

//     // Return the PDF directly streamable in the browser with a clean title
//     return $pdf->stream("Quotation_#{$quotation->id}_Summary.pdf");
// }

public function downloadPdf(string $id)
{
    // 1. Fetch the data or fail with a 404 cleanly
    $quotation = Quotation::findOrFail($id);

    // 2. Multi-Tenant Dynamic Logo & Header Resolution
    $tenantId = tenant('id'); 
    $adminUser = \App\Models\User::where('role', 'admin')->first();

    $companyName = $adminUser->company ?? 'RIB Insurance Brokers';
    $address     = $adminUser->physical_address ?? '5833 Mwange Close, Lusaka';
    $phone       = $adminUser->tel_number ?? '+26 (0) 777 780 882';
    $email       = $adminUser->email ?? 'services@rib.co.zm';

    // 3. Resolve the physical image asset path
    $logoPath = storage_path("app/public/tenants/{$tenantId}/logo.jpg");
    if (!file_exists($logoPath)) {
        $logoPath = public_path('img/rib_logo.jpg');
    }

    // 4. Convert branding asset stream into an inline base64 data URL string
    $logoUrl = '';
    if (file_exists($logoPath) && is_file($logoPath)) {
        $logoData = base64_encode(file_get_contents($logoPath));
        $mimeType = @mime_content_type($logoPath) ?: 'image/jpeg'; 
        $logoUrl  = 'data:' . $mimeType . ';base64,' . trim($logoData);
    }

    // 5. Build the text string context for the header's QR code
    $qrRawText = "RIB QUOTE REF: " . $quotation->id . 
                " | Insured: " . $quotation->insured . 
                " | Premium: " . $quotation->policy_currency . " " . number_format($quotation->gross_premium, 2);

    // =================================================================
    // NATIVE SECURE QR CODE GENERATION (100% OFFLINE VIA BACONQRCODE)
    // =================================================================
    try {
        // Instantiate the native renderer to compile high-quality vector paths
        $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
        $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150, 1);
        $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $renderer));
        
        // Generate a real, 100% scannable vector SVG string natively offline
        $realSvgData = $writer->writeString($qrRawText);
        $qrString = 'data:image/svg+xml;base64,' . base64_encode($realSvgData);
    } catch (\Exception $e) {
        // Safe network fallback ONLY if local rendering engine hits an unexpected environment issue
        $apiBackupUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrRawText);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiBackupUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $qrBytes = curl_exec($ch);
        curl_close($ch);

        if (!empty($qrBytes)) {
            $qrString = 'data:image/png;base64,' . base64_encode($qrBytes);
        } else {
            $qrString = ''; // Clear out cleanly if completely blocked by firewalls
        }
    }

    // 6. NOW load the view and compact ALL required variables for the header template
    $pdf = app('dompdf.wrapper')->loadView('insurance_broking.quotations.pdf', compact(
        'quotation',
        'companyName',
        'address',
        'phone',
        'email',
        'logoUrl',
        'qrString' // Contains the base64-encoded offline vector image
    ));
    
    // 7. Establish layout rules and stream back to the browser sandbox window
    $pdf->setPaper('a4', 'portrait')
        ->setWarnings(false)
        ->setOption([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => true
        ]);

    return $pdf->stream("Quotation_#{$quotation->id}_Summary.pdf");
}

    


}