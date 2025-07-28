<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangEntryM;
use App\Models\CodeM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BarangEntryMController extends Controller
{
    public function index()
    {
        $data = BarangEntryM::all();
        return response()->json([
            'message' => 'Data retrieved successfully',
            'code' => 200,
            'data' => $data
        ], 200);
    }
    
    public function storeDescription(Request $request){
         $data = $request->validate([
            'barangentry_nama' => 'required|string',
            'barangentry_code_id'  => 'required|string',
            'barangentry_warna'  => 'required|string',
            'barangentry_nama_penenun' => 'required|string',
            'barangentry_nama_panirat' => 'required|string',
            'barangentry_dryer' => 'required|string',
            'barangentry_modal' => 'required|numeric',
            'barangentry_price_tag' => 'required|numeric',
            'barangentry_harga_net' => 'required|numeric',
            'barangentry_jumlah_barang' => 'required|numeric',
        ]);

        // Update if code_id exists, otherwise create new
        $record = BarangEntryM::updateOrCreate(
            ['barangentry_code_id' => $data['barangentry_code_id']], 
            $data
        );

        // Check for null ukuran
        $hasNullUkuran = is_null($record->barangentry_ukuran_mandar) && is_null($record->barangentry_ukuran_ulos);

        if(!$hasNullUkuran){
            $record = BarangEntryM::updateOrCreate(
                ['barangentry_code_id' => $data['barangentry_code_id']],
                ['status' => 'READY']
            );
        }

        return response()->json([
            'code' => $hasNullUkuran ? '201' : '200',
            'status' => $hasNullUkuran ? 'warning' : 'success',
            'message' => $hasNullUkuran 
                ? 'Created/updated, but ukuran fields are missing' 
                : 'Created or updated successfully',
            'data' => $record,
        ], $record->wasRecentlyCreated ? 201 : 200);
    }

    public function storeSize(Request $request){
        $data = $request->validate([
            'barangentry_code_id'  => 'required|string',
            'barangentry_ukuran_mandar' => 'nullable|integer',
            'barangentry_ukuran_ulos' => 'nullable|integer',
        ]);

        // Update if code_id exists, otherwise create new
        $record = BarangEntryM::updateOrCreate(
            ['barangentry_code_id' => $data['barangentry_code_id']], 
            $data
        );

        // Check for null ukuran
        $hasNullUkuran = is_null($record->barangentry_nama);

        if(!$hasNullUkuran){
            $record = BarangEntryM::updateOrCreate(
                ['barangentry_code_id' => $data['barangentry_code_id']],
                ['status' => 'READY']
            );
        }

        return response()->json([
            'code' => $hasNullUkuran ? '201' : '200',
            'status' => $hasNullUkuran ? 'warning' : 'success',
            'message' => $hasNullUkuran 
                ? 'Created/updated, but Nama is missing' 
                : 'Created or updated successfully',
            'data' => $record
        ], $record->wasRecentlyCreated ? 201 : 200);
    }

    public function getDataPriceTag($codeNama)
    {
        $entries = BarangEntryM::whereHas('barangentry_code_id', function ($query) use ($codeNama) {
            $query->where('code_nama', $codeNama);
        })->with('barangentry_code_id')->get();

        if ($entries->isEmpty()) {
            return response()->json([
                "code" => 404,
                "message" => "No entries found for code_nama: {$codeNama}"
            ], 404);
        }

        // Check for missing fields
        $incomplete = $entries->filter(function ($entry) {
            return is_null($entry->barangentry_nama) || is_null($entry->barangentry_ukuran_mandar);
        });

        if ($incomplete->isNotEmpty()) {
            return response()->json([
                "code" => 404,
                "message" => "Please fill the data: 'barangentry_nama' or 'barangentry_ukuran_mandar' is missing."
            ], 404);
        }

        return response()->json([
            "code" => 200,
            "data" => $entries
        ]);
    }

    public function getDataKasir($codeNama)
    {
        $code = CodeM::where('code_nama', $codeNama)->first();
        $entries = BarangEntryM::where('barangentry_code_id', $code->code_id)
                       ->where('barangentry_status', 'READY')
                       ->get();
        
        if ($entries->isEmpty()) {
            return response()->json([
                "code" => 404,
                "message" => "No entries found for code_nama: {$codeNama}"
            ], 404);
        }
        
        // Check for missing fields
        $incomplete = $entries->filter(function ($entry) {
            return is_null($entry->barangentry_nama) || is_null($entry->barangentry_ukuran_mandar);
        });

        if ($incomplete->isNotEmpty()) {
            return response()->json([
                "code" => 404,
                "message" => "Please fill the data: 'barangentry_nama' or 'barangentry_ukuran_mandar' is missing."
            ], 404);
        }

        return response()->json([
            "code" => 200,
            "data" => $entries
        ]);
    }

    public function show($id)
    {
        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data found',
            'code' => 200,
            'data' => $entry
        ], 200);
    }

    public function getDataByCode($code_nama)
    {
        $item_code = CodeM::where('code_nama', $code_nama)->first();
        if (!$item_code) {
            return response()->json([
                'message' => 'Kode tidak ditemukan',
                'code' => 404
            ], 404);
        }

        $item = BarangEntryM::where('barangentry_code_id', $item_code->code_id)->first();
        if (!$item) {
            return response()->json([
                'message' => 'Barang entry tidak ditemukan untuk kode tersebut',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'code' => 200,
            'data' => $item
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        $entry->update($request->all());

        return response()->json([
            'message' => 'Barang entry updated successfully',
            'code' => 200,
            'data' => $entry
        ], 200);
    }

    public function updateStatusBarang(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);

        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        $entry->barangentry_status = $request->input('status');
        $entry->save();

        return response()->json([
            'message' => 'Status barang updated to ' . $request->input('status'),
            'code' => 200,
            'data' => $entry
        ], 200);
    }

    public function checkBarangEntryByCodeNama($code_nama)
    {
        $item_code = CodeM::where('code_nama', $code_nama)->first();

        if (!$item_code) {
            return response()->json([
                'message' => 'Kode tidak ditemukan',
                'code' => 404
            ], 404);
        }

        $existingEntry = BarangEntryM::where('barangentry_code_id', $item_code->code_id)->first();

        if ($existingEntry) {
            return response()->json([
                'message' => 'Kode sudah digunakan, tidak bisa entry ulang',
                'data' => ["barangentry_nama" => $existingEntry->barangentry_nama],
                'code' => 409
            ], 409);
        }

        return response()->json([
            'message' => 'Kode tersedia, bisa digunakan untuk entry',
            'code' => 200
        ], 200);
    }

    public function destroy($id)
    {
        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        $entry->delete();

        return response()->json([
            'message' => 'Data deleted successfully',
            'code' => 200
        ], 200);
    }

    public function getAllBarangEntryAmount(){
        $jumlah = BarangEntryM::count();

        return response()->json([
            'message' => 'Total jumlah barang entry',
            'code' => 200,
            'total' => $jumlah
        ]);
    }

    public function getAllDataBarangWaitToEntry()
    {
        $data = BarangEntryM::where('barangentry_status', 'NOT_READY')->get();

        return response()->json([
            'code' => 200,
            'message' => 'Success get data with status "NOT_READY"',
            'data' => $data
        ]);
    }

    public function getAllDataBarangReady()
    {
        $data = BarangEntryM::where('barangentry_status', 'READY')->get();

        return response()->json([
            'code' => 200,
            'message' => 'Success get data with status "READY"',
            'data' => $data
        ]);
    }

    public function updateStok(Request $request, $id)
    {
        $request->validate([
            'jumlah_barang' => 'required|numeric',
        ]);

        $entry = BarangEntryM::find($id);
        if (!$entry || $entry->barangentry_status == "NOT_READY") {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        $entry->barangentry_jumlah_barang += $request->input('jumlah_barang');
        $entry->save();

        return response()->json([
            'message' => 'Jumlah Barang is Updated',
            'code' => 200,
            'data' => $entry
        ], 200);
    }

}
