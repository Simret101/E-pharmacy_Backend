<?php

namespace App\Services;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use App\Models\Prescription;
use Illuminate\Support\Str;

class OcrService
{
    private $googleVisionApiKey;
    private $googleVisionEndpoint = 'https://vision.googleapis.com/v1/images:annotate';

    public function __construct()
    {
        $this->googleVisionApiKey = env('GOOGLE_VISION_API_KEY');
        
        if (empty($this->googleVisionApiKey)) {
            throw new \Exception('GOOGLE_VISION_API_KEY is not set in .env file');
        }
    }

    public function processPrescription($imagePath, $userId)
    {
        try {
            $client = new Client();
            
            // Download image from URL (e.g. Cloudinary)
            $response = $client->get($imagePath);
            $imageContent = $response->getBody()->getContents();
            
            // Get file extension
            $fileExtension = pathinfo($imagePath, PATHINFO_EXTENSION);
            if (!in_array(strtolower($fileExtension), ['pdf', 'jpg', 'png', 'jpeg', 'bmp', 'gif', 'tif', 'tiff', 'webp'])) {
                throw new \Exception('Invalid file extension');
            }

            // Generate unique hash for the prescription
            $prescriptionHash = $this->generatePrescriptionHash($imageContent);

            // Check if prescription has been used before
            $existingPrescription = Prescription::where('prescription_uid', $prescriptionHash)
                ->where('user_id', $userId)
                ->first();

            if ($existingPrescription) {
                throw new \Exception('This prescription has already been used');
            }

            // Save to a temp file
            $tempFile = tempnam(sys_get_temp_dir(), 'ocr_');
            file_put_contents($tempFile, $imageContent);

            try {
                $text = $this->processWithGoogleVision($tempFile);
                $textHash = $this->generateTextHash($text);
                
                // Store the prescription in database
                $prescription = new Prescription([
                    'prescription_uid' => $prescriptionHash,
                    'user_id' => $userId,
                    'attachment_path' => $imagePath,
                    'refill_allowed' => 1, // Default to 1 refill
                    'refill_used' => 0,
                    'status' => 'active',
                    'ocr_text' => $text,
                    'ocr_text_hash' => $textHash
                ]);
                $prescription->save();

                return [
                    'text' => $text,
                    'prescription_id' => $prescription->id,
                    'prescription_uid' => $prescriptionHash
                ];
            } finally {
                // Clean up temporary file
                if (file_exists($tempFile)) {
                    unlink($tempFile);
                }
            }
        } catch (\Exception $e) {
            Log::error('Prescription Processing Error', [
                'image_path' => $imagePath,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    private function generatePrescriptionHash($imageContent)
    {
        // Generate a unique hash based on image content and timestamp
        $hash = hash('sha256', $imageContent . time());
        return $hash;
    }

    private function generateTextHash($text)
    {
        // Generate a hash of the OCR text
        return hash('sha256', $text);
    }

    private function processWithGoogleVision($imagePath)
    {
        try {
            $client = new Client();
            
            // Encode image to base64
            $imageData = base64_encode(file_get_contents($imagePath));
            
            // Prepare the request body
            $requestBody = [
                'requests' => [
                    [
                        'image' => [
                            'content' => $imageData
                        ],
                        'features' => [
                            [
                                'type' => 'TEXT_DETECTION',
                                'maxResults' => 1
                            ]
                        ]
                    ]
                ]
            ];

            // Make API request
            $response = $client->post($this->googleVisionEndpoint, [
                'query' => [
                    'key' => $this->googleVisionApiKey
                ],
                'json' => $requestBody
            ]);

            $result = json_decode($response->getBody(), true);

            if (isset($result['responses'][0]['textAnnotations'][0]['description'])) {
                return trim($result['responses'][0]['textAnnotations'][0]['description']);
            }

            throw new \Exception('No text found by Google Vision');
        } catch (\Exception $e) {
            Log::error('Google Vision Error', [
                'error' => $e->getMessage(),
                'code' => $e->getCode()
            ]);
            throw $e;
        }
    }
}
