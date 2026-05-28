<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisPengeluaranM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JenisPengeluaranMController extends Controller
{
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisPengeluaranM::orderBy('created_at', 'desc')->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data jenis pengeluaran',
            'data'    => $data,
        ], 200);
    }

    public function getActive()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisPengeluaranM::where('jenis_pengeluaran_status', 1)
            ->orderBy('jenis_pengeluaran_nama')
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data jenis pengeluaran aktif',
            'data'    => $data,
        ], 200);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'jenis_pengeluaran_nama'   => 'required|string|max:100',
            'jenis_pengeluaran_status' => 'required|in:0,1',
        ]);

        $validated['jenis_pengeluaran_kode'] = strtoupper(str_replace(' ', '_', $request->jenis_pengeluaran_nama));
        $validated['create_id']              = Auth::id();

        $data = JenisPengeluaranM::create($validated);

        return response()->json([
            'code'    => 201,
            'message' => 'Jenis pengeluaran berhasil ditambahkan',
            'data'    => $data,
        ], 201);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisPengeluaranM::find($id);
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

        $data = JenisPengeluaranM::find($id);
        if (!$data) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'jenis_pengeluaran_nama'   => 'required|string|max:100',
            'jenis_pengeluaran_status' => 'required|in:0,1',
        ]);

        $validated['jenis_pengeluaran_kode'] = strtoupper(str_replace(' ', '_', $request->jenis_pengeluaran_nama));
        $validated['update_id']              = Auth::id();

        $data->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Jenis pengeluaran berhasil diupdate',
            'data'    => $data,
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisPengeluaranM::find($id);
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
            'message' => 'Jenis pengeluaran berhasil dihapus',
            'data'    => null,
        ], 200);
    }
}
