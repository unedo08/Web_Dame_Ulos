<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluarDetailT;
use App\Models\BarangKeluarT;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BarangKeluarTController extends Controller
{
    public function index(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $search = $request->query('search', '');

        $query = BarangKeluarT::with([
            'details',
            'creator:id,name',
            'completer:id,name',
        ])->whereNull('deleted_at');

        if ($search) {
            $query->where('barang_keluar_nama_outsource', 'like', "%{$search}%");
        }

        // PENDING first, then SELESAI; within each group, newest first
        $data = $query->orderByRaw("FIELD(barang_keluar_status, 'PENDING', 'SELESAI')")
            ->orderByDesc('barang_keluar_id')
            ->get();

        return $this->ok($data);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'barang_keluar_nama_outsource' => 'required|string|max:255',
            'items'                        => 'required|array|min:1',
            'items.*.nama_barang'          => 'required|string|max:255',
            'items.*.jumlah'               => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $barangKeluar = BarangKeluarT::create([
                'barang_keluar_nama_outsource' => $request->barang_keluar_nama_outsource,
                'barang_keluar_status'         => 'PENDING',
                'create_id'                    => Auth::id(),
            ]);

            foreach ($request->items as $item) {
                BarangKeluarDetailT::create([
                    'barang_keluar_detail_barang_keluar_id' => $barangKeluar->barang_keluar_id,
                    'barang_keluar_detail_nama_barang'      => $item['nama_barang'],
                    'barang_keluar_detail_jumlah'           => $item['jumlah'],
                    'create_id'                             => Auth::id(),
                ]);
            }

            DB::commit();

            $barangKeluar->load(['details', 'creator:id,name', 'completer:id,name']);

            return $this->created($barangKeluar, 'Data barang keluar berhasil ditambahkan');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail('Gagal menyimpan data barang keluar: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = BarangKeluarT::with([
            'details',
            'creator:id,name',
            'completer:id,name',
        ])->find($id);

        if (!$data) return $this->notFound('Data barang keluar tidak ditemukan');

        return $this->ok($data);
    }

    /**
     * Partial/full completion — splits checked items into a new SELESAI record.
     * Remaining items stay on the original PENDING record with reduced quantities.
     */
    public function selesai(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'items'            => 'required|array|min:1',
            'items.*.detail_id' => 'required|integer',
            'items.*.jumlah'    => 'required|integer|min:1',
        ]);

        $barangKeluar = BarangKeluarT::with('details')->find($id);
        if (!$barangKeluar) return $this->notFound('Data barang keluar tidak ditemukan');

        if ($barangKeluar->barang_keluar_status === 'SELESAI') {
            return $this->fail('Transaksi ini sudah selesai', 422);
        }

        DB::beginTransaction();
        try {
            $submittedItems = collect($request->items)->keyBy('detail_id');
            $originalDetails = $barangKeluar->details->keyBy('barang_keluar_detail_id');

            // Validate submitted items belong to this transaction and qty is valid
            foreach ($submittedItems as $detailId => $submitted) {
                $detail = $originalDetails->get($detailId);
                if (!$detail) {
                    DB::rollBack();
                    return $this->fail("Detail ID {$detailId} tidak ditemukan pada transaksi ini", 422);
                }
                if ($submitted['jumlah'] > $detail->barang_keluar_detail_jumlah) {
                    DB::rollBack();
                    return $this->fail("Jumlah yang diselesaikan melebihi jumlah barang pada item {$detail->barang_keluar_detail_nama_barang}", 422);
                }
            }

            // Create new SELESAI transaction
            $selesaiRecord = BarangKeluarT::create([
                'barang_keluar_nama_outsource' => $barangKeluar->barang_keluar_nama_outsource,
                'barang_keluar_status'         => 'SELESAI',
                'barang_keluar_tanggal_masuk'  => now(),
                'barang_keluar_complete_id'    => Auth::id(),
                'create_id'                    => $barangKeluar->create_id,
            ]);

            // Add completed items to new SELESAI record; reduce original quantities
            foreach ($submittedItems as $detailId => $submitted) {
                $detail = $originalDetails->get($detailId);

                BarangKeluarDetailT::create([
                    'barang_keluar_detail_barang_keluar_id' => $selesaiRecord->barang_keluar_id,
                    'barang_keluar_detail_nama_barang'      => $detail->barang_keluar_detail_nama_barang,
                    'barang_keluar_detail_jumlah'           => $submitted['jumlah'],
                    'create_id'                             => Auth::id(),
                ]);

                $remaining = $detail->barang_keluar_detail_jumlah - $submitted['jumlah'];
                if ($remaining > 0) {
                    $detail->update([
                        'barang_keluar_detail_jumlah' => $remaining,
                        'update_id'                   => Auth::id(),
                    ]);
                } else {
                    $detail->update(['delete_id' => Auth::id()]);
                    $detail->delete();
                }
            }

            // If no details remain, mark original as SELESAI too
            $remainingCount = $barangKeluar->details()->count();
            if ($remainingCount === 0) {
                $barangKeluar->update([
                    'barang_keluar_status'        => 'SELESAI',
                    'barang_keluar_tanggal_masuk' => now(),
                    'barang_keluar_complete_id'   => Auth::id(),
                    'update_id'                   => Auth::id(),
                ]);
            }

            DB::commit();

            return $this->ok(null, 'Transaksi barang keluar berhasil diselesaikan');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail('Gagal menyelesaikan transaksi: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $barangKeluar = BarangKeluarT::with('details')->find($id);
        if (!$barangKeluar) return $this->notFound('Data barang keluar tidak ditemukan');

        DB::beginTransaction();
        try {
            $barangKeluar->update(['delete_id' => Auth::id()]);
            $barangKeluar->details()->update(['delete_id' => Auth::id()]);
            $barangKeluar->details()->delete();
            $barangKeluar->delete();

            DB::commit();
            return $this->ok(null, 'Data barang keluar berhasil dihapus');
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->fail('Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $data = DB::table('barang_keluar_t as bk')
            ->join('barang_keluar_detail_t as bkd', 'bkd.barang_keluar_detail_barang_keluar_id', '=', 'bk.barang_keluar_id')
            ->leftJoin('users as creator', 'creator.id', '=', 'bk.create_id')
            ->leftJoin('users as completer', 'completer.id', '=', 'bk.barang_keluar_complete_id')
            ->whereNull('bk.deleted_at')
            ->whereNull('bkd.deleted_at')
            ->when($request->start_date, fn($q) => $q->whereDate('bk.created_at', '>=', $request->start_date))
            ->when($request->end_date,   fn($q) => $q->whereDate('bk.created_at', '<=', $request->end_date))
            ->select(
                'bk.barang_keluar_id',
                'bk.created_at as tanggal_keluar',
                'creator.name as petugas',
                'bk.barang_keluar_nama_outsource',
                'bkd.barang_keluar_detail_nama_barang',
                'bkd.barang_keluar_detail_jumlah',
                'bk.barang_keluar_status',
                'bk.barang_keluar_tanggal_masuk as tanggal_masuk',
                'completer.name as author'
            )
            ->orderByRaw("FIELD(bk.barang_keluar_status, 'PENDING', 'SELESAI')")
            ->orderByDesc('bk.barang_keluar_id')
            ->get();

        return $this->ok($data, 'Export data barang keluar berhasil');
    }
}
