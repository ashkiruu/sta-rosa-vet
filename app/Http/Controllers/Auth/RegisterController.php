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
use App\Services\IDVerificationService; 
use Illuminate\Support\Str;
use Google\Cloud\Storage\StorageClient;
use App\Services\GcsStorageService;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;


class RegisterController extends Controller
{
    public function notice()
    {
        return view('auth.register.notice');
    }

    public function postNotice(Request $request)
    {
        $request->validate([
            'agree' => 'required|accepted',
        ]);

        session(['register.notice_accepted' => true]);

        return redirect()->route('register.step1');
    }

    public function step1()
    {
        if (!session('register.notice_accepted')) {
            return redirect()->route('register.notice');
        }

        $barangays = \App\Models\Barangay::all();
        $data = session('register.step1', []);

        return view('auth.register.step1', compact('barangays', 'data'));
    }


    public function postStep1(Request $request)
    {
        $validated = $request->validate([
            // Name fields: Letters, spaces, hyphens, apostrophes, periods only
            'Last_Name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[A-Za-z][A-Za-z\s\-\'\.]*$/'
            ],
            'First_Name' => [
                'required',
                'string',
                'min:2',
                'max:50',
                'regex:/^[A-Za-z][A-Za-z\s\-\'\.]*$/'
            ],
            'Middle_Name' => [
                'nullable',
                'string',
                'max:50',
                'regex:/^[A-Za-z][A-Za-z\s\-\'\.]*$/'
            ],
            
            // Mobile number: Must be 10 digits starting with 9 (Philippine format)
            'Contact_Number' => [
                'required',
                'string',
                'size:10',
                'regex:/^9[0-9]{9}$/'
            ],
            
            // Address: Alphanumeric with common address characters
            'Address' => [
                'required',
                'string',
                'min:5',
                'max:500'
            ],
            
            // Barangay: Must exist in database
            'Barangay_ID' => 'required|exists:barangays,Barangay_ID',
            
            // Civil Status: Must be one of the allowed values
            'Civil_Status' => 'required|string|in:Single,Married,Widowed,Separated',
            
            // Years of Residency: Must be 0-100
            'Years_Of_Residency' => [
                'required',
                'integer',
                'min:0',
                'max:100'
            ],
            
            // Birthdate: Must be at least 18 years old
            'Birthdate' => [
                'required',
                'date',
                'before:' . now()->subYears(18)->format('Y-m-d'),
                'after:' . now()->subYears(120)->format('Y-m-d')
            ],
        ], [
            // Custom error messages
            'Last_Name.regex' => 'Last name must start with a letter and contain only letters, spaces, hyphens, apostrophes, and periods.',
            'Last_Name.min' => 'Last name must be at least 2 characters.',
            'First_Name.regex' => 'First name must start with a letter and contain only letters, spaces, hyphens, apostrophes, and periods.',
            'First_Name.min' => 'First name must be at least 2 characters.',
            'Middle_Name.regex' => 'Middle name must start with a letter and contain only letters, spaces, hyphens, apostrophes, and periods.',
            'Contact_Number.regex' => 'Please enter a valid Philippine mobile number (10 digits starting with 9).',
            'Contact_Number.size' => 'Mobile number must be exactly 10 digits.',
            'Address.min' => 'Address must be at least 5 characters.',
            'Address.max' => 'Address cannot exceed 500 characters.',
            'Years_Of_Residency.min' => 'Years of residency cannot be negative.',
            'Years_Of_Residency.max' => 'Years of residency cannot exceed 100 years.',
            'Birthdate.before' => 'You must be at least 18 years old to register.',
            'Birthdate.after' => 'Please enter a valid date of birth.',
        ]);

        // Sanitize/normalize the data before storing
        $validated['First_Name'] = $this->sanitizeName($validated['First_Name']);
        $validated['Last_Name'] = $this->sanitizeName($validated['Last_Name']);
        if (!empty($validated['Middle_Name'])) {
            $validated['Middle_Name'] = $this->sanitizeName($validated['Middle_Name']);
        }
        
        // Remove any non-numeric characters from contact number (extra safety)
        $validated['Contact_Number'] = preg_replace('/[^0-9]/', '', $validated['Contact_Number']);
        
        // Trim and clean address
        $validated['Address'] = trim($validated['Address']);

        session(['register.step1' => $validated]);

        return redirect()->route('register.step2');
    }

    /**
     * Sanitize name fields: trim, normalize spaces, capitalize properly
     */
    private function sanitizeName(string $name): string
    {
        // Trim whitespace
        $name = trim($name);
        
        // Normalize multiple spaces to single space
        $name = preg_replace('/\s+/', ' ', $name);
        
        // Capitalize each word properly (handles hyphenated names)
        $name = preg_replace_callback('/(?:^|[\s\-])(\w)/u', function($matches) {
            return strtoupper($matches[0]);
        }, strtolower($name));
        
        return $name;
    }


    public function step2()
    {
        if (!session('register.notice_accepted')) {
            return redirect()->route('register.notice');
        }

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

    private function isFromStaRosa(string $normalizedOcrText): bool
    {
        $allowedKeywords = [
            'STA ROSA',
            'SANTA ROSA',
            'STA. ROSA',
            'SANTA ROSA CITY',
            'STA ROSA CITY',
            'LAGUNA',
            'SANTA ROSA, LAGUNA',
            'CITY OF SANTA ROSA',
            'CITY OF STA ROSA',
            'SANTA ROSA LAGUNA',
            'CITY OF SANTA ROSA, LAGUNA'
        ];

        foreach ($allowedKeywords as $keyword) {
            if (str_contains($normalizedOcrText, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function normalizeIdImageToJpeg(\Illuminate\Http\UploadedFile $file): array
    {
        $tmpDir = storage_path('app/tmp/ids');
        if (!is_dir($tmpDir)) {
            @mkdir($tmpDir, 0755, true);
        }

        $outPath = $tmpDir . '/' . \Illuminate\Support\Str::uuid() . '.jpg';
        $inPath  = $file->getRealPath();

        // Detect whether ImageMagick uses `magick` or `convert`
        $bin = 'magick';
        $probe = new \Symfony\Component\Process\Process([$bin, '-version']);
        $probe->run();
        if (!$probe->isSuccessful()) {
            $bin = 'convert';
        }

        $cmd = [
            $bin,
            $inPath,
            '-auto-orient',
            '-resize', '2000x2000>',
            '-strip',
            '-quality', '85',
            $outPath,
        ];

        $process = new \Symfony\Component\Process\Process($cmd);
        $process->setTimeout(25);
        $process->run();

        if (
            !$process->isSuccessful() ||
            !file_exists($outPath) ||
            filesize($outPath) === 0
        ) {
            \Log::error('IMAGE_NORMALIZATION_FAILED', [
                'bin'      => $bin,
                'stderr'  => $process->getErrorOutput(),
                'stdout'  => $process->getOutput(),
                'mime'    => $file->getMimeType(),
                'orig'    => $file->getClientOriginalName(),
                'size'    => $file->getSize(),
            ]);

            throw new \RuntimeException(
                'Image normalization failed. ImageMagick/HEIC support missing or input invalid.'
            );
        }

        return [
            'path' => $outPath,
            'mime' => 'image/jpeg',
            'ext'  => 'jpg',
        ];
    }


    public function postStep2(Request $request, IDVerificationService $mlService)
    {
        \Log::info('UPLOAD_DEBUG', [
            'hasFile' => $request->hasFile('id_file'),
            'files' => array_keys($request->allFiles()),
            'php_upload_max' => ini_get('upload_max_filesize'),
            'php_post_max' => ini_get('post_max_size'),
            'loaded_ini' => php_ini_loaded_file(),
            'scanned_ini' => php_ini_scanned_files(),
            'content_length' => $request->server('CONTENT_LENGTH'),
        ]);


        $request->validate([
            // allow HEIC/HEIF from iPhone/iPad, and bigger size for mobile
            'id_file' => 'nullable|file|mimetypes:image/jpeg,image/png,image/heic,image/heif|max:20480',
        ]);

        // Skip logic
        if (!$request->hasFile('id_file')) {
            session(['register.step2' => [
                'id_file_path'           => null,
                'id_file_disk'           => null,
                'verification_status_id' => 3, // Not Verified
                'confidence_score'       => 0,
                'ml_confidence'          => 0,
                'ml_check_passed'        => false,
                'verification_method'    => null,
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

        $file = $request->file('id_file');

        // Normalize HEIC/PNG/JPEG -> JPEG for ML/OCR
        $normalized = $this->normalizeIdImageToJpeg($file);
        $absolutePath = $normalized['path'];

        if (!file_exists($absolutePath) || filesize($absolutePath) === 0) {
            throw new \RuntimeException('Normalized image file missing or empty.');
        }

        // GCS UPLOAD (TMP - UBLA SAFE)
        $filename = 'id_' . now()->format('Ymd_His') . '_' . Str::random(10) . '.jpg';

        $bucketName = env('GOOGLE_CLOUD_STORAGE_BUCKET');
        $projectId  = env('GOOGLE_CLOUD_PROJECT_ID', env('GCLOUD_PROJECT'));
        $prefix     = trim((string) env('GOOGLE_CLOUD_STORAGE_PATH_PREFIX', ''), '/');

        $gcsPath = ($prefix !== '' ? $prefix . '/' : '') . 'id_uploads/tmp/' . $filename;

        \Log::info('BEFORE_GCS_UPLOAD', [
            'bucket' => $bucketName,
            'project' => $projectId,
            'path' => $gcsPath,
        ]);

        $bucket = null;

        try {
            $storage = new StorageClient(['projectId' => $projectId]);
            $bucket  = $storage->bucket($bucketName);

            $bucket->upload(
                fopen($absolutePath, 'r'),
                [
                    'name' => $gcsPath,
                    'metadata' => [
                        'contentType' => 'image/jpeg',
                    ],
                ]
            );

        } catch (\Throwable $e) {
            \Log::error('GCS_UPLOAD_EXCEPTION', [
                'message' => $e->getMessage(),
                'class'   => get_class($e),
                'bucket'  => $bucketName,
                'path'    => $gcsPath,
            ]);

            if ($absolutePath && file_exists($absolutePath)) {
                @unlink($absolutePath);
            }

            throw $e;
        }

        \Log::info('AFTER_GCS_UPLOAD', ['gcs_path' => $gcsPath]);

        // CLEANUP HELPER
        $cleanupFiles = function () use ($absolutePath, $gcsPath, $bucket) {
            \Log::warning('CLEANUP_CALLED', [
                'local_abs' => $absolutePath,
                'gcs'       => $gcsPath,
            ]);

            if ($absolutePath && file_exists($absolutePath)) {
                @unlink($absolutePath);
            }

            if ($gcsPath && $bucket) {
                $object = $bucket->object($gcsPath);
                if ($object->exists()) {
                    $object->delete();
                }
            }
        };

        // STEP 1: ML DOCUMENT AUTHENTICITY CHECK
        $mlResult = $mlService->verifyIDAuthenticity($absolutePath);

        $ML_THRESHOLD = 0.80;
        $mlCheckPassed = false;
        $mlConfidence = 0.0;

        if (!$mlResult['success']) {
            \Log::warning('ML_API_UNAVAILABLE', [
                'error' => $mlResult['error'] ?? 'Unknown error',
                'fallback_mode' => 'continuing_with_ocr_only',
                'user' => [
                    'first_name' => $step1['First_Name'],
                    'last_name'  => $step1['Last_Name'],
                ],
                'storage' => [
                    'gcs_path' => $gcsPath,
                    'local_abs' => $absolutePath,
                ]
            ]);

            $mlCheckPassed = false;
            $mlConfidence = 0.0;

        } else {
            $mlConfidence = (float) ($mlResult['confidence'] ?? 0);

            if (!$mlResult['is_legitimate'] || $mlConfidence < $ML_THRESHOLD) {
                $cleanupFiles();

                \Log::warning('ML_REJECTED_FAKE_ID', [
                    'confidence' => $mlConfidence,
                    'threshold' => $ML_THRESHOLD,
                    'is_legitimate' => $mlResult['is_legitimate'],
                    'user' => [
                        'first_name' => $step1['First_Name'],
                        'last_name'  => $step1['Last_Name'],
                    ],
                    'storage' => [
                        'gcs_path' => $gcsPath,
                        'local_abs' => $absolutePath,
                    ]
                ]);

                return back()->withErrors([
                    'id_file' => 'The uploaded document does not appear to be a valid government-issued ID. Please upload a clear photo of your PhilSys, UMID, Driver\'s License, or other official ID card.'
                ])->withInput();
            }

            $mlCheckPassed = true;

            \Log::info('ML_APPROVED_ID', [
                'confidence' => $mlConfidence,
                'proceeding_to_ocr' => true,
                'storage' => [
                    'gcs_path' => $gcsPath,
                    'local_abs' => $absolutePath,
                ]
            ]);
        }

        // STEP 2: OCR TEXT EXTRACTION
        try {
            $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($absolutePath);

            if (stripos(PHP_OS, 'WIN') === 0) {
                $ocr->executable('C:\Program Files\Tesseract-OCR\tesseract.exe');
            } else {
                $ocr->executable('/usr/bin/tesseract');
            }

            $ocrText = $ocr->run();

            if (empty(trim($ocrText))) {
                $cleanupFiles();

                return back()->withErrors([
                    'id_file' => 'No readable text found. Please ensure you are uploading a clear photo of your ID.'
                ])->withInput();
            }

            $normalizedOcr = $this->normalizeText($ocrText);
            $ocrTokens = explode(' ', $normalizedOcr);

            // STEP 2.5: CITY VALIDATION (Sta. Rosa Only)
            $fromStaRosa = $this->isFromStaRosa($normalizedOcr);

            if (!$fromStaRosa) {
                $cleanupFiles();

                \Log::warning('OUTSIDE_STA_ROSA_DETECTED', [
                    'user' => [
                        'first_name' => $step1['First_Name'],
                        'last_name'  => $step1['Last_Name'],
                    ],
                    'ocr_text_snippet' => substr($normalizedOcr, 0, 300),
                    'storage' => [
                        'gcs_path' => $gcsPath,
                        'local_abs' => $absolutePath,
                    ]
                ]);

                return back()->withErrors([
                    'id_file' => 'This system is designed exclusively for Sta. Rosa City pet owners. Based on the submitted ID, your address does not appear to be within Sta. Rosa. Please contact your local City Veterinary Office for further assistance.'
                ])->withInput();
            }

            $firstName  = $step1['First_Name'] ?? '';
            $middleName = $step1['Middle_Name'] ?? '';
            $lastName   = $step1['Last_Name'] ?? '';
            $address    = $this->normalizeText($step1['Address'] ?? '');

            // STEP 3: FUZZY MATCHING SCORES
            $firstNameScore = $this->getCompositeScore($firstName, $ocrTokens);
            $lastNameScore  = $this->getCompositeScore($lastName, $ocrTokens);

            $hasMiddleName = !empty($middleName);
            $middleNameScore = $hasMiddleName ? $this->getCompositeScore($middleName, $ocrTokens) : 0;

            $addressTokens = explode(' ', $address);
            $addressMatches = 0;
            $validAddressTokens = 0;

            foreach ($addressTokens as $token) {
                if (strlen($token) < 3) continue;
                $validAddressTokens++;
                foreach ($ocrTokens as $ocrToken) {
                    if ($this->similarity($token, $ocrToken) >= 0.80) {
                        $addressMatches++;
                        break;
                    }
                }
            }

            $addressScore = ($validAddressTokens > 0) ? ($addressMatches / $validAddressTokens) : 0;

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

            // STEP 4: FINAL DECISION
            if ($mlCheckPassed && $totalScore >= 0.7) {
                $statusID = 2;
                $verificationMethod = 'ml_and_ocr';
            } elseif (!$mlCheckPassed && $totalScore >= 0.7) {
                $statusID = 1;
                $verificationMethod = 'ocr_only_ml_unavailable';
            } else {
                $statusID = 1;
                $verificationMethod = 'pending_manual_review';
            }

            if ($totalScore < 0.15) {
                $cleanupFiles();

                return back()->withErrors([
                    'id_file' => 'The uploaded document does not appear to match your registered name. Please upload a valid ID.'
                ])->withInput();
            }

            \Log::info('ID_VERIFICATION_COMPLETE', [
                'ml' => [
                    'check_passed' => $mlCheckPassed,
                    'confidence' => $mlConfidence,
                    'api_available' => $mlResult['success']
                ],
                'ocr' => [
                    'first_name_score' => $firstNameScore,
                    'middle_name_score' => $middleNameScore,
                    'last_name_score' => $lastNameScore,
                    'address_score' => $addressScore,
                    'total_score' => $totalScore,
                ],
                'decision' => [
                    'status_id' => $statusID,
                    'verification_method' => $verificationMethod,
                    'ml_threshold' => $ML_THRESHOLD,
                    'ocr_threshold' => 0.7
                ],
                'storage' => [
                    'gcs_path' => $gcsPath,
                ],
                'user' => [
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                ]
            ]);

            session(['register.step2' => [
                'id_file_path'           => $gcsPath,
                'id_file_disk'           => 'gcs',
                'verification_status_id' => $statusID,
                'confidence_score'       => $totalScore,
                'ml_confidence'          => $mlConfidence,
                'ml_check_passed'        => $mlCheckPassed,
                'verification_method'    => $verificationMethod,
                'raw_text'               => substr($ocrText, 0, 500),
                'address_score'          => $addressScore,
                'scores'                 => [
                    'first_name'  => $firstNameScore,
                    'last_name'   => $lastNameScore,
                    'middle_name' => $middleNameScore,
                    'address'     => $addressScore
                ]
            ]]);

            if ($absolutePath && file_exists($absolutePath)) {
                @unlink($absolutePath);
            }

            if ($statusID == 2) {
                $message = 'ID verified successfully! Both ML and text matching passed.';
            } elseif (!$mlCheckPassed) {
                $message = 'ID uploaded. ML verification unavailable - pending manual review.';
            } else {
                $message = 'ID uploaded. Text matching below threshold - pending manual review.';
            }

            return redirect()->route('register.step3')->with([
                'ocr_status'  => ($statusID == 2 ? 'Verified' : 'Pending'),
                'ocr_message' => $message
            ]);

        } catch (\Throwable $e) {
            $cleanupFiles();

            \Log::error('OCR_PROCESSING_ERROR', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->withErrors([
                'id_file' => 'The system could not process this image. Please make sure the file is a clear, bright photo of your ID card.'
            ])->withInput();
        }
    }

    
    public function step3()
    {
        if (!session('register.notice_accepted')) {
            return redirect()->route('register.notice');
        }

        if (!session()->has('register.step1') || !session()->has('register.step2')) {
            return redirect()->route('register.step1')->withErrors(['error' => 'Please complete previous steps first.']);
        }

        $step2 = session('register.step2');
        if ($step2 && isset($step2['verification_status_id'])) {
            $statusId = $step2['verification_status_id'];
            
            if ($statusId == 2) {
                session()->flash('ocr_status', 'Verified');
            } elseif ($statusId == 1) {
                session()->flash('ocr_status', 'Pending');
            } else {
                session()->forget('ocr_status');
            }
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
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $step1 = session('register.step1');
        $step2 = session('register.step2');

        if (!$step1) {
            return redirect()->route('register.step1')
                ->withErrors(['error' => 'Please complete Step 1 first.']);
        }

        $step2 = $step2 ?? [
            'id_file_path' => null,
            'id_file_disk' => null,
            'verification_status_id' => 3,
            'confidence_score' => 0,
            'ml_confidence' => 0,
            'ml_check_passed' => false,
            'verification_method' => null,
            'raw_text' => '',
            'address_score' => 0,
            'scores' => [],
        ];

        DB::beginTransaction();

        try {
            $user = \App\Models\User::create([
                'Barangay_ID'            => $step1['Barangay_ID'],
                'Verification_Status_ID' => $step2['verification_status_id'] ?? 3,
                'Account_Status_ID'      => 1,
                'Username'               => $request->email,
                'Password'               => Hash::make($request->password),
                'First_Name'             => $step1['First_Name'],
                'Middle_Name'            => $step1['Middle_Name'] ?? null,
                'Last_Name'              => $step1['Last_Name'],
                'Contact_Number'         => $step1['Contact_Number'],
                'Email'                  => $request->email,
                'Address'                => $step1['Address'],
                'Registration_Date'      => now(),
                'Civil_Status'           => $step1['Civil_Status'] ?? null,
                'Years_Of_Residency'     => $step1['Years_Of_Residency'] ?? null,
                'Birthdate'              => $step1['Birthdate'] ?? null,
            ]);

            $finalGcsPath = $step2['id_file_path'] ?? null;

            if (!empty($step2['id_file_path']) && ($step2['id_file_disk'] ?? null) === 'gcs') {

                $bucketName = env('GOOGLE_CLOUD_STORAGE_BUCKET');
                $projectId  = env('GOOGLE_CLOUD_PROJECT_ID', env('GCLOUD_PROJECT'));

                $storage = new StorageClient(['projectId' => $projectId]);
                $bucket  = $storage->bucket($bucketName);

                $tmpPath = $step2['id_file_path'];
                $filename = basename($tmpPath);

                $finalPath = 'ids/id_uploads/users/' . $user->User_ID . '/' . $filename;

                \Log::info('GCS_FINALIZE_MOVE_START', [
                    'from' => $tmpPath,
                    'to'   => $finalPath,
                    'user' => $user->User_ID,
                ]);

                $src = $bucket->object($tmpPath);

                if (!$src->exists()) {
                    throw new \RuntimeException("GCS tmp object not found: {$tmpPath}");
                }

                $src->copy($bucket, ['name' => $finalPath]);
                $src->delete();

                $finalGcsPath = $finalPath;

                \Log::info('GCS_FINALIZE_MOVE_DONE', [
                    'final' => $finalGcsPath,
                    'user'  => $user->User_ID,
                ]);
            }

            if (!empty($finalGcsPath)) {
                \App\Models\MlOcrProcessing::create([
                    'User_ID'               => $user->User_ID,
                    'CertificateType_ID'    => 1,
                    'Document_Image_Path'   => $finalGcsPath,
                    'Extracted_Text'        => json_encode(trim(strval($step2['raw_text'] ?? ''))),
                    'Parsed_Data'           => json_encode([
                        'fuzzy_scores' => $step2['scores'] ?? [],
                        'ml_confidence' => $step2['ml_confidence'] ?? 0,
                        'ml_check_passed' => $step2['ml_check_passed'] ?? false,
                        'verification_method' => $step2['verification_method'] ?? 'unknown'
                    ]),
                    'Confidence_Score'      => $step2['confidence_score'] ?? 0,
                    'Address_Match_Status'  => (($step2['address_score'] ?? 0) >= 0.7 ? 'Matched' : 'Discrepancy'),
                    'Created_Date'          => now(),
                ]);
            }

            DB::commit();

            Auth::login($user);

            session()->forget('register.step1');
            session()->forget('register.step2');
            session()->forget('ocr_status');
            session()->forget('ocr_message');

            return redirect()->route('dashboard')->with('success', 'Registration complete!');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('REGISTRATION_ERROR', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->withErrors(['error' => 'Registration failed: ' . $e->getMessage()]);
        }
    }


    public function showReverifyForm()
    {
        if (Auth::user()->Verification_Status_ID == 2) {
            return redirect()->route('dashboard')->with('info', 'Your account is already verified.');
        }
        
        return view('auth.register.step2')->with('isReverifying', true);
    }

    public function processReverify(Request $request)
    {
        $request->validate([
            'id_file' => 'required|file|mimetypes:image/jpeg,image/png,image/heic,image/heif|max:20480',
        ]);

        $user = Auth::user();
        $file = $request->file('id_file');

        $normalized = $this->normalizeIdImageToJpeg($file);
        $absolutePath = $normalized['path'];

        if (!file_exists($absolutePath) || filesize($absolutePath) === 0) {
            throw new \RuntimeException('Normalized image file missing or empty.');
        }

        $bucketName = env('GOOGLE_CLOUD_STORAGE_BUCKET');
        $projectId  = env('GOOGLE_CLOUD_PROJECT_ID', env('GCLOUD_PROJECT'));

        $storage = new StorageClient(['projectId' => $projectId]);
        $bucket  = $storage->bucket($bucketName);

        $filename = 'reverify_' . now()->format('Ymd_His') . '_' . Str::random(10) . '.jpg';
        $tmpGcsPath = 'ids/id_uploads/tmp/' . $filename;

        $cleanup = function () use ($absolutePath, $bucket, $tmpGcsPath) {
            if ($absolutePath && file_exists($absolutePath)) {
                @unlink($absolutePath);
            }

            if ($tmpGcsPath) {
                $obj = $bucket->object($tmpGcsPath);
                if ($obj->exists()) $obj->delete();
            }
        };

        try {
            $bucket->upload(
                fopen($absolutePath, 'r'),
                [
                    'name' => $tmpGcsPath,
                    'metadata' => [
                        'contentType' => 'image/jpeg',
                    ],
                ]
            );
        } catch (\Throwable $e) {
            \Log::error('GCS_REVERIFY_UPLOAD_FAIL', [
                'message' => $e->getMessage(),
                'user' => $user->User_ID,
                'tmp' => $tmpGcsPath,
            ]);

            if ($absolutePath && file_exists($absolutePath)) {
                @unlink($absolutePath);
            }

            return back()->withErrors(['id_file' => 'Upload failed. Please try again.']);
        }

        try {
            $ocr = new \thiagoalessio\TesseractOCR\TesseractOCR($absolutePath);

            if (stripos(PHP_OS, 'WIN') === 0) {
                $ocr->executable('C:\Program Files\Tesseract-OCR\tesseract.exe');
            } else {
                $ocr->executable('/usr/bin/tesseract');
            }

            $ocrText = $ocr->run();

            if (empty(trim($ocrText))) {
                $cleanup();
                return back()->withErrors(['id_file' => 'No readable text found. Please upload a clearer photo.']);
            }

            $normalizedOcr = $this->normalizeText($ocrText);
            $ocrTokens = explode(' ', $normalizedOcr);

            $firstNameScore = $this->getCompositeScore($user->First_Name, $ocrTokens);
            $lastNameScore  = $this->getCompositeScore($user->Last_Name, $ocrTokens);
            $addressScore   = 0.5;

            $totalScore = ($lastNameScore * 0.40) + ($firstNameScore * 0.35) + ($addressScore * 0.25);
            $statusID = ($totalScore >= 0.7) ? 2 : 1;

            $finalGcsPath = 'ids/id_uploads/users/' . $user->User_ID . '/reverify/' . $filename;

            $finalGcsPath = $this->gcsMoveObject($tmpGcsPath, $finalGcsPath);

            if ($absolutePath && file_exists($absolutePath)) {
                @unlink($absolutePath);
            }

            $user->update(['Verification_Status_ID' => $statusID]);

            MlOcrProcessing::create([
                'User_ID'               => $user->User_ID,
                'CertificateType_ID'    => 1,
                'Document_Image_Path'   => $finalGcsPath,
                'Extracted_Text'        => json_encode(trim(strval($ocrText))),
                'Parsed_Data' => json_encode([
                    'fuzzy_scores' => [
                        'first_name'  => $firstNameScore,
                        'last_name'   => $lastNameScore,
                        'middle_name' => 0,
                        'address'     => $addressScore,
                    ],
                    'ml_confidence' => 0,
                    'ml_check_passed' => false,
                    'verification_method' => 'reverify_ocr_only',
                ]),
                'Confidence_Score'      => $totalScore,
                'Address_Match_Status'  => ($addressScore >= 0.7 ? 'Matched' : 'Discrepancy'),
                'Created_Date'          => now(),
            ]);

            \Log::info('REVERIFY_FINALIZED', [
                'user' => $user->User_ID,
                'final_gcs_path' => $finalGcsPath,
                'status_id' => $statusID,
                'score' => $totalScore,
            ]);

            if ($statusID == 2) {
                return redirect()->route('dashboard')->with('success', 'Verification successful! You can now book appointments.');
            }

            return redirect()->route('dashboard')->with('warning', 'ID uploaded, but details did not match perfectly. Admin will review it.');

        } catch (\Throwable $e) {
            \Log::error('REVERIFY_PROCESSING_ERROR', [
                'message' => $e->getMessage(),
                'user' => $user->User_ID,
                'tmp' => $tmpGcsPath,
            ]);

            $cleanup();

            return back()->withErrors(['id_file' => 'Error processing ID: ' . $e->getMessage()]);
        }
    }

    private function gcsMoveObject(string $fromObjectName, string $toObjectName): string
    {
        $bucketName = env('GOOGLE_CLOUD_STORAGE_BUCKET');
        $projectId  = env('GOOGLE_CLOUD_PROJECT_ID', env('GCLOUD_PROJECT'));

        $storage = new StorageClient(['projectId' => $projectId]);
        $bucket  = $storage->bucket($bucketName);

        $src = $bucket->object($fromObjectName);

        if (!$src->exists()) {
            throw new \RuntimeException("GCS source object not found: {$fromObjectName}");
        }

        $src->copy($bucket, ['name' => $toObjectName]);
        $src->delete();

        \Log::info('GCS_OBJECT_MOVED', [
            'from' => $fromObjectName,
            'to' => $toObjectName,
            'bucket' => $bucketName,
        ]);

        return $toObjectName;
    }
}