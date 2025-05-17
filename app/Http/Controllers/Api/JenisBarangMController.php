<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisBarangM;

class JenisBarangMController extends Controller
{
    // GET /api/jenisbarang
    public function index()
    {
        return response()->json(JenisBarangM::all(), 200);
    }

    // GET /api/jenisbarang/{id}
    public function show($id)
    {
        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($item, 200);
    }

    // POST /api/jenisbarang
    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenisbarang_nama' => 'required|string|max:100',
            'jenisbarang_kode' => 'required|string|max:50',
            'jenisbarang_tipe' => 'required|string|max:50',
        ]);

        if($validated['jenisbarang_tipe']  == "tunggal"){
            $validated['jenisbarang_jumlah'] = 1;
        }else{
            $validated['jenisbarang_jumlah'] = 0;
        }

        $item = JenisBarangM::create($validated);
        return response()->json($item, 201);
    }

    // PUT /api/jenisbarang/{id}
    public function update(Request $request, $id)
    {
        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $validated = $request->validate([
            'jenisbarang_nama' => 'sometimes|required|string|max:100',
            'jenisbarang_kode' => 'sometimes|required|string|max:50',
            'jenisbarang_tipe' => 'sometimes|required|string|max:50',
            'jenisbarang_jumlah' => 'sometimes|required|integer',
        ]);

        $item->update($validated);
        return response()->json($item, 200);
    }

    public function updateJumlahAndGetBarcode(Request $request, $id){
        $item = JenisBarangM::find($id);
        if($item->jenisbarang_tipe != "tunggal"){
            $validated = $request->validate([
                'jenisbarang_jumlah' => 'required|integer',
            ]);

            $item->update($validated);
        }
        $jumlahBarang = $item->jenisbarang_tipe == "tunggal" ? $item->jenisbarang_jumlah: $validated['jenisbarang_jumlah'];
        


    }

    // DELETE /api/jenisbarang/{id}
    public function destroy($id)
    {
        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $item->delete();
        return response()->json(['message' => 'Deleted successfully'], 200);
    }
}
