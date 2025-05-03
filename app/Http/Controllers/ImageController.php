<?php
namespace App\Http\Controllers;

use App\Customs\Services\ImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImageController extends Controller
{
    protected $imageService;

    public function __construct(ImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        $image = $this->imageService->storeImage($request, $user->id);

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'image' => $image
        ], 201);
    }

    public function show()
    {
        $userId = Auth::id();
        $image = $this->imageService->getProfilePicture($userId);

        return $image
            ? response()->json($image, 200)
            : response()->json(['message' => 'No profile picture found'], 404);
    }
    public function update(Request $request)
{
    $userId = auth()->id();

    $image = $this->imageService->updateImage($request, $userId);

    return response()->json([
        'message' => 'Profile picture updated successfully',
        'image' => $image
    ], 200);
}

    public function destroy()
    {
        $userId = Auth::id();
        return $this->imageService->deleteProfilePicture($userId)
            ? response()->json(['message' => 'Profile picture deleted successfully'], 200)
            : response()->json(['message' => 'No profile picture found'], 404);
    }
}
