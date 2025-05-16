<?php

namespace App\Services;

use Cloudinary\Cloudinary;
use Cloudinary\Exception\ApiException;
use Illuminate\Support\Facades\Config;

class CloudinaryService
{
    protected $cloudinary;

    public function __construct()
    {
        $this->cloudinary = new Cloudinary([
            'cloud' => [
                'cloud_name' => Config::get('services.cloudinary.cloud_name'),
                'api_key' => Config::get('services.cloudinary.api_key'),
                'api_secret' => Config::get('services.cloudinary.api_secret'),
            ],
            'url' => [
                'secure' => true,
                'transformation' => [
                    'width' => 800,
                    'height' => 800,
                    'crop' => 'limit'
                ]
            ]
        ]);
    }

    /**
     * Upload an image to Cloudinary
     *
     * @param \Illuminate\Http\UploadedFile $file
     * @param string $folder
     * @return array
     */
    public function uploadImage($file, $folder = 'profile_pictures')
    {
        try {
            $result = $this->cloudinary->uploadApi()->upload(
                $file->getRealPath(),
                [
                    'folder' => $folder,
                    'resource_type' => 'image',
                    'unique_filename' => true,
                    'overwrite' => false,
                    'use_filename' => true
                ]
            );

            return [
                'url' => $result->getSecureUrl(),
                'public_id' => $result->getPublicId()
            ];
        } catch (ApiException $e) {
            throw new \Exception('Failed to upload image: ' . $e->getMessage());
        }
    }

    /**
     * Delete an image from Cloudinary
     *
     * @param string $publicId
     * @return bool
     */
    public function deleteImage($publicId)
    {
        try {
            $this->cloudinary->uploadApi()->destroy($publicId);
            return true;
        } catch (ApiException $e) {
            throw new \Exception('Failed to delete image: ' . $e->getMessage());
        }
    }

    /**
     * Get image URL from Cloudinary
     *
     * @param string $publicId
     * @return string
     */
    public function getImageUrl($publicId)
    {
        try {
            return $this->cloudinary->image($publicId)->toUrl();
        } catch (ApiException $e) {
            throw new \Exception('Failed to get image URL: ' . $e->getMessage());
        }
    }
}
