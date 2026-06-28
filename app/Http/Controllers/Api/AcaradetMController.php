<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AcaradetM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BarangEntryM;
use Illuminate\Support\Facades\DB;

class AcaradetMController extends Controller
{
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        return response()->json([
            'message' => 'Data retrieved successfully',
            'code' => 200,
            'data' => AcaradetM::all()
        ]);
    }

    public function getByAcara($acara_id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $items = AcaradetM::where('acaradet_acara_id', $acara_id)->get();

        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data retrieved successfully',
            'code' => 200,
            'data' => $items
        ]);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'acaradet_acara_id' => 'required|exists:acara_m,acara_id',
            'acaradet_barangentry_id' => 'required|array|min:1',
            'acaradet_barangentry_id.*' => 'exists:barangentry_m,barangentry_id',
        ]);

        $inserted = [];

        DB::transaction(function () use ($request, &$inserted) {

            // 1️⃣ normalize input (anti duplicate di request)
            $inputIds = array_unique($request->acaradet_barangentry_id);

            // 2️⃣ LOCK rows for this acara
            $existing = DB::table('acaradet_m')
                ->where('acaradet_acara_id', $request->acaradet_acara_id)
                ->whereIn('acaradet_barangentry_id', $inputIds)
                ->lockForUpdate()
                ->pluck('acaradet_barangentry_id')
                ->toArray();

            // 3️⃣ ambil yang belum ada di acara tsb
            $newIds = array_diff($inputIds, $existing);

            foreach ($newIds as $barangentryId) {
                $inserted[] = [
                    'acaradet_acara_id' => $request->acaradet_acara_id,
                    'acaradet_barangentry_id' => $barangentryId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($inserted)) {
                DB::table('acaradet_m')->insert($inserted);
            }
        });

        return response()->json([
            'message' => empty($inserted)
                ? 'Semua barang entry sudah terdaftar di acara ini'
                : 'Acaradet created successfully',
            'code' => 201,
            'data' => $inserted
        ]);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = AcaradetM::find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data found',
            'code' => 200,
            'data' => $data
        ]);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = AcaradetM::find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        $request->validate([
            'acaradet_acara_id' => 'sometimes|exists:acara_m,acara_id',
            'acaradet_barangentry_id' => 'sometimes|exists:barangentry_m,barangentry_id',
        ]);

        $data->update(array_merge($request->all(), [
            'update_id' => Auth::id(),
        ]));

        return response()->json([
            'message' => 'Data updated',
            'code' => 200,
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = AcaradetM::find($id);
        if (!$data) {
            return response()->json([
                'message' => 'Data not found',
                'code' => 404
            ], 404);
        }

        // Simpan siapa yang delete
        $data->delete_id = Auth::id();
        $data->save();

        // Soft delete / hard delete sesuai kebutuhan
        $data->delete();

        return response()->json([
            'message' => 'Data deleted successfully',
            'code' => 200
        ]);
    }

    public function addListCodeAcara(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'code_nama' => 'required|array|min:1',
            'code_nama.*' => 'exists:code_m,code_nama'
        ]);

        $items = BarangEntryM::join(
            'code_m',
            'barangentry_m.barangentry_code_id',
            '=',
            'code_m.code_id'
        )
            ->whereIn('code_m.code_nama', $request->code_nama)
            ->orderBy('code_m.code_nama', 'asc')
            ->select(
                'barangentry_m.*',
                'code_m.code_nama'
            )
            ->get();

        if ($items->isEmpty()) {
            return response()->json([
                'message' => 'Barang entry tidak ditemukan',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'code' => 200,
            'data' => $items
        ], 200);
    }

    public function getBarangEntryByAcara($acara_id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = BarangEntryM::join(
            'acaradet_m',
            'barangentry_m.barangentry_id',
            '=',
            'acaradet_m.acaradet_barangentry_id'
        )
            ->join(
                'code_m',
                'barangentry_m.barangentry_code_id',
                '=',
                'code_m.code_id'
            )
            ->where('acaradet_m.acaradet_acara_id', $acara_id)
            ->whereNull('acaradet_m.deleted_at')
            ->orderBy('code_m.code_nama', 'asc')
            ->select(
                'barangentry_m.*',
                'acaradet_m.acaradet_id',
                'acaradet_m.acaradet_acara_id',
                'code_m.code_id',
                'code_m.code_nama'
            )
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'message' => 'Barang entry tidak ditemukan',
                'code' => 404
            ], 404);
        }

        return response()->json([
            'message' => 'Data ditemukan',
            'code' => 200,
            'data' => $data
        ], 200);
    }
}
