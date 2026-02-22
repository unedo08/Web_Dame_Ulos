<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangEntryM;
use App\Models\CodeM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangEntryMController extends Controller
{
    // ====== AUTH CHECK TANPA CONSTRUCTOR ======
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

        $data = BarangEntryM::all();
        return response()->json([
            'message' => 'Data retrieved successfully',
            'code' => 200,
            'data' => $data
        ], 200);
    }

    public function storeDescription(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

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
            'barangentry_status' => 'string',
        ]);

        // Tambahkan create_id dan update_id
        $data['update_id'] = Auth::id();

        $record = BarangEntryM::updateOrCreate(
            ['barangentry_code_id' => $data['barangentry_code_id']],
            array_merge($data, [
                'create_id' => Auth::id(), // hanya jika new
            ])
        );

        // Status checker
        if ($record->barangentry_status == "PREORDER") {
            $hasNullUkuran = is_null($record->barangentry_ukuran_mandar) && is_null($record->barangentry_ukuran_ulos);
        } else {
            $hasNullUkuran = is_null($record->barangentry_ukuran_mandar) && is_null($record->barangentry_ukuran_ulos);
            if (!$hasNullUkuran) {
                $record->update([
                    'barangentry_status' => 'READY',
                    'update_id' => Auth::id()
                ]);
            }
        }

        return response()->json([
            'code' => $hasNullUkuran ? '201' : '200',
            'status' => $hasNullUkuran ? 'warning' : 'success',
            'message' => $hasNullUkuran
                ? 'Created, but ukuran fields are missing'
                : 'Created successfully',
            'data' => $record,
        ], $record->wasRecentlyCreated ? 201 : 200);
    }

    public function storeSize(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = $request->validate([
            'barangentry_code_id'  => 'required|string',
            'barangentry_ukuran_mandar' => 'nullable|string',
            'barangentry_ukuran_ulos' => 'nullable|string',
        ]);

        // Tambahkan update_id
        $data['update_id'] = Auth::id();

        $record = BarangEntryM::updateOrCreate(
            ['barangentry_code_id' => $data['barangentry_code_id']],
            array_merge($data, [
                'create_id' => Auth::id(), // jika baru
            ])
        );

        // Status checker
        if ($record->barangentry_status == "PREORDER") {
            $hasNullUkuran = is_null($record->barangentry_nama);
        } else {
            $hasNullUkuran = is_null($record->barangentry_nama);
            if (!$hasNullUkuran) {
                $record->update([
                    'barangentry_status' => 'READY',
                    'update_id' => Auth::id()
                ]);
            }
        }

        return response()->json([
            'code' => $hasNullUkuran ? '201' : '200',
            'status' => $hasNullUkuran ? 'warning' : 'success',
            'message' => $hasNullUkuran
                ? 'Created, but Nama is missing'
                : 'Created successfully',
            'data' => $record
        ], $record->wasRecentlyCreated ? 201 : 200);
    }

    public function getDataPriceTag($codeNama)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $entries = BarangEntryM::whereHas('barangentry_code_id', function ($query) use ($codeNama) {
            $query->where('code_nama', $codeNama);
        })
            ->where('barangentry_status', 'READY')
            ->with('barangentry_code_id')->get();

        if ($entries->isEmpty()) {
            return response()->json([
                "code" => 404,
                "message" => "No entries found for code_nama: {$codeNama}"
            ], 404);
        }

        return response()->json([
            "code" => 200,
            "data" => $entries
        ]);
    }

    public function getDataKasir($codeNama)
    {
        if ($resp = $this->checkAuth()) return $resp;

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

        return response()->json([
            "code" => 200,
            "data" => $entries
        ]);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        $data = $request->all();
        $data['update_id'] = Auth::id();

        $entry->update($data);

        return response()->json([
            'message' => 'Barang entry updated successfully',
            'code' => 200,
            'data' => $entry
        ], 200);
    }

    public function updateStatusBarang(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

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

        $entry->update([
            'barangentry_status' => $request->status,
            'update_id' => Auth::id()
        ]);

        return response()->json([
            'message' => 'Status barang updated to ' . $request->input('status'),
            'code' => 200,
            'data' => $entry
        ], 200);
    }

    public function checkBarangEntryByCodeNama($code_nama)
    {
        if ($resp = $this->checkAuth()) return $resp;

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
        if ($resp = $this->checkAuth()) return $resp;

        $entry = BarangEntryM::find($id);
        if (!$entry) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        // Set delete_id sebelum hapus
        $entry->update([
            'delete_id' => Auth::id()
        ]);

        $entry->delete();

        return response()->json([
            'message' => 'Data deleted successfully',
            'code' => 200
        ], 200);
    }

    public function getAllBarangEntryAmount()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $jumlah = BarangEntryM::count();

        return response()->json([
            'message' => 'Total jumlah barang entry',
            'code' => 200,
            'total' => $jumlah
        ]);
    }

    public function getAllDataBarangWaitToEntry()
    {
        if ($resp = $this->checkAuth()) return $resp;
        $data = BarangEntryM::with('code')
            ->where('barangentry_status', 'NOT_READY')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => 'Success get data with status "NOT_READY"',
            'data' => $data
        ]);
    }

    public function getAllDataBarangReady()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = BarangEntryM::leftJoin('acaradet_m', 'barangentry_m.barangentry_id', '=', 'acaradet_m.acaradet_barangentry_id')
            ->leftJoin('code_m', 'barangentry_m.barangentry_code_id', '=', 'code_m.code_id') // kalau sebelumnya pakai with('code')
            ->leftJoin('acara_m', 'acara_m.acara_id', '=', 'acaradet_m.acaradet_acara_id')
            ->where('barangentry_m.barangentry_status', 'READY')
            ->select(
                'barangentry_m.*',
                'code_m.code_nama',
                'acara_m.acara_nama'
            )
            ->get();

        return response()->json([
            'code' => 200,
            'message' => 'Success get data with status "READY"',
            'data' => $data
        ]);
    }

    public function getAllDataBarangPO()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = BarangEntryM::query()
            ->with([
                'code',
                'preOrderBarang', // 🔥 tambah ini
            ])
            ->where('barangentry_status', 'PREORDER')
            ->whereNull('deleted_at')
            ->whereDoesntHave('transaksiDetail.pengirimanBarang')
            ->select([
                'barangentry_id',
                'barangentry_code_id',
                'barangentry_nama',
                'barangentry_warna',
                'barangentry_nama_penenun',
                'barangentry_nama_panirat',
                'barangentry_dryer',
                'barangentry_modal',
                'barangentry_price_tag',
                'barangentry_harga_net',
                'barangentry_acara_id',
                'barangentry_ukuran_mandar',
                'barangentry_ukuran_ulos',
                'barangentry_jumlah_barang',
                'barangentry_status',
                'created_at',
                'updated_at',
                'create_id',
                'update_id',
                'delete_id',
                'deleted_at'
            ])
            ->orderByDesc('barangentry_id')
            ->get();

        $data = $data->map(function ($item) {

            $fieldsToCheck = [
                'barangentry_nama',
                'barangentry_ukuran_mandar',
                'barangentry_ukuran_ulos'
            ];

            $item->barangfilled = collect($fieldsToCheck)->every(function ($field) use ($item) {
                return !is_null($item->$field) && $item->$field !== '';
            });

            $item->status_barang = 'PREORDER';

            return $item;
        });

        return response()->json([
            'code' => 200,
            'message' => 'Success get data PREORDER that are NOT in pengiriman',
            'total_data' => $data->count(),
            'data' => $data
        ]);
    }



    public function updateStok(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;
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


    public function updateReadyStockDesc(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        $request->validate([
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
            // 'barangentry_code_id'  => 'required|string',
            'barangentry_ukuran_mandar' => 'nullable|string',
            'barangentry_ukuran_ulos' => 'nullable|string',
        ]);

        $updated = BarangEntryM::find($id);
        if (!$updated) {
            return response()->json([
                'message' => 'Data Barang Ready Stock not found',
                'code' => 404
            ], 404);
        }

        $updated->update($request->all());

        return response()->json([
            'message' => 'Barang Ready Stock updated successfully',
            'code' => 200,
            'data' => $updated
        ], 200);
    }

    public function updateReadyStockSize(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        $request->validate([
            // 'barangentry_nama' => 'required|string',
            'barangentry_code_id'  => 'required|string',
            // 'barangentry_warna'  => 'required|string',
            // 'barangentry_nama_penenun' => 'required|string',
            // 'barangentry_nama_panirat' => 'required|string',
            // 'barangentry_dryer' => 'required|string',
            // 'barangentry_modal' => 'required|numeric',
            // 'barangentry_price_tag' => 'required|numeric',
            // 'barangentry_harga_net' => 'required|numeric',
            // 'barangentry_jumlah_barang' => 'required|numeric',
            // 'barangentry_code_id'  => 'required|string',
            'barangentry_ukuran_mandar' => 'nullable|string',
            'barangentry_ukuran_ulos' => 'nullable|string',
        ]);

        $updated = BarangEntryM::find($id);
        if (!$updated) {
            return response()->json([
                'message' => 'Data Barang Ready Stock not found',
                'code' => 404
            ], 404);
        }

        $updated->update($request->all());

        return response()->json([
            'message' => 'Barang Ready Stock updated successfully',
            'code' => 200,
            'data' => $updated
        ], 200);
    }

    public function updateReadyStock(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'barangentry_nama' => 'sometimes|required|string',
            'barangentry_code_id'  => 'sometimes|required|string',
            'barangentry_warna'  => 'sometimes|required|string',
            'barangentry_nama_penenun' => 'sometimes|required|string',
            'barangentry_nama_panirat' => 'sometimes|required|string',
            'barangentry_dryer' => 'sometimes|required|string',
            'barangentry_modal' => 'sometimes|required|numeric',
            'barangentry_price_tag' => 'sometimes|required|numeric',
            'barangentry_harga_net' => 'sometimes|required|numeric',
            'barangentry_jumlah_barang' => 'sometimes|required|numeric',

            // Size fields (optional)
            'barangentry_ukuran_mandar' => 'sometimes|nullable|string',
            'barangentry_ukuran_ulos' => 'sometimes|nullable|string',
        ]);

        $updated = BarangEntryM::find($id);

        if (!$updated) {
            return response()->json([
                'message' => 'Data Barang Ready Stock not found',
                'code' => 404
            ], 404);
        }

        // Only update fields that exist in the payload
        $updated->update($request->only(array_keys($request->all())));

        return response()->json([
            'message' => 'Barang Ready Stock updated successfully',
            'code' => 200,
            'data' => $updated
        ], 200);
    }

    public function deleteBarangEntry(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|string',
        ]);
        $entry = BarangEntryM::findOrFail($id);

        $entry->update([
            'barangentry_status' => 'DELETED',
            'delete_id' => Auth::id()
        ]);

        $entry->delete();

        return response()->json([
            'message' => 'Deleted successfully',
            'code'    => 200
        ], 200);
    }
}
