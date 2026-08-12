<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'super_admin');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->latest()->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function show(Request $request, User $user)
    {
        // Check if user exists
        if (!$user) {
            return abort(404, 'User not found.');
        }

        // Prevent regular admins from viewing super admin accounts
        if ($user->role === 'super_admin') {
            return abort(403, 'Unauthorized access to this user account.');
        }

        $user->load('documentRequests');
        $userRequests = $user->documentRequests()->latest()->get();

        // For AJAX requests, return only the content that needs to be updated
        if ($request->ajax()) {
            return view('admin.users.show', compact('user', 'userRequests'));
        }

        return view('admin.users.show', compact('user', 'userRequests'));
    }

    public function destroy(User $user)
    {
        // Prevent deleting the current user
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        // Prevent regular admins from deleting super admin accounts
        if ($user->role === 'super_admin') {
            return back()->with('error', 'You cannot delete super admin accounts.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'User deleted successfully.');
    }
}
