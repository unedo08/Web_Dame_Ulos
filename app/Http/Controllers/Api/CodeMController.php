<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CodeM;
use App\Models\JenisBarangM;
use Illuminate\Http\Request;

class CodeMController extends Controller
{
    
    // GET /api/jenisbarang
    public function index()
    {
        return response()->json(CodeM::all(), 200);
    }

    // GET /api/jenisbarang/{id}
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
        $validated = $request->validate([
            'jumlah_barang' => 'required|integer',
            'code_jenisbarang_id' => 'required|exists:jenisbarang_m,jenisbarang_id',
        ]);

        $item = JenisBarangM::find($validated['code_jenisbarang_id']);
        $jumlahBarang = $item->jenisbarang_tipe === "tunggal"
            ? $item->jenisbarang_jumlah
            : $validated['jumlah_barang'];

        $createdCodes = [];

        for ($i = 0; $i < $jumlahBarang; $i++) {
            $newKode = $this->generateKode($item->jenisbarang_kode, $item->jenisbarang_id);
            $codeM = CodeM::create([
                'code_nama' => $newKode,
                'code_jenisbarang_id' => $item->jenisbarang_id,
            ]);
            $createdCodes[] = $codeM;
        }

        // Update jumlah jika tipe bukan tunggal
        if ($item->jenisbarang_tipe !== "tunggal") {
            $jumlahBarang = $jumlahBarang + $item->jenisbarang_jumlah;
            $item->update(['jenisbarang_jumlah' => $jumlahBarang]);
        }

        return response()->json([
            'message' => 'Codes generated successfully',
            'data' => $createdCodes
        ], 201);
    }

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
}
