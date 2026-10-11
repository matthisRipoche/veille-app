<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exige « Authorization: Bearer <API_TOKEN> » (API REST et serveur MCP HTTP).
 */
class ApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('veille.api_token');
        $given = (string) $request->bearerToken();

        if ($expected === '' || $given === '' || ! hash_equals(hash('sha256', $expected), hash('sha256', $given))) {
            return response()->json(['error' => 'Jeton invalide ou manquant.'], 401);
        }

        return $next($request);
    }
}
