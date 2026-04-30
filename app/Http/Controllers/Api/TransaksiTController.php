<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TransaksiT;
use App\Models\TransaksiDetailT;
use App\Models\CustomerM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TransaksiTController extends Controller
{
    public function index()
    {
        $transaksis = TransaksiT::with('details')->get();

        if ($transaksis->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data transaksi yang ditemukan.',
                'data' => []
            ], 404);
        }

        return response()->json([
            'message' => 'Data transaksi berhasil diambil.',
            'data' => $transaksis
        ], 200);
    }


    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaksi_id'             => 'nullable|string',
            'transaksi_nama_customer'  => 'required|string|max:255',
            'transaksi_nomor_telepon'  => 'nullable|string|max:20',
            'transaksi_jumlah_barang'  => 'required|integer|min:1',
            'transaksi_total_harga'    => 'required|numeric|min:0',
            'transaksi_cara_bayar'     => 'nullable|string',
            'transaksi_tipe'           => 'nullable|string',
            'transaksi_status'         => 'nullable|string',
            'transaksi_catatan'        => 'nullable|string',
            'transaksi_platform'       => 'nullable|string', // khusus customer
        ]);

        try {
            /** ---------------- CUSTOMER ---------------- */
            $customer = CustomerM::firstOrCreate(
                [
                    'customer_notelepon' => $validated['transaksi_nomor_telepon'],
                ],
                [
                    'customer_nama'     => $validated['transaksi_nama_customer'],
                    'customer_alamat'   => '-',
                    'customer_akun'     => null,
                    'customer_platform' => $validated['transaksi_platform'] ?? '-',
                ]
            );

            // Optional: update platform jika customer sudah ada
            if (!empty($validated['transaksi_platform'])) {
                $customer->update([
                    'customer_platform' => $validated['transaksi_platform']
                ]);
            }

            /** ---------------- TRANSAKSI ---------------- */
            $record = TransaksiT::updateOrCreate(
                ['transaksi_id' => $validated['transaksi_id'] ?? null],
                array_merge($validated, [
                    'transaksi_customer_id' => $customer->customer_id,
                    'transaksi_status'      => $validated['transaksi_status'] ?? 'pending',
                    'create_id'             => Auth::id(),
                ])
            );

            return response()->json([
                'code'     => 201,
                'status'   => true,
                'message'  => 'Transaksi dan customer berhasil disimpan.',
                'customer' => $customer,
                'data'     => $record,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'code'    => 500,
                'status'  => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }
    }



    public function show($id)
    {
        $transaksi = TransaksiT::with('details')->find($id);

        if (!$transaksi) {
            return response()->json([
                'message' => 'Data transaksi tidak ditemukan.',
                'data' => null
            ], 404);
        }

        return response()->json([
            'message' => 'Data transaksi berhasil diambil.',
            'data' => $transaksi
        ], 200);
    }

    public function updateStatus(Request $request, $id)
    {
        if (!Auth::check()) {
            return response()->json([
                'status'  => false,
                'message' => 'Akses ditolak, Anda belum login.'
            ], 401);
        }

        $request->validate([
            'status' => 'required|string',
        ]);

        $transaksi = TransaksiT::find($id);
        if (!$transaksi) {
            return response()->json([
                'status'  => false,
                'message' => 'Data transaksi tidak ditemukan.',
                'data'    => null
            ], 404);
        }

        $transaksi->transaksi_status = $request->status;
        $transaksi->update_id = Auth::id();
        $transaksi->save();

        return response()->json([
            'status'  => true,
            'message' => 'Status transaksi berhasil diperbarui.',
            'data'    => $transaksi
        ], 200);
    }



    public function destroy($id)
    {
        if (!Auth::check()) {
            return response()->json([
                'status' => false,
                'message' => 'Akses ditolak, Anda belum login.'
            ], 401);
        }

        $userId = Auth::id();

        $transaksi = TransaksiT::find($id);

        if (!$transaksi) {
            return response()->json([
                'status'  => false,
                'message' => 'Data transaksi tidak ditemukan.',
                'data'    => null
            ], 404);
        }


        $transaksi->delete_id = $userId;
        $transaksi->save();

        TransaksiDetailT::where('transaksidetail_transaksi_id', $id)
            ->update(['delete_id' => $userId]);

        $transaksi->delete();
        TransaksiDetailT::where('transaksidetail_transaksi_id', $id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Transaksi dan detail berhasil dihapus oleh user ID: ' . $userId
        ], 200);
    }

    public function getHoldTransactions()
    {
        $transactions = TransaksiT::where('transaksi_status', 'hold')->get();

        return response()->json([
            'message' => 'Transactions with status hold retrieved successfully.',
            'data' => $transactions
        ], 200);
    }

    public function getTransaksiGrouped()
    {
        $transaksi = TransaksiT::with([
            'customer',
            'caraBayar',
            'user',
            'details.barang',
            'details.pengiriman'
        ])
            ->get()
            ->groupBy(function ($item) {
                return $item->transaksi_id;
            });

        $result = [];


        foreach ($transaksi as $key => $group) {
            $first = $group->first();
            $result[] = [
                'transaksi_id' => $first->transaksi_id,
                'created_at' => $first->created_at,
                'customer_nama' => $first->customer->customer_nama ?? 'Unknown',
                'transaksi_tipe' => Str::ucfirst($first->transaksi_tipe),
                'cara_bayar' => $first->caraBayar?->carabayar_nama,
                'user' => $first->user?->name,
                'transaksi_platform' => $first->transaksi_platform ?? null,
                'items' => $group->flatMap(function ($trans) use ($first) {
                    return $trans->details->map(function ($d) use ($first) {
                        return [
                            'code_nama' => $d->barang->code->code_nama ?? null,
                            'barang_nama' => $d->barang->barangentry_nama ?? null,
                            'jumlah_barang' => $d->transaksidetail_jumlah_barang,
                            'harga_barang' => $d->transaksidetail_harga_barang,
                            'barang_modal' => $d->barang->barangentry_modal ?? null,
                            'barang_price_tag' => $d->barang->barangentry_price_tag ?? null,
                            'harga_kirim_barang' => $d->pengiriman->pengirimanBarang_harga_kirim_barang ?? 0,
                            'transaksi_catatan' => $d->transaksi_catatan ?? null,
                            'transaksi_status' => $d->transaksidetail_status_penjualan ?? null,
                            'transaksi_platform' => $d->transaksidetail_platform ?? null,
                        ];
                    });
                })
            ];
        }

        return response()->json([
            'code' => 200,
            'message' => 'Data berhasil diambil',
            'data' => $result
        ]);
    }

    public function getexportDataTransaksi(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;
        // ✅ validasi request date
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
        ]);

        $data = DB::table('transaksidetail_t as tdt')
            ->join('transaksi_t as tt', 'tt.transaksi_id', '=', 'tdt.transaksidetail_transaksi_id')
            ->join('customer_m as cm2', 'cm2.customer_id', '=', 'tt.transaksi_customer_id')
            ->join('barangentry_m as bm', 'bm.barangentry_id', '=', 'tdt.transaksidetail_barang_id')
            ->join('code_m as cm', 'bm.barangentry_code_id', '=', 'cm.code_id')
            ->join('carabayar_m as cm3', 'cm3.carabayar_kode', '=', 'tt.transaksi_cara_bayar')
            ->select([
                'cm.code_nama',
                'bm.barangentry_nama',
                'bm.barangentry_warna',
                'bm.barangentry_nama_penenun',
                'bm.barangentry_nama_panirat',
                'bm.barangentry_dryer',
                'bm.barangentry_modal',
                'bm.barangentry_price_tag',
                'bm.barangentry_harga_net',
                'bm.barangentry_ukuran_mandar',
                'bm.barangentry_ukuran_ulos',
                'tt.created_at',
                'cm2.customer_nama',
                'tt.transaksi_tipe',
                'tdt.transaksidetail_platform as transaksi_platform',
                'bm.barangentry_acara_id',
                'tdt.transaksidetail_jumlah_barang',
                'tdt.transaksidetail_harga_barang',
                DB::raw('tdt.transaksidetail_jumlah_barang * tdt.transaksidetail_harga_barang AS subtotal'),
                'tdt.transaksidetail_status_penjualan',
                'cm3.carabayar_nama',
                'tt.transaksi_catatan'
            ])

            ->whereNull('tdt.deleted_at')
            // ✅ filter tanggal transaksi
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('tt.created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('tt.created_at', '<=', $request->end_date);
            })

            ->orderBy('tt.created_at', 'desc')
            ->get();

        return response()->json([
            'code'    => 200,
            'filters' => [
                'start_date' => $request->start_date,
                'end_date'   => $request->end_date,
            ],
            'data' => $data
        ]);
    }

    public function transaksiSummary(Request $request)
    {
        $type = $request->get('type');

        if (!$type) {
            return response()->json([
                'status' => false,
                'message' => 'Type is required (day, month, year)'
            ], 400);
        }

        // 🔥 Tentukan Range Tanggal
        switch ($type) {

            case 'day':
                if (!$request->date) {
                    return response()->json(['status' => false, 'message' => 'date required'], 400);
                }
                $start = Carbon::parse($request->date)->startOfDay();
                $end   = Carbon::parse($request->date)->endOfDay();
                break;

            case 'month':
                if (!$request->month || !$request->year) {
                    return response()->json(['status' => false, 'message' => 'month & year required'], 400);
                }
                $start = Carbon::create($request->year, $request->month, 1)->startOfMonth();
                $end   = Carbon::create($request->year, $request->month, 1)->endOfMonth();
                break;

            case 'year':
                if (!$request->year) {
                    return response()->json(['status' => false, 'message' => 'year required'], 400);
                }
                $start = Carbon::create($request->year, 1, 1)->startOfYear();
                $end   = Carbon::create($request->year, 1, 1)->endOfYear();
                break;

            default:
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid type (day, month, year)'
                ], 400);
        }

        // =============================
        // 1️⃣ SUMMARY PER TRANSAKSI TIPE
        // =============================
        $transaksiSummary = DB::table('transaksi_t as tt')
            ->whereNull('tt.deleted_at')
            ->whereBetween('tt.created_at', [$start, $end])
            ->selectRaw("
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM pengirimanBarang_t pbt
                        JOIN live_order_t lot
                        ON lot.live_order_nama_akun = pbt.pengirimanBarang_akun_penerima
                        WHERE pbt.pengirimanBarang_transaksi_id = tt.transaksi_id
                    )
                    THEN 'Live'
                    ELSE tt.transaksi_tipe
                END AS tipe,
                COUNT(*) as total,
                SUM(tt.transaksi_total_harga) as total_harga
            ")
            ->groupBy('tipe')
            ->get();

        // =============================
        // 2️⃣ SUMMARY PER LIVE PLATFORM
        // =============================
        $platformSummary = DB::table('live_order_t as lot')
            ->where('lot.is_check', 1)
            ->whereNull('lot.deleted_at')
            ->whereBetween('lot.created_at', [$start, $end])
            ->select('lot.live_order_platform as tipe', DB::raw('COUNT(*) as total'))
            ->groupBy('lot.live_order_platform')
            ->get();

        return response()->json([
            'status' => true,
            'type' => $type,
            'start_date' => $start,
            'end_date' => $end,
            'data' => [
                'per_transaksi' => $transaksiSummary,
                'per_platform'  => $platformSummary
            ]
        ]);
    }
}
