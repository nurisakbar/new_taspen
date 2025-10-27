<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $provided = $request->header('X-Api-Token');
        $expected = env('API_TOKEN', 'secret-hardcoded-token');

        if (!$provided || !hash_equals($expected, (string) $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized: invalid API token'
            ], 401);
        }

        return $next($request);
    }
}
