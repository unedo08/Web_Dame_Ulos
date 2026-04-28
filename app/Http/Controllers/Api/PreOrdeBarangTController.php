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
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        return $this->ok(PreOrdeBarangT::all(), 'List of Pre Order Barang retrieved successfully.');
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

        if ($request->hasFile('preOrderBarang_path_gambar')) {
            $path = $request->file('preOrderBarang_path_gambar')->store('products', 'public');
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

        return $this->created([
            'preorder'  => $item,
            'image_url' => asset($item->preOrderBarang_path_gambar)
        ], 'Pre Order Barang created successfully.');
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        
        try {
            return $this->ok(PreOrdeBarangT::findOrFail($id), 'Pre Order Barang details retrieved successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->notFound('Pre Order Barang not found.');
        }
    }

    public function getPreOrderbyBarangEntryID($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        return $this->ok(
            PreOrdeBarangT::where('preOrderBarang_barang_entry_id', $id)->first(),
            'Pre Order Barang details retrieved successfully.'
        );
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        

        try {
            $item = PreOrdeBarangT::findOrFail($id);

            $data = $request->all();
            $data['update_id'] = Auth::id();
            $item->update($data);

            return $this->ok($item, 'Pre Order Barang updated successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->notFound('Pre Order Barang not found.');
        }
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        try {
            $item = PreOrdeBarangT::findOrFail($id);
            $item->delete_id = Auth::id();
            $item->save();
            $item->delete();

            return $this->ok(null, 'Pre Order Barang deleted successfully.');
        } catch (ModelNotFoundException $e) {
            return $this->notFound('Pre Order Barang not found.');
        }
    }

    public function updateStatus(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate(['status' => 'required|string']);

        $item = PreOrdeBarangT::find($id);
        if (!$item) {
            return $this->notFound('Data Pre Order tidak ditemukan.');
        }

        $item->status = $request->status;
        $item->update_id = Auth::id();
        $item->save();

        return $this->ok($item, 'Status updated successfully.');
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

            return $this->ok(['code_nama' => $newKode], 'Kode Barang untuk barang Pre-Order');
        } catch (\Exception $e) {
            return $this->fail('Terjadi kesalahan: ' . $e->getMessage());
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
            "image_url" => asset($imagePath)
        ]);
    }

    public function viewImage($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $item = PreOrdeBarangT::findOrFail($id);

        if (!$item->preOrderBarang_path_gambar) {
            return $this->notFound('Image not found');
        }

        return $this->ok(['image_url' => asset($item->preOrderBarang_path_gambar)]);
    }

    public function updateImage(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate(['image' => 'required|image|max:2048']);

        $item = PreOrdeBarangT::findOrFail($id);

        if ($item->preOrderBarang_path_gambar) {
            Storage::disk('public')->delete(str_replace('storage/', '', $item->preOrderBarang_path_gambar));
        }

        $item->preOrderBarang_path_gambar = 'storage/' . $request->file('image')->store('preorder', 'public');
        $item->update_id = Auth::id();
        $item->save();

        return $this->ok(['image_url' => asset($item->preOrderBarang_path_gambar)], 'Image updated');
    }

    public function deleteImage($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $item = PreOrdeBarangT::findOrFail($id);

        if (!$item->preOrderBarang_path_gambar) {
            return $this->notFound('Image not found');
        }

        Storage::disk('public')->delete(str_replace('storage/', '', $item->preOrderBarang_path_gambar));

        $item->preOrderBarang_path_gambar = null;
        $item->update_id = Auth::id();
        $item->save();

        return $this->ok(null, 'Image deleted');
    }
}
