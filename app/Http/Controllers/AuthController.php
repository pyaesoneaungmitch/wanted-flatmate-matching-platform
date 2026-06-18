<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PublicProfile;
use App\Models\PrivateProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;


class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'min:6', 'max:255'],
            'username' => ['required','string','min:3','max:30','regex:/^[A-Za-z0-9_]+$/','unique:users,username'],
        ]);
        $response = $request->input('g-recaptcha-response');

        $secret = env('RECAPTCHA_SECRET');
        $verify = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $secret,
            'response' => $response,
            'remoteip' => $request->ip(),
        ]);

        abort_if(!($verify->json()['success'] ?? false), 422, 'Captcha failed. Please try again.');

        $user = User::create([
            'email' => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'username' => $data['username'],
        ]);

        PublicProfile::create([
            'user_id' => $user->user_id,
            'display_name' => 'New User', // or derive from email
            ]);

            PrivateProfile::create([
            'user_id' => $user->user_id,
            ]);

        Auth::login($user);

        return redirect()->route('home');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Auth::attempt will check password using getAuthPassword() we added
        if (Auth::attempt(['email' => $data['email'], 'password' => $data['password']])) {
            $request->session()->regenerate();
            return redirect()->route('discover');
        }

        return back()->withErrors([
            'email' => 'Invalid credentials.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login.form');
    }
}