<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisBarangM;
use Illuminate\Support\Facades\DB;

class JenisBarangMController extends Controller
{

    public function index()
    {
       $data = JenisBarangM::select(
                'jenisbarang_m.jenisbarang_id',
                'jenisbarang_m.jenisbarang_kode',
                'jenisbarang_m.jenisbarang_nama',
                DB::raw('SUM(jenisbarang_m.jenisbarang_jumlah) as jumlah_barang')
            )
            ->join('code_m', 'code_m.code_jenisbarang_id', '=', 'jenisbarang_m.jenisbarang_id')
            ->join('barangentry_m', 'barangentry_m.barangentry_code_id', '=', 'code_m.code_id')
            ->where('barangentry_m.barangentry_status', 'READY')
            ->groupBy(
                'jenisbarang_m.jenisbarang_id',
                'jenisbarang_m.jenisbarang_kode',
                'jenisbarang_m.jenisbarang_nama'
            )
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Data Jenis Barang',
            'data'    => $data
        ], 200);
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
