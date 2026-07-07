<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsNotAdmin
{
    /**
     * Handle an incoming request.
     * Blokir admin dari aksi yang hanya boleh dilakukan backer/creator.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user() && $request->user()->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Admin tidak diizinkan melakukan aksi ini.',
            ], 403);
        }

        return $next($request);
    }
}
