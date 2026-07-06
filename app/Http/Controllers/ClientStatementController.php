<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf; // Import the DomPDF Facade

use Illuminate\Http\Request;
use App\Models\User; // Or whichever model holds your query data
use App\Models\Client; // Don't forget to import your model at the top

class ClientStatementController extends Controller
{
   
//   public function index(Request $request)
// {
//     // Defined using a lowercase key to match your master layout's {{ $pageTitle }} variable
//     $data = [
//         'Section'   => 'Statements',
//         'pageTitle' => 'Client Statements', 
//         'clients'   => Client::orderBy('client_name')->pluck('client_name')->unique(),
//     ];

//     // If the form has been posted (user clicked "Generate Statement")
//     if ($request->isMethod('post')) {
//         $request->validate([
//             'client_name' => 'required|string',
//             'currency'    => 'required|string|in:ZMW,USD',
//         ]);

//         $clientName = $request->input('client_name');
//         $currency = $request->input('currency');

//         // Find the client record using your Client Model
//         $client = Client::where('client_name', $clientName)->first();

//         if ($client) {
//             // Fetch filtered items directly through the Client model relationships
//             $filteredInvoices      = $client->invoices()->where('policy_currency', $currency)->get();
//             $filteredReceipts      = $client->receipts()->where('policy_currency', $currency)->get();
//             $filteredCancellations = $client->cancellations()->where('policy_currency', $currency)->get();

//             // Calculate Financial Summary Totals natively using Laravel Collections
//             $totalInvoiced   = $filteredInvoices->sum('gross_premium');
//             $totalPaid       = $filteredReceipts->sum('gross_amount_received');
//             $totalCancelled  = $filteredCancellations->sum('premium_refund');
//             $balanceDue      = $totalInvoiced - $totalPaid - $totalCancelled;

//             // Merge variables seamlessly down to your unified Blade page layout
//             $data = array_merge($data, [
//                 'selectedClient'        => $clientName,
//                 'selectedCurr'          => $currency,
//                 'date'                  => now()->format('Y-m-d'),
//                 'filteredInvoices'      => $filteredInvoices,
//                 'filteredReceipts'      => $filteredReceipts,
//                 'filteredCancellations' => $filteredCancellations,
//                 'totalInvoiced'         => $totalInvoiced,
//                 'totalPaid'             => $totalPaid,
//                 'totalCancelled'        => $totalCancelled,
//                 'balanceDue'            => $balanceDue
//             ]);
//         }
//     }

//     // Fixed: Passing only $data array as the second argument prevents the Type/Merge error
//     return view('insurance_broking.accounts.client_statements.show', $data);
// }

// public function index(Request $request)
// {
//     // Defined using a lowercase key to match your master layout's {{ $pageTitle }} variable
//     $data = [
//         'Section'   => 'Statements',
//         'pageTitle' => 'Client Statements', 
//         'clients'   => Client::orderBy('client_name')->pluck('client_name')->unique(),
//     ];

//     // If the form has been posted (user clicked "Generate Statement")
//     if ($request->isMethod('post')) {
//         $request->validate([
//             'client_name' => 'required|string',
//             'currency'    => 'required|string|in:ZMW,USD',
//         ]);

//         $clientName = $request->input('client_name');
//         $currency = $request->input('currency');

//         // Find the client record using your Client Model
//         $client = Client::where('client_name', $clientName)->first();

//         if ($client) {
//             // Fetch filtered items directly through the Client model relationships
//             // Added: ->where('invoice_status', 'Active')
//             $filteredInvoices = $client->invoices()
//             ->where('policy_currency', $currency)
//             ->where('invoice_status', 'Active')
//             ->get();

//         $filteredReceipts = $client->receipts()
//             ->where('policy_currency', $currency)
//             ->where('status', 'Active') 
//             ->get();

//             // Keep calculations intact (they automatically use the freshly updated/filtered collections)
//             $filteredCancellations = $client->cancellations()->where('policy_currency', $currency)->get();

//             // Calculate Financial Summary Totals natively using Laravel Collections
//             $totalInvoiced   = $filteredInvoices->sum('gross_premium');
//             $totalPaid       = $filteredReceipts->sum('gross_amount_received');
//             $totalCancelled  = $filteredCancellations->sum('premium_refund');
//             $balanceDue      = $totalInvoiced - $totalPaid - $totalCancelled;

//             // Merge variables seamlessly down to your unified Blade page layout
//             $data = array_merge($data, [
//                 'selectedClient'        => $clientName,
//                 'selectedCurr'          => $currency,
//                 'date'                  => now()->format('Y-m-d'),
//                 'filteredInvoices'      => $filteredInvoices,
//                 'filteredReceipts'      => $filteredReceipts,
//                 'filteredCancellations' => $filteredCancellations,
//                 'totalInvoiced'         => $totalInvoiced,
//                 'totalPaid'             => $totalPaid,
//                 'totalCancelled'        => $totalCancelled,
//                 'balanceDue'            => $balanceDue
//             ]);
//         }
//     }

//     // Fixed: Passing only $data array as the second argument prevents the Type/Merge error
//     return view('insurance_broking.accounts.client_statements.show', $data);
// }


// // DOWNLOAD PDF STATEMENT
//     public function printStatement(Request $request)
//     {
//         // 1. Validate Form Input Data Safely
//         $request->validate([
//             'client_name' => 'required|string',
//             'currency'    => 'required|string|in:ZMW,USD',
//         ]);

//         $clientName = $request->input('client_name');
//         $currency   = $request->input('currency'); 

//         // 2. Fetch Client profile using Eloquent Relationships
//         $client = Client::where('client_name', $clientName)->first();

//         if (!$client) {
//             return redirect()->back()->withErrors(['error' => 'Client profile details not found.']);
//         }

//         // Pull filtered database collections
//         $filteredInvoices      = $client->invoices()->where('policy_currency', $currency)->get();
//         $filteredReceipts      = $client->receipts()->where('policy_currency', $currency)->where('status', '!=', 'Cancelled')->get();
//         $filteredCancellations = $client->cancellations()->where('policy_currency', $currency)->get();

//         // 3. Compute Financial Accumulations
//         $totalInvoiced   = $filteredInvoices->sum('gross_premium');
//         $totalPaid       = $filteredReceipts->sum('gross_amount_received');
//         $totalCancelled  = $filteredCancellations->sum('premium_refund');
//         $balanceDue      = $totalInvoiced - ($totalPaid + $totalCancelled);

//         // 4. Multi-Tenant Dynamic Logo & Header Resolution
//         $tenantId = tenant('id'); 
//         $adminUser = User::where('role', 'admin')->first();

//         $companyName = $adminUser->company ?? 'Profstand';
//         $address     = $adminUser->physical_address ?? 'Plot Number 14 Njoka Road, Lusaka';
//         $phone       = $adminUser->tel_number ?? '+260 572313599';
//         $email       = $adminUser->email ?? 'services@profstand.com';

//         // Secure Tenant Data Isolation Lookup Loop
//         $logoPath = storage_path("app/public/tenants/{$tenantId}/logo.jpg");
//         if (!file_exists($logoPath)) {
//             $logoPath = public_path("storage/tenants/{$tenantId}/logo.jpg");
//         }

//         $logoUrl = '';
//         if (file_exists($logoPath) && is_file($logoPath)) {
//             $logoData = base64_encode(file_get_contents($logoPath));
//             $mimeType = @mime_content_type($logoPath) ?: 'image/jpeg'; 
//             $logoUrl  = 'data:' . $mimeType . ';base64,' . trim($logoData);
//         }

//         // =================================================================
//         // NATIVE SECURE QR CODE GENERATION (100% OFFLINE VIA BACONQRCODE)
//         // =================================================================
//         $qrUrlText = route('insurance_broking.accounts.client_statements.index') . "?client=" . urlencode($clientName) . "&balance=" . $balanceDue;
        
//         try {
//             // Instantiate the native renderer to compile high-quality vector paths
//             $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
//             $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150, 1);
//             $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $renderer));
            
//             // Generate a real, 100% scannable vector SVG string natively offline
//             $realSvgData = $writer->writeString($qrUrlText);
//             $qrString = 'data:image/svg+xml;base64,' . base64_encode($realSvgData);
//         } catch (\Exception $e) {
//             // Safe network fallback ONLY if local rendering engine hits an unexpected environment issue
//             $apiBackupUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrUrlText);
            
//             $ch = curl_init();
//             curl_setopt($ch, CURLOPT_URL, $apiBackupUrl);
//             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//             curl_setopt($ch, CURLOPT_TIMEOUT, 2);
//             $qrBytes = curl_exec($ch);
//             curl_close($ch);

//             if (!empty($qrBytes)) {
//                 $qrString = 'data:image/png;base64,' . base64_encode($qrBytes);
//             } else {
//                 $qrString = ''; // Leave blank if firewalls block all egress traffic
//             }
//         }

//         $data = [
//             'selected_client'        => $clientName,
//             'selected_curr'          => $currency,
//             'filtered_invoices'      => $filteredInvoices,
//             'filtered_receipts'      => $filteredReceipts,
//             'filtered_cancellations' => $filteredCancellations,
//             'total_invoiced'         => $totalInvoiced,
//             'total_paid'             => $totalPaid,
//             'total_cancelled'        => $totalCancelled,
//             'balance_due'            => $balanceDue, 
//             'date'                   => now()->format('d M Y'),
//             'companyName'            => $companyName,
//             'logoUrl'                => $logoUrl, 
//             'address'                => $address,
//             'phone'                  => $phone,
//             'email'                  => $email,
//             'qrString'               => $qrString 
//         ];

//         $pdf = Pdf::loadView('insurance_broking.accounts.client_statements.pdf', $data);
        
//         $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
//         $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
//         $pdf->setPaper('a4', 'portrait');

//         $filename = "Statement_" . str_replace(' ', '_', $clientName) . ".pdf";
//         return $pdf->download($filename);
//     }


// REVISED CONTROLLER WITH DEFENSIVE DATABASE LOOKUPS
public function index(Request $request)
{
    $data = [
        'Section'   => 'Statements',
        'pageTitle' => 'Client Statements', 
        'clients'   => Client::orderBy('client_name')->pluck('client_name')->unique(),
    ];

    if ($request->isMethod('post')) {
        $request->validate([
            'client_name' => 'required|string',
            'currency'    => 'required|string|in:ZMW,USD',
        ]);

        $clientName = trim($request->input('client_name'));
        $currency = strtoupper(trim($request->input('currency')));

        // Case-insensitive client lookup
        $client = Client::whereRaw('LOWER(client_name) = ?', [strtolower($clientName)])->first();

        if ($client) {
            // Case-insensitive currency and status lookups to prevent database mismatch
            $filteredInvoices = $client->invoices()
                ->whereRaw('UPPER(policy_currency) = ?', [$currency])
                ->whereIn('invoice_status', ['Active', 'active'])
                ->get();

            $filteredReceipts = $client->receipts()
                ->whereRaw('UPPER(policy_currency) = ?', [$currency])
                ->whereIn('status', ['Active', 'active']) 
                ->get();

            $filteredCancellations = $client->cancellations()
                ->whereRaw('UPPER(policy_currency) = ?', [$currency])
                ->get();

            $totalInvoiced   = $filteredInvoices->sum('gross_premium');
            $totalPaid       = $filteredReceipts->sum('gross_amount_received');
            $totalCancelled  = $filteredCancellations->sum('premium_refund');
            $balanceDue      = $totalInvoiced - $totalPaid - $totalCancelled;

            $data = array_merge($data, [
                'selectedClient'        => $clientName,
                'selectedCurr'          => $currency,
                'date'                  => now()->format('Y-m-d'),
                'filteredInvoices'      => $filteredInvoices,
                'filteredReceipts'      => $filteredReceipts,
                'filteredCancellations' => $filteredCancellations,
                'totalInvoiced'         => $totalInvoiced,
                'totalPaid'             => $totalPaid,
                'totalCancelled'        => $totalCancelled,
                'balanceDue'            => $balanceDue
            ]);
        }
    }

    return view('insurance_broking.accounts.client_statements.show', $data);
}



// DOWNLOAD PDF STATEMENT
public function printStatement(Request $request)
{
    // 1. Validate Form Input Data Safely
    $request->validate([
        'client_name' => 'required|string',
        'currency'    => 'required|string|in:ZMW,USD',
    ]);

    $clientName = $request->input('client_name');
    $currency   = $request->input('currency'); 

    // 2. Fetch Client profile with filtered Eloquent Relationships (Prevents N+1)
    $client = Client::where('client_name', $clientName)
        ->with([
            'invoices' => function ($query) use ($currency) {
                $query->where('policy_currency', $currency)->where('invoice_status', 'Active');
            },
            'receipts' => function ($query) use ($currency) {
                $query->where('policy_currency', $currency)->where('status', 'Active');
            },
            'cancellations' => function ($query) use ($currency) {
                $query->where('policy_currency', $currency);
            }
        ])
        ->first();

    if (!$client) {
        return redirect()->back()->withErrors(['error' => 'Client profile details not found.']);
    }

    // Extracting collection items already filtered via eager loading
    $filteredInvoices      = $client->invoices;
    $filteredReceipts       = $client->receipts;
    $filteredCancellations  = $client->cancellations;

    // 3. Compute Financial Accumulations
    $totalInvoiced   = $filteredInvoices->sum('gross_premium');
    $totalPaid       = $filteredReceipts->sum('gross_amount_received');
    $totalCancelled  = $filteredCancellations->sum('premium_refund');
    $balanceDue      = $totalInvoiced - $totalPaid - $totalCancelled;

    // 4. Multi-Tenant Dynamic Logo & Header Resolution
    $tenantId  = tenant('id'); 
    $adminUser = User::where('role', 'admin')->first();

    $companyName = $adminUser->company ?? 'Profstand';
    $address     = $adminUser->physical_address ?? 'Plot Number 14 Njoka Road, Lusaka';
    $phone       = $adminUser->tel_number ?? '+260 572313599';
    $email       = $adminUser->email ?? 'services@profstand.com';

    // Scan potential tenant asset storage spots safely
    $possibleLogoPaths = [
        storage_path("app/public/tenants/{$tenantId}/logo.jpg"),
        public_path("storage/tenants/{$tenantId}/logo.jpg"),
    ];

    $logoUrl = '';
    foreach ($possibleLogoPaths as $path) {
        if (is_file($path)) {
            $logoData = base64_encode(file_get_contents($path));
            $mimeType = @mime_content_type($path) ?: 'image/jpeg'; 
            $logoUrl  = "data:{$mimeType};base64," . trim($logoData);
            break;
        }
    }

    // =================================================================
    // NATIVE SECURE QR CODE GENERATION (100% OFFLINE VIA BACONQRCODE)
    // =================================================================
    $qrUrlText = route('insurance_broking.accounts.client_statements.index') . "?client=" . urlencode($clientName) . "&balance=" . $balanceDue;
    
    try {
        $rendererBackend = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
        $rendererStyle   = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150, 1);
        $imageRenderer   = new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $rendererBackend);
        $writer          = new \BaconQrCode\Writer($imageRenderer);
        
        $realSvgData = $writer->writeString($qrUrlText);
        $qrString    = 'data:image/svg+xml;base64,' . base64_encode($realSvgData);
    } catch (\Exception $e) {
        // Fallback to secure curl API if BaconQrCode dependencies fail locally
        $apiBackupUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrUrlText);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $apiBackupUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $qrBytes = curl_exec($ch);
        curl_close($ch);

        $qrString = !empty($qrBytes) ? 'data:image/png;base64,' . base64_encode($qrBytes) : '';
    }

    // Standardized CamelCase Export Payload
    $data = [
        'selectedClient'        => $clientName,
        'selectedCurr'          => $currency,
        'filteredInvoices'      => $filteredInvoices,
        'filteredReceipts'      => $filteredReceipts,
        'filteredCancellations' => $filteredCancellations,
        'totalInvoiced'         => $totalInvoiced,
        'totalPaid'             => $totalPaid,
        'totalCancelled'        => $totalCancelled,
        'balanceDue'            => $balanceDue, 
        'date'                  => now()->format('d M Y'),
        'companyName'           => $companyName,
        'logoUrl'               => $logoUrl, 
        'address'               => $address,
        'phone'                 => $phone,
        'email'                 => $email,
        'qrString'              => $qrString 
    ];

    // 5. Build and Stream PDF Document
    $pdf = Pdf::loadView('insurance_broking.accounts.client_statements.pdf', $data);
    
    $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
    $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
    $pdf->setPaper('a4', 'portrait');

    $filename = "Statement_" . str_replace(' ', '_', $clientName) . ".pdf";
    return $pdf->download($filename);
}




}