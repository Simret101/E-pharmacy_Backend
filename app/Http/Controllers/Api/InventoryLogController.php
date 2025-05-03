<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class InventoryLogController extends Controller
{
    public function index(Request $request)
    {
        
        if (Auth::user()->is_role !== 2) {
            return response()->json(['message' => 'Only pharmacists can view the inventory logs.'], 403);
        }

       
        $logs = InventoryLog::with('drug')->latest()->paginate(10);

        return response()->json([
            'data' => $logs,
            'meta' => [
                'current_page' => $logs->currentPage(),
                'from' => $logs->firstItem(),
                'last_page' => $logs->lastPage(),
                'per_page' => $logs->perPage(),
                'to' => $logs->lastItem(),
                'total' => $logs->total(),
            ],
            'links' => [
                'first' => $logs->url(1),
                'last' => $logs->url($logs->lastPage()),
                'prev' => $logs->previousPageUrl(),
                'next' => $logs->nextPageUrl(),
            ]
        ]);
    }
}
