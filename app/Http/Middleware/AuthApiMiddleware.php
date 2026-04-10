<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuthApiMiddleware
{
    // Map API route path prefixes to module IDs
    private const API_MODULE_MAP = [
        'api/manufacturing/boms'        => 28,
        'api/manufacturing/productions' => 29,
    ];

    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('api')->check()) {
            return response()->json(['status' => false, 'error' => 'Unauthorized'], 401);
        }

        $user = Auth::guard('api')->user();

        // Admin and sub-admin have full access
        if (in_array($user->role, ['admin', 'sub-admin'])) {
            return $next($request);
        }

        // For staff: check manufacturing module permissions
        $path = $request->path();
        $moduleId = null;

        foreach (self::API_MODULE_MAP as $prefix => $id) {
            if (str_starts_with($path, $prefix)) {
                $moduleId = $id;
                break;
            }
        }

        if ($moduleId !== null) {
            $method = $request->method();

            $actionMap = [
                'GET'    => 'view',
                'POST'   => 'add',
                'PUT'    => 'edit',
                'PATCH'  => 'edit',
                'DELETE' => 'delete',
            ];

            $action = $actionMap[$method] ?? 'view';

            $permission = DB::table('user_permissions')
                ->where('user_id', $user->id)
                ->where('module_id', $moduleId)
                ->first();

            if (!$permission || (int) ($permission->$action ?? 0) !== 1) {
                return response()->json([
                    'status'  => false,
                    'message' => 'You do not have permission to perform this action.',
                ], 403);
            }
        }

        return $next($request);
    }
}
