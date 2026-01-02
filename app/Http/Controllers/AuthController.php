<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

/**
 * Simple authentication controller.
 *
 * This controller implements a minimal login/logout flow without
 * relying on external scaffolding. It should be sufficient for
 * the governance console, where user registration is not exposed.
 */
class AuthController extends Controller
{
    /**
     * Display the login form.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle a login request to the application.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    /**
     * Redirect to Google OAuth provider.
     */
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * Handle Google OAuth callback.
     */
    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Update existing user with OAuth info
                $user->update([
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'provider' => 'google',
                    'provider_id' => $googleUser->getId(),
                    'avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(uniqid()), // Random password since OAuth users don't need it
                    'role' => 'viewer', // Default role for new OAuth users
                    'email_verified_at' => now(),
                ]);

                // Assign default role via Spatie
                $user->assignRole('viewer');

                Log::info('New user created via Google OAuth', [
                    'email' => $user->email,
                    'name' => $user->name,
                ]);
            }

            Auth::login($user, true);
            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            Log::error('Google OAuth callback failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect('/login')->withErrors([
                'email' => 'Failed to authenticate with Google. Please try again.',
            ]);
        }
    }

    /**
     * Redirect to GitHub OAuth provider.
     */
    public function redirectToGithub()
    {
        return Socialite::driver('github')->redirect();
    }

    /**
     * Handle GitHub OAuth callback.
     */
    public function handleGithubCallback()
    {
        try {
            $githubUser = Socialite::driver('github')->user();

            $user = User::where('email', $githubUser->getEmail())->first();

            if ($user) {
                // Update existing user with OAuth info
                $user->update([
                    'provider' => 'github',
                    'provider_id' => $githubUser->getId(),
                    'avatar' => $githubUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $githubUser->getName() ?? $githubUser->getNickname(),
                    'email' => $githubUser->getEmail(),
                    'provider' => 'github',
                    'provider_id' => $githubUser->getId(),
                    'avatar' => $githubUser->getAvatar(),
                    'password' => Hash::make(uniqid()), // Random password since OAuth users don't need it
                    'role' => 'viewer', // Default role for new OAuth users
                    'email_verified_at' => now(),
                ]);

                // Assign default role via Spatie
                $user->assignRole('viewer');

                Log::info('New user created via GitHub OAuth', [
                    'email' => $user->email,
                    'name' => $user->name,
                ]);
            }

            Auth::login($user, true);
            return redirect()->intended('/dashboard');

        } catch (\Exception $e) {
            Log::error('GitHub OAuth callback failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect('/login')->withErrors([
                'email' => 'Failed to authenticate with GitHub. Please try again.',
            ]);
        }
    }
}