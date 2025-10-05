<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengirimanBarangT;
use App\Models\CustomerM;
use App\Models\TransaksiDetailT;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PengirimanBarangTController extends Controller
{
    public function index()
    {
        $data = PengirimanBarangT::all();

        return response()->json([
            'code' => 200,
            'message' => 'List of Pengiriman Barang retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'pengirimanBarang_transaksi_id'            => 'required|integer',
            'pengirimanBarang_nama_penerima'           => 'required|string|max:255',
            'pengirimanBarang_akun_penerima'           => 'nullable|string|max:255',
            'pengirimanBarang_no_telepon'              => 'nullable|string|max:20',
            'pengirimanBarang_harga_kirim_barang'      => 'required|numeric',
            'pengirimanBarang_jenis_pengiriman_barang' => 'required|string|max:100',
            'pengirimanBarang_alamat_pengiriman_barang'=> 'required|string',
            'pengirimanBarang_catatan'                 => 'nullable|string',
            'pengirimanBarang_status'                  => 'nullable|string|max:50',
        ]);

        try {
            $customer = CustomerM::firstOrCreate(
                [
                    'customer_akun'      => $validated['pengirimanBarang_akun_penerima'] ?? null,
                    'customer_notelepon' => $validated['pengirimanBarang_no_telepon'] ?? null,
                ],
                [
                    'customer_nama'     => $validated['pengirimanBarang_nama_penerima'],
                    'customer_alamat'   => $validated['pengirimanBarang_alamat_pengiriman_barang'],
                    'customer_platform' => '-',
                ]
            );
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
        try {
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
        try {
            $item = PengirimanBarangT::findOrFail($id);
            $item->delete();

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
        $pengirimanBarang->save();

        return response()->json([
            'code' => 200,
            'message' => 'Status updated successfully.',
            'data' => $pengirimanBarang
        ], 200);
    }

    public function getPengirimanBarangByTransaksiId($id){
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

        if($data->isEmpty()){
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
}
