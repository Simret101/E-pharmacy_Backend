<?php
namespace App\Customs\Services;

use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageService
{
    public function storeImage(Request $request, $userId)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $imagePath = $request->file('image')->store('profile_pictures', 'public');

        
        $existingImage = Image::where('user_id', $userId)->first();
        if ($existingImage) {
            Storage::disk('public')->delete($existingImage->image_path);
            $existingImage->delete();
        }

        return Image::create([
            'user_id' => $userId,
            'image_path' => $imagePath,
        ]);
    }

    public function getProfilePicture($userId)
    {
        return Image::where('user_id', $userId)->first();
    }
    public function updateImage(Request $request, $userId)
{
    $request->validate([
        'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
    ]);

    $existingImage = Image::where('user_id', $userId)->first();

    if ($existingImage) {
        Storage::disk('public')->delete($existingImage->image_path);
        $existingImage->delete();
    }

    $imagePath = $request->file('image')->store('profile_pictures', 'public');

    return Image::create([
        'user_id' => $userId,
        'image_path' => $imagePath,
    ]);
}


    public function deleteProfilePicture($userId)
    {
        $image = Image::where('user_id', $userId)->first();
        if (!$image) return false;

        Storage::disk('public')->delete($image->image_path);
        return $image->delete();
    }
}
