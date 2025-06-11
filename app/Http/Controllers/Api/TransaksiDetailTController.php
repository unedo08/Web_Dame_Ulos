<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiDetailT;
use Illuminate\Http\Request;

class TransaksiDetailTController extends Controller
{
    public function index()
    {
        $details = TransaksiDetailT::with('transaksi')->get();

        if ($details->isEmpty()) {
            return response()->json([
                'message' => 'No transaction details found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Transaction details retrieved successfully.',
            'data' => $details
        ], 200);
    }


    public function store(Request $request)
    {
        // Validate the request parameters
        $validatedData = $request->validate([
            'transaksidetail_transaksi_id' => 'required|integer|exists:transaksi_t,transaksi_id',
            'transaksidetail_barang_id' => 'required|integer|exists:barangentry_m,barangentry_id',
            'transaksidetail_jumlah_barang' => 'required|integer|min:1',
            'transaksidetail_harga_barang' => 'required|numeric|min:0',
        ]);

        // Create the transaction detail using validated data
        $detail = TransaksiDetailT::create($validatedData);

        return response()->json([
            'message' => 'Transaction detail created successfully.',
            'data' => $detail
        ], 201);
    }

    public function show($id)
    {
        $detail = TransaksiDetailT::find($id);

        if (!$detail) {
            return response()->json([
                'message' => 'Transaction detail not found.'
            ], 404);
        }

        return response()->json([
            'message' => 'Transaction detail retrieved successfully.',
            'data' => $detail
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $detail = TransaksiDetailT::find($id);

        if (!$detail) {
            return response()->json([
                'message' => 'Transaction detail not found.'
            ], 404);
        }

        $detail->update($request->all());

        return response()->json([
            'message' => 'Transaction detail updated successfully.',
            'data' => $detail
        ], 200);
    }

    public function destroy($id)
    {
        $detail = TransaksiDetailT::find($id);

        if (!$detail) {
            return response()->json([
                'message' => 'Transaction detail not found.'
            ], 404);
        }

        $detail->delete();

        return response()->json([
            'message' => 'Transaction detail deleted successfully.'
        ], 200);
    }

}
