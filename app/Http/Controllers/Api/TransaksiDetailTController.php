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

        $detail->delete_id = Auth::id();  // ⬅️ Save who deleted
        $detail->save();

        $detail->delete(); // If using soft delete, this will fill deleted_at

        return response()->json([
            'message' => 'Transaction detail deleted successfully.'
        ], 200);
    }
}
