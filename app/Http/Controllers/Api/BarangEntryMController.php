<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangEntryM;
use App\Models\CodeM;
use Illuminate\Http\Request;

class BarangEntryMController extends Controller
{
    public function index()
    {
        $data = BarangEntryM::all();
        return response()->json([
            'message' => 'Data retrieved successfully',
            'code' => 200,
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'barangentry_nama' => 'required|string',
            'barangentry_code_id' => 'required|integer',
            'barangentry_warna' => 'required|string',
            'barangentry_nama_penenun' => 'required|string',
            'barangentry_nama_panirat' => 'required|string',
            'barangentry_dryer' => 'required|string',
            'barangentry_modal' => 'required|numeric',
            'barangentry_price_tag' => 'required|numeric',
            'barangentry_harga_net' => 'required|numeric',
            'barangentry_acara' => 'required|string',
            'barangentry_ukuran_mandar' => 'required|integer',
            'barangentry_ukuran_ulos' => 'required|integer',
        ]);

        $entry = BarangEntryM::create($data);

        return response()->json([
            'message' => 'Barang entry created successfully',
            'code' => 201,
            'data' => $entry
        ], 201);
    }

    public function show($id)
    {
        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data found',
            'code' => 200,
            'data' => $entry
        ], 200);
    }

    public function getDataByCode($code_nama)
    {
        $item_code = CodeM::where('code_nama', $code_nama)->first();
        if (!$item_code) {
            return response()->json([
                'message' => 'Kode tidak ditemukan',
                'code' => 404
            ], 404);
        }

        $item = BarangEntryM::where('barangentry_code_id', $item_code->code_id)->first();
        if (!$item) {
            return response()->json([
                'message' => 'Barang entry tidak ditemukan untuk kode tersebut',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'code' => 200,
            'data' => $item
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        $entry->update($request->all());

        return response()->json([
            'message' => 'Barang entry updated successfully',
            'code' => 200,
            'data' => $entry
        ], 200);
    }

    public function updateStatusBarang(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        $entry->barangentry_status = $request->input('status');
        $entry->save();

        return response()->json([
            'message' => 'Status barang updated to ' . $request->input('status'),
            'code' => 200,
            'data' => $entry
        ], 200);
    }

    public function checkBarangEntryByCodeNama($code_nama)
    {
        $item_code = CodeM::where('code_nama', $code_nama)->first();

        if (!$item_code) {
            return response()->json([
                'message' => 'Kode tidak ditemukan',
                'code' => 404
            ], 404);
        }

        $existingEntry = BarangEntryM::where('barangentry_code_id', $item_code->code_id)->first();

        if ($existingEntry) {
            return response()->json([
                'message' => 'Kode sudah digunakan, tidak bisa entry ulang',
                'data' => ["barangentry_nama" => $existingEntry->barangentry_nama],
                'code' => 409
            ], 409);
        }

        return response()->json([
            'message' => 'Kode tersedia, bisa digunakan untuk entry',
            'code' => 200
        ], 200);
    }

    public function destroy($id)
    {
        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        $entry->delete();

        return response()->json([
            'message' => 'Data deleted successfully',
            'code' => 200
        ], 200);
    }

    public function getAllBarangEntryAmount(){
        $jumlah = BarangEntryM::count();

        return response()->json([
            'message' => 'Total jumlah barang entry',
            'code' => 200,
            'total' => $jumlah
        ]);
    }
}
