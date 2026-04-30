<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function transaksiTypeChart(Request $request)
    {
        $type = $request->type ?? 'day';

        if ($type == 'day') {
            $pie = DB::select("
                SELECT tt2.transaksi_tipe, COUNT(*) as total
                FROM transaksi_t tt2
                WHERE tt2.deleted_at IS NULL
                AND DATE(tt2.created_at) = CURDATE()
                GROUP BY tt2.transaksi_tipe
            ");

            $line = DB::select("
                SELECT
                    DATE(tt2.created_at) as Tanggal,
                    tt2.transaksi_tipe,
                    COUNT(*) as total
                FROM transaksi_t tt2
                WHERE tt2.deleted_at IS NULL
                AND MONTH(tt2.created_at) = MONTH(CURDATE())
                AND YEAR(tt2.created_at) = YEAR(CURDATE())
                GROUP BY DATE(tt2.created_at), tt2.transaksi_tipe
                ORDER BY Tanggal
            ");

        } elseif ($type == 'month') {

            $pie = DB::select("
                SELECT tt2.transaksi_tipe, COUNT(*) as total
                FROM transaksi_t tt2
                WHERE tt2.deleted_at IS NULL
                AND MONTH(tt2.created_at) = MONTH(CURDATE())
                AND YEAR(tt2.created_at) = YEAR(CURDATE())
                GROUP BY tt2.transaksi_tipe
            ");

            $line = DB::select("
                SELECT
                    MONTH(tt2.created_at) as Bulan,
                    tt2.transaksi_tipe,
                    COUNT(*) as total
                FROM transaksi_t tt2
                WHERE tt2.deleted_at IS NULL
                AND YEAR(tt2.created_at) = YEAR(CURDATE())
                GROUP BY MONTH(tt2.created_at), tt2.transaksi_tipe
                ORDER BY Bulan
            ");

        } else {

            $pie = DB::select("
                SELECT tt2.transaksi_tipe, COUNT(*) as total
                FROM transaksi_t tt2
                WHERE tt2.deleted_at IS NULL
                AND YEAR(tt2.created_at) = YEAR(CURDATE())
                GROUP BY tt2.transaksi_tipe
            ");

            $line = DB::select("
                SELECT
                    YEAR(tt2.created_at) as Tahun,
                    tt2.transaksi_tipe,
                    COUNT(*) as total
                FROM transaksi_t tt2
                WHERE tt2.deleted_at IS NULL
                GROUP BY YEAR(tt2.created_at), tt2.transaksi_tipe
                ORDER BY Tahun
            ");
        }

        $totalPie = collect($pie)->sum('total');
        foreach ($pie as $item) {
            $item->percent = $totalPie > 0 ? round(($item->total / $totalPie) * 100, 2) : 0;
        }

        return response()->json([
            'pie_chart' => $pie,
            'line_chart' => $line
        ]);
    }

    public function transaksiPlatformChart(Request $request)
    {
        $type = $request->type ?? 'day';

        if ($type == 'day') {
            $date = $request->date ?? Carbon::today()->toDateString();

            $pie = DB::select("
                SELECT tt.transaksidetail_platform, COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND DATE(tt.created_at) = ?
                GROUP BY tt.transaksidetail_platform
            ", [$date]);

            $line = DB::select("
                SELECT
                    DATE(tt.created_at) as Tanggal,
                    tt.transaksidetail_platform,
                    COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND DATE(tt.created_at) = ?
                GROUP BY DATE(tt.created_at), tt.transaksidetail_platform
                ORDER BY Tanggal
            ", [$date]);

        } elseif ($type == 'month') {
            $month = $request->month ?? Carbon::now()->month;
            $year  = $request->year  ?? Carbon::now()->year;

            $pie = DB::select("
                SELECT tt.transaksidetail_platform, COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND MONTH(tt.created_at) = ?
                AND YEAR(tt.created_at) = ?
                GROUP BY tt.transaksidetail_platform
            ", [$month, $year]);

            $line = DB::select("
                SELECT
                    MONTH(tt.created_at) as Bulan,
                    tt.transaksidetail_platform,
                    COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND MONTH(tt.created_at) = ?
                AND YEAR(tt.created_at) = ?
                GROUP BY MONTH(tt.created_at), tt.transaksidetail_platform
                ORDER BY Bulan
            ", [$month, $year]);

        } else {
            $year = $request->year ?? Carbon::now()->year;

            $pie = DB::select("
                SELECT tt.transaksidetail_platform, COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND YEAR(tt.created_at) = ?
                GROUP BY tt.transaksidetail_platform
            ", [$year]);

            $line = DB::select("
                SELECT
                    YEAR(tt.created_at) as Tahun,
                    tt.transaksidetail_platform,
                    COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND YEAR(tt.created_at) = ?
                GROUP BY YEAR(tt.created_at), tt.transaksidetail_platform
                ORDER BY Tahun
            ", [$year]);
        }

        $totalPie = collect($pie)->sum('total');
        foreach ($pie as $item) {
            $item->percent = $totalPie > 0 ? round(($item->total / $totalPie) * 100, 2) : 0;
        }

        return response()->json([
            'pie_chart' => $pie,
            'line_chart' => $line
        ]);
    }

    public function barangChart(Request $request)
    {
        $type = $request->type ?? 'month';

        $query = DB::table('transaksidetail_t as tt')
            ->join('barangentry_m as bm', 'tt.transaksidetail_barang_id', '=', 'bm.barangentry_id')
            ->whereNull('bm.deleted_at')
            ->whereNull('tt.deleted_at');

        if ($type == 'day') {
            $date = $request->date ?? Carbon::today()->toDateString();
            $query->whereDate('tt.created_at', $date);
            $format = '%H:00';
        } elseif ($type == 'month') {
            $month = $request->month ?? Carbon::now()->month;
            $year  = $request->year  ?? Carbon::now()->year;
            $query->whereMonth('tt.created_at', $month)
                  ->whereYear('tt.created_at', $year);
            $format = '%Y-%m-%d';
        } else {
            $year = $request->year ?? Carbon::now()->year;
            $query->whereYear('tt.created_at', $year);
            $format = '%Y-%m';
        }

        $data = $query->select(
            DB::raw("DATE_FORMAT(tt.created_at, '$format') as periode"),
            DB::raw('SUM(bm.barangentry_harga_net) as total_harga_net'),
            DB::raw('SUM(tt.transaksidetail_harga_barang) as total_harga_jual'),
            DB::raw('SUM(bm.barangentry_modal) as total_harga_modal')
        )
        ->groupBy('periode')
        ->orderBy('periode')
        ->get();

        return response()->json([
            'labels' => $data->pluck('periode'),
            'series' => [
                ['name' => 'Harga Net',   'data' => $data->pluck('total_harga_net')],
                ['name' => 'Harga Jual',  'data' => $data->pluck('total_harga_jual')],
                ['name' => 'Harga Modal', 'data' => $data->pluck('total_harga_modal')]
            ]
        ]);
    }

    public function jenisBarangEntryChart(Request $request)
    {
        $type = $request->type ?? 'month';

        if ($type == 'day') {
            $date = $request->date ?? Carbon::today()->toDateString();

            $data = DB::select("
                SELECT
                    DATE(cm.created_at) as Tanggal,
                    CASE
                        WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                        ELSE jm.jenisbarang_kode
                    END AS kategori,
                    COUNT(*) as total
                FROM jenisbarang_m jm
                JOIN code_m cm ON cm.code_jenisbarang_id = jm.jenisbarang_id
                WHERE cm.deleted_at IS NULL
                AND jm.deleted_at IS NULL
                AND DATE(cm.created_at) = ?
                GROUP BY Tanggal, kategori
                ORDER BY Tanggal
            ", [$date]);

        } elseif ($type == 'month') {
            $month = $request->month ?? Carbon::now()->month;
            $year  = $request->year  ?? Carbon::now()->year;

            $data = DB::select("
                SELECT
                    MONTH(cm.created_at) as Bulan,
                    CASE
                        WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                        ELSE jm.jenisbarang_kode
                    END AS kategori,
                    COUNT(*) as total
                FROM jenisbarang_m jm
                JOIN code_m cm ON cm.code_jenisbarang_id = jm.jenisbarang_id
                WHERE cm.deleted_at IS NULL
                AND jm.deleted_at IS NULL
                AND MONTH(cm.created_at) = ?
                AND YEAR(cm.created_at) = ?
                GROUP BY Bulan, kategori
                ORDER BY Bulan
            ", [$month, $year]);

        } else {
            $year = $request->year ?? Carbon::now()->year;

            $data = DB::select("
                SELECT
                    YEAR(cm.created_at) as Tahun,
                    CASE
                        WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                        ELSE jm.jenisbarang_kode
                    END AS kategori,
                    COUNT(*) as total
                FROM jenisbarang_m jm
                JOIN code_m cm ON cm.code_jenisbarang_id = jm.jenisbarang_id
                WHERE cm.deleted_at IS NULL
                AND jm.deleted_at IS NULL
                AND YEAR(cm.created_at) = ?
                GROUP BY Tahun, kategori
                ORDER BY Tahun
            ", [$year]);
        }

        return response()->json($data);
    }

    public function jenisBarangJualChart(Request $request)
    {
        $type = $request->type ?? 'month';

        if ($type == 'day') {
            $date = $request->date ?? Carbon::today()->toDateString();

            $data = DB::select("
                SELECT
                    DATE(tt.created_at) as Tanggal,
                    CASE
                        WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                        ELSE jm.jenisbarang_kode
                    END AS kategori,
                    COUNT(*) AS total
                FROM jenisbarang_m jm
                JOIN code_m cm ON cm.code_jenisbarang_id = jm.jenisbarang_id
                JOIN barangentry_m bm ON bm.barangentry_code_id = cm.code_id
                JOIN transaksidetail_t tt ON tt.transaksidetail_barang_id = bm.barangentry_id
                WHERE cm.deleted_at IS NULL
                AND jm.deleted_at IS NULL
                AND tt.deleted_at IS NULL
                AND DATE(tt.created_at) = ?
                GROUP BY Tanggal, kategori
                ORDER BY Tanggal
            ", [$date]);

        } elseif ($type == 'month') {
            $month = $request->month ?? Carbon::now()->month;
            $year  = $request->year  ?? Carbon::now()->year;

            $data = DB::select("
                SELECT
                    MONTH(tt.created_at) as Bulan,
                    CASE
                        WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                        ELSE jm.jenisbarang_kode
                    END AS kategori,
                    COUNT(*) AS total
                FROM jenisbarang_m jm
                JOIN code_m cm ON cm.code_jenisbarang_id = jm.jenisbarang_id
                JOIN barangentry_m bm ON bm.barangentry_code_id = cm.code_id
                JOIN transaksidetail_t tt ON tt.transaksidetail_barang_id = bm.barangentry_id
                WHERE cm.deleted_at IS NULL
                AND jm.deleted_at IS NULL
                AND tt.deleted_at IS NULL
                AND MONTH(tt.created_at) = ?
                AND YEAR(tt.created_at) = ?
                GROUP BY Bulan, kategori
                ORDER BY Bulan
            ", [$month, $year]);

        } else {
            $year = $request->year ?? Carbon::now()->year;

            $data = DB::select("
                SELECT
                    YEAR(tt.created_at) as Tahun,
                    CASE
                        WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                        ELSE jm.jenisbarang_kode
                    END AS kategori,
                    COUNT(*) AS total
                FROM jenisbarang_m jm
                JOIN code_m cm ON cm.code_jenisbarang_id = jm.jenisbarang_id
                JOIN barangentry_m bm ON bm.barangentry_code_id = cm.code_id
                JOIN transaksidetail_t tt ON tt.transaksidetail_barang_id = bm.barangentry_id
                WHERE cm.deleted_at IS NULL
                AND jm.deleted_at IS NULL
                AND tt.deleted_at IS NULL
                AND YEAR(tt.created_at) = ?
                GROUP BY Tahun, kategori
                ORDER BY Tahun
            ", [$year]);
        }

        return response()->json($data);
    }

    public function customerChart(Request $request)
    {
        $month = $request->month;
        $year  = $request->year;

        $query = DB::table('transaksi_t as tt')
            ->join('customer_m as cm', 'tt.transaksi_customer_id', '=', 'cm.customer_id')
            ->whereNull('tt.deleted_at')
            ->whereNull('cm.deleted_at');

        if ($month && $year) {
            $query->whereMonth('tt.created_at', $month)
                  ->whereYear('tt.created_at', $year);
        } else {
            $query->where('tt.created_at', '>=', Carbon::now()->subMonth());
        }

        $data = $query->select(
                'cm.customer_nama',
                DB::raw('SUM(tt.transaksi_total_harga) as nilai_pembelian'),
                DB::raw('COUNT(tt.transaksi_id) as jumlah_transaksi')
            )
            ->groupBy('cm.customer_id', 'cm.customer_nama')
            ->orderBy('nilai_pembelian', 'DESC')
            ->limit(10)
            ->get();

        return response()->json($data);
    }

    public function jumlahCustomerChart(Request $request)
    {
        $month = $request->month ?? Carbon::now()->month;
        $year  = $request->year  ?? Carbon::now()->year;

        $startOfMonth = Carbon::create($year, $month, 1)->startOfMonth()->toDateString();

        $pie = DB::select("
            SELECT
                SUM(
                    CASE
                        WHEN YEAR(created_at) = ? AND MONTH(created_at) = ?
                        THEN 1 ELSE 0
                    END
                ) AS customer_baru,
                SUM(
                    CASE
                        WHEN created_at < ?
                        THEN 1 ELSE 0
                    END
                ) AS customer_lama
            FROM customer_m
            WHERE deleted_at IS NULL
        ", [$year, $month, $startOfMonth]);

        $line = DB::select("
            SELECT
                DATE_FORMAT(created_at,'%Y-%m') AS bulan,
                COUNT(*) AS total
            FROM customer_m
            WHERE deleted_at IS NULL
            AND YEAR(created_at) = ?
            GROUP BY bulan
            ORDER BY bulan
        ", [$year]);

        $customerBaru  = (int) ($pie[0]->customer_baru ?? 0);
        $customerLama  = (int) ($pie[0]->customer_lama ?? 0);
        $totalCustomer = $customerBaru + $customerLama;

        return response()->json([
            'pie_chart' => [
                'customer_baru'         => $customerBaru,
                'customer_lama'         => $customerLama,
                'customer_baru_percent' => $totalCustomer > 0 ? round(($customerBaru / $totalCustomer) * 100, 2) : 0,
                'customer_lama_percent' => $totalCustomer > 0 ? round(($customerLama / $totalCustomer) * 100, 2) : 0,
            ],
            'line_chart' => $line
        ]);
    }
}
