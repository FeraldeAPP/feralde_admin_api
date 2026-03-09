<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->attributes->get('auth_user');

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // Admin bypass
        $roles = $user['roles'] ?? [];
        $bypassRoles = ['super-admin', 'admin'];
        $isSuperAdmin = collect($roles)->contains(fn($r) => in_array(($r['name'] ?? $r), $bypassRoles, true));

        if ($isSuperAdmin) {
            return $next($request);
        }

        $permissions = $user['permissions'] ?? [];
        if (!$this->hasPermission($permissions, $permission)) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have access to this action.',
            ], 403);
        }

        return $next($request);
    }

    private function hasPermission(array $permissions, string $permission): bool
    {
        foreach ($permissions as $category) {
            if (!is_array($category)) {
                continue;
            }
            foreach ($category as $module => $slugs) {
                if (is_array($slugs) && in_array($permission, $slugs, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}
