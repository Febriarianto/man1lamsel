<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CmsUserMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            return redirect()->route('admin.login')->with('error', 'Silakan masuk ke dashboard.');
        }

        $user = Auth::user();
        if (array_key_exists('active', $user->getAttributes()) && ! $user->active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('admin.login')->with('error', 'Akun Anda sedang dinonaktifkan. Hubungi Administrator.');
        }

        return $next($request);
    }
}
