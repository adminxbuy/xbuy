<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminRole
{
    /**
     * Restrict admin routes to specific admin_role values.
     *
     * Usage: Route::middleware(['auth', 'admin', 'admin.role:finance,super_admin'])
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = Auth::user();

        if (!$user || !$user->is_admin) {
            abort(403, 'Unauthorized.');
        }

        // No roles specified = any admin can pass
        if (empty($roles)) {
            return $next($request);
        }

        if (in_array($user->admin_role, $roles)) {
            return $next($request);
        }

        if ($user->admin_role === 'custom') {
            $path = $request->path();
            $relPath = preg_replace('/^admin\//', '', $path);
            $parts = explode('/', $relPath);
            $baseSection = $parts[0] ?? '';
            $section = str_replace('-', '_', $baseSection);

            // Special mapping checks
            if ($section === 'mail' || $section === 'mail_templates') {
                $section = 'mails';
            }

            if ($user->canAccess($section)) {
                $method = $request->method();
                $isWrite = in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']);
                $permType = $user->permissions[$section] ?? 'none';
                
                if ($isWrite) {
                    if ($method === 'DELETE') {
                        if (in_array($permType, ['delete', 'all'])) {
                            return $next($request);
                        }
                    } else {
                        if (in_array($permType, ['edit', 'delete', 'all'])) {
                            return $next($request);
                        }
                    }
                } else {
                    if (in_array($permType, ['view', 'edit', 'delete', 'all'])) {
                        return $next($request);
                    }
                }
            }
        }

        // AJAX / JSON request
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'You do not have permission to perform this action.',
            ], 403);
        }

        return redirect()->route('admin.dashboard')
            ->with('error', 'Access denied. You do not have permission to view that section.');
    }
}
