<?php

namespace App\Http\Controllers;

use App\Models\SlipCancellation;
use App\Models\CreditNote;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\User;

class CreditNoteController extends Controller
{

    /**
     * Display a listing of all generated credit notes.
     */
    public function index()
    {
        // Fetch all generated credit notes with their cancellation model details
        $creditNotes = CreditNote::with('slipCancellation')->get();

        return view('insurance_broking.accounts.credit_notes.show', [
            'pageTitle'   => 'Credit Notes Log',
            'section'     => 'Credit Notes',
            'creditNotes' => $creditNotes,
        ]);
    }

    /**
     * Display the Generate Credit Note interface.
     */
    /**
     * Display the Generate Credit Note interface.
     */
    public function generateCreditNote()
    {
        // 1. Fetch only cancellations that DO NOT have an associated credit note via relationships
        $pendingCancellations = SlipCancellation::doesntHave('creditNote')->get();

        // 2. Return the view with required template parameters
        return view('insurance_broking.accounts.credit_notes.generate', [
            'pageTitle'            => 'Generate Credit Notes',
            'section'              => 'Generate Credit Notes',
            'pendingCancellations' => $pendingCancellations,
        ]);
    }

    /**
     * Handle the generation request execution.
     */
    public function storeCreditNote(Request $request)
    {
        $request->validate([
            'slip_id' => 'required|exists:slip_cancellations,slip_id'
        ]);

        try {
            // Find the specific cancellation data
            $cancellation = SlipCancellation::where('slip_id', $request->slip_id)->firstOrFail();

            // Create the Credit Note record using your Model
            CreditNote::create([
                'slip_id' => $cancellation->slip_id,
                // Map your other required columns here, for example:
                // 'amount' => $cancellation->premium_refund,
                // 'user_id' => Auth::id(),
            ]);

            return redirect()
                ->route('credit-notes.generate')
                ->with('success_message', 'Credit Note generated successfully.');

        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error_message', 'Failed to generate credit note: ' . $e->getMessage());
        }
    }



/**
 * Generate and stream the Credit Note PDF report using Barryvdh DomPDF.
 */
  /**
     * Generate and stream the Credit Note PDF report using Barryvdh DomPDF.
     */
    public function downloadPdf($slip_id)
    {
        // 1. Fetch the target Credit Note with its cancellation relationship details
        $note = CreditNote::with('slipCancellation')
            ->where('slip_id', $slip_id)
            ->firstOrFail();

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

        // 3. Extract attributes directly from the CreditNote ($note) model instance
        $insuredName = $note->insured_name ?? 'Client';
        $refundAmt   = $note->premium_refund ?? 0;

        // 4. Construct the dynamic statement target routing URL payload
        $qrUrlText = route('insurance_broking.accounts.credit_notes.show') . "?client=" . urlencode($insuredName) . "&balance=" . $refundAmt;
        
        // =================================================================
        // NATIVE SECURE QR CODE GENERATION (100% OFFLINE via BaconQrCode)
        // =================================================================
        try {
            // Instantiate the native renderer to compile high-quality vector paths
            $renderer = new \BaconQrCode\Renderer\Image\SvgImageBackEnd();
            $rendererStyle = new \BaconQrCode\Renderer\RendererStyle\RendererStyle(150, 1);
            $writer = new \BaconQrCode\Writer(new \BaconQrCode\Renderer\ImageRenderer($rendererStyle, $renderer));
            
            // Generate a real, 100% scannable vector SVG string natively offline
            $realSvgData = $writer->writeString($qrUrlText);
            $qrString = 'data:image/svg+xml;base64,' . base64_encode($realSvgData);
        } catch (\Exception $e) {
            // Safe network failover backup ONLY if local rendering breaks down
            $apiBackupUrl = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($qrUrlText);
            
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
                $qrString = ''; // Leave blank if firewalls strictly block external cURL
            }
        }

        // 5. Compile dynamic layout parameters mapping our Tenant logic context
        $data = [
            'note'         => $note,
            'current_date' => now()->format('d M Y'),
            'logoUrl'      => $logoUrl, 
            'companyName'  => $companyName,
            'address'      => $address,
            'phone'        => $phone,
            'email'        => $email,
            'qrString'     => $qrString,
        ];

        // 6. Generate and stream the layout template view directly
        $pdf = Pdf::loadView('insurance_broking.accounts.credit_notes.pdf', $data)
                  ->setPaper('a4', 'portrait');

        return $pdf->stream("CN_Report_{$slip_id}.pdf");
    }



}