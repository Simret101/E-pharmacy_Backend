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
        try {
            $user = Auth::user();

            // Only allow pharmacists (role 2) to access inventory logs
            if ($user->is_role !== 2) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Only pharmacists can access inventory logs'
                ], 403);
            }

            // Get logs where the user_id matches the authenticated pharmacist
            $logs = InventoryLog::with(['drug', 'user'])
                ->where('user_id', $user->id)  // Filter by the authenticated pharmacist's ID
                ->when($request->has('start_date'), function($query) use ($request) {
                    $query->whereDate('created_at', '>=', $request->start_date);
                })
                ->when($request->has('end_date'), function($query) use ($request) {
                    $query->whereDate('created_at', '<=', $request->end_date);
                })
                ->when($request->has('drug_id'), function($query) use ($request) {
                    $query->where('drug_id', $request->drug_id);
                })
                ->latest()
                ->paginate(10);

            return response()->json([
                'data' => $logs,
                'meta' => [
                    'current_page' => $logs->currentPage(),
                    'from' => $logs->firstItem(),
                    'last_page' => $logs->lastPage(),
                    'per_page' => $logs->perPage(),
                    'to' => $logs->lastItem(),
                    'total' => $logs->total(),
                    'total_amount' => $logs->sum('amount'),
                    'total_quantity' => $logs->sum('quantity')
                ],
                'links' => [
                    'first' => $logs->url(1),
                    'last' => $logs->url($logs->lastPage()),
                    'prev' => $logs->previousPageUrl(),
                    'next' => $logs->nextPageUrl(),
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve inventory logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}