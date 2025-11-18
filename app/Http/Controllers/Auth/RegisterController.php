<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /**
     * Show the registration form.
     */
    public function create()
    {
        return view('auth.register');
    }

    /**
     * Handle registration request.
     */
    public function store(Request $request)
    {
        // Check if user is trying to log in as guest
        if ($request->has('is_guest') && $request->input('is_guest')) {
            $user = User::create([
                'is_guest' => true,
            ]);
            
            // Create company for guest too
            Company::create([
                'owner_user_id' => $user->id,
            ]);

            Auth::login($user);

            return redirect()->route('onboarding');
        }

        // Regular registration
        $request->validate([
            'first_name' => ['nullable', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['nullable', Rules\Password::defaults()],
        ]);

        // Check if user already exists
        $existingUser = User::where('email', $request->email)->first();
        
        if ($existingUser) {
            // User exists, try to log them in with password if provided
            if ($request->password && Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                $request->session()->regenerate();
                return redirect()->route('home');
            }
            
            return back()->withErrors([
                'email' => 'This email is already registered. Please provide your password to log in.',
            ])->withInput($request->only('email', 'first_name', 'last_name'));
        }

        // Create new user (password optional for first-time users)
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : null,
            'is_guest' => false,
        ]);

        // Create company for the user
        Company::create([
            'owner_user_id' => $user->id,
        ]);

        Auth::login($user);

        // Redirect to onboarding
        return redirect()->route('onboarding');
    }
}

