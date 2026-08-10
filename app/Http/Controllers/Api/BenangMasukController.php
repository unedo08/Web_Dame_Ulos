<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BenangMasukT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BenangMasukController extends Controller
{
    public const TIPE_PEWARNA_ALAM = 'PEWARNA_ALAM';
    public const TIPE_TEXTILE      = 'TEXTILE';

    private const TIPE_LABEL = [
        self::TIPE_PEWARNA_ALAM => 'Pewarna Alam',
        self::TIPE_TEXTILE      => 'Textile',
    ];

    private function tipeLabel(?string $kode): ?string
    {
        return self::TIPE_LABEL[$kode] ?? $kode;
    }

    /**
     * Benang Masuk table.
     * Columns: Tanggal (created_at), Tipe, Jenis Benang, Warna, Jumlah, Author.
     */
    public function index(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $tipe   = strtoupper($request->query('tipe', ''));
        $search = $request->query('search', '');

        $rows = BenangMasukT::with(['jenis:id,jenisbenang_nama', 'creator:id,name'])
            ->when($tipe && $tipe !== 'ALL', fn($q) => $q->where('benang_masuk_tipe', $tipe))
            ->when($search, fn($q) => $q->where('benang_masuk_warna', 'like', "%{$search}%"))
            ->orderByDesc('benang_masuk_id')
            ->get();

        $data = $rows->map(function ($row) {
            return [
                'benang_masuk_id'          => $row->benang_masuk_id,
                'tanggal'                  => $row->created_at,
                'benang_masuk_tipe'        => $row->benang_masuk_tipe,
                'tipe_label'               => $this->tipeLabel($row->benang_masuk_tipe),
                'benang_masuk_jenis_id'    => $row->benang_masuk_jenis_id,
                'jenis_nama'               => optional($row->jenis)->jenisbenang_nama,
                'benang_masuk_warna'       => $row->benang_masuk_warna,
                'benang_masuk_jumlah'      => $row->benang_masuk_jumlah,
                'benang_masuk_sumber_warna' => $row->benang_masuk_sumber_warna,
                'author'                   => optional($row->creator)->name,
                'create_id'                => $row->create_id,
            ];
        });

        return $this->ok($data);
    }

    /**
     * Sumber warna dropdown (Pewarna Alam form) = distinct colors from Textile
     * stock — the undyed base threads that natural dye is applied to.
     * "Putih" is always available (mixable base) and is returned first.
     */
    public function sumberWarna()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $colors = BenangMasukT::where('benang_masuk_tipe', self::TIPE_TEXTILE)
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('benang_masuk_warna')
            ->pluck('benang_masuk_warna')
            ->reject(fn($c) => strtolower(trim($c)) === 'putih')
            ->values();

        $data = collect(['Putih'])->merge($colors)->values();

        return $this->ok($data, 'Berhasil mendapatkan sumber warna');
    }

    /**
     * Warna dropdown (Benang Keluar form) = distinct colors from Pewarna Alam
     * stock only — thread that goes out to a penenun is already dyed, so the
     * Textile base colors used by sumberWarna() must not appear here.
     */
    public function warnaPewarnaAlam()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = BenangMasukT::where('benang_masuk_tipe', self::TIPE_PEWARNA_ALAM)
            ->whereNull('deleted_at')
            ->distinct()
            ->orderBy('benang_masuk_warna')
            ->pluck('benang_masuk_warna')
            ->filter()
            ->values();

        return $this->ok($data, 'Berhasil mendapatkan warna pewarna alam');
    }

    /** Tambah Pewarna Alami */
    public function storePewarnaAlam(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'benang_masuk_warna'        => 'required|string|max:100',
            'benang_masuk_jenis_id'     => 'required|integer',
            'benang_masuk_jumlah'       => 'required|integer|min:1',
            'benang_masuk_sumber_warna' => 'required|string|max:100',
        ]);

        $validated['benang_masuk_tipe'] = self::TIPE_PEWARNA_ALAM;
        $validated['create_id']         = Auth::id();

        $data = BenangMasukT::create($validated);

        return $this->created($data, 'Data pewarna alami berhasil ditambahkan');
    }

    /** Tambah Textile */
    public function storeTextile(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'benang_masuk_warna'    => 'required|string|max:100',
            'benang_masuk_jenis_id' => 'required|integer',
            'benang_masuk_jumlah'   => 'required|integer|min:1',
        ]);

        $validated['benang_masuk_tipe'] = self::TIPE_TEXTILE;
        $validated['create_id']         = Auth::id();

        $data = BenangMasukT::create($validated);

        return $this->created($data, 'Data textile berhasil ditambahkan');
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = BenangMasukT::with(['jenis:id,jenisbenang_nama', 'creator:id,name'])->find($id);
        if (!$data) return $this->notFound('Data benang masuk tidak ditemukan');

        return $this->ok($data);
    }

    /** Edit (frontend restricts to superadmin). */
    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = BenangMasukT::find($id);
        if (!$data) return $this->notFound('Data benang masuk tidak ditemukan');

        $validated = $request->validate([
            'benang_masuk_warna'        => 'sometimes|required|string|max:100',
            'benang_masuk_jenis_id'     => 'sometimes|required|integer',
            'benang_masuk_jumlah'       => 'sometimes|required|integer|min:1',
            'benang_masuk_sumber_warna' => 'nullable|string|max:100',
        ]);

        $validated['update_id'] = Auth::id();
        $data->update($validated);

        return $this->ok($data, 'Data benang masuk berhasil diupdate');
    }

    /** Delete (frontend restricts to superadmin). */
    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = BenangMasukT::find($id);
        if (!$data) return $this->notFound('Data benang masuk tidak ditemukan');

        $data->delete_id = Auth::id();
        $data->save();
        $data->delete();

        return $this->ok(null, 'Data benang masuk berhasil dihapus');
    }

    /**
     * Stok Benang — mirror of Benang Masuk accumulated by (tipe, jenis, warna).
     * Filter by tipe: ALL | PEWARNA_ALAM | TEXTILE (default ALL).
     */
    public function stok(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $tipe = strtoupper($request->query('tipe', 'ALL'));

        $rows = DB::table('benang_masuk_t as bm')
            ->leftJoin('jenis_benang_m as jb', 'jb.id', '=', 'bm.benang_masuk_jenis_id')
            ->whereNull('bm.deleted_at')
            ->when($tipe && $tipe !== 'ALL', fn($q) => $q->where('bm.benang_masuk_tipe', $tipe))
            ->groupBy('bm.benang_masuk_tipe', 'bm.benang_masuk_jenis_id', 'jb.jenisbenang_nama', 'bm.benang_masuk_warna')
            ->select(
                'bm.benang_masuk_tipe',
                'bm.benang_masuk_jenis_id',
                'jb.jenisbenang_nama as jenis_nama',
                'bm.benang_masuk_warna',
                DB::raw('SUM(bm.benang_masuk_jumlah) as total_jumlah')
            )
            ->orderBy('bm.benang_masuk_tipe')
            ->orderBy('bm.benang_masuk_warna')
            ->get()
            ->map(function ($row) {
                $row->tipe_label = $this->tipeLabel($row->benang_masuk_tipe);
                return $row;
            });

        return $this->ok($rows, 'Berhasil mendapatkan stok benang');
    }
}
