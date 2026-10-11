<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Protège la lecture du site par mot de passe (HTTP Basic, nom d'utilisateur
 * libre) lorsque SITE_PASSWORD est défini.
 */
class SitePassword
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('veille.site_password');

        if ($expected === '') {
            return $next($request);
        }

        $given = (string) $request->getPassword();

        if ($given !== '' && hash_equals(hash('sha256', $expected), hash('sha256', $given))) {
            return $next($request);
        }

        return response('Authentification requise.', 401, [
            'WWW-Authenticate' => 'Basic realm="Veille", charset="UTF-8"',
        ]);
    }
}
