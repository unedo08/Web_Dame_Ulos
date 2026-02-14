<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CodeM;
use App\Models\JenisBarangM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    public function store(Request $request)
    {
        if ($auth = $this->checkAuth()) return $auth;

        $validated = $request->validate([
            'jumlah_barang' => 'required|integer|min:1',
            'code_jenisbarang_id' => 'required|exists:jenisbarang_m,jenisbarang_id',
        ]);

        return DB::transaction(function () use ($validated) {

            $item = JenisBarangM::lockForUpdate()
                ->findOrFail($validated['code_jenisbarang_id']);

            $jumlahBarang = $validated['jumlah_barang'];
            $insertData = [];
            $createdCodes = [];

            // =============================
            // CASE PREFIX BUKAN PO
            // =============================
            if (substr($item->jenisbarang_kode, 0, 2) !== 'PO') {

                $lastCode = CodeM::where('code_jenisbarang_id', $item->jenisbarang_id)
                    ->where('code_nama', 'like', $item->jenisbarang_kode . '%')
                    ->max('code_nama');

                $lastNumber = $lastCode
                    ? (int) substr($lastCode, strlen($item->jenisbarang_kode))
                    : 0;

                $now = now();

                for ($i = 1; $i <= $jumlahBarang; $i++) {

                    $lastNumber++;

                    $newKode = $item->jenisbarang_kode .
                        str_pad($lastNumber, 5, '0', STR_PAD_LEFT);

                    $insertData[] = [
                        'code_nama' => $newKode,
                        'code_jenisbarang_id' => $item->jenisbarang_id,
                        'create_id' => Auth::id(),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $createdCodes[] = $newKode;
                }

                CodeM::insert($insertData);
            } else {

                $newKode = $item->jenisbarang_kode;

                CodeM::create([
                    'code_nama' => $newKode,
                    'code_jenisbarang_id' => $item->jenisbarang_id,
                    'create_id' => Auth::id(),
                ]);

                $createdCodes[] = $newKode;
            }

            // Update jumlah barang
            $item->update([
                'jenisbarang_jumlah' => $item->jenisbarang_jumlah + $jumlahBarang,
                'update_id' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Codes generated successfully',
                'data' => $createdCodes
            ], 201);
        });
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
