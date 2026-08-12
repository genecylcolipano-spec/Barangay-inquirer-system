<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Rules\StrongPassword;

class AuthController extends Controller
{
         // Shows the registration form
     public function showRegistrationForm()
     {
         return view('auth.register'); // Matches your view path
     }
     // Handles form submission (POST)
     public function register(Request $request)
     {
         // Validate input first
         $request->validate([
             'name' => 'required|string|max:255',
             'email' => 'required|string|email|max:255|unique:users',
             'password' => ['required', 'string', 'confirmed', new StrongPassword()],
             // Add other fields you need (e.g., contact number)
         ]);
         // Create new user
         $user = User::create([
             'name' => $request->name,
             'email' => $request->email,
             'password' => Hash::make($request->password),
         ]);

         // log registration activity
         \App\Models\Activity::log("New user registered: {$user->name} (ID #{$user->id})", 'registration', $user->id);

         // Log the user in and redirect to resident dashboard
         Auth::login($user);

         return redirect()->route('dashboard')->with('success', 'Registration successful!');
     }
}
