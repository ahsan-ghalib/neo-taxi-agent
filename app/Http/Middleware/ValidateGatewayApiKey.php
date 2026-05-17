<?php

namespace App\Http\Middleware;

use App\Models\SystemConfig;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates the X-NEO-API-Key header on all AI gateway requests.
 *
 * Key resolution order:
 *   1. system_configs table (operator-managed, cached 10 min)
 *   2. NEO_GATEWAY_API_KEY env variable (fallback)
 */
class ValidateGatewayApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $providedKey = $request->header('X-NEO-API-Key');

        if (empty($providedKey)) {
            return response()->json([
                'error' => 'Missing API key.',
                'code' => 'MISSING_API_KEY',
            ], Response::HTTP_UNAUTHORIZED);
        }

        $validKey = SystemConfig::get('gateway.api_key', config('neo.gateway_api_key'));

        if (empty($validKey) || ! hash_equals((string) $validKey, $providedKey)) {
            return response()->json([
                'error' => 'Invalid API key.',
                'code' => 'INVALID_API_KEY',
            ], Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
