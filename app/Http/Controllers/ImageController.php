<?php
namespace App\Http\Controllers;

use App\Customs\Services\CloudinaryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ImageController extends Controller
{
    protected $cloudinaryService;

    public function __construct(CloudinaryService $cloudinaryService)
    {
        $this->cloudinaryService = $cloudinaryService;
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        // Check if file exists first
        if (!$request->hasFile('image')) {
            return response()->json([
                'message' => 'No image file provided'
            ], 400);
        }

        // Get the file
        $file = $request->file('image');

        // Validate the file
        $validator = Validator::make(['image' => $file], [
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid image file',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Upload image to Cloudinary
            $result = $this->cloudinaryService->uploadImage($file, 'profile_pictures');

            // Update user's profile picture
            $user->update([
                'profile_image' => $result['secure_url'],
                'cloudinary_public_id' => $result['public_id']
            ]);

            return response()->json([
                'message' => 'Profile picture updated successfully',
                'image' => [
                    'url' => $result['secure_url'],
                    'public_id' => $result['public_id']
                ]
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show()
    {
        $user = Auth::user();

        if (!$user->profile_image) {
            return response()->json([
                'message' => 'No profile picture found'
            ], 404);
        }

        return response()->json([
            'image' => [
                'url' => $user->profile_image,
                'public_id' => $user->cloudinary_public_id
            ]
        ], 200);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Check if file exists first
        if (!$request->hasFile('image')) {
            return response()->json([
                'message' => 'No image file provided'
            ], 400);
        }

        // Get the file
        $file = $request->file('image');

        // Validate the file
        $validator = Validator::make(['image' => $file], [
            'image' => 'required|image|mimes:jpg,jpeg,png|max:2048'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Invalid image file',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // If there's an existing image, delete it first
            if ($user->cloudinary_public_id) {
                $this->cloudinaryService->deleteImage($user->cloudinary_public_id);
            }

            // Upload new image to Cloudinary
            $result = $this->cloudinaryService->uploadImage($file, 'profile_pictures');

            // Update user's profile picture
            $user->update([
                'profile_image' => $result['secure_url'],
                'cloudinary_public_id' => $result['public_id']
            ]);

            return response()->json([
                'message' => 'Profile picture updated successfully',
                'image' => [
                    'url' => $result['secure_url'],
                    'public_id' => $result['public_id']
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy()
    {
        $user = Auth::user();

        if (!$user->profile_image) {
            return response()->json([
                'message' => 'No profile picture found'
            ], 404);
        }

        try {
            // Delete from Cloudinary
            $this->cloudinaryService->deleteImage($user->cloudinary_public_id);

            // Remove from database
            $user->update([
                'profile_image' => null,
                'cloudinary_public_id' => null
            ]);

            return response()->json([
                'message' => 'Profile picture deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete profile picture',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
