<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class VerifiedUser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Check verification status:
        // 1 = Pending (awaiting OCR or Admin review)
        // 2 = Verified (ID matched successfully via OCR)
        // 3 = Not Verified (No ID uploaded or identification rejected)
        
        // Only users with Verification_Status_ID = 2 can access protected routes
        if ($user->Verification_Status_ID != 2) {
            // Allow access to these routes even if not verified:
            // - dashboard (they can see it but features are disabled)
            // - verification-related routes
            // - logout
            if ($request->routeIs('dashboard') ||
                $request->routeIs('verification.pending') || 
                $request->routeIs('logout') ||
                $request->routeIs('verify.reverify') ||
                $request->routeIs('verify.process')) {
                return $next($request);
            }

            // Determine appropriate message based on status
            $message = match($user->Verification_Status_ID) {
                1 => 'Your account is pending verification. This feature is temporarily disabled.',
                3 => 'Your account verification was rejected. Please re-submit your identification to access this feature.',
                default => 'Your account is not verified. Please complete the verification process to access this feature.',
            };

            // Redirect back to dashboard with error message
            return redirect()->route('dashboard')->with('error', $message);
        }

        return $next($request);
    }
}