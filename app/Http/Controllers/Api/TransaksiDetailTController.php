<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiDetailT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BarangEntryM;
use App\Models\LiveOrderT;

class TransaksiDetailTController extends Controller
{
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $details = TransaksiDetailT::with('transaksi')->get();

        if ($details->isEmpty()) {
            return response()->json(['message' => 'No transaction details found.'], 404);
        }

        return response()->json([
            'message' => 'Transaction details retrieved successfully.',
            'data' => $details
        ], 200);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validatedData = $request->validate([
            'transaksidetail_id' => 'nullable|string',
            'transaksidetail_transaksi_id' => 'required|integer|exists:transaksi_t,transaksi_id',
            'transaksidetail_barang_id' => 'required|integer|exists:barangentry_m,barangentry_id',
            'transaksidetail_jumlah_barang' => 'required|integer|min:1',
            'transaksidetail_harga_barang' => 'required|numeric|min:0',
            'transaksidetail_status_penjualan' => 'required|integer',
            'transaksidetail_platform' => 'nullable|string'
        ]);

        return DB::transaction(function () use ($validatedData) {

            $barang = BarangEntryM::lockForUpdate()->findOrFail(
                $validatedData['transaksidetail_barang_id']
            );

            // 🔎 CEK: apakah transaksi ini berasal dari LIVE
            $isFromLive = LiveOrderT::where(
                'live_order_barang_id',
                $validatedData['transaksidetail_barang_id']
            )->exists();

            // ❌ Kurangi stok HANYA jika BUKAN dari live
            if (!$isFromLive) {

                if ($barang->barangentry_jumlah_barang < $validatedData['transaksidetail_jumlah_barang']) {
                    return response()->json([
                        'message' => 'Stok barang tidak mencukupi'
                    ], 422);
                }

                $barang->decrement(
                    'barangentry_jumlah_barang',
                    $validatedData['transaksidetail_jumlah_barang']
                );
            }

            $validatedData['create_id'] = Auth::id();

            $detail = TransaksiDetailT::updateOrCreate(
                ['transaksidetail_id' => $validatedData['transaksidetail_id'] ?? null],
                $validatedData
            );

            return response()->json([
                'message' => 'Transaction detail created successfully.',
                'data' => $detail
            ], 201);
        });
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $detail = TransaksiDetailT::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Transaction detail not found.'], 404);
        }

        return response()->json([
            'message' => 'Transaction detail retrieved successfully.',
            'data' => $detail
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $detail = TransaksiDetailT::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Transaction detail not found.'], 404);
        }

        $data = $request->all();
        $data['update_id'] = Auth::id(); // ⬅️ Save who updated

        $detail->update($data);

        return response()->json([
            'message' => 'Transaction detail updated successfully.',
            'data' => $detail
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $detail = TransaksiDetailT::find($id);

        if (!$detail) {
            return response()->json(['message' => 'Transaction detail not found.'], 404);
        }

        $barang = BarangEntryM::find($detail->transaksidetail_barang_id);
        if ($barang) {
            $barang->increment('barangentry_jumlah_barang', $detail->transaksidetail_jumlah_barang);
        }

        $detail->delete_id = Auth::id();
        $detail->save();
        $detail->delete();

        return response()->json([
            'message' => 'Transaction detail deleted successfully.'
        ], 200);
    }

    // public function updateStatusPenjualan($transaksidetail_id)
    // {
    //     if ($resp = $this->checkAuth()) return $resp;
    //     $detail = TransaksiDetailT::find($transaksidetail_id);
    //     if (!$detail) {
    //         return response()->json(['message' => 'Transaction detail not found.'], 404);
    //     }
    //     $detail->transaksidetail_status_penjualan = 1;
    //     $detail->update_id = Auth::id();
    //     $detail->save();
    //     return response()->json([
    //         'message' => 'Transaction detail updated successfully.',
    //         'data' => $detail
    //     ], 200);
    // }

    public function updateStatusPenjualan($transaksi_id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        $detail = TransaksiDetailT::where('transaksidetail_transaksi_id', $transaksi_id)->update([
            'transaksidetail_status_penjualan' => 1,
            'update_id' => Auth::id()
        ]);
        if (!$detail) {
            return response()->json(['message' => 'Transaction detail not found.'], 404);
        }
        return response()->json([
            'message' => 'Transaction detail updated successfully.',
            'data' => $detail
        ], 200);
    }
}
