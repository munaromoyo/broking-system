<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // 👈 Make sure to import the DB facade
use App\Models\InsurerRemittance;
use Illuminate\Support\Facades\Response;

class RemittanceController extends Controller
{
   

public function index(Request $request)
{
    $fromDate = $request->query('from_date');
    $toDate = $request->query('to_date');
    $records = collect();

    if ($fromDate && $toDate) {
        $records = InsurerRemittance::whereBetween('remittance_date', [$fromDate, $toDate])
            ->orderBy('remittance_date', 'desc')
            ->get();
    }

    // dd((new InsurerRemittance)->getCasts());


    return view('insurance_broking.accounts.insurer_remittances.show', compact('records', 'fromDate', 'toDate'));
}


// Inside your RemittanceController class...

/**
     * Export All Insurers Remittance Full Summary to CSV.
     */
    public function exportFullSummary(Request $request)
    {
        // 1. Validate that 'from_date' is provided
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'nullable|date',
        ]);

        $from = $request->input('from_date');
        $to = $request->input('to_date');

        // 2. Fetch records dynamically using Eloquent (Replacing missing method)
        $records = InsurerRemittance::whereDate('remittance_date', '>=', $from)
            ->when($to, function ($query, $to) {
                return $query->whereDate('remittance_date', '<=', $to);
            })
            ->get();

        $filename = "All_Insurers_Remittance_Full_Summary_" . $from . "_" . now()->format('Ymd') . ".csv";

        // 3. Stream the CSV download directly to the browser
        return Response::streamDownload(function () use ($records) {
            $output = fopen('php://output', 'w');

            // Write the Main Header Row
            fputcsv($output, [
                'Remittance Date', 'Reference', 'Receipt Number', 'Invoice Number', 
                'Client Name', 'Insurer', 'Policy Name', 'Start Date', 'Expiry Date', 
                'Currency', 'Sum Insured', 'Basic Rate', 'Gross Received', 
                'Basic Premium', 'Premium Levy', 'RIB Commission', 
                'Insurer Premium Received', 'Amount Remitted', 'Processed By'
            ]);

            $summary = []; 
            $grand_totals = []; 

            if ($records->isNotEmpty()) {
                foreach ($records as $row) {
                    $ins_name = $row->insurer_name;
                    $currency = $row->policy_currency ?? 'ZMW';
                    
                    $remitted = (float)$row->amount_remitted;
                    $commission = (float)$row->rib_commission_received;
                    $levy = (float)$row->premium_levy_received;

                    // Initialize nested summary structures
                    if (!isset($summary[$ins_name][$currency])) {
                        $summary[$ins_name][$currency] = ['remitted' => 0, 'commission' => 0, 'levy' => 0];
                    }
                    if (!isset($grand_totals[$currency])) {
                        $grand_totals[$currency] = ['remitted' => 0, 'commission' => 0, 'levy' => 0];
                    }

                    // Aggregate calculations
                    $summary[$ins_name][$currency]['remitted'] += $remitted;
                    $summary[$ins_name][$currency]['commission'] += $commission;
                    $summary[$ins_name][$currency]['levy'] += $levy;

                    $grand_totals[$currency]['remitted'] += $remitted;
                    $grand_totals[$currency]['commission'] += $commission;
                    $grand_totals[$currency]['levy'] += $levy;

                    // Write detailed data row using Eloquent object properties
                    fputcsv($output, [
                        $row->remittance_date, 
                        $row->remittance_reference, 
                        $row->receipt_number,
                        $row->invoice_number, 
                        $row->client_name, 
                        $ins_name, 
                        $row->policy_name, 
                        $row->policy_start_date, 
                        $row->policy_expiry_date,
                        $currency, 
                        $row->total_sum_insured, 
                        $row->basic_rate,
                        $row->gross_amount_received, 
                        $row->basic_premium_received,
                        number_format($levy, 2, '.', ''),
                        number_format($commission, 2, '.', ''),
                        $row->insurer_premium_received,
                        number_format($remitted, 2, '.', ''), 
                        $row->processed_by
                    ]);
                }

                // --- Summary Section ---
                fputcsv($output, []); 
                fputcsv($output, ['SUMMARY BY INSURER & CURRENCY']);
                fputcsv($output, ['Insurer Name', 'Currency', 'Total Amount Remitted', 'Total RIB Commission', 'Total Premium Levy']);

                foreach ($summary as $name => $currencies) {
                    foreach ($currencies as $curr => $vals) {
                        fputcsv($output, [
                            $name, 
                            $curr, 
                            number_format($vals['remitted'], 2, '.', ''),
                            number_format($vals['commission'], 2, '.', ''),
                            number_format($vals['levy'], 2, '.', '')
                        ]);
                    }
                }

                // --- Grand Totals Section ---
                fputcsv($output, []);
                fputcsv($output, ['GRAND TOTALS']);
                fputcsv($output, ['Currency', 'Grand Total Remitted', 'Grand Total Commission', 'Grand Total Levy']);
                foreach ($grand_totals as $curr => $vals) {
                    fputcsv($output, [
                        $curr, 
                        number_format($vals['remitted'], 2, '.', ''),
                        number_format($vals['commission'], 2, '.', ''),
                        number_format($vals['levy'], 2, '.', '')
                    ]);
                }
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }


}