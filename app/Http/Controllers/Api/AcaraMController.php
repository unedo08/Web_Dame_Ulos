<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcaraM;
use App\Models\AcaradetM;
use Illuminate\Http\Request;

class AcaraMController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Data retrieved',
            'code' => 200,
            'data' => AcaraM::all()
        ], 200);
    }

    public function addAcara(Request $request){
        $data = $request->validate([
            'acara_nama' => 'required|string',
            'acara_keterangan' => 'nullable|string'
        ]);

        $acara = AcaraM::create($data);

        return response()->json([
            'message' => 'Acara created',
            'code' => 201,
            'data' => $acara
        ], 201);
    }

    public function updateAcara(Request $request, $id)
    {
        $request->validate([
            'acara_jumlahbarang' => 'required|integer',
            'acara_modalbarang' => 'required|numeric',
            'acara_harganetbarang' => 'required|numeric',
            'acara_hargapricetagbarang' => 'required|numeric',
            'acara_keterangan' => 'nullable|string',
            'acara_status' => 'nullable|string',
        ]);

        $updated = AcaraM::find($id);
        if (!$updated) {
            return response()->json([
                'message' => 'Data Acara not found',
                'code' => 404
            ], 404);
        }

        $updated->update($request->all());

        return response()->json([
            'message' => 'Acara updated successfully',
            'code' => 200,
            'data' => $updated
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'acara_nama' => 'required|string',
            'acara_jumlahbarang' => 'required|integer',
            'acara_modalbarang' => 'required|numeric',
            'acara_harganetbarang' => 'required|numeric',
            'acara_hargapricetagbarang' => 'required|numeric',
            'acara_keterangan' => 'nullable|string',
            'acara_status' => 'required|string',
        ]);

        $acara = AcaraM::create($data);

        return response()->json([
            'message' => 'Acara created',
            'code' => 201,
            'data' => $acara
        ], 201);
    }

    public function show($id)
    {
        $acara = AcaraM::find($id);
        if (!$acara) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data found',
            'code' => 200,
            'data' => $acara
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $acara = AcaraM::find($id);
        if (!$acara) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $acara->update($request->all());

        return response()->json([
            'message' => 'Acara updated',
            'code' => 200,
            'data' => $acara
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'acara_status' => 'required|string',
        ]);

        $acara = AcaraM::find($id);
        if (!$acara) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $acara->acara_status = $request->input('acara_status');
        $acara->save();

        return response()->json([
            'message' => 'Status updated',
            'code' => 200,
            'data' => $acara
        ], 200);
    }

    public function destroy($id)
    {
        $acara = AcaraM::find($id);
        if (!$acara) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        $acara->delete();
        if(!$deleted->isEmpty()){
             $deleted = AcaradetM::where('acaradet_acara_id', $acara->acara_id)->delete();
        }

        return response()->json([
            'message' => 'Data deleted',
            'code' => 200
        ], 200);
    }
}

