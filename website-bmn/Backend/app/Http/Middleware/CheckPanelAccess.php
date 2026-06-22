<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPanelAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect('/admin/login');
        }

        $path = $request->path();

        // Cek akses berdasarkan path
        if (str_starts_with($path, 'admin/staff') && !$user->hasRole('staff') && !$user->hasRole('super_admin')) {
            return redirect('/admin/peminjam');
        }

        if (str_starts_with($path, 'admin/peminjam') && !$user->hasRole('user') && !$user->hasRole('super_admin') && !$user->hasRole('staff')) {
            return redirect('/admin');
        }

        return $next($request);
    }
}
