<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Show the forgot password form
     */
    public function showForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle the forgot password request
     * For local development: displays the reset link directly
     */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        // Find user by Email (your custom column name)
        $user = User::where('Email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'No account found with this email address.']);
        }

        // Generate a unique token
        $token = Str::random(64);

        // Delete any existing tokens for this email
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        // Insert new token
        DB::table('password_reset_tokens')->insert([
            'email' => $request->email,
            'token' => Hash::make($token),
            'created_at' => Carbon::now()
        ]);

        // Build the reset URL
        $resetUrl = url('/reset-password/' . $token . '?email=' . urlencode($request->email));

        // For LOCAL DEVELOPMENT: Display the link directly
        // In production, you would send this via email instead
        return back()->with([
            'status' => 'Password reset link generated successfully!',
            'reset_link' => $resetUrl
        ]);
    }
}