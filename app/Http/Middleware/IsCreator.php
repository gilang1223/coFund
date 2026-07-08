<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsCreator
{
    /**
     * Handle an incoming request.
     * Hanya user dengan role 'creator' yang diizinkan.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || (!$request->user()->isCreator() && !$request->user()->isAdmin())) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. Hanya creator yang dapat melakukan aksi ini.',
            ], 403);
        }

        return $next($request);
    }
}
