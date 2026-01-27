<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiT;
use App\Models\TransaksiDetailT;
use App\Models\CustomerM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
            'transaksi_nomor_telepon'  => 'nullable|string|max:20',
            'transaksi_jumlah_barang'  => 'required|integer|min:1',
            'transaksi_total_harga'    => 'required|numeric|min:0',
            'transaksi_cara_bayar'     => 'nullable|string',
            'transaksi_tipe'           => 'nullable|string',
            'transaksi_status'         => 'nullable|string',
            'transaksi_catatan'        => 'nullable|string',
            'transaksi_platform'       => 'nullable|string', // khusus customer
        ]);

        try {
            /** ---------------- CUSTOMER ---------------- */
            $customer = CustomerM::firstOrCreate(
                [
                    'customer_notelepon' => $validated['transaksi_nomor_telepon'],
                ],
                [
                    'customer_nama'     => $validated['transaksi_nama_customer'],
                    'customer_alamat'   => '-',
                    'customer_akun'     => null,
                    'customer_platform' => $validated['transaksi_platform'] ?? '-',
                ]
            );

            // Optional: update platform jika customer sudah ada
            if (!empty($validated['transaksi_platform'])) {
                $customer->update([
                    'customer_platform' => $validated['transaksi_platform']
                ]);
            }

            /** ---------------- TRANSAKSI ---------------- */
            $record = TransaksiT::updateOrCreate(
                ['transaksi_id' => $validated['transaksi_id'] ?? null],
                array_merge($validated, [
                    'transaksi_customer_id' => $customer->customer_id,
                    'transaksi_status'      => $validated['transaksi_status'] ?? 'pending',
                    'create_id'             => Auth::id(),
                ])
            );

            return response()->json([
                'code'     => 201,
                'status'   => true,
                'message'  => 'Transaksi dan customer berhasil disimpan.',
                'customer' => $customer,
                'data'     => $record,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'status'  => false,
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
        if (!Auth::check()) {
            return response()->json([
                'status'  => false,
                'message' => 'Akses ditolak, Anda belum login.'
            ], 401);
        }

        $request->validate([
            'status' => 'required|string',
        ]);

        $transaksi = TransaksiT::find($id);
        if (!$transaksi) {
            return response()->json([
                'status'  => false,
                'message' => 'Data transaksi tidak ditemukan.',
                'data'    => null
            ], 404);
        }

        $transaksi->transaksi_status = $request->status;
        $transaksi->update_id = Auth::id();
        $transaksi->save();

        return response()->json([
            'status'  => true,
            'message' => 'Status transaksi berhasil diperbarui.',
            'data'    => $transaksi
        ], 200);
    }



    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak, Anda belum login.'
            ], 401);
        }

        $userId = Auth::id();

        $transaksi = TransaksiT::find($id);

        if (!$transaksi) {
            return response()->json([
                'status'  => false,
                'message' => 'Data transaksi tidak ditemukan.',
                'data'    => null
            ], 404);
        }


        $transaksi->delete_id = $userId;
        $transaksi->save();

        TransaksiDetailT::where('transaksidetail_transaksi_id', $id)
            ->update(['delete_id' => $userId]);

        $transaksi->delete();
        TransaksiDetailT::where('transaksidetail_transaksi_id', $id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Transaksi dan detail berhasil dihapus oleh user ID: ' . $userId
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

    public function getTransaksiGrouped()
    {
        $transaksi = TransaksiT::with([
            'customer',
            'caraBayar',
            'user',
            'details.barang',
            'details.pengiriman'
        ])
            ->get()
            ->groupBy(function ($item) {
                return $item->transaksi_id;
            });

        $result = [];


        foreach ($transaksi as $key => $group) {
            $first = $group->first();
            $result[] = [
                'transaksi_id' => $first->transaksi_id,
                'created_at' => $first->created_at,
                'customer_nama' => $first->customer->customer_nama ?? 'Unknown',
                'transaksi_tipe' => Str::ucfirst($first->transaksi_tipe),
                'cara_bayar' => $first->caraBayar?->carabayar_nama,
                'user' => $first->user?->name,
                'transaksi_platform' => $first->transaksi_platform ?? null,
                'items' => $group->flatMap(function ($trans) use ($first) {
                    return $trans->details->map(function ($d) use ($first) {
                        return [
                            'code_nama' => $d->barang->code->code_nama ?? null,
                            'barang_nama' => $d->barang->barangentry_nama ?? null,
                            'jumlah_barang' => $d->transaksidetail_jumlah_barang,
                            'harga_barang' => $d->transaksidetail_harga_barang,
                            'barang_modal' => $d->barang->barangentry_modal ?? null,
                            'barang_price_tag' => $d->barang->barangentry_price_tag ?? null,
                            'harga_kirim_barang' => $d->pengiriman->pengirimanBarang_harga_kirim_barang ?? 0,
                            'transaksi_catatan' => $d->transaksi_catatan ?? null,
                            'transaksi_status' => $d->transaksi_status ?? null,
                            'transaksi_platform' => $first->transaksi_platform ?? null,

                        ];
                    });
                })
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Data berhasil diambil',
            'data' => $result
        ]);
    }
}
