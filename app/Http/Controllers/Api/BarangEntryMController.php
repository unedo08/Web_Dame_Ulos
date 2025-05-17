<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangEntryM;
use Illuminate\Http\Request;

class BarangEntryMController extends Controller
{
     public function index()
    {
        return BarangEntryM::all();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'barangentry_nama' => 'required|string',
            'barangentry_warna' => 'required|string',
            'barangentry_nama_penenun' => 'required|string',
            'barangentry_nama_panirat' => 'required|string',
            'barangentry_dryer' => 'required|string',
            'barangentry_modal' => 'required|numeric',
            'barangentry_price_tag' => 'required|numeric',
            'barangentry_harga_net' => 'required|numeric',
            'barangentry_acara' => 'required|string',
            'barangentry_ukuran_mandar' => 'required|integer',
            'barangentry_ukuran_ulos' => 'required|integer',
        ]);

        return BarangEntryM::create($data);
    }

    public function show($id)
    {
        return BarangEntryM::findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $barangEntry = BarangEntryM::findOrFail($id);
        $barangEntry->update($request->all());
        return $barangEntry;
    }

    public function destroy($id)
    {
        BarangEntryM::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
