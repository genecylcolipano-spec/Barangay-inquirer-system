<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\AdminCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of admins.
     */
    public function index()
    {
        $admins = User::where('role', 'admin')->paginate(15);
        return view('superadmin.admins.index', compact('admins'));
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        return view('superadmin.admins.create');
    }

    /**
     * Store a newly created admin in storage.
     */
    public function store(Request $request)
    {
        // Validation and creation logic
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8|confirmed',
        ]);

        $admin = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'admin',
        ]);

        // log admin creation
        \App\Models\Activity::log("New admin created: {$admin->name} (ID #{$admin->id})", 'admin_create');
        
        // Notify all super admins
        $superAdmins = User::where('role', 'super_admin')->get();
        foreach ($superAdmins as $superAdmin) {
            $superAdmin->notify(new AdminCreatedNotification($admin));
        }

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Admin created successfully');
    }

    /**
     * Display the specified admin.
     */
    public function show(User $admin)
    {
        return view('superadmin.admins.show', compact('admin'));
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(User $admin)
    {
        return view('superadmin.admins.edit', compact('admin'));
    }

    /**
     * Update the specified admin in storage.
     */
    public function update(Request $request, User $admin)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
        ]);

        $admin->update($validated);

        \App\Models\Activity::log("Admin updated: {$admin->name} (ID #{$admin->id})", 'admin_update');

        return redirect()->route('superadmin.admins.show', $admin)
            ->with('success', 'Admin updated successfully');
    }

    /**
     * Remove the specified admin from storage.
     */
    public function destroy(User $admin)
    {
        $admin->delete();

        \App\Models\Activity::log("Admin deleted: {$admin->name} (ID #{$admin->id})", 'admin_delete');

        return redirect()->route('superadmin.admins.index')
            ->with('success', 'Admin deleted successfully');
    }
}
