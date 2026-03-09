<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Validates the incoming session cookie against feralde_auth.
 * On success, attaches the resolved user array to request attributes
 * under the key 'auth_user' for downstream use.
 */
class AuthProxyMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authUrl = rtrim(config('services.auth.url', env('AUTH_SERVICE_URL', 'http://127.0.0.1:8000')), '/');
        $cookieName = config('services.auth.session_cookie', env('AUTH_SESSION_COOKIE_NAME', 'auth-session'));
        $timeout = (int) config('services.auth.timeout', env('AUTH_REQUEST_TIMEOUT', 30));

        $sessionCookie = $request->cookie($cookieName);

        if (!$sessionCookie) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated',
            ], 401);
        }

        try {
            $response = Http::timeout($timeout)
                ->withHeaders([
                    'Accept'       => 'application/json',
                    'Cookie'       => $cookieName . '=' . $sessionCookie,
                    'X-Forwarded-For' => $request->ip(),
                ])
                ->get($authUrl . '/api/user');

            if (!$response->successful()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $body = $response->json();
            $user = $body['data'] ?? null;

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            // Attach resolved user to request for downstream controllers/middleware
            $request->attributes->set('auth_user', $user);

        } catch (\Exception $e) {
            Log::error('AuthProxyMiddleware: failed to contact auth service', [
                'message' => $e->getMessage(),
                'url'     => $authUrl . '/api/user',
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Authentication service unavailable',
            ], 503);
        }

        return $next($request);
    }
}
