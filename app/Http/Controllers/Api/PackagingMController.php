<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PackagingM;
use App\Models\TransaksiDetailT;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class PackagingMController extends Controller
{
    public function index()
    {
        $data = PackagingM::all();
        return response()->json([
            'status' => true,
            'message' => 'List of all packaging',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'packaging_transactiondetail_id' => 'nullable|integer',
            'packaging_nama_akun' => 'required|string|max:255',
            'packaging_alamat' => 'nullable|string',
        ]);

        $packaging = PackagingM::create($validated);

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

        $packaging->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Packaging updated successfully',
            'data' => $packaging
        ], 200);
    }

    public function destroy($id)
    {
        $packaging = PackagingM::find($id);

        if (!$packaging) {
            return response()->json([
                'status' => false,
                'message' => 'Packaging not found',
                'data' => null
            ], 404);
        }

        $packaging->delete();

        return response()->json([
            'status' => true,
            'message' => 'Packaging deleted successfully',
            'data' => null
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
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

        $packaging->update($validated);

        return response()->json([
            'status' => true,
            'message' => 'Packaging Status updated successfully',
            'data' => $packaging
        ], 200);
    }
}
