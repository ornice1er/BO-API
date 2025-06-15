<?php

namespace App\Http\Middleware;

use App\Services\JWTService;
use App\Utilities\Common;
use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Token;
use Symfony\Component\HttpFoundation\Response;

class EnsureBasicAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        $authorizationHeader = request()->header('Authorization');

        if ($request->header('Authorization') == null) {
            return Common::error('authorization header not found', []);
        }

        if ($authorizationHeader && str_starts_with($authorizationHeader, 'Basic ')) {
            // Extraire la partie encodée en Base64
            $encodedCredentials = substr($authorizationHeader, 6);

            // Décoder les identifiants
            $decodedCredentials = base64_decode($encodedCredentials);

            // Séparer l'username et le password
            [$username, $password] = explode(':', $decodedCredentials, 2);
            $envUsername = env('BASIC_AUTH_USERNAME');
            $envPassword = env('BASIC_AUTH_PASSWORD');

            if ($username !== $envUsername || $password !== $envPassword) {
                return Common::error('Accès non autorisé', []);
            }
        } else {
            if (! preg_match('/Bearer\s(\S+)/', $request->header('Authorization'), $matches)) {
                return Common::error('Token not found', []);
            }
            $token = $matches[1];

            $verify = app(JWTService::class)->verify(new Token($token));
            if (! $verify) {
                return Common::error('Token is not valid', []);
            }

        }

        return $next($request);
    }
}
