<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentVoucher;
use App\Models\Insurer;
use App\Models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Voucher;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentVoucherController extends Controller
{


    /**
 * Handle voucher approval or rejection.
 * Replaces PaymentVoucher_approval_rejection()
 */
    public function updateStatus(Request $request, $id, $action)
{
    
    try {
        // 1. Find the voucher or fail with a 404 error
        $voucher = PaymentVoucher::findOrFail($id);

        // 2. Prepare update payload based on action
        $updateData = [];

        if ($action === 'approve') {
            $updateData = [
                'status'      => 'Approved',
                'approved_by' => Auth::user()->user_name ?? 'Unknown System User',
                'approved_at' => now(),
            ];
            $messageStatus = 'approved';
        } elseif ($action === 'reject') {
            $updateData = [
                'status'      => 'Rejected',
                'approved_by' => null, // Reset or leave blank on rejection
                'approved_at' => null,
                // If you have 'rejected_by' or 'rejected_at' columns, you can add them here!
            ];
            $messageStatus = 'rejected';
        } else {
            return back()->with('error', "Invalid action requested.");
        }

        // 3. Update using Eloquent
        $voucher->update($updateData);

        // 4. Redirect back with a success message (Flash Session)
        return back()->with('success', "Voucher #{$id} has been successfully {$messageStatus}.");

    } catch (\Exception $e) {
        // Log the actual error for debugging purposes
        Log::error("Voucher status update failed for ID {$id}: " . $e->getMessage());
        return back()->with('error', "Error updating record.");
    }
}



    public function index()
    {
        try {
            // Fetch all vouchers ordered by Pending first, then date
            $vouchers = PaymentVoucher::orderByRaw("CASE WHEN status = 'Pending' THEN 1 ELSE 2 END")
                ->orderBy('created_at', 'DESC')
                ->get();

            return view('finance.vouchers', [
                'vouchers' => $vouchers,
                'pendingCount' => $vouchers->where('status', 'Pending')->count()
            ]);

        } catch (\Exception $e) {
            Log::error("Failed to fetch vouchers: " . $e->getMessage());
            return back()->withErrors('Could not load voucher data.');
        }
    }


    public function store(Request $request)
    {
        // 1. Validation: Ensures data is correct before touching the DB
        $validated = $request->validate([
            'payee_name'       => 'required|string|max:255',
            'amount'           => 'required|numeric',
            'currency'         => 'required|string|max:10',
            'payment_method'   => 'required|string',
            'description'      => 'nullable|string',
            'expense_category' => 'required|string',
        ]);

        // 2. Create the record using Eloquent
        PaymentVoucher::create([
            'client_name'      => $validated['payee_name'],
            'amount'           => $validated['amount'],
            'currency'         => $validated['currency'],
            'payment_method'   => $validated['payment_method'],
            'description'      => $validated['description'],
            'expense_category' => $validated['expense_category'],
            'created_by'       => Auth::user()->name, // Replaces $_SESSION["name"]
            'status'           => 'Pending',
        ]);

        // 3. Redirect with a flash message
        return redirect()->route('vouchers.index')
            ->with('success', 'Voucher submitted and is now PENDING approval.');
    }



    public function download($id)
    {
        // 1. Fetch data using Eloquent (replacing the manual loop)
        $voucher = Voucher::findOrFail($id);

        // 2. Prepare data for the view
        $data = [
            'row' => $voucher,
            'voucherNumber' => "PV-" . str_pad($voucher->id, 5, "0", STR_PAD_LEFT),
            'formattedDate' => $voucher->created_at->format('d M Y'),
            'amount' => number_format($voucher->amount, 2),
        ];

        // 3. Setup mPDF
        $mpdf = new Mpdf([
            'margin_top' => 10,
            'margin_bottom' => 10,
            'font' => 'sans-serif'
        ]);

        // 4. Load the HTML from a Blade view
        $html = view('finance.pdf.voucher', $data)->render();

        $mpdf->WriteHTML($html);

        // 5. Output
        return $mpdf->Output("VOUCHER_" . str_pad($id, 4, "0", STR_PAD_LEFT) . ".pdf", "D");
    }









    // HANDLERS/ASSISTANT ACCOUNTANT


    /**
     * Display the Payment Voucher Dashboard.
     */
    public function index_handler()
    {
        $pageTitle = 'Payment Vouchers';

        // 1. Fetching combined unique & sorted Payee / Client List
        $insurers = Insurer::pluck('insurer_name')->toArray();
        $clients = Client::pluck('client_name')->toArray();
        
        $combined = array_merge($insurers, $clients);
        $formatted = array_map(function($name) {
            return ucwords(strtolower(trim($name)));
        }, $combined);
        
        $finalClientList = array_filter(array_unique($formatted));
        asort($finalClientList);

        // 2. Fetch payment vouchers (grouped or query-split by status)
        // Adjust these queries based on your actual schema/scopes
        $vouchers = PaymentVoucher::all(); 
        $pendingItems = $vouchers->where('status', 'Pending');
        $approvedItems = $vouchers->where('status', 'Approved');

        return view('insurance_broking.accounts.payment_vouchers.show', compact('finalClientList', 'pendingItems', 'approvedItems', 'pageTitle'));
    }

    /**
     * Store a new Payment Voucher.
     */
    public function store_handler(Request $request)
    {
        $validated = $request->validate([
            'payee_name'       => 'required|string|max:255',
            'currency'         => 'required|string|in:ZMW,USD',
            'amount'           => 'required|numeric|min:0',
            'expense_category' => 'required|string',
            'payment_method'   => 'required|string',
            'description'      => 'required|string',
        ]);

        PaymentVoucher::create([
            'client_name'    => $validated['payee_name'],
            'currency'       => $validated['currency'],
            'amount'         => $validated['amount'],
            'expense_category'       => $validated['expense_category'],
            'payment_method' => $validated['payment_method'],
            'description'    => $validated['description'],
            'status'         => 'Pending',
            'created_by'     => Auth::user()->name,
        ]);

        return redirect()->route('insurance_broking.accounts.payment_vouchers.show')->with('status', 'created');
    }

    /**
     * Update an approved/existing voucher via the Modal form.
     */
   public function update_handler(Request $request)
    {
        $validated = $request->validate([
            'id'               => 'required|exists:payment_vouchers,id',
            'client_name'      => 'required|string|max:255',
            'created_at'       => 'required|date',
            'expense_category' => 'required|string|max:255', // Added validation rule
            'amount'           => 'required|numeric|min:0',
            'currency'         => 'required|string|in:ZMW,USD',
            'payment_method'   => 'required|string',
            'description'      => 'required|string',
        ]);

        $voucher = PaymentVoucher::findOrFail($validated['id']);
        
        $voucher->update([
            'client_name'      => $validated['client_name'],
            'created_at'       => $validated['created_at'],
            'expense_category' => $validated['expense_category'], // Saved to database
            'amount'           => $validated['amount'],
            'currency'         => $validated['currency'],
            'payment_method'   => $validated['payment_method'],
            'description'      => $validated['description'],
            'updated_by'       => Auth::user()->name, 
        ]);

        return redirect()->route('insurance_broking.accounts.payment_vouchers.show')->with('status', 'updated');
    }


    /**
     * Compile and download tenant isolated document layout via mPDF.
     */
    public function print_handler($id)
    {
        $voucher = PaymentVoucher::findOrFail($id);

        // Multi-Tenant Isolation Variable Resolution Blocks
        $tenantId = tenant('id'); 
        $adminUser = User::where('role', 'admin')->first();

        $companyName = $adminUser->company ?? 'Tenant #' . strtoupper($tenantId);
        $address     = $adminUser->physical_address ?? 'Update Corporate Address in Settings';
        $phone       = $adminUser->tel_number ?? 'N/A';
        $email       = $adminUser->email ?? 'admin@' . request()->getHost();

        // Logo Path Finding Security Engine
        $logoPath = storage_path("app/public/tenants/{$tenantId}/logo.jpg");
        if (!file_exists($logoPath)) {
            $logoPath = public_path("storage/tenants/{$tenantId}/logo.jpg");
        }

        $logoUrl = '';
        if (file_exists($logoPath) && is_file($logoPath)) {
            $logoData = base64_encode(file_get_contents($logoPath));
            $mimeType = @mime_content_type($logoPath) ?: 'image/jpeg'; 
            $logoUrl  = 'data:' . $mimeType . ';base64,' . trim($logoData);
        }

        // Offline Native QR Code Generation Builder Logic
        $qrUrlText = route('insurance_broking.accounts.payment_vouchers.show') . "?verify=" . $voucher->id . "&amt=" . $voucher->amount;
        
        try {
            $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
            $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150, 1);
            $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $renderer));
            $realSvgData = $writer->writeString($qrUrlText);
            $qrString = 'data:image/svg+xml;base64,' . base64_encode($realSvgData);
        } catch (\Exception $e) {
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

        $payload = [
            'companyName'     => $companyName,
            'address'         => $address,
            'phone'           => $phone,
            'email'           => $email,
            'logoUrl'         => $logoUrl,
            'qrString'        => $qrString,
            'voucher'         => $voucher,
            'voucherNumber'   => "PV-" . str_pad($voucher->id, 5, "0", STR_PAD_LEFT),
            'formattedDate'   => Carbon::parse($voucher->created_at)->format('d M Y'),
            'currentTime'     => Carbon::now()->format('d/m/Y H:i')
        ];

        $html = view('insurance_broking.accounts.payment_vouchers.print', $payload)->render();

        try {
            $mpdf = new \Mpdf\Mpdf([
                'tempDir'       => storage_path('app/mpdf'),
                'margin_left'   => 0,
                'margin_right'  => 0,
                'margin_top'    => 0,
                'margin_bottom' => 0,
            ]);

            $mpdf->WriteHTML($html);
            $downloadName = "VOUCHER_" . str_pad($voucher->id, 5, "0", STR_PAD_LEFT) . ".pdf";

            return response()->streamDownload(function() use ($mpdf) {
                echo $mpdf->Output('', 'S');
            }, $downloadName, ['Content-Type' => 'application/pdf']);

        } catch (\Mpdf\MpdfException $e) {
            return redirect()->back()->with('status', 'error')->withErrors('PDF Error: ' . $e->getMessage());
        }
    }



}