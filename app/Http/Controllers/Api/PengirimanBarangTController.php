<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PengirimanBarangT;

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
        $validatedData = $request->validate([
            'pengirimanBarang_transaksi_id' => 'required|integer',
            'pengirimanBarang_nama_penerima' => 'required|string|max:255',
            'pengirimanBarang_akun_penerima' => 'nullable|string|max:255',
            'pengirimanBarang_no_telepon' => 'nullable|string|max:20',
            'pengirimanBarang_harga_kirim_barang' => 'required|numeric',
            'pengirimanBarang_jenis_pengiriman_barang' => 'required|string|max:100',
            'pengirimanBarang_alamat_pengiriman_barang' => 'required|string',
            'pengirimanBarang_catatan' => 'nullable|string',
            'pengirimanBarang_status' => 'nullable|string|max:50',
        ]);

        $item = PengirimanBarangT::create($validatedData);
       
        return response()->json([
            'code' => 201,
            'message' => 'Pengiriman Barang created successfully.',
            'data' => $item
        ], 201);
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
            $item = PengirimanBarangT::findOrFail($id);
            $item->update($request->all());

            return response()->json([
                'code' => 200,
                'message' => 'Pengiriman Barang updated successfully.',
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
            'status' => 'required|string',
        ]);

        
        $pengirimanBarang = PengirimanBarangT::find($id);
        if (!$pengirimanBarang) {
            return response()->json([
                'message' => 'Data Pengiriman Barang tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $pengirimanBarang->status = $request->status;
        $pengirimanBarang->save();

        // Return success response with message and updated data
        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => $pengirimanBarang
        ], 200);

        
    }
}
