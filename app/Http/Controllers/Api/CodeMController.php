<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CodeM;
use App\Models\JenisBarangM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CodeMController extends Controller
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

    // PUBLIC
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;
        return response()->json(CodeM::all(), 200);
    }

    // PUBLIC
    public function show($id)
    {
        $item = CodeM::find($id);
        if (!$item) {
            return response()->json(['message' => 'Data not found'], 404);
        }
        return response()->json($item, 200);
    }

    // LOGIN REQUIRED
    public function store(Request $request)
    {
        if ($auth = $this->checkAuth()) return $auth;

        $validated = $request->validate([
            'jumlah_barang' => 'required|integer',
            'code_jenisbarang_id' => 'required|exists:jenisbarang_m,jenisbarang_id',
        ]);

        $item = JenisBarangM::find($validated['code_jenisbarang_id']);
        $jumlahBarang = $validated['jumlah_barang'];
        $createdCodes = [];

        if (substr($item->jenisbarang_kode, 0, 2) !== 'PO') {

            for ($i = 0; $i < $jumlahBarang; $i++) {
                $newKode = $this->generateKode($item->jenisbarang_kode, $item->jenisbarang_id);

                $codeM = CodeM::create([
                    'code_nama'           => $newKode,
                    'code_jenisbarang_id' => $item->jenisbarang_id,
                    'create_id'           => Auth::id(),   // <<--- HERE
                ]);

                $createdCodes[] = $codeM;
            }

        } else {
            $codeM = CodeM::create([
                'code_nama'           => $item->jenisbarang_kode,
                'code_jenisbarang_id' => $item->jenisbarang_id,
                'create_id'           => Auth::id(),     // <<--- HERE
            ]);

            $createdCodes[] = $codeM;
        }

        // Update jumlah di jenis barang
        $item->update([
            'jenisbarang_jumlah' => $item->jenisbarang_jumlah + $jumlahBarang,
            'update_id'          => Auth::id(),        // <<--- HERE
        ]);

        return response()->json([
            'message' => 'Codes generated successfully',
            'data' => $createdCodes
        ], 201);
    }

    // Generate kode unik
    public function generateKode($prefix_code, $jenisbarang_id)
    {
        $maxRetries = 10000;

        for ($i = 1; $i <= $maxRetries; $i++) {

            $nextCode = str_pad($i, 5, '0', STR_PAD_LEFT);
            $newKode = $prefix_code . $nextCode;

            $exists = CodeM::where('code_nama', $newKode)
                ->where('code_jenisbarang_id', $jenisbarang_id)
                ->exists();

            if (!$exists) {
                return $newKode;
            }
        }

        throw new \Exception("Unable to generate unique code after $maxRetries attempts.");
    }

    // LOGIN REQUIRED
    public function destroy(Request $request, $jenisbarang_id)
    {
        if ($auth = $this->checkAuth()) return $auth;

        $items = CodeM::where('code_jenisbarang_id', $jenisbarang_id)->get();

        if ($items->isEmpty()) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        // Tandai delete_id sebelum delete
        foreach ($items as $item) {
            $item->update([
                'delete_id' => Auth::id()   // <<--- HERE
            ]);
        }

        // Hapus data
        CodeM::where('code_jenisbarang_id', $jenisbarang_id)->delete();

        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    // PUBLIC
    public function getAllCodeAmount()
    {
        $jumlah = CodeM::count();

        return response()->json([
            'message' => 'Total jumlah barang entry',
            'code' => 200,
            'total' => $jumlah
        ]);
    }

    // PUBLIC
    public function getDataByCode($code_nama)
    {
        $item_code = CodeM::where('code_nama', $code_nama)->first();

        if (!$item_code) {
            return response()->json([
                'message' => 'Kode tidak ditemukan',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'code' => 200,
            'data' => $item_code
        ], 200);
    }
}
