<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     * Allows all admin roles (staff, doctor, admin) to access admin routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect('/login')->with('error', 'Please log in to access this area.');
        }

        $admin = Admin::find(auth()->id());

        if (!$admin) {
            return redirect('/dashboard')->with('error', 'You do not have administrative access.');
        }

        // Share admin info with all views
        view()->share('currentAdmin', $admin);
        view()->share('adminRole', $admin->admin_role);

        return $next($request);
    }
}