<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcaradetM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcaradetMController extends Controller
{
    private function checkAuth()
    {
        if (!Auth::check()) {
            return response()->json([
                'code'    => 401,
                'message' => 'Unauthorized. Please login.',
                'data'    => null
            ], 401);
        }
        return null;
    }

    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        return response()->json([
            'message' => 'Data retrieved successfully',
            'code' => 200,
            'data' => AcaradetM::all()
        ]);
    }

    public function getByAcara($acara_id)
    {
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

        $data = AcaradetM::find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data found',
            'code' => 200,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = AcaradetM::find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
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
        if ($resp = $this->checkAuth()) return $resp;

        $data = AcaradetM::find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        // Simpan siapa yang delete
        $data->delete_id = Auth::id();
        $data->save();

        // Soft delete / hard delete sesuai kebutuhan
        $data->delete();

        return response()->json([
            'message' => 'Data deleted successfully',
            'code' => 200
        ]);
    }
}
