<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisBarangM;

class JenisBarangMController extends Controller
{

    public function index()
    {
        return response()->json(JenisBarangM::all(), 200);
    }

    
    public function show($id)
    {
        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($item, 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenisbarang_nama' => 'required|string|max:100',
            'jenisbarang_kode' => 'required|string|max:50',
        ]);
        $validated['jenisbarang_jumlah'] = 1;
        $item = JenisBarangM::create($validated);
        return response()->json($item, 201);
    }

    public function update(Request $request, $id)
    {
        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $validated = $request->validate([
            'jenisbarang_nama' => 'sometimes|required|string|max:100',
            'jenisbarang_kode' => 'sometimes|required|string|max:50',
        ]);

        $item->update($validated);
        return response()->json($item, 200);
    }

    public function updateJumlahAndGetBarcode(Request $request, $id){
        $item = JenisBarangM::find($id);
        $validated = $request->validate([
            'jenisbarang_jumlah' => 'required|integer',
        ]);
        $item->update($validated);
    }

    public function destroy($id)
    {
        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $item->delete();
        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function getAllJenisBarangAmount(){
        $jumlah = JenisBarangM::count();

        return response()->json([
            'message' => 'Total jumlah barang entry',
            'code' => 200,
            'total' => $jumlah
        ]);
    }
}
