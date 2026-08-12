<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsController extends Controller
{
    /**
     * Show admin settings page
     */
    public function index()
    {
        $user = auth()->user();
        $settings = [
            'footer_address' => Setting::get('footer_address', 'Barangay Hall, Your City, Your Province'),
            'footer_phone' => Setting::get('footer_phone', '+63 (XXX) XXX-XXXX'),
            'footer_email' => Setting::get('footer_email', 'info@barangay.gov.ph'),
            'footer_facebook' => Setting::get('footer_facebook', '#'),
            'footer_twitter' => Setting::get('footer_twitter', '#'),
            'footer_linkedin' => Setting::get('footer_linkedin', '#'),
            'footer_instagram' => Setting::get('footer_instagram', '#'),
            'privacy_policy' => Setting::get('privacy_policy', ''),
            'terms_of_service' => Setting::get('terms_of_service', ''),
        ];
        return view('admin.settings', compact('user', 'settings'));
    }

    /**
     * Update admin profile information
     */
    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
        ]);

        auth()->user()->update($validated);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Profile updated successfully!');
    }

    /**
     * Update admin password
     */
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

        return redirect()->route('admin.settings.index')
            ->with('success', 'Password changed successfully!');
    }

    /**
     * Update admin profile photo
     */
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
            $filename = 'admin_' . auth()->id() . '_' . time() . '_' . Str::random(12) . '.' . $extension;
            $path = $file->storeAs('uploads/profiles', $filename, 'public');

            if (! $path) {
                return redirect()->route('admin.settings.index')->with('error', 'Unable to upload profile photo.');
            }

            // Update user record
            $user->update(['profile_photo' => $filename]);
            
            // Refresh the authenticated user to reflect changes
            auth()->guard('web')->setUser($user->fresh());
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Profile photo updated successfully!');
    }

    /**
     * Update footer settings
     */
    public function updateFooterSettings(Request $request)
    {
        $validated = $request->validate([
            'footer_address' => 'required|string|max:500',
            'footer_phone' => 'required|string|max:50',
            'footer_email' => 'required|email|max:255',
            'footer_facebook' => 'nullable|url|max:500',
            'footer_twitter' => 'nullable|url|max:500',
            'footer_linkedin' => 'nullable|url|max:500',
            'footer_instagram' => 'nullable|url|max:500',
            'privacy_policy' => 'nullable|string',
            'terms_of_service' => 'nullable|string',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set($key, $value);
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Footer settings updated successfully!');
    }

}