<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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

        $user = User::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($credentials['email'])])
            ->whereIn('auth_provider', ['local', 'local_kemenag_sso'])
            ->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            if (! $user->active) {
                return back()->withErrors(['email' => 'Akun ini sedang dinonaktifkan.'])->onlyInput('email');
            }

            if (Hash::needsRehash($user->password)) {
                $user->forceFill(['password' => Hash::make($credentials['password'])])->save();
            }

            $user->forceFill(['last_login_at' => now()])->save();
            Auth::login($user, $request->boolean('remember'));

            $request->session()->regenerate();
            $request->session()->put('authenticated_via', 'local');

            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors(['email' => 'Email atau kata sandi akun manual tidak sesuai.'])->onlyInput('email');
    }

    public function destroy(Request $request)
    {
        $authenticatedVia = (string) $request->session()->get('authenticated_via');
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($authenticatedVia === 'kemenag_sso' && filled(config('kemenag-sso.signout_url'))) {
            return redirect()->away((string) config('kemenag-sso.signout_url'));
        }

        return redirect()->route('admin.login');
    }
}
