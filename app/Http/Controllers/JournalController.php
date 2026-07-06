<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Auth;

class JournalController extends Controller
{
    public function create()
    {
        // Replaces your $user->loginStatus() and user details logic
        $user = Auth::user();
        $jv_number = 'JV-' . Carbon::now()->format('YmdHis');
        $current_date = Carbon::now()->format('Y-m-d');

        return view('finance.journal_voucher', compact('user', 'jv_number', 'current_date'));
    }

    public function store(Request $request)
    {
        // Validation
        $request->validate([
            'jv_number' => 'required',
            'jv_date' => 'required|date',
            'currency' => 'required',
            'account.*' => 'required',
            'dr.*' => 'numeric',
            'cr.*' => 'numeric',
        ]);

        // Logic for $user->postJournalVoucher() goes here
        // Example: Journal::create([...]);

        return redirect()->back()->with('success', 'Journal Voucher posted successfully!');
    }

  



}
