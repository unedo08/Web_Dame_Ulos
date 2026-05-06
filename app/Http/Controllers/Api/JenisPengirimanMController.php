<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JenisPengirimanM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JenisPengirimanMController extends Controller
{
    public function getActive()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisPengirimanM::where('jenispengiriman_status', 1)
            ->orderBy('jenispengiriman_nama')
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data jenis pengiriman aktif',
            'data'    => $data,
        ], 200);
    }

    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisPengirimanM::orderBy('created_at', 'desc')->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data jenis pengiriman',
            'data'    => $data,
        ], 200);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisPengirimanM::find($id);
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
            'jenispengiriman_nama'   => 'required|string|max:100',
            'jenispengiriman_status' => 'required|in:0,1',
        ]);

        $validated['jenispengiriman_kode'] = strtoupper(str_replace(' ', '_', $request->jenispengiriman_nama));
        $validated['create_id']            = Auth::id();

        $data = JenisPengirimanM::create($validated);

        return response()->json([
            'code'    => 201,
            'message' => 'Jenis pengiriman berhasil ditambahkan',
            'data'    => $data,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisPengirimanM::find($id);
        if (!$data) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'jenispengiriman_nama'   => 'required|string|max:100',
            'jenispengiriman_status' => 'required|in:0,1',
        ]);

        $validated['jenispengiriman_kode'] = strtoupper(str_replace(' ', '_', $request->jenispengiriman_nama));
        $validated['update_id']            = Auth::id();

        $data->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Jenis pengiriman berhasil diupdate',
            'data'    => $data,
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = JenisPengirimanM::find($id);
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
            'message' => 'Jenis pengiriman berhasil dihapus',
            'data'    => null,
        ], 200);
    }
}
