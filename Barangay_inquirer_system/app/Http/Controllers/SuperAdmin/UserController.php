<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->has('role') && $request->role !== 'all') {
            // whitelist acceptable roles to prevent misuse
            $allowed = ['resident','admin','super_admin'];
            if (in_array($request->role, $allowed, true)) {
                $query->where('role', $request->role);
            }
        }

        $users = $query->paginate(15);
        
        return view('superadmin.users.index', compact('users'));
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        return view('superadmin.users.show', compact('user'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('superadmin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'sometimes|in:resident,admin,super_admin',
        ]);

        $user->update($validated);

        // log update
        \App\Models\Activity::log("User updated: {$user->name} (ID #{$user->id})");

        return redirect()->route('superadmin.users.show', $user)
            ->with('success', 'User updated successfully');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        $user->delete();

        // log deletion
        \App\Models\Activity::log("User deleted: {$user->name} (ID #{$user->id})");

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User deleted successfully');
    }
}
