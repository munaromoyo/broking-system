<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ClientController extends Controller
{


    // Add this method

public function index()
    {
        // 1. Fetch the clients from the database
        $clients = Client::all(); 

        // 2. Pass the variable to the view using compact()
        return view('clients.list', compact('clients'));
    }


    public function update(Request $request, $id)
    {
        // 1. Validate the incoming data
        $validated = $request->validate([
            'client_name'        => 'required|string|max:255',
            'physical_address'   => 'nullable|string',
            'postal_address'     => 'nullable|string',
            'contact_number'     => 'nullable|string',
            'email_address'      => 'nullable|email',
            'nature_of_business' => 'nullable|string',
            'client_type'        => 'nullable|string',
        ]);

        // 2. Find the client by ID
        $client = Client::findOrFail($id);

        // 3. Format data (like your ucwords logic)
        $formattedName = ucwords(strtolower(trim($request->client_name)));

        // 4. Update the record
        $client->update([
            'client_name'        => $formattedName,
            'physical_address'   => $request->physical_address,
            'postal_address'     => $request->postal_address,
            'contact_number'     => $request->contact_number,
            'email_address'      => $request->email_address,
            'nature_of_business' => $request->nature_of_business,
            'client_type'        => $request->client_type,
            'updated_by'         => Auth::user()->name, // Replaces $_SESSION["name"]
            // 'updated_at' is handled automatically by Laravel if timestamps are on
        ]);

        // 5. Redirect with a success message (Session Flash)
        return redirect()->route('clients.index')
                         ->with('status', 'Client updated successfully!');
    }


    public function edit()
{
    $clients = Client::all(); // Fetches all from 'client_register'
    return view('clients.edit', compact('clients'));
}


public function store(Request $request)
    {
        // 1. Validate & Check if exists (unique:table,column)
        $request->validate([
            'client_name' => 'required|string|unique:client_register,client_name',
            'email_address' => 'required|email',
            'contact_number' => 'required',
            // Add other validation rules as needed
        ], [
            'client_name.unique' => 'Client already registered.'
        ]);

        // 2. Format the name
        $formattedName = ucwords(strtolower(trim($request->client_name)));

        // 3. Create the record
        Client::create([
            'user'               => Auth::user()->name, // Replaces $_SESSION["name"]
            'client_name'        => $formattedName,
            'physical_address'   => $request->physical_address,
            'postal_address'     => $request->postal_address,
            'nature_of_business' => $request->nature_of_business,
            'client_type'        => $request->client_type,
            'email_address'      => $request->email_address,
            'contact_number'     => $request->contact_number,
        ]);

        return redirect()->back()->with('success', 'Client successfully registered');
    }


}