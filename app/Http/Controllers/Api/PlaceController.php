<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Place;
use App\Models\User;
class PlaceController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string',
                'address' => 'required|string',
                'lat' => 'required|numeric',
                'lng' => 'required|numeric',
            ]);

            Place::create($validated);

            return response()->json(['success' => true, 'message' => 'Place saved successfully!']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save place.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    public function userLocations()
{
    $users = User::whereNotNull('lat')
                ->whereNotNull('lng')
                ->get(['id', 'name', 'address', 'lat', 'lng']);

    return response()->json($users);
}
   
}