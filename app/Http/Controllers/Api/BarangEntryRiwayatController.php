<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangEntryRiwayat;

class BarangEntryRiwayatController extends Controller
{
    public function getByBarang($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $riwayat = BarangEntryRiwayat::where('riwayat_barangentry_id', $id)
            ->with('user:id,name')
            ->orderBy('updated_at')
            ->get()
            ->values()
            ->map(function ($item, $index) {
                return [
                    'no'        => $index + 1,
                    'aktivitas' => $item->riwayat_aktivitas,
                    'tanggal'   => $item->updated_at,
                    'author'    => $item->user?->name ?? '-',
                ];
            });

        return response()->json([
            'code'    => 200,
            'message' => 'Riwayat retrieved',
            'data'    => $riwayat,
        ]);
    }
}
