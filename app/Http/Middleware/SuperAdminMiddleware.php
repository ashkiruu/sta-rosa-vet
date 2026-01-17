<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;

class SuperAdminMiddleware
{
    /**
     * Handle an incoming request.
     * Only allows super admins to access these routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Please log in to access this area.');
        }

        // Check if user is a super admin
        $admin = Admin::find(auth()->id());
        
        if (!$admin || !$admin->isSuperAdmin()) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'You do not have permission to access this area. Super Admin access required.');
        }

        return $next($request);
    }
}