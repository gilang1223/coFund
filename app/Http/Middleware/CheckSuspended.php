<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSuspended
{
    /**
     * Handle an incoming request.
     * Blokir user yang di-suspend dari aksi apa pun (GET-only untuk view).
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->is_suspended) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang disuspend. Hubungi admin untuk informasi lebih lanjut.',
            ], 403);
        }

        return $next($request);
    }
}
