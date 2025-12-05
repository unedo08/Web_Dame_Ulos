<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\CaraBayarM;
use Illuminate\Http\Request;

class CaraBayarMController extends Controller
{
    // GET ALL
    public function index()
    {
        return response()->json([
            'status' => true,
            'data'   => CaraBayarM::all()
        ]);
    }

    // STORE
    public function store(Request $request)
    {
        $validated = $request->validate([
            'carabayar_nama' => 'required|string|max:100',
            'carabayar_kode' => 'nullable|string|max:50',
        ]);

        $data = CaraBayarM::create($validated);

        return response()->json([
            'status' => true,
            'message' => 'Cara bayar berhasil ditambahkan',
            'data' => $data
        ]);
    }

    // SHOW BY ID
    public function show($id)
    {
        $data = CaraBayarM::find($id);

        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    // UPDATE
    public function update(Request $request, $id)
    {
        $data = CaraBayarM::find($id);

        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $validated = $request->validate([
            'carabayar_nama' => 'required|string|max:100',
            'carabayar_kode' => 'nullable|string|max:50',
        ]);

        $data->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil diupdate',
            'data' => $data
        ]);
    }

    // DELETE
    public function destroy($id)
    {
        $data = CaraBayarM::find($id);

        if (!$data) {
            return response()->json(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }

        $data->delete();

        return response()->json([
            'status' => true,
            'message' => 'Data berhasil dihapus'
        ]);
    }
}
