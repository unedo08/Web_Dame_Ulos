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
        $jumlahBarang = $validated['jumlah_barang'];

        $createdCodes = [];

        for ($i = 0; $i < $jumlahBarang; $i++) {
            $newKode = $this->generateKode($item->jenisbarang_kode, $item->jenisbarang_id);
            $codeM = CodeM::create([
                'code_nama' => $newKode,
                'code_jenisbarang_id' => $item->jenisbarang_id,
            ]);
            $createdCodes[] = $codeM;
        }

        $jumlahBarang = $jumlahBarang + $item->jenisbarang_jumlah;
        $item->update(['jenisbarang_jumlah' => $jumlahBarang]);
        

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

    public function destroy($jenisbarang_id)
    {
        $items = CodeM::where('code_jenisbarang_id', $jenisbarang_id)->get();
        if ($items->isEmpty()) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        CodeM::where('code_jenisbarang_id', $jenisbarang_id)->delete();
        return response()->json(['message' => 'Deleted successfully'], 200);
    }

    public function getAllCodeAmount(){
        $jumlah = CodeM::count();

        return response()->json([
            'message' => 'Total jumlah barang entry',
            'code' => 200,
            'total' => $jumlah
        ]);
    }
}
