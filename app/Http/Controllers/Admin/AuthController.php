<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $credentials['auth_provider'] = 'local';

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();
            if (array_key_exists('active', $user->getAttributes()) && ! $user->active) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akun ini sedang dinonaktifkan.'])->onlyInput('email');
            }

            if (array_key_exists('last_login_at', $user->getAttributes())) {
                $user->forceFill(['last_login_at' => now()])->save();
            }

            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau kata sandi akun manual tidak sesuai.'])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
