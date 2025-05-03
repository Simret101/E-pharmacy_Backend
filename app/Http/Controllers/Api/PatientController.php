<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; 
use App\Http\Resources\PatientResource;
use App\Http\Resources\PatientCollection;
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

    public function getAllPatients()
    {
        $patients = User::where('is_role', 1)->get();

        if ($patients->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No patients found in the database'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'patients' => $patients
        ]);
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
                'message' => 'No patients found',
                'data' => []
            ]);
        }

       
        return PatientResource::collection($patients);
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

        
        return new PatientResource($patient);
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

        
        return new PatientResource($patient);
    }

    public function destroy($id)
    {
        $patient = User::findOrFail($id);

        if ($patient->is_role != 1) { 
            return response()->json([
                'status' => 'error',
                'message' => 'User is not a patient'
            ], 400);
        }

        $patient->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Patient deleted successfully'
        ]);
    }
}
