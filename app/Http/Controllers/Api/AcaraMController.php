<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcaraM;
use App\Models\AcaradetM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AcaraMController extends Controller
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
        return response()->json([
            'message' => 'Data retrieved',
            'code' => 200,
            'data' => AcaraM::all()
        ], 200);
    }

    public function addAcara(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'acara_status' => 'required|string',
        ]);

        $acara = AcaraM::find($id);
        if (!$acara) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $acara->acara_status = $request->acara_status;
        $acara->save();

        return response()->json([
            'message' => 'Status updated',
            'code' => 200,
            'data' => $acara
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $acara = AcaraM::find($id);
        if (!$acara) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        // simpan siapa yang delete
        $acara->delete_id = Auth::id();
        $acara->save();

        // update delete_id untuk detail
        AcaradetM::where('acaradet_acara_id', $acara->acara_id)->update([
            'delete_id' => Auth::id(),
        ]);

        // hapus detail
        AcaradetM::where('acaradet_acara_id', $acara->acara_id)->delete();

        // hapus acara
        $acara->delete();

        return response()->json([
            'message' => 'Data deleted',
            'code' => 200
        ], 200);
    }

    public function exportData($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $acara = AcaraM::find($id);
        if (!$acara) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $acaraDet = AcaradetM::where('acaradet_acara_id', $acara->acara_id)->get();

        return response()->json([
            'message' => 'Data found',
            'code' => 200,
            'data' => [
                'acara' => $acara,
                'detail' => $acaraDet
            ]
        ], 200);
    }
}
