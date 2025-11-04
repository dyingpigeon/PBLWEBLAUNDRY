<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showLogin()
    {
        Log::debug('AuthController@showLogin: Memulai proses menampilkan halaman login');
        
        // Jika sudah login, redirect ke dashboard
        if (auth()->check()) {
            $user = auth()->user();
            Log::debug('AuthController@showLogin: User sudah login, redirect ke dashboard', [
                'user_id' => $user->id,
                'user_email' => $user->email
            ]);
            return redirect()->route('dashboard');
        }
        
        Log::debug('AuthController@showLogin: User belum login, menampilkan halaman login');
        return view('auth.login');
    }

    public function login(Request $request)
    {
        Log::debug('AuthController@login: Memulai proses login');
        Log::debug('AuthController@login: Data request received', [
            'email' => $request->email,
            'has_password' => !empty($request->password),
            'remember' => $request->boolean('remember')
        ]);

        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        Log::debug('AuthController@login: Validasi berhasil', [
            'email' => $credentials['email'],
            'remember_me' => $request->boolean('remember')
        ]);

        Log::debug('AuthController@login: Mencoba melakukan authentication');
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            Log::debug('AuthController@login: Login berhasil', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'remember_me' => $request->boolean('remember')
            ]);

            $request->session()->regenerate();
            Log::debug('AuthController@login: Session regenerated');

            $intendedUrl = redirect()->intended()->getTargetUrl();
            Log::debug('AuthController@login: Redirect ke intended URL', [
                'intended_url' => $intendedUrl
            ]);

            return redirect()->intended('/dashboard');
        }

        Log::warning('AuthController@login: Login gagal - Email atau password salah', [
            'email' => $credentials['email'],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        Log::debug('AuthController@logout: Memulai proses logout', [
            'user_id' => $user ? $user->id : null,
            'user_email' => $user ? $user->email : null
        ]);

        Auth::logout();
        Log::debug('AuthController@logout: User telah di-logout dari Auth');

        $request->session()->invalidate();
        Log::debug('AuthController@logout: Session invalidated');

        $request->session()->regenerateToken();
        Log::debug('AuthController@logout: CSRF token regenerated');

        Log::debug('AuthController@logout: Redirect ke halaman login');
        return redirect('/login');
    }
}