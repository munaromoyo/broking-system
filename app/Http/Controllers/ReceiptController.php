<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentReceiptRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
// 2. Crucial Framework Facades
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
// 2. Import your Eloquent Models
use App\Models\Invoice;
use App\Models\Receipt;
use App\Models\InsurerRemittance;
use App\Models\BankTransaction;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Throwable;

// 3. Native PHP Root Exception Class
use Exception;

class ReceiptController extends Controller
{
    /**
     * Show the receipts dashboard.
     */
    public function showReceipt(Request $request)
    {
        // Fetch real data directly from the database using your models
        $invoice_infor     = Invoice::where('status', '!=', 'Fully Paid')->orderBy('created_at', 'desc')->get();       
        $cash_book_infor   = Receipt::orderBy('created_at', 'desc')->get();
        $bank_transactions = BankTransaction::orderBy('transaction_date', 'desc')->get(); 

        $pageTitle = 'Receipts Dashboard';

        return view('insurance_broking.accounts.receipts.show', compact(
            'invoice_infor', 
            'cash_book_infor', 
            'bank_transactions',
            'pageTitle'
        ));
    }

    // GENERATE RECEIPT/POST PAYMENT
    public function storePayment(Request $request)
{
    // 1. VALIDATION: Check required fields up front using Laravel validator
    $validated = $request->validate([
        'receipt_payment'        => 'required',
        'invoice_number'         => 'required|string',
        'gross_amount_received'  => 'required|numeric|min:0.01',
        'receipt_date'           => 'required|string',
        'description'            => 'nullable|string',
        'payment_method'         => 'nullable|string',
        'payment_ref'            => 'nullable|string',
        'reference_no'           => 'nullable|string',
    ]);

    $invoiceNo = $validated['invoice_number'];
    $amtReceived = (float) $validated['gross_amount_received'];

    // 2. FETCH original invoice data
    $invoice = Invoice::where('invoice_number', $invoiceNo)->first();

    if (!$invoice) {
        return back()->with('error', "Error: Invoice number " . e($invoiceNo) . " not found.");
    }

    if (($invoice->status ?? '') === 'Fully Paid') {
        return back()->with('error', "Error: This invoice is already Fully Paid. No further receipts can be created.");
    }

    // 3. CALCULATE PREVIOUS PAYMENTS via Eloquent Receipt Model
    $previousPaid = (float) Receipt::where('invoice_number', $invoiceNo)->sum('gross_amount_received');
    $grossPremium = (float) $invoice->gross_premium;
    $totalSoFar = $previousPaid + $amtReceived;

    // Determine Status (using your small epsilon for float comparison safety)
    $newStatus = ($totalSoFar >= ($grossPremium - 0.01)) ? 'Fully Paid' : 'Partial Payment';

    // 4. ABSOLUTE DATE SANITIZATION USING CARBON
    try {
        $receiptDate = Carbon::parse(trim($validated['receipt_date']))->format('Y-m-d');
    } catch (Throwable $e) {
        $receiptDate = now()->format('Y-m-d'); // Fallback to today if string is entirely unparseable
    }

    // 5. DATABASE TRANSACTION
    DB::beginTransaction();

    try {
        // --- CALCULATIONS ---
        $commRate = (float) ($invoice->commission_rate ?? 0);
        if ($commRate >= 1) { 
            $commRate /= 100; 
        }

        $basicReceived          = round(($amtReceived / 1.05), 2);
        $levyReceived           = round(($amtReceived - $basicReceived), 2);
        $commissionReceived     = round(($basicReceived * $commRate), 2);
        $insurerPremiumReceived = round(($basicReceived - $commissionReceived), 2);

        // 6. INSERT INTO RECEIPTS TABLE via Eloquent
        Receipt::create([
            'invoice_number'           => $invoice->invoice_number,
            'client_name'              => $invoice->client_name,
            'insurer'                  => $invoice->insurer,
            'policy_name'              => $invoice->policy_name,
            'policy_start_date'        => $invoice->policy_start_date,
            'policy_expiry_date'       => $invoice->policy_expiry_date,
            'policy_currency'          => $invoice->policy_currency,
            'total_sum_insured'        => $invoice->total_sum_insured,
            'basic_rate'               => $invoice->basic_rate,
            'basic_premium'            => $invoice->basic_premium,
            'premium_levy_rate'        => $invoice->premium_levy_rate,
            'premium_levy'             => $invoice->premium_levy,
            'gross_premium'            => $invoice->gross_premium,
            'commission_rate'          => $invoice->commission_rate,
            'commission_amount'        => $invoice->commission_amount,
            'insurer_premium'          => $invoice->insurer_premium,
            'user'                     => Auth::user()->name ?? 'System', 
            'description'              => $validated['description'],
            'payment_method'           => $validated['payment_method'],
            'payment_ref'              => $validated['payment_ref'],
            'reference_no'             => $validated['reference_no'],
            'gross_amount_received'    => $amtReceived,
            'basic_premium_received'   => $basicReceived,
            'premium_levy_received'    => $levyReceived,
            'rib_commission_received'  => $commissionReceived,
            'insurer_premium_received' => $insurerPremiumReceived,
            'receipt_date'             => $receiptDate,
        ]);

        // 7. UPDATE INVOICE STATUS
        $invoice->update(['status' => $newStatus]);

        DB::commit();

        // 8. REDIRECT ON SUCCESS WITH SESSION MESSAGE
        return redirect()
        ->route('insurance_broking.accounts.receipts.show', ['msg' => 'success'])
        ->with('success', 'Receipt created successfully!');

    } catch (Throwable $e) {
        DB::rollBack();

        return back()
            ->withInput()
            ->with('error_details', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine()
            ])
            ->with('error', 'Execution Failed!');
    }
}




    /**
     * Generate and download the Official Receipt PDF.
     */
    public function generatePdf_receipt($id)
    {
        try {
            // 1. Query the specific receipt directly using Eloquent or throw a 404
            // Assumes your database column is matching 'receipt_number'. If it is your primary key, use findOrFail($id)
            $receipt = Receipt::where('receipt_number', $id)->firstOrFail();

            // 2. Multi-Tenant Dynamic Logo & Header Resolution
            $tenantId = tenant('id'); 
            $adminUser = User::where('role', 'admin')->first();

            $companyName = $adminUser->company ?? 'Profstand';
            $address     = $adminUser->physical_address ?? 'Plot Number 14 Njoka Road, Lusaka';
            $phone       = $adminUser->tel_number ?? '+260 572313599';
            $email       = $adminUser->email ?? 'services@profstand.com';

            // Secure Tenant Data Isolation Lookup Loop
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

            // 3. Extract explicit values for the QR context safely from the model attributes
            $clientName = $receipt->client_name ?? $receipt->insured_name ?? 'Client';
            $amountPaid = $receipt->gross_amount_received ?? $receipt->amount ?? 0;
            $currency   = $receipt->policy_currency ?? 'ZMW';

            // 4. Construct QR Raw Text Payload 
            $qrRawText = "RIB REC: " . $id . " | Client: " . $clientName . " | Paid: " . $currency . " " . number_format($amountPaid, 2);

            // =================================================================
            // NATIVE SECURE QR CODE GENERATION (100% OFFLINE VIA BACONQRCODE)
            // =================================================================
            try {
                $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
                $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150, 1);
                $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $renderer));
                
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
                    $qrString = '';
                }
            }

            // 5. Compile dynamic data array parameters for the layout engine
            $data = [
                'receipt'       => $receipt, // Now an Eloquent object instance rather than an array element
                'id'            => $id,
                'current_date'  => now()->format('d M Y'),
                'companyName'   => $companyName,
                'address'       => $address,
                'phone'         => $phone,
                'email'         => $email,
                'logoUrl'       => $logoUrl,
                'qrString'      => $qrString
            ];

            // 6. Render view template payload using the DomPDF Facade
            $pdf = Pdf::loadView('insurance_broking.accounts.receipts.generate_pdf', $data);

            // 7. Establish canvas layout parameters and paper sizing constraints
            $pdf->getDomPDF()->set_option('isHtml5ParserEnabled', true);
            $pdf->getDomPDF()->set_option('isRemoteEnabled', true);
            $pdf->setPaper('a4', 'portrait');

            // 8. Build dynamic file download name matching your padding structure
            $fileName = "REC" . str_pad($id, 4, "0", STR_PAD_LEFT) . ".pdf";

            return $pdf->download($fileName);

        } catch (\Exception $e) {
            Log::error("DomPDF Error generating receipt ID {$id}: " . $e->getMessage());
            return redirect()->back()->with('error', 'An error occurred while generating the PDF document.');
        }
    }


    // BANK RECON TEMPLATE
        public function bankReconTemplate()
    {
        $headers = [
            'Transaction Date',
            'Value Date',
            'Description',
            'Reference Number',
            'Currency',
            'Debits',
            'Credits',
            'Balance'
        ];

        // Multiple sample rows to clearly show expected data patterns
        $sample_rows = [
            ['10/04/2026', '10/04/2026', 'PREMIUM PAYMENT - MUNARO MOYO',  'FT260410', 'ZMW', '0.00',    '5000.00', '15000.00'],
            ['11/04/2026', '11/04/2026', 'DIRECT DEBIT - UTILITY BILL',    'FT260411', 'ZMW', '1200.00', '0.00',    '13800.00'],
            ['12/04/2026', '12/04/2026', 'PREMIUM PAYMENT - JOHN BANDA',   'FT260412', 'ZMW', '0.00',    '3500.00', '17300.00'],
        ];

        $fileName = 'bank_import_template_' . now()->format('Ymd') . '.csv';

        return response()->streamDownload(function () use ($headers, $sample_rows) {
            $file = fopen('php://output', 'w');

            // Write UTF-8 BOM so Excel opens it cleanly without encoding issues
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, $headers);

            foreach ($sample_rows as $row) {
                fputcsv($file, $row);
            }

            fclose($file);

        }, $fileName, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ]);
    }




    // BULK BANK TRANSACTION IMPORT
    // Render the bulk file upload view interface
        public function importView()
    {
        $pageTitle = 'Bulk Bank Import';
        
        return view('insurance_broking.accounts.receipts.import', compact('pageTitle'));
    }




    // IMPORT BANK TRANSACTION DATA
    public function importStore(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();

        // 1. Read the entire file content into memory strings
        $fileContent = file_get_contents($filePath);
        if ($fileContent === false) {
            return redirect()->back()->with('error', 'Failed to read the uploaded CSV file structure.');
        }

        // 2. Standardize all variants of line breaks (\r\n and \r) down to standard Unix newlines (\n)
        $fileContent = str_replace(["\r\n", "\r"], "\n", $fileContent);    
        
        // 3. Break content apart line-by-line 
        $rows = explode("\n", $fileContent);

        // Remove the CSV Header Row (First element)
        array_shift($rows);

        $insertBuffer = [];
        $count = 0;
        $batchSize = 250; 

        $delimiter = str_contains($rows[1] ?? '', "\t") ? "\t" : ",";

        // 4. Loop through every isolated row item explicitly
        foreach ($rows as $row) {
            if (trim($row) === '') {
                continue; // Skip trailing empty spreadsheet spaces safely
            }

            // Convert string block characters cleanly into an array format mapping
            $column = str_getcsv($row, $delimiter);

            // Skip invalid or completely empty rows
            if (empty($column) || !isset($column[0]) || trim($column[0]) === '') {
                continue;
            }

            // Map data securely using null-coalescing operators to avoid missing key errors
            $insertBuffer[] = [
                'transaction_date' => trim($column[0] ?? ''),
                'value_date'       => trim($column[1] ?? ''),
                'description'      => trim($column[2] ?? ''),
                'reference_number' => trim($column[3] ?? ''),
                'currency'         => trim($column[4] ?? 'ZMW'),
                'debits'           => isset($column[5]) && trim($column[5]) !== '' ? (float)trim($column[5]) : 0.00,
                'credits'          => isset($column[6]) && trim($column[6]) !== '' ? (float)trim($column[6]) : 0.00,
                'balance'          => isset($column[7]) && trim($column[7]) !== '' ? (float)trim($column[7]) : 0.00,
                'status'           => 'unallocated',
                'created_at'       => now(),
                'updated_at'       => now(),
            ];

            $count++;

            // Perform batch database inserts
            if (count($insertBuffer) >= $batchSize) {
                DB::table('bank_transactions')->insert($insertBuffer);
                $insertBuffer = []; 
            }
        }

        // Insert any leftover rows in the stack buffer
        if (count($insertBuffer) > 0) {
            DB::table('bank_transactions')->insert($insertBuffer);
        }

        if ($count === 0) {
            return redirect()->back()->with('error', 'No valid financial rows were discovered inside your document.');
        }

        return redirect()->back()->with('success', "Successfully imported {$count} transactions straight to your reconciliation stack!");
    }




    // POST PAYMENT RECEIPT
  /**
     * Post a new client payment receipt using Eloquent Models and update invoice status.
     *
     * @param  \App\Http\Requests\StorePaymentReceiptRequest  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postPayment(StorePaymentReceiptRequest $request)
    {
        $invoiceNo = $request->input('invoice_number');
        $amtReceived = (float) $request->input('gross_amount_received');

        // 1. Fetch original invoice data securely using the Invoice Model
        $invoice = Invoice::where('invoice_number', $invoiceNo)->first();

        // 2. Validate structural status
        if (!$invoice) {
            return redirect()->back()->with('error', "Invoice number {$invoiceNo} could not be found.");
        }

        if (($invoice->status ?? '') === 'Fully Paid') {
            return redirect()->back()->with('error', "This invoice is already Fully Paid. Action denied.");
        }

        // 3. Calculate historical status directly from the Receipt Model
        $previousPaid = (float) Receipt::where('invoice_number', $invoiceNo)
            ->sum('gross_amount_received');

        $grossPremium = (float) $invoice->gross_premium;
        $totalSoFar = $previousPaid + $amtReceived;

        // Determine status flag safely with epsilon compensation logic
        $newStatus = ($totalSoFar >= ($grossPremium - 0.01)) ? 'Fully Paid' : 'Partial Payment';

        // 4. Atomic transaction processing execution
        DB::beginTransaction();

        try {
            // Brokerage Premium and Levy Calculations
            $commRate = (float) ($invoice->commission_rate ?? 0);
            if ($commRate >= 1) {
                $commRate /= 100;
            }

            // Calculations splitting basic and VAT/Levy margins (Zambian 5% Insurance Premium Levy)
            $basicReceived = round(($amtReceived / 1.05), 2);
            $levyReceived  = round(($amtReceived - $basicReceived), 2);
            $commissionReceived = round(($basicReceived * $commRate), 2);
            $insurerPremiumReceived = round(($basicReceived - $commissionReceived), 2);

            // Fetch absolute formatted date string from request helper
            $receiptDate = $request->getSanitizedDate();

            // 5. Build financial record entry inside the database using the Receipt Model
            Receipt::create([
                'invoice_number'           => $invoice->invoice_number,
                'client_name'              => $invoice->client_name,
                'insurer'                  => $invoice->insurer,
                'policy_name'              => $invoice->policy_name,
                'policy_start_date'        => $invoice->policy_start_date,
                'policy_expiry_date'       => $invoice->policy_expiry_date,
                'policy_currency'          => $invoice->policy_currency,
                'total_sum_insured'        => $invoice->total_sum_insured,
                'basic_rate'               => $invoice->basic_rate,
                'basic_premium'            => $invoice->basic_premium,
                'premium_levy_rate'        => $invoice->premium_levy_rate,
                'premium_levy'             => $invoice->premium_levy,
                'gross_premium'            => $invoice->gross_premium,
                'commission_rate'          => $invoice->commission_rate,
                'commission_amount'        => $invoice->commission_amount,
                'insurer_premium'          => $invoice->insurer_premium,
                'user'                     => Auth::user()->name ?? session('name') ?? 'System Administrator',
                'description'              => $request->input('description'),
                'payment_method'           => $request->input('payment_method'),
                'payment_ref'              => $request->input('payment_ref'),
                'reference_no'             => $request->input('reference_no'),
                'gross_amount_received'    => $amtReceived,
                'basic_premium_received'   => $basicReceived,
                'premium_levy_received'    => $levyReceived,
                'rib_commission_received'  => $commissionReceived,
                'insurer_premium_received' => $insurerPremiumReceived,
                'receipt_date'             => $receiptDate,
                // created_at and updated_at are automatically appended by the Model!
            ]);

            // 6. Push state update upstream using the Invoice Model instance directly
            $invoice->update([
                'status' => $newStatus
            ]);

            DB::commit();

            return redirect()->back()->with('success', "Payment transaction for Invoice #{$invoiceNo} has been posted successfully.");

        } catch (Exception $e) {
            DB::rollBack();
            
            // Log the detailed trace system error message for debugging
            logger()->error("Receipt creation runtime break: " . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return redirect()->back()->with('error', "Transaction processing failed: " . $e->getMessage());
        }
    }


    // INSURER REMITTANCES
    public function remitPremium(Request $request)
    {
        // Validate the incoming form payload
        $validatedData = $request->validate([
            'receipt_number'    => 'required|string',
            'invoice_number'    => 'required|string',
            'remittance_amount' => 'required|numeric',
            'remittance_date'   => 'required|date',
            'remittance_ref'    => 'required|string',
            'policy_currency'   => 'required|string'
        ]);

        // Grab the authenticated user's name
        $processorName = auth()->user()->name ?? 'System';

        // Process the transaction locally inside this controller instance
        $success = $this->createInsurerRemittance($validatedData, $processorName);

        // Redirect back using Laravel's session flash messages
        if ($success) {
            return redirect()->back()->with('success', 'Remittance processed successfully.');
        }

        return redirect()->back()->with('error', 'Failed to process remittance.');
    }

    /**
     * 2. THE PROCESSING METHOD
     * Modernized utilizing your Eloquent Models & Laravel DB Transactions.
     */
   /**
 * Handle the form submission to create an insurer remittance entry.
 *
 * Create an insurer remittance entry using a data array.
 *
 * @param  array  $data
 * @param  string $processedBy
 * @return bool
 */
protected function createInsurerRemittance(array $data, string $processedBy = 'System') 
{
    // 1. SAFE ARRAY KEY CHECK
    if (empty($data["receipt_number"]) || empty($data["remittance_amount"])) {
        return false;
    }

    $receiptNo   = $data['receipt_number'];
    $remitAmount = (float) $data['remittance_amount'];

    try {
        // 2. EXECUTE ATOMIC TRANSACTION STREAM
        return DB::transaction(function () use ($data, $receiptNo, $remitAmount, $processedBy) {
            
            // Fetch matching Receipt model profile criteria
            $receipt = Receipt::where('receipt_number', $receiptNo)->first();
            
            if (!$receipt) {
                throw new \Exception("Receipt record #{$receiptNo} not found in system.");
            }
        
            // Convert date streams cleanly to ISO formats via Carbon
            $pStart = null;
            if (!empty($receipt->policy_start_date)) {
                $pStart = Carbon::hasFormat($receipt->policy_start_date, 'd/m/Y')
                    ? Carbon::createFromFormat('d/m/Y', $receipt->policy_start_date)->format('Y-m-d')
                    : Carbon::parse($receipt->policy_start_date)->format('Y-m-d');
            }

            $pExpiry = null;
            if (!empty($receipt->policy_expiry_date)) {
                $pExpiry = Carbon::hasFormat($receipt->policy_expiry_date, 'd/m/Y')
                    ? Carbon::createFromFormat('d/m/Y', $receipt->policy_expiry_date)->format('Y-m-d')
                    : Carbon::parse($receipt->policy_expiry_date)->format('Y-m-d');
            }

            // 3. MASS-ASSIGN AND INSERT NEW RECORD VIA THE MODEL
            InsurerRemittance::create([
                'receipt_number'           => $receipt->receipt_number,
                'invoice_number'           => $receipt->invoice_number,
                'client_name'              => $receipt->client_name,
                'insurer_name'             => $receipt->insurer, 
                'policy_name'              => $receipt->policy_name,
                'policy_start_date'        => $pStart,
                'policy_expiry_date'       => $pExpiry,
                'policy_currency'          => $receipt->policy_currency,
                'total_sum_insured'        => $receipt->total_sum_insured,
                'basic_rate'               => $receipt->basic_rate,
                'gross_amount_received'    => $receipt->gross_amount_received,
                'basic_premium_received'   => $receipt->basic_premium_received,
                'premium_levy_received'    => $receipt->premium_levy_received,
                'rib_commission_received'  => $receipt->rib_commission_received,
                'insurer_premium_received' => $receipt->insurer_premium_received,
                'amount_remitted'          => $remitAmount,
                'remittance_reference'     => $data["remittance_ref"] ?? '',
                'remittance_date'          => $data["remittance_date"] ?? now()->format('Y-m-d'),
                'processed_by'             => $processedBy,
                'remittance_status'        => 'Pending'
            ]);
        
            // 4. UPDATE LEDGER LOG STATUS FOR TARGET RECEIPT
            $receipt->update([
                'remittance_status' => 'Remitted'
            ]);

            return true;

        }, 3); // Retries 3 times if an SQL deadlock error crops up

    } catch (\Exception $e) {
        // Log structural error details for audit tracks
        Log::error("Remittance Execution Exception for Receipt #{$receiptNo}: " . $e->getMessage());
        
        // Rethrow the exception so that the calling method (on line 438) can catch it and display it on-screen
        throw $e;
    }
}


    // BANK ALLOCATION
    /**
 * Allocate a receipt to a specific bank transaction.
 *
 * @param int $receiptId
 * @param int $bankId
 * @return bool
 */

public function allocateReceipt(Request $request)
{
    // 1. VALIDATION
    $validated = $request->validate([
        'receipt_id' => 'required|integer',
        'bank_id'    => 'required|integer'
    ]);

    $bankId    = $validated['bank_id'];
    $receiptId = $validated['receipt_id'];

    // 2. START DATABASE TRANSACTION
    DB::beginTransaction();

    try {
        // --- STEP 1: Fetch the reference from the bank transaction first ---
        $bankTransaction = BankTransaction::find($bankId);

        if (!$bankTransaction) {
            throw new \Exception("Bank transaction not found.");
        }
        
        $bankRef = $bankTransaction->reference_number;

        // --- STEP 2: Update Receipt (Formerly cash_book) ---
        // We use a query builder update to match your specific WHERE constraint cleanly
        // and safely bypass any timestamp features for this table.
        $receiptUpdated = Receipt::where('receipt_number', $receiptId) // Swap with 'id' if receipt_number isn't the primary key
            ->update([
                'allocation_status'   => 'allocated',
                'bank_transaction_id' => $bankId,
                'bank_reference'       => $bankRef,
                'allocated_at'        => now() // Laravel alternative to NOW()
            ]);

        // --- STEP 3: Update Bank Transactions ---
        // Explicitly override Eloquent's timestamp tracking on this instance to avoid column errors
        $bankTransaction->timestamps = false; 
        
        $bankTransaction->update([
            'status'            => 'allocated',
            'linked_receipt_id' => $receiptId
        ]);

        // 3. COMMIT EVERYTHING
        DB::commit();

        return redirect()
            ->route('insurance_broking.accounts.receipts.show')
            ->with('success', 'Allocation successful!');

    } catch (Throwable $e) {
        // 4. ROLLBACK ON ANY ERROR
        DB::rollBack();

        // Standard systemic logging tool built into Laravel
        logger()->error("Allocation Error: " . $e->getMessage(), [
            'bank_id'    => $bankId,
            'receipt_id' => $receiptId,
            'trace'      => $e->getTraceAsString()
        ]);

        return redirect()
            ->back()
            ->with('error', 'Error: Could not complete allocation. ' . $e->getMessage());
    }
}


    // RECEIPT CANCELLATION
    /**
     * Handle the POST submission to cancel a receipt.
     */

    public function handleCancelRequest(Request $request)
    {
        // 1. Validate the form data sent by your cancel modal
        $validated = $request->validate([
            'receipt_number' => 'required|string',
            'remarks'        => 'required|string|max:500',
        ]);

        try {
            $receiptNumber = $validated['receipt_number'];
            $remarks       = $validated['remarks'];
            $userName      = Auth::user()->name ?? 'System User';

            // 2. Execute your helper method
            $this->cancelReceipt($receiptNumber, $remarks, $userName);

            // 3. SUCCESS MESSAGE: Redirect back to the dashboard with a success session banner
            return redirect()
                ->route('insurance_broking.accounts.receipts.show')
                ->with('success', "Receipt #{$receiptNumber} was cancelled successfully.");

        } catch (Throwable $e) {
            // Log the structural error stack trace
            Log::error("Receipt Cancellation Failed for #{$request->receipt_number}: " . $e->getMessage());

            // 4. FAILURE MESSAGE: Redirect back to the view with an error banner
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Could not cancel receipt: ' . $e->getMessage());
        }
    }

    /**
     * Cancels a receipt and updates the database records using Eloquent.
     *
     * @param string $receiptNumber
     * @param string $remarks
     * @param string $userName
     * @return bool
     * @throws Exception
     */
    public function cancelReceipt(string $receiptNumber, string $remarks, string $userName): bool
    {
        try {
            return DB::transaction(function () use ($receiptNumber, $remarks, $userName) {
                
                // Perform the update using the Receipt model
                $affectedRows = Receipt::where('receipt_number', $receiptNumber)
                    ->where('status', '!=', 'Cancelled')
                    ->update([
                        'status'               => 'Cancelled',
                        'cancelled_by'         => trim($userName),
                        'cancelled_at'         => now(), // Generates current standard Y-m-d H:i:s timestamp
                        'cancellation_remarks' => trim($remarks),
                    ]);

                // Check if any row was actually updated (Mimics $stmt->affected_rows)
                if ($affectedRows === 0) {
                    throw new Exception("Receipt not found or it has already been cancelled.");
                }

                // [OPTIONAL]: Add your invoice reversal logic here if needed
                // e.g., Invoice::where(...)->update(...);

                return true;
            });

        } catch (Exception $e) {
            // DB::transaction automatically rolled back what it executed. 
            // Re-throw so the controller can handle the failure view state.
            throw $e;
        }
    }

    



}