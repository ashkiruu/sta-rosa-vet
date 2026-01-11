<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is logged in AND exists in the 'admin' table
        if (auth()->check() && \DB::table('admins')->where('User_ID', auth()->id())->exists()) {
            return $next($request);
        }

        // If not an admin, kick them back to the home/dashboard with a message
        return redirect('/dashboard')->with('error', 'You do not have administrative access.');
    }
}