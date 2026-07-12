<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BenangKeluarT;
use App\Models\BenangKeluarDetailT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BenangKeluarController extends Controller
{
    private const TIPE_LABEL = [
        'PEWARNA_ALAM' => 'Pewarna Alam',
        'TEXTILE'      => 'Textile',
    ];

    private function tipeLabel(?string $kode): ?string
    {
        return self::TIPE_LABEL[$kode] ?? $kode;
    }

    /**
     * Build one table row from a header + its details, joining the distinct
     * warna / tipe / jenis values with commas (per spec notes).
     */
    private function formatRow(BenangKeluarT $row): array
    {
        $details = $row->details;

        $warna = $details->pluck('benang_keluar_detail_warna')->filter()->unique()->values();
        $tipe  = $details->pluck('benang_keluar_detail_tipe')->filter()->unique()
            ->map(fn($k) => $this->tipeLabel($k))->values();
        $jenis = $details->map(fn($d) => optional($d->jenis)->jenisbenang_nama)
            ->filter()->unique()->values();
        $jumlah = $details->sum('benang_keluar_detail_jumlah');

        return [
            'benang_keluar_id'    => $row->benang_keluar_id,
            'petugas'             => optional($row->creator)->name,
            'tanggal_keluar'      => $row->benang_keluar_tanggal_keluar ?? $row->created_at,
            'tanggal_selesai'     => $row->benang_keluar_tanggal_selesai,
            'nama_penenun'        => $row->benang_keluar_nama_penenun,
            'warna'               => $warna->implode(', '),
            'tipe'                => $tipe->implode(', '),
            'jenis'               => $jenis->implode(', '),
            'jumlah'              => $jumlah,
            'author'              => optional($row->completer)->name,
            'status'              => $row->benang_keluar_status,
            'catatan'             => $row->benang_keluar_catatan,
            'foto_hasil'          => $row->benang_keluar_foto_hasil ? asset($row->benang_keluar_foto_hasil) : null,
            'details'             => $details->map(fn($d) => [
                'benang_keluar_detail_id'    => $d->benang_keluar_detail_id,
                'warna'                      => $d->benang_keluar_detail_warna,
                'tipe'                       => $d->benang_keluar_detail_tipe,
                'tipe_label'                 => $this->tipeLabel($d->benang_keluar_detail_tipe),
                'jenis_id'                   => $d->benang_keluar_detail_jenis_id,
                'jenis_nama'                 => optional($d->jenis)->jenisbenang_nama,
                'jumlah'                     => $d->benang_keluar_detail_jumlah,
            ])->values(),
        ];
    }

    /**
     * Benang Keluar table. Ordering: DONE rows shift above PROSES rows
     * ("kalau statusnya done maka urutannya bergeser jadi diatas"),
     * newest first within each group.
     */
    public function index(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $search = $request->query('search', '');

        $rows = BenangKeluarT::with([
            'details.jenis:id,jenisbenang_nama',
            'creator:id,name',
            'completer:id,name',
        ])
            ->when($search, fn($q) => $q->where('benang_keluar_nama_penenun', 'like', "%{$search}%"))
            ->orderByRaw("FIELD(benang_keluar_status, 'DONE', 'PROSES')")
            ->orderByDesc('benang_keluar_id')
            ->get();

        return $this->ok($rows->map(fn($r) => $this->formatRow($r))->values());
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $row = BenangKeluarT::with([
            'details.jenis:id,jenisbenang_nama',
            'creator:id,name',
            'completer:id,name',
        ])->find($id);

        if (!$row) return $this->notFound('Data benang keluar tidak ditemukan');

        return $this->ok($this->formatRow($row));
    }

    /** Tambah Benang Keluar (header + Jenis benang rows). */
    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'benang_keluar_nama_penenun' => 'required|string|max:150',
            'items'                      => 'required|array|min:1',
            'items.*.warna'              => 'required|string|max:100',
            'items.*.tipe'               => 'required|string|max:50',
            'items.*.jenis_id'           => 'required|integer',
            'items.*.jumlah'             => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $keluar = BenangKeluarT::create([
                'benang_keluar_nama_penenun'   => $request->benang_keluar_nama_penenun,
                'benang_keluar_status'         => 'PROSES',
                'benang_keluar_tanggal_keluar' => now(),
                'create_id'                    => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                BenangKeluarDetailT::create([
                    'benang_keluar_detail_keluar_id' => $keluar->benang_keluar_id,
                    'benang_keluar_detail_warna'     => $item['warna'],
                    'benang_keluar_detail_tipe'      => strtoupper($item['tipe']),
                    'benang_keluar_detail_jenis_id'  => $item['jenis_id'],
                    'benang_keluar_detail_jumlah'    => $item['jumlah'],
                    'create_id'                      => Auth::id(),
                ]);
            }

            DB::commit();

            $keluar->load(['details.jenis:id,jenisbenang_nama', 'creator:id,name', 'completer:id,name']);
            return $this->created($this->formatRow($keluar), 'Data benang keluar berhasil ditambahkan ke sistem');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail('Data benang keluar gagal disimpan ke sistem. Silakan coba kembali. ' . $e->getMessage());
        }
    }

    /**
     * Selesai — completion form. Catatan and Foto Hasil are required
     * (foto requirement matches the Pre-Order photo: image, max 2048 KB).
     */
    public function selesai(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $keluar = BenangKeluarT::find($id);
        if (!$keluar) return $this->notFound('Data benang keluar tidak ditemukan');

        if ($keluar->benang_keluar_status === 'DONE') {
            return $this->fail('Transaksi ini sudah diselesaikan', 422);
        }

        $request->validate([
            'benang_keluar_catatan'    => 'required|string',
            'benang_keluar_foto_hasil' => 'required|image|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $path = $request->file('benang_keluar_foto_hasil')->store('benang-keluar', 'public');

            $keluar->update([
                'benang_keluar_status'          => 'DONE',
                'benang_keluar_tanggal_selesai' => now(),
                'benang_keluar_catatan'         => $request->benang_keluar_catatan,
                'benang_keluar_foto_hasil'      => 'storage/' . $path,
                'benang_keluar_complete_id'     => Auth::id(),
                'update_id'                     => Auth::id(),
            ]);

            DB::commit();

            $keluar->load(['details.jenis:id,jenisbenang_nama', 'creator:id,name', 'completer:id,name']);
            return $this->ok($this->formatRow($keluar), 'Data benang keluar berhasil diselesaikan');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail('Data benang keluar gagal disimpan ke sistem. Silakan coba kembali. ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $keluar = BenangKeluarT::with('details')->find($id);
        if (!$keluar) return $this->notFound('Data benang keluar tidak ditemukan');

        DB::beginTransaction();
        try {
            $keluar->update(['delete_id' => Auth::id()]);
            $keluar->details()->update(['delete_id' => Auth::id()]);
            $keluar->details()->delete();
            $keluar->delete();

            DB::commit();
            return $this->ok(null, 'Data benang keluar berhasil dihapus');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail('Data benang gagal dihapus dari sistem. Silakan coba kembali. ' . $e->getMessage());
        }
    }

    /**
     * Export — date range, default today, max 30 days. Same shape as table.
     */
    public function export(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $start = $request->start_date ?: now()->toDateString();
        $end   = $request->end_date ?: now()->toDateString();

        if (\Carbon\Carbon::parse($start)->diffInDays(\Carbon\Carbon::parse($end)) > 30) {
            return $this->fail('Rentang tanggal maksimal 30 hari', 422);
        }

        $rows = BenangKeluarT::with([
            'details.jenis:id,jenisbenang_nama',
            'creator:id,name',
            'completer:id,name',
        ])
            ->whereDate('created_at', '>=', $start)
            ->whereDate('created_at', '<=', $end)
            ->orderByRaw("FIELD(benang_keluar_status, 'DONE', 'PROSES')")
            ->orderByDesc('benang_keluar_id')
            ->get();

        return $this->ok($rows->map(fn($r) => $this->formatRow($r))->values(), 'Export data benang keluar berhasil');
    }
}
