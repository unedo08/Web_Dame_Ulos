<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcaradetM;
use Illuminate\Http\Request;

class AcaradetMController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Data retrieved successfully',
            'code' => 200,
            'data' => AcaradetM::all()
        ]);
    }

    public function getByAcara($acara_id)
    {
        $items = AcaradetM::where('acaradet_acara_id', $acara_id)->get();
        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }
        return response()->json([
            'message' => 'Data retrieved successfully',
            'code' => 200,
            'data' => $items
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'acaradet_acara_id' => 'required|exists:acara_m,acara_id',
            'acaradet_barangentry_id' => 'required|exists:barangentry_m,barangentry_id',
        ]);

        
        $acaradet = AcaradetM::create($request->all());

        return response()->json([
            'message' => 'Acaradet created successfully',
            'code' => 201,
            'data' => $acaradet
        ]);
    }

    public function show($id)
    {
        $data = AcaradetM::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found', 'code' => 404], 404);
        }

        return response()->json([
            'message' => 'Data found',
            'code' => 200,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        $data = AcaradetM::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found', 'code' => 404], 404);
        }

        $request->validate([
            'acaradet_acara_id' => 'sometimes|exists:acara_m,acara_id',
            'acaradet_barangentry_id' => 'sometimes|exists:barangentry_m,barangentry_id',
        ]);

        $data->update($request->all());

        return response()->json([
            'message' => 'Data updated',
            'code' => 200,
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $data = AcaradetM::find($id);
        if (!$data) {
            return response()->json(['message' => 'Data not found', 'code' => 404], 404);
        }

        $data->delete();

        return response()->json([
            'message' => 'Data deleted successfully',
            'code' => 200
        ]);
    }
}
