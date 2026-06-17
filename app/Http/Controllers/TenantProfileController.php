<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class TenantProfileController extends Controller
{
    /**
     * Show the profile edit page
     */
    public function edit()
    {
        // Fetch the currently logged-in administrator
        $user = auth()->user(); 

        $tenantKey = tenant('id'); // e.g., "rib"
        
        $logoPath = "storage/tenants/{$tenantKey}/logo.jpg";
        $logoPreview = File::exists(public_path($logoPath)) ? asset($logoPath) : null;

        // Pass the $user data payload over to the blade template
        return view('admin.profile.edit', compact('user', 'logoPreview'));
    }

    /**
     * Update the user table data and save the uploaded brand asset
     */
    public function update(Request $request)
{
    $request->validate([
        'company'          => 'required|string|max:255',
        'company_tpin'     => 'nullable|string|max:50', // Added TPIN validation rule
        'physical_address' => 'nullable|string|max:500',
        'tel_number'       => 'nullable|string|max:50',
        'email'            => 'required|email|max:100',
        'logo'             => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $user = auth()->user();

    // Update the user table details natively
    $user->update([
        'company'          => $request->company,
        'company_tpin'     => $request->company_tpin, // Added TPIN save mapping
        'physical_address' => $request->physical_address,
        'tel_number'       => $request->tel_number,
        'email'            => $request->email,
    ]);

    // Handle Tenant Brand Asset File Generation
    if ($request->hasFile('logo')) {
        $tenantId = tenant('id');
        $targetDirectory = storage_path("app/public/tenants/{$tenantId}");
        $targetFile = $targetDirectory . '/logo.jpg';

        if (!File::isDirectory($targetDirectory)) {
            File::makeDirectory($targetDirectory, 0755, true, true);
        }

        if (File::exists($targetFile)) {
            File::delete($targetFile);
        }

        $request->file('logo')->move($targetDirectory, 'logo.jpg');
    }

    return redirect()->back()->with('success', 'Profile identity updated successfully.');
}


}