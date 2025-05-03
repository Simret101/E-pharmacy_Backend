<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\Like;
use App\Models\Drug;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class DrugLikeController extends Controller
{
    public function toggleLike(Request $request)
    {
        $request->validate([
            'drug_id' => 'required|exists:drugs,id',
        ]);

        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'message' => 'Unauthorized'
            ], 401);
        }

        // Fix: Use correct request parameter
        $like = Like::where('user_id', $user->id)
                    ->where('drug_id', $request->drug_id)
                    ->first();

        if ($like) {
            $like->delete();
            return response()->json([
                'message' => 'You unliked the drug',
            ], 200);
        } else {
            $like = new Like();
            $like->user_id = $user->id;

            // Fix: Correct assignment
            $like->drug_id = $request->drug_id;

            if ($like->save()) {
                return response()->json([
                    'message' => 'You liked the drug',
                    'like' => $like
                ], 201);
            } else {
                return response()->json([
                    'message' => 'An error occurred, please try again'
                ], 500);
            }
        }
    }
}
