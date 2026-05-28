<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PengeluaranT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PengeluaranTController extends Controller
{
    public function index(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $data = PengeluaranT::with([
            'jenisPengeluaran:jenis_pengeluaran_id,jenis_pengeluaran_nama,jenis_pengeluaran_kode',
            'divisi:divisi_id,divisi_nama,divisi_kode',
            'sumberDana:sumber_dana_id,sumber_dana_nama,sumber_dana_kode',
            'caraBayar:id,carabayar_nama,carabayar_kode',
        ])
            ->when($request->start_date, fn($q) => $q->whereDate('pengeluaran_tanggal', '>=', $request->start_date))
            ->when($request->end_date,   fn($q) => $q->whereDate('pengeluaran_tanggal', '<=', $request->end_date))
            ->orderBy('pengeluaran_tanggal', 'desc')
            ->orderBy('pengeluaran_id', 'desc')
            ->get()
            ->map(fn($item) => $this->formatRow($item));

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data pengeluaran',
            'total'   => $data->count(),
            'data'    => $data,
        ], 200);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'pengeluaran_tanggal'      => 'required|date',
            'pengeluaran_nama'         => 'required|string|max:255',
            'pengeluaran_jenis_id'     => 'required|integer|exists:jenis_pengeluaran_m,jenis_pengeluaran_id',
            'pengeluaran_divisi_id'    => 'required|integer|exists:divisi_m,divisi_id',
            'pengeluaran_jumlah'       => 'required|numeric|min:0',
            'pengeluaran_sumber_dana_id' => 'required|integer|exists:sumber_dana_m,sumber_dana_id',
            'pengeluaran_cara_bayar_id'  => 'required|integer|exists:carabayar_m,id',
        ], [
            'pengeluaran_tanggal.required'        => 'Data wajib diisi',
            'pengeluaran_nama.required'           => 'Data wajib diisi',
            'pengeluaran_jenis_id.required'       => 'Data wajib diisi',
            'pengeluaran_jenis_id.exists'         => 'Jenis pengeluaran tidak valid',
            'pengeluaran_divisi_id.required'      => 'Data wajib diisi',
            'pengeluaran_divisi_id.exists'        => 'Divisi tidak valid',
            'pengeluaran_jumlah.required'         => 'Data wajib diisi',
            'pengeluaran_jumlah.numeric'          => 'Jumlah pengeluaran harus berupa angka',
            'pengeluaran_jumlah.min'              => 'Jumlah pengeluaran tidak boleh negatif',
            'pengeluaran_sumber_dana_id.required' => 'Data wajib diisi',
            'pengeluaran_sumber_dana_id.exists'   => 'Sumber dana tidak valid',
            'pengeluaran_cara_bayar_id.required'  => 'Data wajib diisi',
            'pengeluaran_cara_bayar_id.exists'    => 'Jenis pembayaran tidak valid',
        ]);

        $validated['create_id'] = Auth::id();

        $pengeluaran = PengeluaranT::create($validated);

        $pengeluaran->load([
            'jenisPengeluaran:jenis_pengeluaran_id,jenis_pengeluaran_nama,jenis_pengeluaran_kode',
            'divisi:divisi_id,divisi_nama,divisi_kode',
            'sumberDana:sumber_dana_id,sumber_dana_nama,sumber_dana_kode',
            'caraBayar:id,carabayar_nama,carabayar_kode',
        ]);

        return response()->json([
            'code'    => 201,
            'message' => 'Pengeluaran berhasil ditambahkan',
            'data'    => $this->formatRow($pengeluaran),
        ], 201);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $pengeluaran = PengeluaranT::with([
            'jenisPengeluaran:jenis_pengeluaran_id,jenis_pengeluaran_nama,jenis_pengeluaran_kode',
            'divisi:divisi_id,divisi_nama,divisi_kode',
            'sumberDana:sumber_dana_id,sumber_dana_nama,sumber_dana_kode',
            'caraBayar:id,carabayar_nama,carabayar_kode',
        ])->find($id);

        if (!$pengeluaran) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan data',
            'data'    => $this->formatRow($pengeluaran),
        ], 200);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $pengeluaran = PengeluaranT::find($id);
        if (!$pengeluaran) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        $validated = $request->validate([
            'pengeluaran_tanggal'        => 'required|date',
            'pengeluaran_nama'           => 'required|string|max:255',
            'pengeluaran_jenis_id'       => 'required|integer|exists:jenis_pengeluaran_m,jenis_pengeluaran_id',
            'pengeluaran_divisi_id'      => 'required|integer|exists:divisi_m,divisi_id',
            'pengeluaran_jumlah'         => 'required|numeric|min:0',
            'pengeluaran_sumber_dana_id' => 'required|integer|exists:sumber_dana_m,sumber_dana_id',
            'pengeluaran_cara_bayar_id'  => 'required|integer|exists:carabayar_m,id',
        ], [
            'pengeluaran_tanggal.required'        => 'Data wajib diisi',
            'pengeluaran_nama.required'           => 'Data wajib diisi',
            'pengeluaran_jenis_id.required'       => 'Data wajib diisi',
            'pengeluaran_jenis_id.exists'         => 'Jenis pengeluaran tidak valid',
            'pengeluaran_divisi_id.required'      => 'Data wajib diisi',
            'pengeluaran_divisi_id.exists'        => 'Divisi tidak valid',
            'pengeluaran_jumlah.required'         => 'Data wajib diisi',
            'pengeluaran_jumlah.numeric'          => 'Jumlah pengeluaran harus berupa angka',
            'pengeluaran_jumlah.min'              => 'Jumlah pengeluaran tidak boleh negatif',
            'pengeluaran_sumber_dana_id.required' => 'Data wajib diisi',
            'pengeluaran_sumber_dana_id.exists'   => 'Sumber dana tidak valid',
            'pengeluaran_cara_bayar_id.required'  => 'Data wajib diisi',
            'pengeluaran_cara_bayar_id.exists'    => 'Jenis pembayaran tidak valid',
        ]);

        $validated['update_id'] = Auth::id();

        $pengeluaran->update($validated);

        $pengeluaran->load([
            'jenisPengeluaran:jenis_pengeluaran_id,jenis_pengeluaran_nama,jenis_pengeluaran_kode',
            'divisi:divisi_id,divisi_nama,divisi_kode',
            'sumberDana:sumber_dana_id,sumber_dana_nama,sumber_dana_kode',
            'caraBayar:id,carabayar_nama,carabayar_kode',
        ]);

        return response()->json([
            'code'     => 200,
            'title'    => 'Data berhasil diubah',
            'message'  => 'Data pengeluaran berhasil diperbarui.',
            'data'     => $this->formatRow($pengeluaran),
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $pengeluaran = PengeluaranT::find($id);
        if (!$pengeluaran) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data tidak ditemukan',
                'data'    => null,
            ], 404);
        }

        $pengeluaran->delete_id = Auth::id();
        $pengeluaran->save();
        $pengeluaran->delete();

        return response()->json([
            'code'     => 200,
            'title'    => 'Data berhasil dihapus',
            'message'  => 'Data pengeluaran berhasil dihapus.',
            'data'     => null,
        ], 200);
    }

    public function summary(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $query = PengeluaranT::query()
            ->when($request->start_date, fn($q) => $q->whereDate('pengeluaran_tanggal', '>=', $request->start_date))
            ->when($request->end_date,   fn($q) => $q->whereDate('pengeluaran_tanggal', '<=', $request->end_date));

        $totalPengeluaran = (clone $query)->sum('pengeluaran_jumlah');

        $perJenis = (clone $query)
            ->join('jenis_pengeluaran_m as jp', 'jp.jenis_pengeluaran_id', '=', 'pengeluaran_t.pengeluaran_jenis_id')
            ->select('jp.jenis_pengeluaran_nama', DB::raw('SUM(pengeluaran_jumlah) as total'))
            ->groupBy('jp.jenis_pengeluaran_nama')
            ->get();

        $perDivisi = (clone $query)
            ->join('divisi_m as dm', 'dm.divisi_id', '=', 'pengeluaran_t.pengeluaran_divisi_id')
            ->select('dm.divisi_nama', DB::raw('SUM(pengeluaran_jumlah) as total'))
            ->groupBy('dm.divisi_nama')
            ->get();

        $perSumberDana = (clone $query)
            ->join('sumber_dana_m as sd', 'sd.sumber_dana_id', '=', 'pengeluaran_t.pengeluaran_sumber_dana_id')
            ->select('sd.sumber_dana_nama', DB::raw('SUM(pengeluaran_jumlah) as total'))
            ->groupBy('sd.sumber_dana_nama')
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Berhasil mendapatkan ringkasan pengeluaran',
            'filters' => [
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
            ],
            'data'    => [
                'total_pengeluaran' => (float) $totalPengeluaran,
                'per_jenis'         => $perJenis,
                'per_divisi'        => $perDivisi,
                'per_sumber_dana'   => $perSumberDana,
            ],
        ], 200);
    }

    private function formatRow(PengeluaranT $item): array
    {
        return [
            'pengeluaran_id'           => $item->pengeluaran_id,
            'pengeluaran_tanggal'      => $item->pengeluaran_tanggal
                ? Carbon::parse($item->pengeluaran_tanggal)->format('Y-m-d')
                : null,
            'pengeluaran_nama'         => $item->pengeluaran_nama,
            'pengeluaran_jenis_id'     => $item->pengeluaran_jenis_id,
            'jenis_pengeluaran_nama'   => $item->jenisPengeluaran?->jenis_pengeluaran_nama,
            'jenis_pengeluaran_kode'   => $item->jenisPengeluaran?->jenis_pengeluaran_kode,
            'pengeluaran_divisi_id'    => $item->pengeluaran_divisi_id,
            'divisi_nama'              => $item->divisi?->divisi_nama,
            'divisi_kode'              => $item->divisi?->divisi_kode,
            'pengeluaran_jumlah'       => (float) $item->pengeluaran_jumlah,
            'pengeluaran_sumber_dana_id' => $item->pengeluaran_sumber_dana_id,
            'sumber_dana_nama'         => $item->sumberDana?->sumber_dana_nama,
            'sumber_dana_kode'         => $item->sumberDana?->sumber_dana_kode,
            'pengeluaran_cara_bayar_id'  => $item->pengeluaran_cara_bayar_id,
            'cara_bayar_nama'          => $item->caraBayar?->carabayar_nama,
            'cara_bayar_kode'          => $item->caraBayar?->carabayar_kode,
            'created_at'               => $item->created_at,
            'updated_at'               => $item->updated_at,
        ];
    }
}
