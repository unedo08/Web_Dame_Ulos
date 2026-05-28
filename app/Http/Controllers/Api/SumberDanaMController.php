<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SumberDanaM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SumberDanaMController extends Controller
{
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = SumberDanaM::orderBy('created_at', 'desc')->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data sumber dana',
            'data'    => $data,
        ], 200);
    }

    public function getActive()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = SumberDanaM::where('sumber_dana_status', 1)
            ->orderBy('sumber_dana_nama')
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data sumber dana aktif',
            'data'    => $data,
        ], 200);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'sumber_dana_nama'   => 'required|string|max:100',
            'sumber_dana_status' => 'required|in:0,1',
        ]);

        $validated['sumber_dana_kode'] = strtoupper(str_replace(' ', '_', $request->sumber_dana_nama));
        $validated['create_id']        = Auth::id();

        $data = SumberDanaM::create($validated);

        return response()->json([
            'code'    => 201,
            'message' => 'Sumber dana berhasil ditambahkan',
            'data'    => $data,
        ], 201);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = SumberDanaM::find($id);
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

        $data = SumberDanaM::find($id);
        if (!$data) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'sumber_dana_nama'   => 'required|string|max:100',
            'sumber_dana_status' => 'required|in:0,1',
        ]);

        $validated['sumber_dana_kode'] = strtoupper(str_replace(' ', '_', $request->sumber_dana_nama));
        $validated['update_id']        = Auth::id();

        $data->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Sumber dana berhasil diupdate',
            'data'    => $data,
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = SumberDanaM::find($id);
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
            'message' => 'Sumber dana berhasil dihapus',
            'data'    => null,
        ], 200);
    }
}
