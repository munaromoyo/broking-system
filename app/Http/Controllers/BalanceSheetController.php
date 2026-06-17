<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CashBook;
use App\Models\Invoice;
use App\Models\PaymentVoucher;
use App\Models\FixedAsset; // Create these models
use App\Models\Liability;  // Create these models
use App\Models\ChartOfAccount;
use Carbon\Carbon;

class BalanceSheetController extends Controller
{
    public function index(Request $request)
    {
        $targetYear = $request->get('year', date('Y'));
        $conversionRate = (float) $request->get('rate', 25.00);
        $asOfDate = Carbon::create($targetYear, 12, 31)->endOfDay();

        // 1. Fetch Raw Data
        $cashBook = CashBook::all();
        $invoices = Invoice::all();
        $vouchers = PaymentVoucher::where('status', 'approved')->get();

        // 2. Report Structure
        $bs = [
            'ASSETS' => [
                'FIXED' => ['Equipment & Furniture' => 0, 'Motor Vehicles' => 0, 'Long-term Investments' => 0],
                'CURRENT' => ['Cash & Bank Balances' => 0, 'Accounts Receivable' => 0, 'Pre-payments & Advances' => 0]
            ],
            'LIABILITIES' => [
                'CURRENT' => ['Premiums Received in Advance' => 0, 'Accounts Payable' => 0, 'Accrued Expenses' => 0],
                'LONG_TERM' => ['Bank Loans' => 0]
            ],
            'EQUITY' => ['Retained Earnings' => 0]
        ];

        // 3. Logic: Assets & Cash
        foreach ($cashBook as $row) {
            $rDate = Carbon::parse($row->receipt_date);
            $amtZmw = $this->toZMW($row->rib_commission_received, $row->policy_currency, $conversionRate);
            
            if ($rDate->lte($asOfDate)) {
                $bs['ASSETS']['CURRENT']['Cash & Bank Balances'] += $amtZmw;
                if ($row->policy_start_date && Carbon::parse($row->policy_start_date)->gt($asOfDate)) {
                    $bs['LIABILITIES']['CURRENT']['Premiums Received in Advance'] += $amtZmw;
                }
            }
        }

        // 4. Logic: Receivables
        foreach ($invoices as $row) {
            if (Carbon::parse($row->policy_start_date)->lte($asOfDate)) {
                $bs['ASSETS']['CURRENT']['Accounts Receivable'] += $this->toZMW($row->commission_amount, $row->policy_currency, $conversionRate);
            }
        }
        
        $bs['ASSETS']['CURRENT']['Accounts Receivable'] = max(0, $bs['ASSETS']['CURRENT']['Accounts Receivable'] - $bs['ASSETS']['CURRENT']['Cash & Bank Balances']);

        // 5. Final Calculations
        $totalExpenses = 0;
        foreach ($vouchers as $v) {
            if (Carbon::parse($v->created_at)->lte($asOfDate)) {
                $totalExpenses += $this->toZMW($v->amount, $v->currency, $conversionRate);
            }
        }

        $totalFixed = array_sum($bs['ASSETS']['FIXED']);
        $totalCurrent = array_sum($bs['ASSETS']['CURRENT']);
        $grandTotalAssets = $totalFixed + $totalCurrent;
        
        $bs['EQUITY']['Retained Earnings'] = ($bs['ASSETS']['CURRENT']['Cash & Bank Balances'] + $totalFixed) - $totalExpenses - array_sum($bs['LIABILITIES']['CURRENT']) - array_sum($bs['LIABILITIES']['LONG_TERM']);

        return view('finance.balance-sheet', compact('bs', 'targetYear', 'conversionRate', 'grandTotalAssets'));
    }

    public function store(Request $request)
    {
        $action = $request->input('action');
        
        try {
            if ($action == 'save_account') {
                ChartOfAccount::create($request->all());
            } elseif ($action == 'save_asset') {
                FixedAsset::create($request->all());
            } elseif ($action == 'save_liability') {
                Liability::create($request->all());
            }
            return back()->with('success', 'Record saved successfully!');
        } catch (\Exception $e) {
            return back()->with('danger', 'Error: ' . $e->getMessage());
        }
    }

    private function toZMW($amount, $curr, $rate) {
        return (strtoupper($curr) === 'USD') ? ($amount * $rate) : (float)$amount;
    }
}