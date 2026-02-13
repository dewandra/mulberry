<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->with(['client'])
            ->when($request->search, function ($query, $search) {
                $query->where('full_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
            })
            ->when($request->role, function ($query, $role) {
                $query->where('role', $role);
            })
            ->when($request->client_id, function ($query, $clientId) {
                $query->where('client_id', $clientId);
            })
            ->when($request->status !== null, function ($query) use ($request) {
                $query->where('is_active', $request->boolean('status'));
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $clients = Client::active()->get(['id', 'company_name']);

        return Inertia::render('Users/Index', [
            'users' => $users,
            'clients' => $clients,
            'filters' => $request->only(['search', 'role', 'client_id', 'status']),
        ]);
    }

    public function create()
    {
        $clients = Client::active()->get(['id', 'company_name']);
        
        return Inertia::render('Users/Create', [
            'clients' => $clients,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => ['required', Rule::in(['super_admin', 'admin', 'client', 'pic'])],
            'client_id' => 'nullable|uuid|exists:clients,id',
            'is_active' => 'boolean',
        ]);

        // Validate client_id required for client and pic roles
        if (in_array($validated['role'], ['client', 'pic']) && empty($validated['client_id'])) {
            return back()->withErrors(['client_id' => 'Client is required for this role.']);
        }

        $validated['password'] = Hash::make($validated['password']);
        $validated['created_by'] = $request->user()->id;

        User::create($validated);

        return redirect()->route('users.index')
            ->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        $clients = Client::active()->get(['id', 'company_name']);
        
        return Inertia::render('Users/Edit', [
            'user' => $user->load('client'),
            'clients' => $clients,
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
            'role' => ['required', Rule::in(['super_admin', 'admin', 'client', 'pic'])],
            'client_id' => 'nullable|uuid|exists:clients,id',
            'is_active' => 'boolean',
        ]);

        // Validate client_id required for client and pic roles
        if (in_array($validated['role'], ['client', 'pic']) && empty($validated['client_id'])) {
            return back()->withErrors(['client_id' => 'Client is required for this role.']);
        }

        // Prevent users from changing their own role
        if ($user->id === $request->user()->id && $user->role !== $validated['role']) {
            return back()->withErrors(['role' => 'You cannot change your own role.']);
        }

        // Prevent users from deactivating themselves
        if ($user->id === $request->user()->id && !$validated['is_active']) {
            return back()->withErrors(['is_active' => 'You cannot deactivate your own account.']);
        }

        // Only update password if provided
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['updated_by'] = $request->user()->id;

        $user->update($validated);

        return redirect()->route('users.index')
            ->with('success', 'User updated successfully.');
    }

    public function destroy(Request $request, User $user)
    {
        // Prevent users from deleting themselves
        if ($user->id === $request->user()->id) {
            return back()->withErrors(['error' => 'You cannot delete your own account.']);
        }

        $user->deleted_by = $request->user()->id;
        $user->save();
        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}