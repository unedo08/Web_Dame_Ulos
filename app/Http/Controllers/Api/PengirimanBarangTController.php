<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengirimanBarangT;
use App\Models\CustomerM;
use App\Models\TransaksiDetailT;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PengirimanBarangTController extends Controller
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

        $data = PengirimanBarangT::all();

        return response()->json([
            'code' => 200,
            'message' => 'List of Pengiriman Barang retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;


        $validated = $request->validate([
            'pengirimanBarang_transaksi_id'            => 'required|integer',
            'pengirimanBarang_nama_penerima'           => 'required|string|max:255',
            'pengirimanBarang_akun_penerima'           => 'nullable|string|max:255',
            'pengirimanBarang_no_telepon'              => 'nullable|string|max:20',
            'pengirimanBarang_harga_kirim_barang'      => 'required|numeric',
            'pengirimanBarang_jenis_pengiriman_barang' => 'required|string|max:100',
            'pengirimanBarang_alamat_pengiriman_barang' => 'required|string',
            'pengirimanBarang_catatan'                 => 'nullable|string',
            'pengirimanBarang_status'                  => 'nullable|string|max:50',
        ]);

        try {
            $customer = CustomerM::updateOrCreate(
                [
                    'customer_notelepon' => $validated['pengirimanBarang_no_telepon'] ?? null,
                ],
                [
                    'customer_akun'      => $validated['pengirimanBarang_akun_penerima'] ?? null,
                    'customer_nama'      => $validated['pengirimanBarang_nama_penerima'],
                    'customer_alamat'    => $validated['pengirimanBarang_alamat_pengiriman_barang'],
                    'customer_platform'  => '-',
                ]
            );

            // Tambah create_id + update_id
            $validated['create_id'] = Auth::id();
            $validated['update_id'] = Auth::id();

            $pengiriman = PengirimanBarangT::create([
                ...$validated,
                'pengirimanBarang_status'       => $validated['pengirimanBarang_status'] ?? 'pending',
                'pengirimanBarang_customer_id'  => $customer->customer_id,
            ]);

            return response()->json([
                'status'  => true,
                'code'    => 201,
                'message' => 'Data pengiriman dan customer berhasil disimpan otomatis',
                'data'    => [
                    'customer'   => $customer,
                    'pengiriman' => $pengiriman,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'code'    => 500,
                'message' => 'Terjadi kesalahan saat menyimpan data',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        try {
            $item = PengirimanBarangT::findOrFail($id);

            return response()->json([
                'code' => 200,
                'message' => 'Pengiriman Barang retrieved successfully.',
                'data' => $item
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'message' => 'Pengiriman Barang not found.',
                'data' => null
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        try {

            if (!Auth::check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized: Please login first.'
                ], 401);
            }
            $item = PengirimanBarangT::with('customer')->findOrFail($id);

            $validated = $request->validate([
                'pengirimanBarang_transaksi_id'             => 'sometimes|integer',
                'pengirimanBarang_nama_penerima'            => 'sometimes|string|max:255',
                'pengirimanBarang_akun_penerima'            => 'nullable|string|max:255',
                'pengirimanBarang_no_telepon'               => 'nullable|string|max:20',
                'pengirimanBarang_harga_kirim_barang'       => 'sometimes|numeric',
                'pengirimanBarang_jenis_pengiriman_barang'  => 'sometimes|string|max:100',
                'pengirimanBarang_alamat_pengiriman_barang' => 'sometimes|string',
                'pengirimanBarang_catatan'                  => 'nullable|string',
                'pengirimanBarang_status'                   => 'nullable|string|max:50',
            ]);

            // Tambahkan update_id
            $validated['update_id'] = Auth::id();

            $item->update($validated);

            if (
                isset($validated['pengirimanBarang_nama_penerima']) &&
                $item->customer &&
                $validated['pengirimanBarang_nama_penerima'] !== $item->customer->customer_nama
            ) {
                $item->customer->update([
                    'customer_nama'   => $validated['pengirimanBarang_nama_penerima'],
                    'customer_alamat' => $validated['pengirimanBarang_alamat_pengiriman_barang']
                        ?? $item->customer->customer_alamat,
                ]);
            }

            return response()->json([
                'status'  => true,
                'code'    => 200,
                'message' => 'Pengiriman Barang updated successfully.',
                'data'    => [
                    'pengiriman' => $item,
                    'customer'   => $item->customer,
                ],
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status'  => false,
                'code'    => 404,
                'message' => 'Pengiriman Barang not found.',
                'data'    => null,
            ], 404);
        } catch (\Exception $e) {
            return response()->json([
                'status'  => false,
                'code'    => 500,
                'message' => 'Terjadi kesalahan saat memperbarui data.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        try {

            if (!Auth::check()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Unauthorized: Please login first.'
                ], 401);
            }
            $item = PengirimanBarangT::findOrFail($id);

            // simpan delete_id sebelum soft delete
            $item->delete_id = Auth::id();
            $item->save();

            $item->delete(); // soft delete

            return response()->json([
                'code' => 200,
                'message' => 'Pengiriman Barang deleted successfully.',
                'data' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'code' => 404,
                'message' => 'Pengiriman Barang not found.',
                'data' => null
            ], 404);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;


        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized: Please login first.'
            ], 401);
        }

        $request->validate([
            'pengirimanBarang_status' => 'required|string|max:50',
        ]);

        $pengirimanBarang = PengirimanBarangT::find($id);

        if (!$pengirimanBarang) {
            return response()->json([
                'code' => 404,
                'message' => 'Data Pengiriman Barang tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $pengirimanBarang->pengirimanBarang_status = $request->pengirimanBarang_status;
        $pengirimanBarang->update_id = Auth::id();
        $pengirimanBarang->save();

        return response()->json([
            'code' => 200,
            'message' => 'Status updated successfully.',
            'data' => $pengirimanBarang
        ], 200);
    }

    public function getPengirimanBarangByTransaksiId($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = TransaksiDetailT::select(
            'transaksidetail_t.*',
            'barangentry_m.barangentry_nama',
            'code_m.code_nama'
        )
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'transaksidetail_t.transaksidetail_barang_id')
            ->join('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
            ->where('transaksidetail_t.transaksidetail_transaksi_id', $id)
            ->where('transaksidetail_status_penjualan', "0")
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'code'    => 404,
                'message' => 'Pengiriman order not found',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Data Pengiriman',
            'data'    => $data
        ], 200);
    }

    public function getPengiriman()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = DB::table('pengirimanBarang_t')
            ->leftJoin('transaksi_t', 'transaksi_t.transaksi_id', '=', 'pengirimanBarang_t.pengirimanBarang_transaksi_id')
            ->leftJoin('transaksidetail_t', 'transaksidetail_t.transaksidetail_transaksi_id', '=', 'transaksi_t.transaksi_id')
            ->leftJoin('packaging_m', 'packaging_m.packaging_transactiondetail_id', '=', 'transaksidetail_t.transaksidetail_id')
            ->leftJoin('barangentry_m', 'barangentry_m.barangentry_id', '=', 'transaksidetail_t.transaksidetail_barang_id')
            ->leftJoin('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
            ->whereNull('pengirimanBarang_t.deleted_at')
            ->where('pengirimanBarang_t.pengirimanBarang_is_export_excel', '=', 0)
            ->where(function ($q) {
                $q->where('packaging_m.packaging_status', '!=', 'DONE')
                    ->orWhereNull('packaging_m.packaging_status');
            })

            ->select(
                'pengirimanBarang_t.pengirimanBarang_id',
                'pengirimanBarang_t.pengirimanBarang_transaksi_id',
                'pengirimanBarang_t.pengirimanBarang_nama_penerima',
                'pengirimanBarang_t.pengirimanBarang_akun_penerima',
                'pengirimanBarang_t.pengirimanBarang_no_telepon',
                'pengirimanBarang_t.pengirimanBarang_harga_kirim_barang',
                'pengirimanBarang_t.pengirimanBarang_jenis_pengiriman_barang',
                'pengirimanBarang_t.pengirimanBarang_alamat_pengiriman_barang',
                'pengirimanBarang_t.pengirimanBarang_catatan',
                'pengirimanBarang_t.pengirimanBarang_status',
                'pengirimanBarang_t.pengirimanBarang_customer_id',
                'pengirimanBarang_t.created_at',

                // 🔥 STATUS PER ROW
                DB::raw("
                    CASE
                        WHEN packaging_m.packaging_id IS NOT NULL THEN 'DONE'
                        ELSE 'IN PROGRESS'
                    END AS status_pengiriman
                "),

                DB::raw("GROUP_CONCAT(DISTINCT barangentry_m.barangentry_id) as list_barang_id"),
                DB::raw("GROUP_CONCAT(DISTINCT code_m.code_nama) as list_code_nama")
            )

            ->groupBy(
                'pengirimanBarang_t.pengirimanBarang_id',
                'pengirimanBarang_t.pengirimanBarang_transaksi_id',
                'pengirimanBarang_t.pengirimanBarang_nama_penerima',
                'pengirimanBarang_t.pengirimanBarang_akun_penerima',
                'pengirimanBarang_t.pengirimanBarang_no_telepon',
                'pengirimanBarang_t.pengirimanBarang_harga_kirim_barang',
                'pengirimanBarang_t.pengirimanBarang_jenis_pengiriman_barang',
                'pengirimanBarang_t.pengirimanBarang_alamat_pengiriman_barang',
                'pengirimanBarang_t.pengirimanBarang_catatan',
                'pengirimanBarang_t.pengirimanBarang_status',
                'pengirimanBarang_t.pengirimanBarang_customer_id',
                'pengirimanBarang_t.created_at',
                'status_pengiriman'
            )

            ->orderBy('pengirimanBarang_t.updated_at', 'DESC')
            ->get();

        $data = $data->map(function ($item) {
            $item->list_barang_id = $item->list_barang_id ? explode(',', $item->list_barang_id) : [];
            $item->list_code_nama = $item->list_code_nama ? explode(',', $item->list_code_nama) : [];
            return $item;
        });

        return response()->json([
            'code' => 200,
            'message' => 'Pengiriman Barang found',
            'count' => $data->count(),
            'data' => $data
        ], 200);
    }

    public function exportPengirimanBarang()
    {
        if ($resp = $this->checkAuth()) return $resp;

        DB::beginTransaction();

        try {
            // 1️⃣ GET DATA
            $data = DB::table('transaksidetail_t as tt')
                ->join('pengirimanBarang_t as pbt', 'pbt.pengirimanBarang_transaksi_id', '=', 'tt.transaksidetail_transaksi_id')
                ->join('packaging_m as pm', 'pm.packaging_transactiondetail_id', '=', 'tt.transaksidetail_id')
                ->join('barangentry_m as bm', 'bm.barangentry_id', '=', 'tt.transaksidetail_barang_id')
                ->join('code_m as cm', 'cm.code_id', '=', 'bm.barangentry_code_id')
                ->whereNull('pbt.deleted_at')
                ->where('pbt.pengirimanBarang_is_export_excel', 0)
                ->select(
                    'pbt.created_at',
                    'pbt.pengirimanBarang_id',
                    'pbt.pengirimanBarang_nama_penerima',
                    'pbt.pengirimanBarang_akun_penerima',
                    'pbt.pengirimanBarang_alamat_pengiriman_barang',
                    'cm.code_nama',
                    'tt.transaksidetail_jumlah_barang',
                    'tt.transaksidetail_harga_barang'
                )
                ->get();


            if ($data->isEmpty()) {
                return response()->json([
                    'code' => 200,
                    'message' => 'No data to export',
                    'count' => 0,
                    'data' => []
                ]);
            }


            $pengirimanIds = $data->pluck('pengirimanBarang_id')->unique();


            DB::table('pengirimanBarang_t')
                ->whereIn('pengirimanBarang_id', $pengirimanIds)
                ->update([
                    'pengirimanBarang_is_export_excel' => 1,
                    'updated_at' => now()
                ]);

            DB::commit();

            return response()->json([
                'code' => 200,
                'message' => 'Export data success',
                'count' => $data->count(),
                'data' => $data
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'code' => 500,
                'message' => 'Export failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
