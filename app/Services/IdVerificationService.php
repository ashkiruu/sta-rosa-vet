<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class IDVerificationService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim(config('ml.url'), '/');
        $this->apiKey = config('ml.key');
        $this->timeout = (int) config('ml.timeout', 60);
    }


    public function verifyIDAuthenticity(string $imagePath): array
    {
        try {
            if (!file_exists($imagePath)) {
                return [
                    'is_legitimate' => false,
                    'confidence' => 0.0,
                    'success' => false,
                    'error' => 'Image file not found'
                ];
            }

            $imageContent = file_get_contents($imagePath);
            if ($imageContent === false) {
                return [
                    'is_legitimate' => false,
                    'confidence' => 0.0,
                    'success' => false,
                    'error' => 'Could not read image file'
                ];
            }

            // ✅ Add API key header
            $headers = [];
            if (!empty($this->apiKey)) {
                $headers['X-ML-API-KEY'] = $this->apiKey;
            }
            // Check if url is being used
            Log::info('ML_API_TARGET', [
                'url' => $this->baseUrl . '/verify-id'
            ]);      

            $response = Http::timeout($this->timeout)
                ->withHeaders($headers)
                ->attach('image', $imageContent, basename($imagePath))
                ->post($this->baseUrl . '/verify-id');

            if ($response->successful()) {
                $result = $response->json();

                Log::info('ML_API_SUCCESS', [
                    'file' => basename($imagePath),
                    'is_legitimate' => $result['is_legitimate'] ?? false,
                    'confidence' => $result['confidence'] ?? 0.0
                ]);

                return [
                    'is_legitimate' => (bool)($result['is_legitimate'] ?? false),
                    'confidence' => (float)($result['confidence'] ?? 0.0),
                    'success' => true,
                    'error' => null
                ];
            }

            // Better error message for debugging
            Log::error('ML_API_HTTP_ERROR', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            $msg = 'ML API returned HTTP ' . $response->status();
            if ($response->status() === 401) $msg .= ' (Unauthorized: missing/invalid API key)';
            if ($response->status() === 413) $msg .= ' (File too large)';

            return [
                'is_legitimate' => false,
                'confidence' => 0.0,
                'success' => false,
                'error' => $msg
            ];

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('ML_API_CONNECTION_FAILED', [
                'error' => $e->getMessage(),
                'api_url' => $this->baseUrl
            ]);

            return [
                'is_legitimate' => false,
                'confidence' => 0.0,
                'success' => false,
                'error' => 'ML API unavailable (connection failed)'
            ];

        } catch (\Exception $e) {
            Log::error('ML_API_EXCEPTION', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'is_legitimate' => false,
                'confidence' => 0.0,
                'success' => false,
                'error' => 'ML API error: ' . $e->getMessage()
            ];
        }


    }
}
