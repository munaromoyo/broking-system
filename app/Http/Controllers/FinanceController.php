<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\PaymentVoucher; // Assuming you have a PaymentVoucher model
use Auth;
use Illuminate\Support\Facades\DB;
use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalVoucher;
use App\Models\FixedAsset;
use App\Models\Liability;
use App\Http\Controllers\Finance\JournalController;
use App\Models\Receipt;
use App\Models\Invoice;
use App\Models\InsurerRemittance;



class FinanceController extends Controller
{
    public function index()
    {
        $vouchers = PaymentVoucher::all(); // Or your custom query
        
        return view('finance.vouchers.show', [
            'section' => 'Finance',
            'pageTitle' => 'Finance',
            'vouchers' => $vouchers,
            'pendingCount' => $vouchers->where('status', 'Pending')->count()
        ]);
    }

    public function processVoucher($id, $action)
{
    $voucher = PaymentVoucher::findOrFail($id);
    
    if ($action === 'approve') {
        // Fallback to a placeholder string if Auth fails, so you can see if Auth is the issue
        $modifierName = Auth::check() ? Auth::user()->name : 'Unknown User (Not Logged In)';
        $voucher->update([
            'status' => 'Approved',
            'approved_by' => $modifierName,
            'approved_at' => now()
        ]);
        $message = "Voucher approved successfully.";
    } elseif ($action === 'reject') {
        $voucher->update([
            'status' => 'Rejected'
        ]);
        $message = "Voucher rejected successfully.";
    } else {
        return back()->with('error', 'Invalid action.');
    }

    return back()->with('success', $message);
}



    //INCOME STATEMENT

    public function incomeStatement(Request $request)
    {
        $targetYear = $request->get('year', date('Y'));
        $conversionRate = (float) $request->get('rate', 25.00);

        $startOfYear = Carbon::create($targetYear, 1, 1)->startOfDay();
        $endOfYear = Carbon::create($targetYear, 12, 31)->endOfDay();

        // 1. Fetch Data (Assuming you have these models set up)
        $cashBook = Receipt::all(); 
        $invoices = Invoice::all();
        $vouchers = PaymentVoucher::where('status', 'approved')->get();

        // 2. Initialize Report
        $report = [
            'ZMW_TOTAL' => [],
            'ADVANCES'  => 0,
            'BBF'       => 0
        ];

        for ($i = 1; $i <= 4; $i++) {
            $report['ZMW_TOTAL']["Q$i"] = [
                'received_comm' => 0, 
                'invoiced_comm' => 0, 
                'expenses'      => 0
            ];
        }

        // 3. Process Cash Book
        foreach ($cashBook as $row) {
            $date = Carbon::parse($row->receipt_date);
            $amtZmw = $this->toZMW($row->rib_commission_received, $row->policy_currency, $conversionRate);

            if ($date->lt($startOfYear)) {
                $report['BBF'] += $amtZmw;
            } elseif ($date->gt($endOfYear)) {
                $report['ADVANCES'] += $amtZmw;
            } else {
                $q = 'Q' . ceil($date->month / 3);
                $report['ZMW_TOTAL'][$q]['received_comm'] += $amtZmw;
            }
        }

        // 4. Process Invoices
        foreach ($invoices as $row) {
            $date = Carbon::parse($row->policy_start_date);
            if ($date->between($startOfYear, $endOfYear)) {
                $q = 'Q' . ceil($date->month / 3);
                $report['ZMW_TOTAL'][$q]['invoiced_comm'] += $this->toZMW($row->commission_amount, $row->policy_currency, $conversionRate);
            }
        }

        // 5. Process Vouchers
        foreach ($vouchers as $row) {
            $date = Carbon::parse($row->created_at);
            if ($date->between($startOfYear, $endOfYear)) {
                $q = 'Q' . ceil($date->month / 3);
                $report['ZMW_TOTAL'][$q]['expenses'] += $this->toZMW($row->amount, $row->currency, $conversionRate);
            }
        }

        return view('finance.income', compact('report', 'targetYear', 'conversionRate'));
    }

    private function toZMW($amount, $currency, $rate)
    {
        return (strtoupper($currency) === 'USD') ? ($amount * $rate) : $amount;
    }

// //LEDGER ACCOUNTS
// public function ledgerAccounts()
//     {
//         // 1. Fetch data from models
//         $invoices = Invoice::all();
//         $cashBook = Receipt::all();
//         $remittances = InsurerRemittance::all();

//         // 2. Initialize Report Structure
//         $ledgers = [
//             'clients' => ['ZMW' => [], 'USD' => []], 
//             'insurers' => ['ZMW' => [], 'USD' => []]
//         ];

//         // 3. Process Invoices (Debits for Clients, Credits for Insurers)
//         foreach ($invoices as $inv) {
//             $curr = strtoupper($inv->policy_currency);
            
//             // Client Side
//             $ledgers['clients'][$curr][$inv->client_name][] = [
//                 'date' => $inv->created_at, 'ref' => $inv->slip_number, 
//                 'desc' => "Invoice: " . $inv->policy_name, 'dr' => (float)$inv->gross_premium, 'cr' => 0
//             ];
            
//             // Insurer Side
//             $ledgers['insurers'][$curr][$inv->insurer][] = [
//                 'date' => $inv->created_at, 'ref' => $inv->slip_number, 
//                 'desc' => "Premium: " . $inv->policy_name, 'dr' => 0, 'cr' => (float)$inv->insurer_premium
//             ];
//         }

//         // 4. Process Cash Book (Credits for Clients - they paid us)
//         foreach ($cashBook as $cb) {
//             $curr = strtoupper($cb->policy_currency);
//             $ledgers['clients'][$curr][$cb->client_name][] = [
//                 'date' => $cb->receipt_date, 'ref' => $cb->receipt_number, 
//                 'desc' => "Payment Received", 'dr' => 0, 'cr' => (float)$cb->amount_paid
//             ];
//         }

//         // 5. Process Remittances (Debits for Insurers - we paid them)
//         foreach ($remittances as $rem) {
//             $curr = strtoupper($rem->currency);
//             $ledgers['insurers'][$curr][$rem->insurer_name][] = [
//                 'date' => $rem->created_at, 'ref' => $rem->remittance_no, 
//                 'desc' => "Remittance to Insurer", 'dr' => (float)$rem->amount_paid, 'cr' => 0
//             ];
//         }

//         // 6. Sort transactions by date for each entity
//         foreach (['clients', 'insurers'] as $type) {
//             foreach ($ledgers[$type] as $curr => &$entities) {
//                 foreach ($entities as $name => &$txns) {
//                     usort($txns, function($a, $b) {
//                         return strtotime($a['date']) <=> strtotime($b['date']);
//                     });
//                 }
//                 ksort($entities); // Sort names alphabetically
//             }
//         }

//         return view('finance.ledger', compact('ledgers'));
//     }


public function ledgerAccounts()
{
    // 1. Fetch data from models
    $invoices = Invoice::all();
    $cashBook = Receipt::all();
    $remittances = InsurerRemittance::all();

    // 2. Initialize Report Structure
    $ledgers = [
        'clients' => ['ZMW' => [], 'USD' => []], 
        'insurers' => ['ZMW' => [], 'USD' => []]
    ];

    // 3. Process Invoices (Debits for Clients, Credits for Insurers unless Cancelled)
    foreach ($invoices as $inv) {
        $curr = strtoupper($inv->policy_currency);
        
        // Skip or ignore if the currency doesn't match predefined structures
        if (!isset($ledgers['clients'][$curr])) continue;

        // Check if invoice is cancelled
        $isCancelled = (strtolower($inv->invoice_status) === 'Cancelled');

        // Client Side
        $ledgers['clients'][$curr][$inv->client_name][] = [
            'date' => $inv->created_at, 
            'ref'  => $inv->slip_number, 
            'desc' => $isCancelled ? "Invoice Cancelled: " . $inv->policy_name : "Invoice: " . $inv->policy_name, 
            'dr'   => $isCancelled ? 0 : (float)$inv->gross_premium, 
            'cr'   => $isCancelled ? (float)$inv->gross_premium : 0 // Reversed to Credit side
        ];
        
        // Insurer Side
        $ledgers['insurers'][$curr][$inv->insurer][] = [
            'date' => $inv->created_at, 
            'ref'  => $inv->slip_number, 
            'desc' => $isCancelled ? "Premium Cancelled: " . $inv->policy_name : "Premium: " . $inv->policy_name, 
            'dr'   => $isCancelled ? (float)$inv->insurer_premium : 0, // Reversed to Debit side
            'cr'   => $isCancelled ? 0 : (float)$inv->insurer_premium
        ];
    }

    // 4. Process Cash Book (Credits for Clients - reversed to Debit if Cancelled)
    // foreach ($cashBook as $cb) {
    //     $curr = strtoupper($cb->policy_currency);
        
    //     if (!isset($ledgers['clients'][$curr])) continue;

    //     // Check if receipt is cancelled/reversed
    //     $isCancelled = (strtolower($cb->Status) === 'cancelled');

    //     $ledgers['clients'][$curr][$cb->client_name][] = [
    //         'date' => $cb->receipt_date, 
    //         'ref'  => $cb->receipt_number, 
    //         'desc' => $isCancelled ? "Payment Received (Reversed/Cancelled)" : "Payment Received", 
    //         'dr'   => $isCancelled ? (float)$cb->amount_paid : 0, // Reversed back to Debit side
    //         'cr'   => $isCancelled ? 0 : (float)$cb->amount_paid
    //     ];
    // }

    // 4. Process Cash Book (Credits for Clients - reversed to Debit if Cancelled)
    foreach ($cashBook as $cb) {
        $curr = strtoupper($cb->policy_currency);
        
        if (!isset($ledgers['clients'][$curr])) continue;

        // Check if receipt is cancelled/reversed
        $isCancelled = (strtolower($cb->status) === 'Cancelled');

        // FIX: Swapped $cb->amount_paid with $cb->gross_amount_received to match your database schema
        $receiptAmount = (float)$cb->gross_amount_received;

        $ledgers['clients'][$curr][$cb->client_name][] = [
            'date' => $cb->receipt_date, 
            'ref'  => $cb->receipt_number, 
            'desc' => $isCancelled ? "Payment Received (Reversed/Cancelled)" : "Payment Received", 
            'dr'   => $isCancelled ? $receiptAmount : 0, 
            'cr'   => $isCancelled ? 0 : $receiptAmount
        ];
    }

    // 5. Process Remittances (Debits for Insurers - we paid them)
    foreach ($remittances as $rem) {
        $curr = strtoupper($rem->currency);
        
        if (!isset($ledgers['insurers'][$curr])) continue;

        $ledgers['insurers'][$curr][$rem->insurer_name][] = [
            'date' => $rem->created_at, 
            'ref'  => $rem->remittance_no, 
            'desc' => "Remittance to Insurer", 
            'dr'   => (float)$rem->amount_paid, 
            'cr'   => 0
        ];
    }

    // 6. Sort transactions by date for each entity
    foreach (['clients', 'insurers'] as $type) {
        foreach ($ledgers[$type] as $curr => &$entities) {
            foreach ($entities as $name => &$txns) {
                usort($txns, function($a, $b) {
                    return strtotime($a['date']) <=> strtotime($b['date']);
                });
            }
            ksort($entities); // Sort names alphabetically
        }
    }

    return view('finance.ledger', compact('ledgers'));
}


      // POST JOURNAL VOUCHERS
    public function postJournalVoucher(Request $request)
    {
        // 1. Validation & Accounting Balance Check
        $request->validate([
            'jv_number' => 'required|string',
            'jv_date'   => 'required|date',
            'account.*' => 'required', // ensures each row has an account
        ]);

        $total_dr = array_sum($request->dr);
        $total_cr = array_sum($request->cr);

        if (round($total_dr, 2) !== round($total_cr, 2)) {
            return back()->withInput()->with('msg', "Error: Journal out of balance. DR: $total_dr, CR: $total_cr");
        }

        try {
            // 2. Execute Transaction
            DB::transaction(function () use ($request) {
                
                // Insert Header
                $voucher = JournalVoucher::create([
                    'jv_number'   => $request->jv_number,
                    'jv_date'     => $request->jv_date,
                    'description' => $request->main_narration,
                    'currency'    => $request->currency,
                    'status'      => 'posted'
                ]);

                // Insert Rows (Line Entries)
                foreach ($request->account as $i => $acc_name) {
                    $dr_val = (float)($request->dr[$i] ?? 0);
                    $cr_val = (float)($request->cr[$i] ?? 0);

                    if ($dr_val > 0 || $cr_val > 0) {
                        $voucher->entries()->create([
                            'account_name' => $acc_name,
                            'debit'        => $dr_val,
                            'credit'       => $cr_val,
                        ]);
                    }
                }
            });

            return redirect()->route('ledger.index')->with('msg', 'Journal Voucher Posted Successfully!');

        } catch (\Exception $e) {
            return back()->with('msg', 'Post Failed: ' . $e->getMessage());
        }
    }

    // BALANCE SHEET REPORT
    /**
 * Fetches consolidated Balance Sheet data for a specific year-end
 * 
 * @param string $asOfDate Format 'YYYY-MM-DD'
 * @return \Illuminate\Support\Collection
 */
public function getBalanceSheetReport($asOfDate) 
{
    $sql = "
        SELECT 
            'Current Asset' AS category,
            'Accounts Receivable' AS description,
            IFNULL(SUM(commission_amount), 0) AS total_amount
        FROM invoices
        WHERE policy_start_date <= :date1

        UNION ALL

        SELECT 
            'Current Asset' AS category,
            'Cash & Bank' AS description,
            IFNULL(SUM(rib_commission_received), 0) AS total_amount
        FROM cash_book
        WHERE receipt_date <= :date2

        UNION ALL

        SELECT 
            'Fixed Asset' AS category,
            asset_name AS description,
            IFNULL(SUM(current_value), 0) AS total_amount
        FROM fixed_assets
        GROUP BY asset_name

        UNION ALL

        SELECT 
            'Current Liability' AS category,
            'Premiums Received in Advance' AS description,
            IFNULL(SUM(rib_commission_received), 0) AS total_amount
        FROM cash_book
        WHERE receipt_date <= :date3 
        AND policy_start_date > :date4

        UNION ALL

        SELECT 
            'Long-term Liability' AS category,
            loan_name AS description,
            IFNULL(SUM(balance_owed), 0) AS total_amount
        FROM liabilities
        WHERE liability_type = 'Long-term'
        GROUP BY loan_name
    ";

    // Use named parameters for clarity or sequential [?] placeholders
    $reportData = DB::select($sql, [
        'date1' => $asOfDate,
        'date2' => $asOfDate,
        'date3' => $asOfDate,
        'date4' => $asOfDate
    ]);

    return collect($reportData);
}


// BALANCE SHEET AS OF DATE
/**
 * Fetches the General Ledger Balance for all accounts up to a specific date.
 * 
 * @param string $asOfDate Format 'YYYY-MM-DD'
 * @return \Illuminate\Support\Collection
 */
public function getGLBalanceSheet($asOfDate) 
{
    return DB::table('chart_of_accounts as a')
        ->select(
            'a.category',
            'a.sub_category',
            'a.account_name',
            DB::raw('IFNULL(SUM(l.debit - l.credit), 0) AS balance')
        )
        ->leftJoin('journal_entry_lines as l', 'a.id', '=', 'l.account_id')
        // --- PUT THE ADVANCED JOIN HERE ---
        ->leftJoin('journal_entries as j', function($join) use ($asOfDate) {
            $join->on('l.journal_entry_id', '=', 'j.id')
                 ->where('j.transaction_date', '<=', $asOfDate);
        })
        ->leftJoin('journal_entries as j', 'l.journal_entry_id', '=', 'j.id')
        ->where('j.transaction_date', '<=', $asOfDate)
        ->groupBy('a.id', 'a.category', 'a.sub_category', 'a.account_name')
        ->orderBy('a.category', 'asc')
        ->orderBy('a.sub_category', 'desc')
        ->get();
}


// CHART OF ACCOUNTS
public function storeAccount(Request $request)
{
    // 1. Validation
    $validatedData = $request->validate([
        'account_code' => 'required|unique:chart_of_accounts,account_code',
        'account_name' => 'required|string|max:255',
        'category'     => 'required|string',
        'sub_category' => 'nullable|string',
    ]);

    // 2. Save using Eloquent
    try {
        ChartOfAccount::create($validatedData);

        return back()->with('msg', 'Account successfully added to the Chart of Accounts.');
    } catch (\Exception $e) {
        return back()->with('msg', 'Error saving account: ' . $e->getMessage());
    }
}

// REGISTER FIXED ASSETS
public function storeAsset(Request $request)
{
    // 1. Validation
    $validated = $request->validate([
        'asset_name'          => 'required|string|max:255',
        'cost_price'          => 'required|numeric|min:0',
        'current_value'       => 'required|numeric|min:0',
        'purchase_date'       => 'required|date',
        'depreciation_method' => 'required|string',
    ]);

    // 2. Save using Eloquent
    try {
        FixedAsset::create($validated);

        return redirect()->back()->with('msg', 'Fixed Asset registered successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->with('msg', 'Error: Could not save asset. ' . $e->getMessage());
    }
}

// DEPRECIATION CALCULATION
public function getBookValueAttribute() {
    // Logic for Straight Line or Reducing Balance could go here
    return $this->cost_price - $this->accumulated_depreciation;
}


//LIABILITIES REGISTRATION
public function storeLiability(Request $request)
{
    // 1. Validation
    $validated = $request->validate([
        'description'    => 'required|string|max:255',
        'total_amount'   => 'required|numeric|min:0',
        'balance_owed'   => 'required|numeric|min:0',
        'liability_type' => 'required|in:Current,Long-term', // Restricts to valid types
        'due_date'       => 'required|date',
    ]);

    // 2. Save
    try {
        Liability::create($validated);

        return redirect()->back()->with('msg', 'Liability recorded successfully.');
    } catch (\Exception $e) {
        return redirect()->back()->with('msg', 'Error: ' . $e->getMessage());
    }
}

    // LIABILITY REPORT
    public function showLiabilitiesReport()
    {
        // THIS IS WHERE THE LINE GOES:
        // It uses the 'scopeLongTerm' we defined in the Liability model
        $longTermDebt = Liability::longTerm()->get();

        // Pass the data to the view
        return view('reports.liabilities', compact('longTermDebt'));
    }



}