<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PreOrdeBarangT;

class PreOrdeBarangTController extends Controller
{
    public function index()
    {
        $data = PreOrdeBarangT::all();

        return response()->json([
            'success' => true,
            'message' => 'List of Pre Order Barang retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'preOrderBarang_transaksi_id' => 'nullable|integer',
            'preOrderBarang_nama_barang' => 'required|string|max:255',
            'preOrderBarang_nama_akun' => 'required|string|max:255',
            'preOrderBarang_no_telepon' => 'required|string|max:20',
            'preOrderBarang_target_selesai' => 'required|date_format:Y-m-d H:i:s',
            'preOrderBarang_total_pembayaran' => 'required|numeric',
            'preOrderBarang_uang_muka' => 'required|numeric',
            'preOrderBarang_sisa_pembayaran' => 'required|numeric',
            'preOrderBarang_deskripsi_barang' => 'required|string',
            'preOrderBarang_catatan' => 'required|string',
            'preOrderBarang_path_gambar' => 'required|string|max:255'
        ]);

        $item = PreOrdeBarangT::create($validatedData);

        return response()->json([
            'success' => true,
            'message' => 'Pre Order Barang created successfully.',
            'data' => $item
        ], 201);
    }

    public function show($id)
    {
        try {
            $item = PreOrdeBarangT::findOrFail($id);
            return response()->json([
                'success' => true,
                'message' => 'Pre Order Barang details retrieved successfully.',
                'data' => $item
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pre Order Barang not found.',
                'data' => null
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $item = PreOrdeBarangT::findOrFail($id);
            $item->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Pre Order Barang updated successfully.',
                'data' => $item
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pre Order Barang not found.',
                'data' => null
            ], 404);
        }
    }

    public function destroy($id)
    {
        try {
            $item = PreOrdeBarangT::findOrFail($id);
            $item->delete();

            return response()->json([
                'success' => true,
                'message' => 'Pre Order Barang deleted successfully.',
                'data' => null
            ], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Pre Order Barang not found.',
                'data' => null
            ], 404);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        
        $preOrderbarang = PreOrdeBarangT::find($id);
        if (!$preOrderbarang) {
            return response()->json([
                'message' => 'Data Pre Order tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $preOrderbarang->status = $request->status;
        $preOrderbarang->save();

        // Return success response with message and updated data
        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => $preOrderbarang
        ], 200);

        
    }
}
