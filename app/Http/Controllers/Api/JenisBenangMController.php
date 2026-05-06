<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisBenangM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JenisBenangMController extends Controller
{
    public function getActive()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisBenangM::where('jenisbenang_status', 1)
            ->orderBy('jenisbenang_nama')
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data jenis benang aktif',
            'data'    => $data,
        ], 200);
    }

    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisBenangM::orderBy('created_at', 'desc')->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data jenis benang',
            'data'    => $data,
        ], 200);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisBenangM::find($id);
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

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'jenisbenang_nama'   => 'required|string|max:100',
            'jenisbenang_status' => 'required|in:0,1',
        ]);

        $validated['jenisbenang_kode'] = strtoupper(str_replace(' ', '_', $request->jenisbenang_nama));
        $validated['create_id']        = Auth::id();

        $data = JenisBenangM::create($validated);

        return response()->json([
            'code'    => 201,
            'message' => 'Jenis benang berhasil ditambahkan',
            'data'    => $data,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisBenangM::find($id);
        if (!$data) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'jenisbenang_nama'   => 'required|string|max:100',
            'jenisbenang_status' => 'required|in:0,1',
        ]);

        $validated['jenisbenang_kode'] = strtoupper(str_replace(' ', '_', $request->jenisbenang_nama));
        $validated['update_id']        = Auth::id();

        $data->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Jenis benang berhasil diupdate',
            'data'    => $data,
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisBenangM::find($id);
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
            'message' => 'Jenis benang berhasil dihapus',
            'data'    => null,
        ], 200);
    }
}
