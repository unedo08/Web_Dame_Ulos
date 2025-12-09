<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PackagingM;
use App\Models\TransaksiDetailT;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class PackagingMController extends Controller
{
    private function checkAuth()
    {
        if (!Auth::check()) {
            return response()->json([
                'code'    => 401,
                'message' => 'Unauthorized. Please login.',
                'data'    => null
            ], 401);
        }
        return null;
    }

    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PackagingM::all();

        return response()->json([
            'status' => true,
            'message' => 'List of all packaging',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'packaging_transactiondetail_id' => 'nullable|integer',
            'packaging_nama_akun' => 'required|string|max:255',
            'packaging_alamat' => 'nullable|string',
        ]);

        // Tambah create_id & update_id otomatis
        $validated['create_id'] = Auth::id();
        $validated['update_id'] = Auth::id();

        $packaging = PackagingM::create($validated);

        // Update status transaksi detail jika ada ID
        if (!empty($validated['packaging_transactiondetail_id'])) {
            TransaksiDetailT::where('transaksidetail_id', $validated['packaging_transactiondetail_id'])
                ->update(['transaksidetail_status_penjualan' => 1]);
        }

        return response()->json([
            'status' => true,
            'message' => 'Packaging created successfully',
            'data' => $packaging
        ], 201);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        $packaging = PackagingM::find($id);

        if (!$packaging) {
            return response()->json([
                'status' => false,
                'message' => 'Packaging not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Packaging detail',
            'data' => $packaging
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $packaging = PackagingM::find($id);

        if (!$packaging) {
            return response()->json([
                'status' => false,
                'message' => 'Packaging not found',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'packaging_transactiondetail_id' => 'nullable|integer',
            'packaging_nama_akun' => 'required|string|max:255',
            'packaging_alamat' => 'nullable|string',
        ]);

        // update_id otomatis
        $validated['update_id'] = Auth::id();

        $packaging->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Packaging updated successfully',
            'data' => $packaging
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $packaging = PackagingM::find($id);

        if (!$packaging) {
            return response()->json([
                'status' => false,
                'message' => 'Packaging not found',
                'data' => null
            ], 404);
        }

        // Simpan siapa yang menghapus
        $packaging->delete_id = Auth::id();
        $packaging->save();

        // Soft delete
        $packaging->delete();

        return response()->json([
            'status' => true,
            'message' => 'Packaging deleted successfully',
            'data' => null
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $packaging = PackagingM::find($id);

        if (!$packaging) {
            return response()->json([
                'status' => false,
                'message' => 'Packaging not found',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'packaging_status' => 'required|string',
        ]);

        $packaging->update_id = Auth::id();
        $packaging->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Packaging Status updated successfully',
            'data' => $packaging
        ], 200);
    }
}
