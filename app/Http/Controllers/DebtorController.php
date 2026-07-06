<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use Carbon\Carbon;

class DebtorController extends Controller
{
    // public function index()
    // {
    //     $pageTitle = "View Debtors";
    //     $today = Carbon::now();

    //     // Eager load only the clients who have invoices to keep processing efficient
    //     $clients = Client::has('invoices')
    //         ->with(['invoices', 'receipts', 'cancellations'])
    //         ->get();

    //     $debtors_master = [];
    //     $grand_totals = [];

    //     foreach ($clients as $client) {
    //         // Invoices may have multiple currencies; split calculations by currency
    //         $currencies = $client->invoices->pluck('policy_currency')->unique();

    //         foreach ($currencies as $curr) {
    //             $curr = trim($curr);
                
    //             // Filter client's invoices for this currency
    //             $clientInvoices = $client->invoices->where('policy_currency', $curr);
                
    //             // If there are unique insurers within this currency segment, we break it down
    //             $insurers = $clientInvoices->pluck('insurer')->unique();

    //             foreach ($insurers as $insurer) {
    //                 $insurer = trim($insurer);
    //                 $key = $client->client_name . '_' . $insurer . '_' . $curr;

    //                 // Calculate total invoiced for this specific currency/insurer pair
    //                 $specificInvoices = $clientInvoices->where('insurer', $insurer);
    //                 $totalInvoiced = $specificInvoices->sum('gross_premium');

    //                 // Filter matching receipts and cancellations for this currency 
    //                 // (Assuming legacy architecture matches on client_name & currency)
    //                 $totalReceipts = $client->receipts
    //                     ->where('policy_currency', $curr)
    //                     ->sum('gross_amount_received');

    //                 $totalCancellations = $client->cancellations
    //                     ->where('policy_currency', $curr)
    //                     ->sum('rev_gross'); // Adjust 'rev_gross' if column name differs

    //                 $totalCredits = $totalReceipts + $totalCancellations;
    //                 $remainingCredits = $totalCredits;

    //                 $aging = ['current' => 0, '31_60' => 0, '61_90' => 0, '91_plus' => 0];

    //                 // Sort invoices using Collection helper for FIFO calculation
    //                 $sortedInvoices = $specificInvoices->sortBy(function ($inv) {
    //                     return $this->parseDate($inv->policy_start_date)->timestamp;
    //                 });

    //                 foreach ($sortedInvoices as $inv) {
    //                     $amount = $inv->gross_premium;
    //                     $inceptionDate = $this->parseDate($inv->policy_start_date);
    //                     $diff = $today->diffInDays($inceptionDate);

    //                     if ($remainingCredits >= $amount) {
    //                         $remainingCredits -= $amount;
    //                         $unpaid = 0;
    //                     } else {
    //                         $unpaid = $amount - $remainingCredits;
    //                         $remainingCredits = 0;
    //                     }

    //                     if ($unpaid > 0.01) {
    //                         if ($diff <= 30) $aging['current'] += $unpaid;
    //                         elseif ($diff <= 60) $aging['31_60'] += $unpaid;
    //                         elseif ($diff <= 90) $aging['61_90'] += $unpaid;
    //                         else $aging['91_plus'] += $unpaid;
    //                     }
    //                 }

    //                 $balanceDue = $totalInvoiced - $totalCredits;

    //                 // Only add to list if they owe a balance
    //                 if ($balanceDue > 0.01) {
    //                     $debtors_master[$key] = [
    //                         'client' => $client->client_name,
    //                         'insurer' => $insurer,
    //                         'currency' => $curr,
    //                         'aging' => $aging,
    //                         'balance_due' => $balanceDue,
    //                     ];

    //                     // Aggregate global currency totals
    //                     if (!isset($grand_totals[$curr])) {
    //                         $grand_totals[$curr] = ['current' => 0, 'aged' => 0, 'total' => 0];
    //                     }
    //                     $grand_totals[$curr]['current'] += $aging['current'];
    //                     $grand_totals[$curr]['aged'] += ($aging['31_60'] + $aging['61_90'] + $aging['91_plus']);
    //                     $grand_totals[$curr]['total'] += $balanceDue;
    //                 }
    //             }
    //         }
    //     }

    //     return view('insurance_broking.accounts.debtors.index', compact('debtors_master', 'grand_totals', 'pageTitle'));
    // }


    public function index()
{
    $pageTitle = "View Debtors";
    $today = Carbon::now()->startOfDay(); // Normalize time component

    // Eager load related balances efficiently
    $clients = Client::has('invoices')
        ->with(['invoices', 'receipts', 'cancellations'])
        ->get();

    $debtors_master = [];
    $grand_totals = [];

    foreach ($clients as $client) {
        // Break segments down by unique currencies
        $currencies = $client->invoices->pluck('policy_currency')->unique();

        foreach ($currencies as $curr) {
            $curr = trim($curr);
            
            // Filter client's invoices for this currency
            $clientInvoices = $client->invoices->where('policy_currency', $curr);
            
            // Break segments down by unique insurers
            $insurers = $clientInvoices->pluck('insurer')->unique();

            foreach ($insurers as $insurer) {
                $insurer = trim($insurer);
                $key = $client->client_name . '_' . $insurer . '_' . $curr;

                // Calculate total gross premium for this specific currency/insurer pair
                $specificInvoices = $clientInvoices->where('insurer', $insurer);
                $totalInvoiced = $specificInvoices->sum('gross_premium');

                // FIX: If receipts & cancellations have an insurer column, filter by it here
                // to prevent double-allocating global client credits across multiple insurers.
                $receiptsQuery = $client->receipts->where('policy_currency', $curr);
                if ($client->receipts->first() && isset($client->receipts->first()->insurer)) {
                    $receiptsQuery = $receiptsQuery->where('insurer', $insurer);
                }
                $totalReceipts = $receiptsQuery->sum('gross_amount_received');

                $cancellationsQuery = $client->cancellations->where('policy_currency', $curr);
                if ($client->cancellations->first() && isset($client->cancellations->first()->insurer)) {
                    $cancellationsQuery = $cancellationsQuery->where('insurer', $insurer);
                }
                $totalCancellations = $cancellationsQuery->sum('rev_gross');

                $totalCredits = $totalReceipts + $totalCancellations;
                $remainingCredits = $totalCredits;

                $aging = ['current' => 0, '31_60' => 0, '61_90' => 0, '91_plus' => 0];

                // Sort invoices chronologically (FIFO calculation: oldest paid off first)
                $sortedInvoices = $specificInvoices->sortBy(function ($inv) {
                    return $this->parseDate($inv->policy_start_date)->timestamp;
                });

                foreach ($sortedInvoices as $inv) {
                    $amount = $inv->gross_premium;
                    $inceptionDate = $this->parseDate($inv->policy_start_date)->startOfDay();
                    
                    // FIX: Chronological calculation. Older dates yield bigger positive differences.
                    $diff = $inceptionDate->diffInDays($today, false);

                    if ($remainingCredits >= $amount) {
                        $remainingCredits -= $amount;
                        $unpaid = 0;
                    } else {
                        $unpaid = $amount - $remainingCredits;
                        $remainingCredits = 0;
                    }

                    if ($unpaid > 0.01) {
                        // Classify unpaid segment according to time gap
                        if ($diff <= 30) {
                            $aging['current'] += $unpaid;
                        } elseif ($diff <= 60) {
                            $aging['31_60'] += $unpaid;
                        } elseif ($diff <= 90) {
                            $aging['61_90'] += $unpaid;
                        } else {
                            $aging['91_plus'] += $unpaid;
                        }
                    }
                }

                $balanceDue = $totalInvoiced - $totalCredits;

                // Only add to dataset if they realistically owe a balance
                if ($balanceDue > 0.01) {
                    $debtors_master[$key] = [
                        'client' => $client->client_name,
                        'insurer' => $insurer,
                        'currency' => $curr,
                        'aging' => $aging,
                        'balance_due' => $balanceDue,
                    ];

                    // Aggregate global currency totals
                    if (!isset($grand_totals[$curr])) {
                        $grand_totals[$curr] = ['current' => 0, 'aged' => 0, 'total' => 0];
                    }
                    $grand_totals[$curr]['current'] += $aging['current'];
                    $grand_totals[$curr]['aged'] += ($aging['31_60'] + $aging['61_90'] + $aging['91_plus']);
                    $grand_totals[$curr]['total'] += $balanceDue;
                }
            }
        }
    }

    return view('insurance_broking.accounts.debtors.index', compact('debtors_master', 'grand_totals', 'pageTitle'));
}



    /**
     * Safely parse strings or Carbon dates
     */
    private function parseDate($date)
    {
        if (empty($date)) return Carbon::now();
        if ($date instanceof Carbon) return $date;

        try {
            return Carbon::createFromFormat('d/m/Y', trim($date));
        } catch (\Exception $e) {
            return Carbon::parse($date);
        }
    }
}