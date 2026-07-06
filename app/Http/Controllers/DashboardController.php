<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
   public function index()
    {
        $user = Auth::user();
        
        // 1. FETCH ACTUAL DATA
        // We use get() to retrieve the collection so your foreach logic remains valid
        $placement_infor = \App\Models\PlacingSlip::all(); 
        $claim_infor = \App\Models\Claim::all();

        // 2. DATA CONSOLIDATION (Slips & Premiums)
        $tempData = [];
        $tempPremium = [];
        $totalSlips = $placement_infor->count();

        foreach ($placement_infor as $placement) {
            // Adjust these keys if your database column names are different
            $premium = (float)($placement->gross_premium ?? 0);
            $currency = strtoupper($placement->policy_currency ?? 'ZMW');
            $ins = $placement->insurer ?? 'Unknown Insurer';

            // Grouping for Policy Doughnut
            if (!isset($tempData[$ins])) {
                $tempData[$ins] = ['insurer' => $ins, 'count' => 0];
            }
            $tempData[$ins]['count']++;

            // Grouping for Premium Bars
            if (!isset($tempPremium[$ins])) {
                $tempPremium[$ins] = ['insurer' => $ins, 'zmw_total' => 0, 'usd_total' => 0];
            }
            
            if ($currency === 'ZMW') { 
                $tempPremium[$ins]['zmw_total'] += $premium; 
            } elseif ($currency === 'USD') { 
                $tempPremium[$ins]['usd_total'] += $premium; 
            }
        }

        // 3. CLAIMS LOGIC
        $claimsPending = 0;
        $claimsSettled = 0;
        foreach ($claim_infor as $claim) {
            $status = strtolower($claim->claim_status ?? '');
            if (str_contains($status, 'pending')) $claimsPending++;
            if ($status === 'settled') $claimsSettled++;
        }

        // 4. ADMIN ANALYTICS & INITIALS
        $adminData = [];
        if ($user->role === 'Admin') {
            $adminData['siteVisits'] = [
                ['day' => 'Mon', 'visits' => 45], ['day' => 'Tue', 'visits' => 52],
                ['day' => 'Wed', 'visits' => 38], ['day' => 'Thu', 'visits' => 65],
                ['day' => 'Fri', 'visits' => 48], ['day' => 'Sat', 'visits' => 24],
                ['day' => 'Sun', 'visits' => 15]
            ];
            // ... rest of your mock recentActions
        }

            // 4. ADMIN ANALYTICS & INITIALS
            // Initialize keys with empty arrays so the Blade file doesn't crash for non-admins
            $adminData = [
                'siteVisits' => [],
                'recentActions' => []
            ];

            if ($user->role === 'Admin') {
                $adminData['siteVisits'] = [
                    ['day' => 'Mon', 'visits' => 45], 
                    ['day' => 'Tue', 'visits' => 52],
                    ['day' => 'Wed', 'visits' => 38], 
                    ['day' => 'Thu', 'visits' => 65],
                    ['day' => 'Fri', 'visits' => 48], 
                    ['day' => 'Sat', 'visits' => 24],
                    ['day' => 'Sun', 'visits' => 15]
                ];

                $adminData['recentActions'] = [
                    [
                        'task' => 'System Backup Completed',
                        'ref'  => 'SYS-7721', // Added the missing 'ref' key
                        'time' => 'Just now', 
                        'color' => 'text-success'
                    ],
                    [
                        'task' => 'New User Registered',
                        'ref'  => 'USR-9902',
                        'time' => '15 mins ago', 
                        'color' => 'text-primary'
                    ],
                    [
                        'task' => 'Large Claim Submitted',
                        'ref'  => 'CLM-4431',
                        'time' => '1 hour ago', 
                        'color' => 'text-warning'
                    ],
                    [
                        'task' => 'Policy Renewal Processed',
                        'ref'  => 'POL-1109',
                        'time' => '2 hours ago', 
                        'color' => 'text-info'
                    ],
                    [
                        'task' => 'Security Audit Logged',
                        'ref'  => 'AUD-5562',
                        'time' => 'Yesterday', 
                        'color' => 'text-dark'
                    ],
                ];
            }

        $initials = strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1));

        return view('dashboard', [
        'userDetail'    => $user,
        'user'          => $user,
        'initials'      => $initials,
        'jsData'        => array_values($tempData),
        'premiumData'   => array_values($tempPremium),
        'totalSlips'    => $totalSlips,
        'claimsPending' => $claimsPending,
        'claimsSettled' => $claimsSettled,
        'adminData'     => $adminData, 
    ]);
    }

    // ADD THESE TWO LINES TO MATCH YOUR BLADE FILE:
        // 'placements'    => $placement_infor, 
        // 'activeClaims'  => $claim_infor,    
}