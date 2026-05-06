<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlatformMController extends Controller
{
    public function getActive()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PlatformM::where('platform_status', 1)
            ->orderBy('platform_nama')
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data platform aktif',
            'data'    => $data,
        ], 200);
    }

    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PlatformM::orderBy('created_at', 'desc')->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data platform',
            'data'    => $data,
        ], 200);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PlatformM::find($id);
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
            'platform_nama'   => 'required|string|max:100',
            'platform_status' => 'required|in:0,1',
        ]);

        $validated['platform_kode'] = strtoupper(str_replace(' ', '_', $request->platform_nama));
        $validated['create_id']     = Auth::id();

        $data = PlatformM::create($validated);

        return response()->json([
            'code'    => 201,
            'message' => 'Platform berhasil ditambahkan',
            'data'    => $data,
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PlatformM::find($id);
        if (!$data) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'platform_nama'   => 'required|string|max:100',
            'platform_status' => 'required|in:0,1',
        ]);

        $validated['platform_kode'] = strtoupper(str_replace(' ', '_', $request->platform_nama));
        $validated['update_id']     = Auth::id();

        $data->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Platform berhasil diupdate',
            'data'    => $data,
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PlatformM::find($id);
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
            'message' => 'Platform berhasil dihapus',
            'data'    => null,
        ], 200);
    }
}
