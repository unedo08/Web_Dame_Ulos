<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Grafik Benang — read-only charts over the existing benang tables.
 *
 * Produksi benang = benang_masuk_t (per created_at, same as the Tanggal column).
 * Benang keluar   = benang_keluar_detail_t, dated by its header's
 *                   tanggal_keluar (falls back to created_at).
 * Soft-deleted rows on either side are excluded, matching stokTersedia().
 */
class GrafikBenangController extends Controller
{
    private const BULAN = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];

    private function tipe(Request $request): string
    {
        $tipe = strtoupper($request->query('tipe', BenangMasukController::TIPE_PEWARNA_ALAM));

        return in_array($tipe, [BenangMasukController::TIPE_PEWARNA_ALAM, BenangMasukController::TIPE_TEXTILE])
            ? $tipe
            : BenangMasukController::TIPE_PEWARNA_ALAM;
    }

    private function produksiQuery(string $tipe)
    {
        return DB::table('benang_masuk_t as bm')
            ->whereNull('bm.deleted_at')
            ->where('bm.benang_masuk_tipe', $tipe);
    }

    private function keluarQuery(string $tipe)
    {
        return DB::table('benang_keluar_detail_t as bkd')
            ->join('benang_keluar_t as bk', 'bk.benang_keluar_id', '=', 'bkd.benang_keluar_detail_keluar_id')
            ->whereNull('bkd.deleted_at')
            ->whereNull('bk.deleted_at')
            ->where('bkd.benang_keluar_detail_tipe', $tipe);
    }

    /** [{label, total}] sorted by total desc, as ints. */
    private function toRows($rows)
    {
        return collect($rows)
            ->map(fn($r) => ['label' => $r->label, 'total' => (int) $r->total])
            ->sortByDesc('total')
            ->values();
    }

    /**
     * Grafik Pertahun — produksi benang vs benang keluar per bulan.
     * Query: tahun (default tahun ini), tipe (default PEWARNA_ALAM).
     */
    public function pertahun(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $tipe  = $this->tipe($request);
        $tahun = (int) $request->query('tahun', Carbon::now()->year);

        $produksi = $this->produksiQuery($tipe)
            ->whereYear('bm.created_at', $tahun)
            ->groupBy(DB::raw('MONTH(bm.created_at)'))
            ->select(
                DB::raw('MONTH(bm.created_at) as bulan'),
                DB::raw('SUM(bm.benang_masuk_jumlah) as total')
            )
            ->pluck('total', 'bulan');

        $tanggalKeluar = 'COALESCE(bk.benang_keluar_tanggal_keluar, bk.created_at)';

        $keluar = $this->keluarQuery($tipe)
            ->whereRaw("YEAR({$tanggalKeluar}) = ?", [$tahun])
            ->groupBy(DB::raw("MONTH({$tanggalKeluar})"))
            ->select(
                DB::raw("MONTH({$tanggalKeluar}) as bulan"),
                DB::raw('SUM(bkd.benang_keluar_detail_jumlah) as total')
            )
            ->pluck('total', 'bulan');

        // Always 12 points so empty months show as 0 instead of disappearing.
        $data = [
            'tahun'    => $tahun,
            'tipe'     => $tipe,
            'labels'   => self::BULAN,
            'produksi' => collect(range(1, 12))->map(fn($b) => (int) ($produksi[$b] ?? 0))->values(),
            'keluar'   => collect(range(1, 12))->map(fn($b) => (int) ($keluar[$b] ?? 0))->values(),
        ];

        return $this->ok($data, 'Berhasil mendapatkan grafik pertahun');
    }

    /**
     * Grafik Perbulan — dalam satu bulan:
     *  - produksi_warna : jumlah produksi benang per warna
     *  - produksi_orang : jumlah produksi benang per orang (user yang input benang masuk)
     *  - keluar_warna   : jumlah benang keluar per warna
     * Query: tahun, bulan (default bulan ini), tipe (default PEWARNA_ALAM).
     */
    public function perbulan(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $tipe  = $this->tipe($request);
        $tahun = (int) $request->query('tahun', Carbon::now()->year);
        $bulan = (int) $request->query('bulan', Carbon::now()->month);

        // Warna is grouped trimmed + case-insensitive, like BenangMasukT::stokKey().
        $produksiWarna = $this->produksiQuery($tipe)
            ->whereYear('bm.created_at', $tahun)
            ->whereMonth('bm.created_at', $bulan)
            ->groupBy(DB::raw('LOWER(TRIM(bm.benang_masuk_warna))'))
            ->select(
                DB::raw('MIN(TRIM(bm.benang_masuk_warna)) as label'),
                DB::raw('SUM(bm.benang_masuk_jumlah) as total')
            )
            ->get();

        $produksiOrang = $this->produksiQuery($tipe)
            ->leftJoin('users as u', 'u.id', '=', 'bm.create_id')
            ->whereYear('bm.created_at', $tahun)
            ->whereMonth('bm.created_at', $bulan)
            ->groupBy('bm.create_id', 'u.name')
            ->select(
                DB::raw("COALESCE(u.name, '-') as label"),
                DB::raw('SUM(bm.benang_masuk_jumlah) as total')
            )
            ->get();

        $tanggalKeluar = 'COALESCE(bk.benang_keluar_tanggal_keluar, bk.created_at)';

        $keluarWarna = $this->keluarQuery($tipe)
            ->whereRaw("YEAR({$tanggalKeluar}) = ?", [$tahun])
            ->whereRaw("MONTH({$tanggalKeluar}) = ?", [$bulan])
            ->groupBy(DB::raw('LOWER(TRIM(bkd.benang_keluar_detail_warna))'))
            ->select(
                DB::raw('MIN(TRIM(bkd.benang_keluar_detail_warna)) as label'),
                DB::raw('SUM(bkd.benang_keluar_detail_jumlah) as total')
            )
            ->get();

        $data = [
            'tahun'          => $tahun,
            'bulan'          => $bulan,
            'bulan_label'    => self::BULAN[$bulan - 1] ?? null,
            'tipe'           => $tipe,
            'produksi_warna' => $this->toRows($produksiWarna),
            'produksi_orang' => $this->toRows($produksiOrang),
            'keluar_warna'   => $this->toRows($keluarWarna),
        ];

        return $this->ok($data, 'Berhasil mendapatkan grafik perbulan');
    }
}
