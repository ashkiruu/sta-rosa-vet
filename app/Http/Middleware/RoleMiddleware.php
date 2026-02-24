<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     * Accepts one or more roles separated by commas.
     *
     * Usage in routes:
     *   ->middleware('role:staff')
     *   ->middleware('role:doctor')
     *   ->middleware('role:admin')
     *   ->middleware('role:staff,doctor')   // either role allowed
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Please log in to access this area.');
        }

        $admin = Admin::find(auth()->id());

        if (!$admin) {
            return redirect('/dashboard')->with('error', 'You do not have administrative access.');
        }

        // Parse roles (handle both "staff,doctor" and "staff", "doctor" formats)
        $allowedRoles = [];
        foreach ($roles as $role) {
            foreach (explode(',', $role) as $r) {
                $allowedRoles[] = trim($r);
            }
        }

        if (!in_array($admin->admin_role, $allowedRoles)) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this area.');
        }

        return $next($request);
    }
}