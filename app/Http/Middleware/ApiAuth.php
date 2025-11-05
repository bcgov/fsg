<?php

namespace App\Http\Middleware;

use Closure;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Response;

class ApiAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  ...$roles
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $forwardedHost = $request->headers->get('X-Forwarded-Host');

        // Prevent access to the API except via the gateway
        if ($forwardedHost !== env('KEYCLOAK_APS_URL')) {
            Log::warning('403 API access denied: Invalid forwarded host', [
                'forwarded_host' => $forwardedHost,
                'expected_host' => env('KEYCLOAK_APS_URL'),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);
            return Response::json(['error' => 'Unauthorized.' . $forwardedHost . ',,,' . env('KEYCLOAK_APS_URL')], 403);
        }

        $token = request()->bearerToken();
        if(is_null($token)){
            return Response::json(['status' => false, 'error' => 'Missing token.'], 401);
        }
        $jwksUri = env('KEYCLOAK_APS_ISS') . env('KEYCLOAK_APS_CERT_PATH');
        $jwksJson = file_get_contents($jwksUri);
        $jwksData = json_decode($jwksJson, true);
        $matchingKey = null;
        foreach ($jwksData['keys'] as $key) {
            if (isset($key['use']) && $key['use'] === 'sig') {
                $matchingKey = $key;
                break;
            }
        }

        $wrappedPk = wordwrap($matchingKey['x5c'][0], 64, "\n", true);
        $pk = "-----BEGIN CERTIFICATE-----\n" . $wrappedPk . "\n-----END CERTIFICATE-----";

        try {
            $decoded = JWT::decode($token, new Key($pk, 'RS256'));
        } catch (ExpiredException $e) {
            return Response::json(['status' => false, 'error' => 'Token has expired.'], 401);
        } catch (\Exception $e) {
            return Response::json(['status' => false, 'error' => "An error occurred: " . $e->getMessage()], 401);
        }

        if(is_null($decoded)) {
            return Response::json(['status' => false, 'error' => "Invalid token."], 401);
        }else{
            //only validate for accounts that we have registered
            if($decoded->iss === env('KEYCLOAK_APS_ISS')){
                return $next($request);
            }
        }

        Log::warning('403 API access denied: Invalid token issuer', [
            'issuer' => $decoded->iss ?? 'unknown',
            'expected_issuer' => env('KEYCLOAK_APS_ISS'),
            'ip' => $request->ip(),
            'url' => $request->fullUrl(),
        ]);
        
        return Response::json(['status' => false, 'error' => "Generic error."], 403);
    }
}
