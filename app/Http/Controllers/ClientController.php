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
        return redirect()->route('clients.list')
                         ->with('status', 'Client updated successfully!');
    }

// EDIT CLIENT
//     public function edit()
// {
//     $clients = Client::all(); // Fetches all from 'client_register'
//     return view('clients.edit', compact('clients'));
// }

// public function edit($id)
// {
//     // 1. Find the client by ID (or throw a 404 if not found)
//     $client = Client::findOrFail($id);

//     // 2. Pass the $client variable to your edit blade view
//     return view('clients.edit', compact('client'));
// }

public function edit($id)
{
    // 1. Get the current active client for the form inputs
    $client = Client::findOrFail($id);

    // 2. Fetch all clients to populate the dropdown switcher
    $allClients = Client::all();

    // 3. Pass BOTH variables to your view
    return view('clients.edit', compact('client', 'allClients'));
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

    // DELETE CLIENT
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->delete();

        return redirect()->back()->with('status', 'Client successfully deleted from the registry.');
    }

    // 1. Display the Trash Bin View
public function trash()
{
    // Fetches ONLY soft-deleted clients
    $deletedClients = Client::onlyTrashed()->get();
    
    return view('clients.trash', compact('deletedClients'));
}

// 2. Restore a Soft-Deleted Client
public function restore($id)
{
    $client = Client::onlyTrashed()->findOrFail($id);
    $client->restore(); // Removes the deleted_at timestamp

    return redirect()->route('clients.list')
        ->with('status', "Client '{$client->client_name}' has been successfully restored.");
}

// 3. Permanently Delete a Client
public function forceDelete($id)
{
    $client = Client::onlyTrashed()->findOrFail($id);
    $client->forceDelete(); // Wipes the row completely from the database

    return redirect()->back()
        ->with('status', 'Client profile has been permanently deleted.');
}


}