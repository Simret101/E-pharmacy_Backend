<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; 

use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function getAllPharmacists()
    {
        $pharmacists = User::where('is_role', 2)->get();

        if ($pharmacists->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No pharmacists found in the database'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'pharmacists' => $pharmacists
        ]);
    }

    public function getAllPatients(Request $request)
    {
        try {
            $query = User::where('is_role', 1);

            // Apply search
            if ($request->has('search')) {
                $searchTerm = $request->search;
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', "%{$searchTerm}%")
                      ->orWhere('email', 'like', "%{$searchTerm}%")
                      ->orWhere('phone', 'like', "%{$searchTerm}%")
                      ->orWhere('address', 'like', "%{$searchTerm}%");
                });
            }

            // Apply filters
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }
            if ($request->has('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            // Apply sorting
            $sortBy = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';
            $query->orderBy($sortBy, $sortOrder);

            // Paginate results
            $perPage = $request->per_page ?? 10;
            $patients = $query->paginate($perPage)->withQueryString();

            if ($patients->isEmpty()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No patients found'
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Patients retrieved successfully',
                'data' => $patients,
                'meta' => [
                    'current_page' => $patients->currentPage(),
                    'from' => $patients->firstItem(),
                    'last_page' => $patients->lastPage(),
                    'per_page' => $patients->perPage(),
                    'to' => $patients->lastItem(),
                    'total' => $patients->total(),
                    'active_count' => $patients->where('status', 'active')->count(),
                    'inactive_count' => $patients->where('status', 'inactive')->count()
                ],
                'links' => [
                    'first' => $patients->url(1),
                    'last' => $patients->url($patients->lastPage()),
                    'prev' => $patients->previousPageUrl(),
                    'next' => $patients->nextPageUrl()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve patients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request)
    {
        $search_param = $request->query('search');

        $patients_query = User::where('is_role', 1); 

        if ($search_param) {
            $patients_query->where('name', 'LIKE', "%{$search_param}%")
                           ->orWhere('email', 'LIKE', "%{$search_param}%");
        }

        $patients = $patients_query->get();

        if ($patients->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No patients found',
                'data' => []
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => $patients
        ]);
    }

    public function show($id)
    {
        $patient = User::findOrFail($id);

        if ($patient->is_role != 1) { 
            return response()->json([
                'status' => 'error',
                'message' => 'User is not a patient'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'data' => $patient
        ]);
    }

    public function update(Request $request, $id)
    {
        $patient = User::findOrFail($id);

        if ($patient->is_role != 1) { 
            return response()->json([
                'status' => 'error',
                'message' => 'User is not a patient'
            ], 400);
        }

        $patient->update($request->only('address'));

        return response()->json([
            'status' => 'success',
            'data' => $patient
        ]);
    }

}
