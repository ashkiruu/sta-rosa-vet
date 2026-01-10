<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;

class RegisterController extends Controller
{
     public function step1()
    {
        // Fetch all 18 barangays from database
        $barangays = \App\Models\Barangay::all();
        return view('auth.register.step1', compact('barangays'));
    }

    public function postStep1(Request $request)
    {
        $request->validate([
            'Last_Name' => 'required|string|max:255',
            'First_Name' => 'required|string|max:255',
            'Middle_Name' => 'required|string|max:255',
            'Contact_Number' => 'required|string|max:15',
            'Address' => 'required|string|max:500',
            'Barangay_ID' => 'required|exists:barangays,Barangay_ID',
        ]);

        session(['register.step1' => $request->all()]);

        return redirect()->route('register.step2');
    }

    public function step2()
    {
        return view('auth.register.step2');
    }

    public function postStep2(Request $request)
    {
        $request->validate([
            'id_file' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        // 1. Store the file
        $path = $request->file('id_file')->store('ids', 'public');
        $absolutePath = storage_path('app/public/' . $path);

        try {
            // 2. Run OCR
            $ocr = new TesseractOCR($absolutePath);
            $ocrText = $ocr->run(); 
        
            $ocrTextClean = strtolower(preg_replace('/[^a-z0-9\s]/i', '', $ocrText));

            // 3. Get Step 1 data (Ensure keys match your Step 1 session)
            $step1 = session('registration_data'); 

            // 4. Scoring
            $score = 0;
            if (str_contains($ocrTextClean, strtolower($step1['First_Name']))) $score += 2;
            if (str_contains($ocrTextClean, strtolower($step1['Last_Name']))) $score += 2;

            // 5. Set Status (Using IDs from your StatusSeeder)
            // Let's assume 1 = Pending, 2 = Verified/Approved
            $statusID = ($score >= 4) ? 2 : 1;

            session(['register.step2' => [
                'id_file_path' => $path,
                'verification_status_id' => $statusID
            ]]);

            return redirect()->route('register.step2')->with([
                'ocr_status' => ($statusID == 2 ? 'Verified' : 'Pending'),
                'ocr_message' => ($statusID == 2 ? 'ID Confirmed! You can proceed.' : 'ID uploaded but requires manual review.')
            ]);

        } catch (\Exception $e) {
            return back()->withErrors(['id_file' => 'OCR Error: ' . $e->getMessage()]);
        }
    }

}
