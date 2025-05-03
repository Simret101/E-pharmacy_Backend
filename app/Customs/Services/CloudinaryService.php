<?php

namespace App\Customs\Services;

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;
use GuzzleHttp\Psr7\Utils;

class CloudinaryService
{
    protected $cloudinary;
    protected $uploadApi;

    public function __construct()
    {
        // Configure Cloudinary using environment variables
        Configuration::instance([
            'cloud' => [
                'cloud_name' => env('CLOUDINARY_CLOUD_NAME', 'djcel1cai'),
                'api_key' => env('CLOUDINARY_API_KEY', '792377839957387'),
                'api_secret' => env('CLOUDINARY_API_SECRET', 'TGv0-xMnEMvvaynYkNsOQzBmQcU'),
            ],
            'url' => [
                'secure' => true
            ]
        ]);

        $this->uploadApi = new UploadApi();
    }

    public function uploadImage(UploadedFile $file, $folder = 'user_profiles')
    {
        try {
            // Get the temporary file path
            $tempPath = $file->getRealPath();
            
            if (!$tempPath) {
                throw new \Exception('Failed to get temporary file path');
            }

            // Generate a unique public ID
            $publicId = 'user_profile_' . time() . '_' . $file->getClientOriginalName();
            
            // Create a stream from the file
            $stream = Utils::streamFor(fopen($tempPath, 'r'));
            
            // Upload the file using the stream
            $result = $this->uploadApi->upload($stream, [
                'folder' => $folder,
                'public_id' => $publicId,
                'resource_type' => 'auto'
            ]);
            
            return [
                'secure_url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ];
        } catch (\Exception $e) {
            Log::error('Cloudinary upload error: ' . $e->getMessage());
            throw new \Exception('Failed to upload image: ' . $e->getMessage());
        }
    }

    public function deleteImage($publicId)
    {
        try {
            return $this->uploadApi->destroy($publicId);
        } catch (\Exception $e) {
            Log::error('Cloudinary delete error: ' . $e->getMessage());
            throw new \Exception('Failed to delete image: ' . $e->getMessage());
        }
    }

    public function getImageUrl($url)
    {
        // If the URL is already a Cloudinary URL, return it as is
        if (strpos($url, 'cloudinary.com') !== false) {
            return $url;
        }

        // If the URL is a public_id, construct the Cloudinary URL
        if (strpos($url, '/') === false) {
            return "https://res.cloudinary.com/" . env('CLOUDINARY_CLOUD_NAME') . "/image/upload/" . $url;
        }

        // If it's a local URL or path, return it as is
        return $url;
    }
} 