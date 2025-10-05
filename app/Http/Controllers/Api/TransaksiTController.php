<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiT;
use App\Models\CustomerM;
use Illuminate\Http\Request;

class TransaksiTController extends Controller
{
    public function index()
    {
        $transaksis = TransaksiT::with('details')->get();

        if ($transaksis->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data transaksi yang ditemukan.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Data transaksi berhasil diambil.',
            'data' => $transaksis
        ], 200);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaksi_id'             => 'nullable|string',
            'transaksi_nama_customer'  => 'required|string|max:255',
            'transaksi_nomor_telepon'  => 'required|string|max:20',
            'transaksi_jumlah_barang'  => 'required|integer|min:1',
            'transaksi_total_harga'    => 'required|numeric|min:0',
            'transaksi_cara_bayar'     => 'nullable|string',
            'transaksi_tipe'           => 'nullable|string',
            'transaksi_status'         => 'nullable|string',
            'transaksi_catatan'        => 'nullable|string',
        ]);

        try {
            $customer = CustomerM::firstOrCreate(
                [
                    'customer_notelepon' => $validated['transaksi_nomor_telepon'],
                ],
                [
                    'customer_nama'     => $validated['transaksi_nama_customer'],
                    'customer_alamat'   => '-', // default value if not provided
                    'customer_akun'     => null,
                    'customer_platform' => '-',
                ]
            );

            $record = TransaksiT::updateOrCreate(
                ['transaksi_id' => $validated['transaksi_id'] ?? null],
                array_merge($validated, [
                    'transaksi_customer_id' => $customer->customer_id,
                    'transaksi_status' => $validated['transaksi_status'] ?? 'pending',
                ])
            );

            return response()->json([
                'code'    => 201,
                'message' => 'Transaksi dan customer berhasil disimpan.',
                'customer'=> $customer,
                'data'    => $record,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function show($id)
    {
        $transaksi = TransaksiT::with('details')->find($id);

        if (!$transaksi) {
            return response()->json([
                'message' => 'Data transaksi tidak ditemukan.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Data transaksi berhasil diambil.',
            'data' => $transaksi
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        
        $transaksi = TransaksiT::find($id);
        if (!$transaksi) {
            return response()->json([
                'message' => 'Data transaksi tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $transaksi->status = $request->status;
        $transaksi->save();

        // Return success response with message and updated data
        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => $transaksi
        ], 200);

        
    }



    public function destroy($id)
    {
        $transaksi = TransaksiT::find($id);
        if (!$transaksi) {
            return response()->json([
                'message' => 'Data transaksi tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $transaksi->delete();
        return response()->json([
            'message' => 'Transaction deleted successfully.'
        ], 200);
    }

    public function getHoldTransactions()
    {
        $transactions = TransaksiT::where('transaksi_status', 'hold')->get();

        if ($transactions->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data transaksi yang ditemukan.',
                'data' => []
            ], 404);
        }
        
        return response()->json([
            'message' => 'Transactions with status hold retrieved successfully.',
            'data' => $transactions
        ], 200);
    }

}
