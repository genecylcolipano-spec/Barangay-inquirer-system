<?php

namespace App\Http\Controllers\Resident;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        return view('resident.settings', compact('user'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
        ]);

        auth()->user()->update($validated);

        return redirect()->route('resident.settings')
            ->with('success', 'Profile updated successfully!');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', function ($attribute, $value, $fail) {
                if (!Hash::check($value, auth()->user()->password)) {
                    $fail('The current password is incorrect.');
                }
            }],
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password'])
        ]);

        return redirect()->route('resident.settings')
            ->with('success', 'Password changed successfully!');
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = auth()->user();

        // Delete old photo if exists
        if ($user->profile_photo && Storage::disk('public')->exists('uploads/profiles/' . $user->profile_photo)) {
            Storage::disk('public')->delete('uploads/profiles/' . $user->profile_photo);
        }

        // Store new photo
        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $extension = strtolower($file->getClientOriginalExtension());
            $filename = 'resident_' . auth()->id() . '_' . time() . '_' . Str::random(12) . '.' . $extension;
            $path = $file->storeAs('uploads/profiles', $filename, 'public');

            if (! $path) {
                return redirect()->route('resident.settings')->with('error', 'Unable to upload profile photo.');
            }

            // Update user record
            $user->update(['profile_photo' => $filename]);
            
            // Refresh the authenticated user to reflect changes
            auth()->guard('web')->setUser($user->fresh());
        }

        return redirect()->route('resident.settings')
            ->with('success', 'Profile photo updated successfully!');
    }
}
