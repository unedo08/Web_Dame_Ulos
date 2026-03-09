<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CodeM;
use Illuminate\Http\Request;
use App\Models\PreOrdeBarangT;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PreOrdeBarangTController extends Controller
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
        
        $data = PreOrdeBarangT::all();

        return response()->json([
            'success' => true,
            'message' => 'List of Pre Order Barang retrieved successfully.',
            'data' => $data
        ], 200);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;
        

        $validatedData = $request->validate([
            'preOrdeBarang_id' => 'nullable|integer|exists:preOrdeBarang_t,preOrdeBarang_id',
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
            'preOrderBarang_path_gambar' => 'nullable|image|max:2048',
            'preOrderBarang_barang_entry_id' => 'required|string|max:255',
            'preOrderBarang_cara_bayar' => 'string|max:255'
        ]);

        $imagePath = null;

        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $imagePath = 'storage/' . $path;
        }

        $validatedData['preOrderBarang_path_gambar'] = $imagePath;

        // Tambahkan create_id & update_id
        $validatedData['create_id'] = Auth::id();
        $validatedData['update_id'] = Auth::id();

        $item = PreOrdeBarangT::updateOrCreate(
            ['preOrdeBarang_id' => $validatedData['preOrdeBarang_id'] ?? null],
            $validatedData
        );

        return response()->json([
            'success' => true,
            'message' => 'Pre Order Barang created successfully.',
            'data' => $item
        ], 201);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        
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

    public function getPreOrderbyBarangEntryID($id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        
        $item = PreOrdeBarangT::where('preOrderBarang_barang_entry_id', $id)->first();

        return response()->json([
            'success' => true,
            'message' => 'Pre Order Barang details retrieved successfully.',
            'data' => $item
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        

        try {
            $item = PreOrdeBarangT::findOrFail($id);

            $data = $request->all();
            $data['update_id'] = Auth::id();

            $item->update($data);

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
        if ($resp = $this->checkAuth()) return $resp;
        

        try {
            $item = PreOrdeBarangT::findOrFail($id);

            // simpan delete_id sebelum soft delete
            $item->delete_id = Auth::id();
            $item->save();

            // soft delete
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
        if ($resp = $this->checkAuth()) return $resp;
        

        $request->validate([
            'status' => 'required|string',
        ]);

        $item = PreOrdeBarangT::find($id);
        if (!$item) {
            return response()->json([
                'message' => 'Data Pre Order tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $item->status = $request->status;
        $item->update_id = Auth::id();
        $item->save();

        return response()->json([
            'message' => 'Status updated successfully.',
            'data' => $item
        ], 200);
    }

    public function kodePO()
    {
        if ($resp = $this->checkAuth()) return $resp;
        
        try {
            $prefix = 'PO';
            $length = 6;

            $lastCode = CodeM::where('code_nama', 'like', $prefix . '%')
                ->orderBy('code_nama', 'desc')
                ->first();

            $nextNumber = $lastCode
                ? ((int) substr($lastCode->code_nama, strlen($prefix))) + 1
                : 1;

            $newKode = $prefix . str_pad($nextNumber, $length, '0', STR_PAD_LEFT);

            return response()->json([
                'sukses' => true,
                'message' => 'Kode Barang untuk barang Pre-Order',
                'data' => ['code_nama' => $newKode]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'sukses' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function storeImage(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required',
            'image' => 'required|image|max:2048'
        ]);

        // store image
        $path = $request->file('image')->store('products', 'public');

        // convert to public url
        $imagePath = 'storage/' . $path;

        return response()->json([
            "message" => "Product created",
            "image_url" => $imagePath
        ]);
    }

    public function viewImage($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $item = PreOrdeBarangT::findOrFail($id);

        if (!$item->preOrderBarang_path_gambar) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'image_url' => asset($item->preOrderBarang_path_gambar)
        ]);
    }

    public function updateImage(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'image' => 'required|image|max:2048'
        ]);

        $item = PreOrdeBarangT::findOrFail($id);

        // delete old image if exists
        if ($item->preOrderBarang_path_gambar) {
            $oldPath = str_replace('storage/', '', $item->preOrderBarang_path_gambar);
            Storage::disk('public')->delete($oldPath);
        }

        // upload new image
        $path = $request->file('image')->store('preorder', 'public');

        $item->preOrderBarang_path_gambar = 'storage/' . $path;
        $item->update_id = Auth::id();
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Image updated',
            'image_url' => asset($item->preOrderBarang_path_gambar)
        ]);
    }

    public function deleteImage($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $item = PreOrdeBarangT::findOrFail($id);

        if (!$item->preOrderBarang_path_gambar) {
            return response()->json([
                'success' => false,
                'message' => 'Image not found'
            ]);
        }

        $path = str_replace('storage/', '', $item->preOrderBarang_path_gambar);

        Storage::disk('public')->delete($path);

        $item->preOrderBarang_path_gambar = null;
        $item->update_id = Auth::id();
        $item->save();

        return response()->json([
            'success' => true,
            'message' => 'Image deleted'
        ]);
    }
}
