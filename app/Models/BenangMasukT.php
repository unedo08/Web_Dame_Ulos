<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BenangMasukT extends Model
{
    use SoftDeletes;

    protected $table = 'benang_masuk_t';
    protected $primaryKey = 'benang_masuk_id';

    protected $fillable = [
        'benang_masuk_tipe',
        'benang_masuk_jenis_id',
        'benang_masuk_warna',
        'benang_masuk_jumlah',
        'benang_masuk_sumber_warna',
        'create_id',
        'update_id',
        'delete_id',
    ];

    public function jenis()
    {
        return $this->belongsTo(JenisBenangM::class, 'benang_masuk_jenis_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_id', 'id');
    }

    /**
     * Lookup key for one stock bucket: tipe + jenis + warna.
     *
     * Warna is compared case-insensitively and trimmed — a benang keluar row
     * copies its warna from a dropdown fed by these same rows, but casing and
     * stray spaces still differ between records typed by hand.
     */
    public static function stokKey($tipe, $jenisId, $warna): string
    {
        return strtoupper(trim((string) $tipe))
            . '|' . (int) $jenisId
            . '|' . mb_strtolower(trim((string) $warna));
    }

    /**
     * Stok benang per (tipe, jenis, warna) = jumlah masuk - jumlah keluar.
     *
     * Keyed by stokKey() so both the Stok Benang tab and the Benang Keluar
     * stock check read the same numbers. Soft-deleted rows on either side are
     * excluded, so deleting a benang keluar returns its thread to the stock.
     *
     * @return Collection<string, array>
     */
    public static function stokTersedia(): Collection
    {
        $masuk = DB::table('benang_masuk_t as bm')
            ->leftJoin('jenis_benang_m as jb', 'jb.id', '=', 'bm.benang_masuk_jenis_id')
            ->whereNull('bm.deleted_at')
            ->groupBy('bm.benang_masuk_tipe', 'bm.benang_masuk_jenis_id', 'jb.jenisbenang_nama', 'bm.benang_masuk_warna')
            ->select(
                'bm.benang_masuk_tipe',
                'bm.benang_masuk_jenis_id',
                'jb.jenisbenang_nama as jenis_nama',
                'bm.benang_masuk_warna',
                DB::raw('SUM(bm.benang_masuk_jumlah) as total_masuk')
            )
            ->orderBy('bm.benang_masuk_tipe')
            ->orderBy('bm.benang_masuk_warna')
            ->get();

        $keluar = DB::table('benang_keluar_detail_t as bkd')
            ->join('benang_keluar_t as bk', 'bk.benang_keluar_id', '=', 'bkd.benang_keluar_detail_keluar_id')
            ->whereNull('bkd.deleted_at')
            ->whereNull('bk.deleted_at')
            ->select(
                'bkd.benang_keluar_detail_tipe as tipe',
                'bkd.benang_keluar_detail_jenis_id as jenis_id',
                'bkd.benang_keluar_detail_warna as warna',
                DB::raw('SUM(bkd.benang_keluar_detail_jumlah) as total_keluar')
            )
            ->groupBy('bkd.benang_keluar_detail_tipe', 'bkd.benang_keluar_detail_jenis_id', 'bkd.benang_keluar_detail_warna')
            ->get()
            ->groupBy(fn($r) => self::stokKey($r->tipe, $r->jenis_id, $r->warna))
            ->map(fn($group) => (int) $group->sum('total_keluar'));

        // Fold the SQL groups again in PHP: two masuk rows whose warna differs
        // only by case or padding are one bucket here, and must not each
        // subtract the whole keluar total.
        $rows = [];
        foreach ($masuk as $m) {
            $key = self::stokKey($m->benang_masuk_tipe, $m->benang_masuk_jenis_id, $m->benang_masuk_warna);

            if (isset($rows[$key])) {
                $rows[$key]['total_masuk'] += (int) $m->total_masuk;
                continue;
            }

            $rows[$key] = [
                'benang_masuk_tipe'     => $m->benang_masuk_tipe,
                'benang_masuk_jenis_id' => (int) $m->benang_masuk_jenis_id,
                'jenis_nama'            => $m->jenis_nama,
                'benang_masuk_warna'    => $m->benang_masuk_warna,
                'total_masuk'           => (int) $m->total_masuk,
            ];
        }

        foreach ($rows as $key => $row) {
            $keluarJumlah = $keluar->get($key, 0);

            $rows[$key]['total_keluar'] = $keluarJumlah;
            $rows[$key]['total_jumlah'] = max(0, $row['total_masuk'] - $keluarJumlah);
        }

        return collect($rows);
    }
}
