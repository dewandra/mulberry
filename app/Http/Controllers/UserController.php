<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::query()
            ->with(['creator', 'updater'])
            ->when($request->search, function ($query, $search) {
                $query->where('company_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('is_active', $request->boolean('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Clients/Index', [
            'clients' => $clients,
            'filters' => $request->only(['search', 'status']),
        ]);
    }

    public function create()
    {
        return Inertia::render('Clients/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        $validated['created_by'] = $request->user()->id;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $clientId = Str::uuid();
            $logo = $request->file('logo');
            $filename = $logo->getClientOriginalName();
            $path = $logo->storeAs("client-logos/{$clientId}", $filename, 'public');
            
            $validated['id'] = $clientId;
            $validated['logo_url'] = $path;
            $validated['logo_filename'] = $filename;
        }

        Client::create($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client created successfully.');
    }

    public function edit(Client $client)
    {
        return Inertia::render('Clients/Edit', [
            'client' => $client,
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg,webp|max:2048',
            'is_active' => 'boolean',
        ]);

        $validated['updated_by'] = $request->user()->id;

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($client->logo_url) {
                Storage::disk('public')->delete($client->logo_url);
            }

            $logo = $request->file('logo');
            $filename = $logo->getClientOriginalName();
            $path = $logo->storeAs("client-logos/{$client->id}", $filename, 'public');
            
            $validated['logo_url'] = $path;
            $validated['logo_filename'] = $filename;
        }

        $client->update($validated);

        return redirect()->route('clients.index')
            ->with('success', 'Client updated successfully.');
    }

    public function destroy(Request $request, Client $client)
    {
        $client->deleted_by = $request->user()->id;
        $client->save();
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Client deleted successfully.');
    }
}