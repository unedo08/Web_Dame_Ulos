<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangEntryTemp;
use App\Models\BarangEntryM;
use Illuminate\Http\Request;

class BarangEntryTempController extends Controller
{
     public function index()
    {
        return BarangEntryTemp::all();
    }

    public function storeDescription(Request $request){
         $data = $request->validate([
            'barangentry_temp_nama' => 'required|string',
            'barangentry_temp_code_id'  => 'required|string',
            'barangentry_temp_warna'  => 'required|string',
            'barangentry_temp_nama_penenun' => 'required|string',
            'barangentry_temp_nama_panirat' => 'required|string',
            'barangentry_temp_dryer' => 'required|string',
            'barangentry_temp_modal' => 'required|numeric',
            'barangentry_temp_price_tag' => 'required|numeric',
            'barangentry_temp_harga_net' => 'required|numeric',
        ]);

        // Update if code_id exists, otherwise create new
        $record = BarangEntryTemp::updateOrCreate(
            ['barangentry_temp_code_id' => $data['barangentry_temp_code_id']], 
            $data
        );

        // Check for null ukuran
        $hasNullUkuran = is_null($record->barangentry_temp_ukuran_mandar) && is_null($record->barangentry_temp_ukuran_ulos);

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
            'barangentry_temp_code_id'  => 'required|string',
            'barangentry_temp_ukuran_mandar' => 'nullable|integer',
            'barangentry_temp_ukuran_ulos' => 'nullable|integer',
        ]);

        // Update if code_id exists, otherwise create new
        $record = BarangEntryTemp::updateOrCreate(
            ['barangentry_temp_code_id' => $data['barangentry_temp_code_id']], 
            $data
        );

        // Check for null ukuran
        $hasNullUkuran = is_null($record->barangentry_temp_nama);

        return response()->json([
            'code' => $hasNullUkuran ? '201' : '200',
            'status' => $hasNullUkuran ? 'warning' : 'success',
            'message' => $hasNullUkuran 
                ? 'Created/updated, but Nama is missing' 
                : 'Created or updated successfully',
            'data' => $record
        ], $record->wasRecentlyCreated ? 201 : 200);
    }

    public function storeToMaster($code_id){
        $temp = BarangEntryTemp::where('barangentry_temp_code_id', $code_id)->first();

        if (!$temp) {
            return response()->json(['message' => 'Temp record not found'], 404);
        }

        $data = [
            'barangentry_nama' => $temp->barangentry_temp_nama,
            'barangentry_code_id' => $temp->barangentry_temp_code_id,
            'barangentry_warna' => $temp->barangentry_temp_warna,
            'barangentry_nama_penenun' => $temp->barangentry_temp_nama_penenun,
            'barangentry_nama_panirat' => $temp->barangentry_temp_nama_panirat,
            'barangentry_dryer' => $temp->barangentry_temp_dryer,
            'barangentry_modal' => $temp->barangentry_temp_modal,
            'barangentry_price_tag' => $temp->barangentry_temp_price_tag,
            'barangentry_harga_net' => $temp->barangentry_temp_harga_net,
            'barangentry_acara' => $temp->barangentry_temp_acara, // can be null
            'barangentry_ukuran_mandar' => $temp->barangentry_temp_ukuran_mandar,
            'barangentry_ukuran_ulos' => $temp->barangentry_temp_ukuran_ulos,
        ];

        $master = BarangEntryM::create($data);

        return response()->json([
             'code' => '200',
            'message' => 'Data successfully transferred to master table',
            'data' => $master
        ], 201);
    }

    public function showDataTable(){
       
        $existingCodeIds = BarangEntryM::pluck('barangentry_code_id')->toArray();
        $tempOnly = BarangEntryTemp::whereNotIn('barangentry_temp_code_id', $existingCodeIds)->get();

        return response()->json([
            'code' => '200',
            'message' => 'Temp records not yet in master',
            'data' => $tempOnly
        ]);
    }

    public function show($id)
    {
        return BarangEntryTemp::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $barangEntry = BarangEntryTemp::findOrFail($id);
        $barangEntry->update($request->all());
        return $barangEntry;
    }

    public function destroy($id)
    {
        BarangEntryTemp::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
