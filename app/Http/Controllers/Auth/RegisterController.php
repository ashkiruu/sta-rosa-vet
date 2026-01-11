<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use thiagoalessio\TesseractOCR\TesseractOCR;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\MlOcrProcessing;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rules\Password;

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

    private function normalizeText(string $text): string
    {
        $text = strtoupper($text);
        // 1. Turn Newlines and Tabs into Spaces FIRST
        $text = preg_replace('/[\r\n\t]+/', ' ', $text);
        // 2. Turn special chars (like / in "Name/Apelyido") into Spaces
        $text = preg_replace('/[^A-Z0-9 ]/', ' ', $text);
        // 3. Clean up multiple spaces
        $text = preg_replace('/\s+/', ' ', $text);
        
        return trim($text);
    }

    private function similarity(string $a, string $b): float
    {
        similar_text($a, $b, $percent);
        return $percent / 100;
    }

    private function bestTokenMatch(string $needle, array $haystack): float
    {
        if (empty($needle)) return 0;

        $best = 0;
        foreach ($haystack as $token) {
            $score = $this->similarity($needle, $token);
            if ($score > $best) {
                $best = $score;
            }
        }
        return $best;
    }

    private function getCompositeScore(string $inputName, array $ocrTokens): float
    {
        // Split user input into parts (e.g., "MARIA", "LOULYNN")
        $inputTokens = explode(' ', $this->normalizeText($inputName));
        
        if (empty($inputTokens)) return 0;

        $totalScore = 0;
        foreach ($inputTokens as $namePart) {
            // Find the best match for THIS specific word in the OCR tokens
            $totalScore += $this->bestTokenMatch($namePart, $ocrTokens);
        }

        // Average the score
        return $totalScore / count($inputTokens);
    }

    public function postStep2(Request $request)
    {
        $request->validate([
            'id_file' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

         // Handle the "Skip" logic
        if (!$request->hasFile('id_file')) {
            session(['register.step2' => [
                'id_file_path'           => null,
                'verification_status_id' => 3, // Set to 'Not Verified'
                'confidence_score'       => 0,
                'raw_text'               => '',
                'address_score'          => 0,
                'scores'                 => []
            ]]);

            return redirect()->route('register.step3');
        }

        $step1 = session('register.step1');
        if (!$step1) {
            return redirect()->route('register.step1')->withErrors(['error' => 'Please complete Step 1 first.']);
        }

        // Process File
        $path = $request->file('id_file')->store('ids', 'public');
        $absolutePath = storage_path('app/public/' . $path);

        try {
            $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($absolutePath);
            $ocr->executable('C:\Program Files\Tesseract-OCR\tesseract.exe'); // Uncomment for Windows
            $ocrText = $ocr->run();

            if (empty(trim($ocrText))) {
                // Delete the "garbage" image to save storage
                Storage::disk('public')->delete($path);
                
                return back()->withErrors([
                    'id_file' => 'No readable text found. Please ensure you are uploading a clear photo of your ID.'
                ]);
            }
            
            // 1. Normalization (Now fixes the "NAMESMARIA" bug)
            $normalizedOcr = $this->normalizeText($ocrText);
            $ocrTokens = explode(' ', $normalizedOcr);

            // 2. User Input
            $firstName  = $step1['First_Name'] ?? '';
            $middleName = $step1['Middle_Name'] ?? '';
            $lastName   = $step1['Last_Name'] ?? '';
            $address    = $this->normalizeText($step1['Address'] ?? '');

            // 3. New Scoring Logic (Splits multi-word names)
            // This ensures "MARIA" matches "MARIA" (100%) and "LOULYNN" matches "LOULYNN" (100%)
            $firstNameScore = $this->getCompositeScore($firstName, $ocrTokens);
            $lastNameScore  = $this->getCompositeScore($lastName, $ocrTokens);
            
            $hasMiddleName = !empty($middleName);
            $middleNameScore = $hasMiddleName ? $this->getCompositeScore($middleName, $ocrTokens) : 0;

            // 4. Address Logic (Unchanged)
            $addressTokens = explode(' ', $address);
            $addressMatches = 0;
            $validAddressTokens = 0;
            foreach ($addressTokens as $token) {
                if (strlen($token) < 3) continue;
                $validAddressTokens++;
                foreach ($ocrTokens as $ocrToken) {
                    if ($this->similarity($token, $ocrToken) >= 0.80) { // Increased strictness slightly
                        $addressMatches++;
                        break;
                    }
                }
            }
            $addressScore = ($validAddressTokens > 0) ? ($addressMatches / $validAddressTokens) : 0;

            // 5. Calculate Final Weighted Score
            if ($hasMiddleName) {
                $totalScore = ($lastNameScore * 0.35) + 
                            ($firstNameScore * 0.30) + 
                            ($middleNameScore * 0.10) + 
                            ($addressScore * 0.25);
            } else {
                $totalScore = ($lastNameScore * 0.40) + 
                            ($firstNameScore * 0.35) + 
                            ($addressScore * 0.25);
            }

            /*--- DEBUGGING: REMOVE THIS AFTER TESTING ---
            dd([
                'OCR_Text_Raw' => $ocrText,
                'OCR_Tokens' => $ocrTokens,
                'User_Input' => [
                    'First' => $firstName,
                    'Last' => $lastName,
                    'Middle' => $middleName
                ],
                'Calculated_Scores' => [
                    'First_Name_Score' => $firstNameScore,
                    'Last_Name_Score' => $lastNameScore,
                    'Middle_Name_Score' => $middleNameScore,
                    'Address_Score' => $addressScore,
                ],
                'FINAL_TOTAL' => $totalScore
            ]);
            */

            $statusID = ($totalScore >= 0.7) ? 2 : 1; 

            if ($totalScore < 0.15) {
                return back()->withErrors([
                    'id_file' => 'The uploaded document does not appear to match your registered name. Please upload a valid ID.'
                ]);
            }
            // Log logic...
            \Log::info('ID_VERIFICATION', [
            'input' => [
                'first_name' => $firstName,
                'middle_name' => $middleName,
                'last_name' => $lastName,
                'address' => $address,
            ],
            'ocr' => [
                'raw_excerpt' => substr($normalizedOcr, 0, 300),
                'tokens' => array_slice($ocrTokens, 0, 20),
            ],
            'scores' => [
                'first_name' => $firstNameScore,
                'middle_name' => $middleNameScore,
                'last_name' => $lastNameScore,
                'address' => $addressScore,
                'total' => $totalScore,
            ],
            'decision' => [
                'status_id' => $statusID,
                'threshold' => 0.7,
                'rule_based' => true,
            ],
        ]);

            session(['register.step2' => [
                'id_file_path'           => $path,
                'verification_status_id' => $statusID,
                'confidence_score'       => $totalScore,
                'raw_text'               => $ocrText, // Required for your ML table
                'address_score'          => $addressScore,
                'scores'                 => [ // Detailed breakdown for Parsed_Data
                    'first_name' => $firstNameScore,
                    'last_name'  => $lastNameScore,
                    'middle_name' => $middleNameScore,
                    'address'    => $addressScore
                ]
            ]]);

            return redirect()->route('register.step3')->with([
                'ocr_status' => ($statusID == 2 ? 'Verified' : 'Pending'),
                'ocr_message' => ($statusID == 2 ? 'ID Confirmed!' : 'ID uploaded, pending review.')
            ]);

        } catch (\Exception $e) {
            // 5. This catches the "Command did not produce output" error specifically
            Storage::disk('public')->delete($path); // Clean up
            
            \Log::error('OCR_CRASH: ' . $e->getMessage()); // Log the real error for you to see

            return back()->withErrors([
                'id_file' => 'The system could not process this image. Please make sure the file is a clear, bright photo of your ID card.'
            ]);
        }
    }
    
    public function step3()
    {
        // Ensure previous steps exist
        if (!session()->has('register.step1') || !session()->has('register.step2')) {
            return redirect()->route('register.step1')->withErrors(['error' => 'Please complete previous steps first.']);
        }

        return view('auth.register.step3');
    }

    public function postStep3(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,Email',
            'password' => [
                'required',
                'string',
                'confirmed',
                Password::min(8)
                    ->letters()      // Requires at least one letter
                    ->mixedCase()    // Requires both uppercase and lowercase
                    ->numbers()      // Requires at least one number
                    ->symbols(),     // Requires at least one symbol (!, @, #, $, etc.)
            ],
        ]);

        $step1 = session('register.step1');
        $step2 = session('register.step2');

        if (!$step1 || !$step2) {
            return redirect()->route('register.step1')->withErrors(['error' => 'Session expired. Please start over.']);
        }

        DB::beginTransaction();

        try {
            // 1. Save to 'users' table
            $user = \App\Models\User::create([
                'Barangay_ID'            => $step1['Barangay_ID'],
                'Verification_Status_ID' => $step2['verification_status_id'] ?? 3,
                'Account_Status_ID'      => 1, // Active
                'Username'               => $request->email,
                'Password'               => \Illuminate\Support\Facades\Hash::make($request->password),
                'First_Name'             => $step1['First_Name'],
                'Middle_Name'            => $step1['Middle_Name'],
                'Last_Name'              => $step1['Last_Name'],
                'Contact_Number'         => $step1['Contact_Number'],
                'Email'                  => $request->email,
                'Address'                => $step1['Address'],
                'Registration_Date'      => now(),
            ]);

            // 2. Save to 'ml_ocr_processing' table
           if (!empty($step2['id_file_path'])) {
                \App\Models\MlOcrProcessing::create([
                    'User_ID'               => $user->User_ID,
                    'CertificateType_ID'    => 1,
                    'Document_Image_Path'   => $step2['id_file_path'],
                    'Extracted_Text'        => json_encode(trim(strval($step2['raw_text']))), 
                    'Parsed_Data'           => json_encode($step2['scores']), 
                    'Confidence_Score'      => $step2['confidence_score'],
                    'Address_Match_Status'  => ($step2['address_score'] >= 0.7 ? 'Matched' : 'Discrepancy'),
                    'Created_Date'          => now(),
                ]);
            }

            DB::commit();

            // 3. Log them in and clean up
            \Illuminate\Support\Facades\Auth::login($user);
            session()->forget('register');

            return redirect()->route('dashboard')->with('success', 'Registration complete!');

        } catch (\Exception $e) {
            DB::rollBack();
            // This will stop the reload and show the actual error message on a white screen
            dd($e->getMessage()); 
        }
    }

    /**
     * Show the standalone verification form for logged-in users
     */
    public function showReverifyForm()
    {
        // If already verified, don't let them upload again
        if (Auth::user()->Verification_Status_ID == 2) {
            return redirect()->route('dashboard')->with('info', 'Your account is already verified.');
        }
        
        // We reuse the Step 2 view but ensure the form POSTs to verify.process
        return view('auth.register.step2')->with('isReverifying', true);
    }

    /**
     * Process late ID upload for existing users
     */
    public function processReverify(Request $request)
    {
        $request->validate([
            'id_file' => 'required|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $user = Auth::user();
        
        // Process File
        $path = $request->file('id_file')->store('ids', 'public');
        $absolutePath = storage_path('app/public/' . $path);

        try {
            $ocr = new TesseractOCR($absolutePath);
            $ocr->executable('C:\Program Files\Tesseract-OCR\tesseract.exe'); 
            $ocrText = $ocr->run();

            if (empty(trim($ocrText))) {
                Storage::disk('public')->delete($wpath);
                return back()->withErrors(['id_file' => 'No readable text found. Please upload a clearer photo.']);
            }

            // --- Reuse your existing scoring logic ---
            $normalizedOcr = $this->normalizeText($ocrText);
            $ocrTokens = explode(' ', $normalizedOcr);

            $firstNameScore = $this->getCompositeScore($user->First_Name, $ocrTokens);
            $lastNameScore  = $this->getCompositeScore($user->Last_Name, $ocrTokens);
            $addressScore   = 0.5; // Default or run your address logic here

            $totalScore = ($lastNameScore * 0.40) + ($firstNameScore * 0.35) + ($addressScore * 0.25);
            $statusID = ($totalScore >= 0.7) ? 2 : 1; 

            // Update User
            $user->update(['Verification_Status_ID' => $statusID]);

            // Save to ML table
            MlOcrProcessing::create([
                'User_ID'               => $user->User_ID,
                'CertificateType_ID'    => 1,
                'Document_Image_Path'   => $path,
                'Extracted_Text'        => json_encode(trim(strval($ocrText))), 
                'Parsed_Data'           => json_encode(['first' => $firstNameScore, 'last' => $lastNameScore]), 
                'Confidence_Score'      => $totalScore,
                'Address_Match_Status'  => ($addressScore >= 0.7 ? 'Matched' : 'Discrepancy'),
                'Created_Date'          => now(),
            ]);

            if ($statusID == 2) {
                return redirect()->route('dashboard')->with('success', 'Verification successful! You can now book appointments.');
            } else {
                return redirect()->route('dashboard')->with('warning', 'ID uploaded, but details did not match perfectly. Admin will review it.');
            }

        } catch (\Exception $e) {
            Storage::disk('public')->delete($path);
            return back()->withErrors(['id_file' => 'Error processing ID: ' . $e->getMessage()]);
        }
    }
}
