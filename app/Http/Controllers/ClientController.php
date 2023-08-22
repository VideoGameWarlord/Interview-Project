<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;

class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clients = Client::where('user_id', auth()->id())->get();
        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('clients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required|email',
            // Add other validation rules as needed
        ]);
    
        $client = new Client($request->all());
        $client->user_id = auth()->id();
        $client->save();
    
        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Client $client)
    {
        $this->authorizeClient($client);
        return view('clients.edit', compact('client'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Client $client)
    {
        $this->authorizeClient($client);

        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:Male,Female',
            'ip_address' => 'nullable|ip',
        ]);

        $client->update($request->all());

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Client $client)
    {
        $this->authorizeClient($client);
        $client->delete();
        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }

    protected function authorizeClient(Client $client)
    {
        if ($client->user_id !== auth()->id()) {
            abort(403, 'Unauthorized action.');
        }
    }

    public function showImportForm()
    {
        return view('clients.import');
    }

    public function importClients(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:csv,txt|max:10240', // 10MB Max
        ]);
    
        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator);
        }
    
        $file = $request->file('file');
        $path = $file->getRealPath();
        $rows = array_map('str_getcsv', file($path));
        $header = array_shift($rows);
    
        foreach ($rows as $row) {
            $clientData = array_combine($header, $row);
            $clientData['user_id'] = auth()->id(); // Assign to the authenticated user
            Client::create($clientData);
        }

        return redirect()->route('clients.index')->with('success', 'Clients imported successfully.');
    }

    public function exportClients()
    {
        $userId = auth()->id();
        $query = "SELECT clients.*, IF(clients.username IS NOT NULL, 'yes', 'no') as has_user_account
                FROM clients
                WHERE clients.user_id = :userId";
                
        $clients = DB::select($query, ['userId' => $userId]);

        $headers = array(
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=clients.csv",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        );

        return response()->stream(
            function () use ($clients) {
                $handle = fopen('php://output', 'w');
    
                // Write the CSV header
                fputcsv($handle, ['id', 'first_name', 'last_name', 'email', 'gender', 'ip_address', 'has_user_account']);
    
                // Write the client data
                foreach ($clients as $client) {
                    fputcsv($handle, [
                        $client->id,
                        $client->first_name,
                        $client->last_name,
                        $client->email,
                        $client->gender,
                        $client->ip_address,
                        $client->has_user_account // This assumes the query includes the has_user_account field
                    ]);
                }
    
                fclose($handle); // Close the handle only once
            },
            200,
            $headers
        );
    }

    public function assignUser(Client $client)
    {
        // Check if the client already has a username
        if ($client->username) {
            return redirect()->route('clients.index')->with('error', 'User already assigned.');
        }

        // Creates a username
        $username = strtolower($client->first_name . $client->last_name);

        // Set a generic password
        $password = Hash::make('password');

        // Update the client's username and password
        $client->username = $username;
        $client->password = $password;
        $client->save();
    
        return redirect()->route('clients.index')->with('success', 'User assigned successfully.');
    }
}
