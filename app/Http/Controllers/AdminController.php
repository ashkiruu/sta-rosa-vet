<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function index()
    {
        // This combines both methods into one
        $stats = [
            'pending_users' => User::where('Verification_Status_ID', 1)->count(),
            'today_appointments' => Appointment::whereDate('Date', now())->count(),
            // Add one more stat for your thesis: Total Registered Pets
            'total_pets' => \App\Models\Pet::count(), 
        ];
        
        return view('admin.dashboard', compact('stats'));
    }


    public function dashboard()
    {
        // Summary stats for the Admin Dashboard
        $stats = [
            'pending_users' => User::where('Verification_Status_ID', 1)->count(), // Assuming 1 is Pending
            'today_appointments' => Appointment::whereDate('Date', now())->count(),
        ];
        return view('admin.dashboard', compact('stats'));
    }

    public function pendingVerifications(Request $request)
    {
        // 1. Start with all users who are NOT admins
        // We use a join or whereNotExists to make sure we don't list admin accounts here
        $query = User::whereNotExists(function ($query) {
            // Make sure this is 'admin' or 'admins' depending on your phpMyAdmin
            $query->select(DB::raw(1))
                ->from('admins') 
                ->whereColumn('admins.User_ID', 'users.User_ID');
        });

        // 2. Search by Name or Email
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('First_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Last_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Email', 'like', '%' . $request->search . '%');
            });
        }

        // 3. Filter by Status (If the user selects one)
        if ($request->filled('status')) {
            $query->where('Verification_Status_ID', $request->status);
        }

        // 4. Get the results
        $users = $query->latest()->paginate(10);

        return view('admin.verifications', compact('users'));
    }

    public function approveUser($id)
    {
        $user = \App\Models\User::findOrFail($id);
        
        // Update the status based on your Data Dictionary
        // Assuming 2 = 'Verified' in your verification_statuses table
        $user->update([
            'Verification_Status_ID' => 2 
        ]);

        return redirect()->route('admin.verifications')->with('success', 'Resident ' . $user->First_Name . ' has been successfully verified.');
    }

    public function verifications(Request $request)
    {
        $query = User::query();

        // 1. Search by Name or Email
        if ($request->has('search')) {
            $query->where(function($q) use ($request) {
                $q->where('First_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Last_Name', 'like', '%' . $request->search . '%')
                ->orWhere('Email', 'like', '%' . $request->search . '%');
            });
        }

        // 2. Filter by Status (Verified/Pending)
        if ($request->has('status') && $request->status != '') {
            $query->where('Verification_Status_ID', $request->status);
        }

        $users = $query->latest()->paginate(10); // Added pagination for efficiency
        return view('admin.verifications', compact('users'));
    }
    
    public function showUser($id)
    {
        // We load the user along with their OCR data and pets
        $user = User::with(['ocrData'])->findOrFail($id);
        
        // Fetch pets for this user as well
        $pets = \App\Models\Pet::where('Owner_ID', $id)->get(); 
        
        return view('admin.user_details', compact('user', 'pets'));
    }
}
