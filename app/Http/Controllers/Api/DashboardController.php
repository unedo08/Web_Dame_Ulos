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
            // PIE CHART (hari ini)
            $pie = DB::select("
                SELECT tt2.transaksi_tipe, COUNT(*) as total
                FROM transaksi_t tt2 
                WHERE tt2.deleted_at IS NULL
                AND DATE(tt2.created_at) = CURDATE()
                GROUP BY tt2.transaksi_tipe
            ");

            // LINE CHART (per hari bulan ini)
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

            // PIE CHART (bulan ini)
            $pie = DB::select("
                SELECT tt2.transaksi_tipe, COUNT(*) as total
                FROM transaksi_t tt2
                WHERE tt2.deleted_at IS NULL
                AND MONTH(tt2.created_at) = MONTH(CURDATE())
                AND YEAR(tt2.created_at) = YEAR(CURDATE())
                GROUP BY tt2.transaksi_tipe
            ");

            // LINE CHART (per bulan tahun ini)
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

            // PIE CHART (tahun ini)
            $pie = DB::select("
                SELECT tt2.transaksi_tipe, COUNT(*) as total
                FROM transaksi_t tt2
                WHERE tt2.deleted_at IS NULL
                AND YEAR(tt2.created_at) = YEAR(CURDATE())
                GROUP BY tt2.transaksi_tipe
            ");

            // LINE CHART (per tahun)
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
            // PIE CHART (hari ini)
            $pie = DB::select("
                SELECT tt.transaksidetail_platform, COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND DATE(tt.created_at) = CURDATE()
                GROUP BY tt.transaksidetail_platform
            ");

            // LINE CHART (per hari bulan ini)
            $line = DB::select("
                SELECT 
                    DATE(tt.created_at) as Tanggal,
                    tt.transaksidetail_platform,
                    COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND MONTH(tt.created_at) = MONTH(CURDATE())
                AND YEAR(tt.created_at) = YEAR(CURDATE())
                GROUP BY DATE(tt.created_at), tt.transaksidetail_platform
                ORDER BY Tanggal
            ");

        } elseif ($type == 'month') {

            // PIE CHART (bulan ini)
            $pie = DB::select("
                SELECT tt.transaksidetail_platform, COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND MONTH(tt.created_at) = MONTH(CURDATE())
                AND YEAR(tt.created_at) = YEAR(CURDATE())
                GROUP BY tt.transaksidetail_platform
            ");

            // LINE CHART (per bulan tahun ini)
            $line = DB::select("
                SELECT 
                    MONTH(tt.created_at) as Bulan,
                    tt.transaksidetail_platform,
                    COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND MONTH(tt.created_at) = MONTH(CURDATE())
                AND YEAR(tt.created_at) = YEAR(CURDATE())
                GROUP BY MONTH(tt.created_at), tt.transaksidetail_platform
                ORDER BY Bulan
            ");

        } else {

            // PIE CHART (tahun ini)
            $pie = DB::select("
                SELECT tt.transaksidetail_platform, COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                AND YEAR(tt.created_at) = YEAR(CURDATE())
                GROUP BY tt.transaksidetail_platform
            ");

            // LINE CHART (per tahun)
            $line = DB::select("
                SELECT 
                    YEAR(tt.created_at) as Tahun,
                    tt.transaksidetail_platform,
                    COUNT(*) as total
                FROM transaksidetail_t tt
                WHERE tt.deleted_at IS NULL
                AND tt.transaksidetail_platform IS NOT NULL
                GROUP BY YEAR(tt.created_at), tt.transaksidetail_platform
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

    public function barangChart(Request $request)
{
    $type = $request->type ?? 'month';

    $query = DB::table('transaksidetail_t as tt')
        ->join('barangentry_m as bm', 'tt.transaksidetail_barang_id', '=', 'bm.barangentry_id')
        ->whereNull('bm.deleted_at')
        ->whereNull('tt.deleted_at');

    // FILTER BY TIME
    if ($type == 'day') {
        $query->whereDate('tt.created_at', Carbon::today());
        $format = '%H:00'; // per jam
    }

    if ($type == 'month') {
        $query->whereMonth('tt.created_at', Carbon::now()->month)
              ->whereYear('tt.created_at', Carbon::now()->year);
        $format = '%Y-%m-%d'; // per hari
    }

    if ($type == 'year') {
        $query->whereYear('tt.created_at', Carbon::now()->year);
        $format = '%Y-%m'; // per bulan
    }

    // QUERY DATA
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
            [
                'name' => 'Harga Net',
                'data' => $data->pluck('total_harga_net')
            ],
            [
                'name' => 'Harga Jual',
                'data' => $data->pluck('total_harga_jual')
            ],
            [
                'name' => 'Harga Modal',
                'data' => $data->pluck('total_harga_modal')
            ]
        ]
    ]);
}

    public function jenisBarangEntryChart(Request $request)
    {
        $type = $request->type ?? 'month';
        
        if ($type == 'day') {

            $data = DB::select("
                SELECT 
                    DATE(cm.created_at) as Tanggal,
                    CASE 
                        WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                        ELSE jm.jenisbarang_kode
                    END AS kategori,
                    COUNT(*) as total
                FROM jenisbarang_m jm
                JOIN code_m cm 
                    ON cm.code_jenisbarang_id = jm.jenisbarang_id
                WHERE cm.deleted_at IS NULL
                AND jm.deleted_at IS NULL
                AND MONTH(cm.created_at) = MONTH(CURRENT_DATE())
                AND YEAR(cm.created_at) = YEAR(CURRENT_DATE())
                GROUP BY Tanggal, kategori
                ORDER BY Tanggal
            ");

        } elseif ($type == 'month') {

            $data = DB::select("
                SELECT 
                    MONTH(cm.created_at) as Bulan,
                    CASE 
                        WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                        ELSE jm.jenisbarang_kode
                    END AS kategori,
                    COUNT(*) as total
                FROM jenisbarang_m jm
                JOIN code_m cm 
                    ON cm.code_jenisbarang_id = jm.jenisbarang_id
                WHERE cm.deleted_at IS NULL
                AND jm.deleted_at IS NULL
                AND YEAR(cm.created_at) = YEAR(CURRENT_DATE())
                GROUP BY Bulan, kategori
                ORDER BY Bulan
            ");

        } else {

            $data = DB::select("
                SELECT 
                    YEAR(cm.created_at) as Tahun,
                    CASE 
                        WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                        ELSE jm.jenisbarang_kode
                    END AS kategori,
                    COUNT(*) as total
                FROM jenisbarang_m jm
                JOIN code_m cm 
                    ON cm.code_jenisbarang_id = jm.jenisbarang_id
                WHERE cm.deleted_at IS NULL
                AND jm.deleted_at IS NULL
                GROUP BY Tahun, kategori
                ORDER BY Tahun
            ");
        }

        return response()->json($data);
    }

    public function jenisBarangJualChart(Request $request)
    {
        $type = $request->type ?? 'month';

        if ($type == 'day') {

            $data = DB::select("
            SELECT 
                DATE(tt.created_at) as Tanggal,
                CASE 
                    WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                    ELSE jm.jenisbarang_kode 
                END AS kategori,
                COUNT(*) AS total
            FROM jenisbarang_m jm
            JOIN code_m cm 
                ON cm.code_jenisbarang_id = jm.jenisbarang_id
            JOIN barangentry_m bm 
                ON bm.barangentry_code_id = cm.code_id
            JOIN transaksidetail_t tt 
                ON tt.transaksidetail_barang_id = bm.barangentry_id
            WHERE cm.deleted_at IS NULL
            AND jm.deleted_at IS NULL
            AND tt.deleted_at IS NULL
            AND MONTH(tt.created_at) = MONTH(CURRENT_DATE())
            AND YEAR(tt.created_at) = YEAR(CURRENT_DATE())
            GROUP BY Tanggal, kategori
            ORDER BY Tanggal
            ");

        } elseif ($type == 'month') {

            $data = DB::select("
            SELECT 
                MONTH(tt.created_at) as Bulan,
                CASE 
                    WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                    ELSE jm.jenisbarang_kode 
                END AS kategori,
                COUNT(*) AS total
            FROM jenisbarang_m jm
            JOIN code_m cm 
                ON cm.code_jenisbarang_id = jm.jenisbarang_id
            JOIN barangentry_m bm 
                ON bm.barangentry_code_id = cm.code_id
            JOIN transaksidetail_t tt 
                ON tt.transaksidetail_barang_id = bm.barangentry_id
            WHERE cm.deleted_at IS NULL
            AND jm.deleted_at IS NULL
            AND tt.deleted_at IS NULL
            AND YEAR(tt.created_at) = YEAR(CURRENT_DATE())
            GROUP BY Bulan, kategori
            ORDER BY Bulan
            ");

        } else {

            $data = DB::select("
            SELECT 
                YEAR(tt.created_at) as Tahun,
                CASE 
                    WHEN jm.jenisbarang_kode LIKE 'PO%' THEN 'PO'
                    ELSE jm.jenisbarang_kode 
                END AS kategori,
                COUNT(*) AS total
            FROM jenisbarang_m jm
            JOIN code_m cm 
                ON cm.code_jenisbarang_id = jm.jenisbarang_id
            JOIN barangentry_m bm 
                ON bm.barangentry_code_id = cm.code_id
            JOIN transaksidetail_t tt 
                ON tt.transaksidetail_barang_id = bm.barangentry_id
            WHERE cm.deleted_at IS NULL
            AND jm.deleted_at IS NULL
            AND tt.deleted_at IS NULL
            GROUP BY Tahun, kategori
            ORDER BY Tahun
            ");
        }

        return response()->json($data);
    }

    public function jumlahCustomerChart(){
        $pie = DB::select("
            SELECT 
                SUM(
                    CASE 
                        WHEN YEAR(created_at) = YEAR(CURDATE()) 
                        AND MONTH(created_at) = MONTH(CURDATE()) 
                        THEN 1 ELSE 0
                    END
                ) AS customer_baru,

                SUM(
                    CASE 
                        WHEN created_at < DATE_FORMAT(CURDATE(),'%Y-%m-01')
                        THEN 1 ELSE 0
                    END
                ) AS customer_lama
            FROM customer_m
            WHERE deleted_at IS NULL
        ");

        $line = DB::select("
            SELECT 
                DATE_FORMAT(created_at,'%Y-%m') AS bulan,
                COUNT(*) AS total
            FROM customer_m
            WHERE deleted_at IS NULL
            AND YEAR(created_at) = YEAR(CURDATE())
            GROUP BY bulan
            ORDER BY bulan
        ");
        $customerBaru = (int) ($pie[0]->customer_baru ?? 0);
        $customerLama = (int) ($pie[0]->customer_lama ?? 0);
        $totalCustomer = $customerBaru + $customerLama;

        return response()->json([
            'pie_chart' => [
                'customer_baru' => $customerBaru,
                'customer_lama' => $customerLama,
                'customer_baru_percent' => $totalCustomer > 0 ? round(($customerBaru / $totalCustomer) * 100, 2) : 0,
                'customer_lama_percent' => $totalCustomer > 0 ? round(($customerLama / $totalCustomer) * 100, 2) : 0,
            ],
            'line_chart' => $line
        ]);
    }
}