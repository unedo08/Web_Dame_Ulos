<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DivisiM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DivisiMController extends Controller
{
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = DivisiM::orderBy('created_at', 'desc')->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data divisi',
            'data'    => $data,
        ], 200);
    }

    public function getActive()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = DivisiM::where('divisi_status', 1)
            ->orderBy('divisi_nama')
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data divisi aktif',
            'data'    => $data,
        ], 200);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'divisi_nama'   => 'required|string|max:100',
            'divisi_status' => 'required|in:0,1',
        ]);

        $validated['divisi_kode'] = strtoupper(str_replace(' ', '_', $request->divisi_nama));
        $validated['create_id']   = Auth::id();

        $data = DivisiM::create($validated);

        return response()->json([
            'code'    => 201,
            'message' => 'Divisi berhasil ditambahkan',
            'data'    => $data,
        ], 201);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = DivisiM::find($id);
        if (!$data) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data',
            'data'    => $data,
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = DivisiM::find($id);
        if (!$data) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'divisi_nama'   => 'required|string|max:100',
            'divisi_status' => 'required|in:0,1',
        ]);

        $validated['divisi_kode'] = strtoupper(str_replace(' ', '_', $request->divisi_nama));
        $validated['update_id']   = Auth::id();

        $data->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Divisi berhasil diupdate',
            'data'    => $data,
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = DivisiM::find($id);
        if (!$data) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        $data->delete_id = Auth::id();
        $data->save();
        $data->delete();

        return response()->json([
            'code'    => 200,
            'message' => 'Divisi berhasil dihapus',
            'data'    => null,
        ], 200);
    }
}
