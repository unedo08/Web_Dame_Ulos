<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\JenisBarangM;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class JenisBarangMController extends Controller
{
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisBarangM::select(
                'jenisbarang_m.jenisbarang_id',
                'jenisbarang_m.jenisbarang_kode',
                'jenisbarang_m.jenisbarang_nama',
                DB::raw('SUM(jenisbarang_m.jenisbarang_jumlah) as jumlah_barang')
            )
            ->whereRaw('LOWER(jenisbarang_m.jenisbarang_kode) NOT LIKE ?', ['%po%'])
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
        if ($resp = $this->checkAuth()) return $resp;

        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($item, 200);
    }

    // CREATE
    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'jenisbarang_nama' => 'required|string|max:100',
            'jenisbarang_kode' => 'required|string|max:50',
        ]);

        $validated['jenisbarang_jumlah'] = 1;

        // ⬅️ Simpan create_id pakai Auth
        $validated['create_id'] = Auth::id();

        $item = JenisBarangM::create($validated);

        return response()->json($item, 201);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $validated = $request->validate([
            'jenisbarang_nama' => 'sometimes|required|string|max:100',
            'jenisbarang_kode' => 'sometimes|required|string|max:50',
        ]);

        // ⬅️ update_id
        $validated['update_id'] = Auth::id();

        $item->update($validated);

        return response()->json($item, 200);
    }

    // UPDATE JUMLAH ONLY
    public function updateJumlahAndGetBarcode(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $validated = $request->validate([
            'jenisbarang_jumlah' => 'required|integer',
        ]);

        // ⬅️ update_id
        $validated['update_id'] = Auth::id();

        $item->update($validated);

        return response()->json($item, 200);
    }

    // DELETE (SOFT DELETE)
    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $item = JenisBarangM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        // ⬅️ delete_id
        $item->delete_id = Auth::id();
        $item->save();

        $item->delete(); // soft delete

        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function getAllJenisBarangAmount()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $jumlah = JenisBarangM::count();

        return response()->json([
            'message' => 'Total jumlah barang entry',
            'code' => 200,
            'total' => $jumlah
        ]);
    }
}
