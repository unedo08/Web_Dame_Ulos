<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerM;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CustomerMController extends Controller
{
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $customers = CustomerM::orderBy('customer_nama')->get();

        return response()->json([
            'code' => 200,
            'message' => 'Customers retrieved',
            'data' => $customers
        ], 200);
    }

    public function getSummaryByCustomer()
    {
        if ($resp = $this->checkAuth()) return $resp;

        // Per phone group: pick latest record (MAX id) + earliest registration date (MIN created_at)
        $phoneGroups = DB::table('customer_m')
            ->select(
                'customer_notelepon',
                DB::raw('MAX(customer_id) as max_id'),
                DB::raw('MIN(created_at) as tanggal_daftar')
            )
            ->whereNull('deleted_at')
            ->groupBy('customer_notelepon');

        // Total purchase amount per phone group (across all duplicate customer IDs)
        $transaksiCounts = DB::table('transaksi_t as tt')
            ->join('customer_m as c2', 'c2.customer_id', '=', 'tt.transaksi_customer_id')
            ->select(
                'c2.customer_notelepon',
                DB::raw('COALESCE(SUM(tt.transaksi_total_harga), 0) as total_transaksi')
            )
            ->whereNull('tt.deleted_at')
            ->whereNull('c2.deleted_at')
            ->groupBy('c2.customer_notelepon');

        $data = DB::table('customer_m as cm')
            ->joinSub($phoneGroups, 'pg', 'cm.customer_id', '=', 'pg.max_id')
            ->leftJoinSub($transaksiCounts, 'tc', 'tc.customer_notelepon', '=', 'cm.customer_notelepon')
            ->whereNull('cm.deleted_at')
            ->select(
                'cm.customer_id',
                'cm.customer_nama',
                'cm.customer_akun',
                'cm.customer_alamat',
                'cm.customer_notelepon',
                'cm.customer_platform',
                'pg.tanggal_daftar',
                DB::raw('COALESCE(tc.total_transaksi, 0) as total_transaksi')
            )
            ->orderBy('cm.customer_nama')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => 'Customers retrieved',
            'count' => $data->count(),
            'data' => $data
        ]);
    }


    public function getCustomerDetail($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $customer = CustomerM::find($id);

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        // All customer IDs sharing the same phone number (handle duplicates)
        $customerIds = CustomerM::where('customer_notelepon', $customer->customer_notelepon)
            ->pluck('customer_id');

        // Use the latest record's name/phone/address for display
        $latestCustomer = CustomerM::whereIn('customer_id', $customerIds)
            ->orderBy('created_at', 'desc')
            ->first();

        $transactions = DB::table('transaksi_t as tt')
            ->join('transaksidetail_t as tdt', 'tdt.transaksidetail_transaksi_id', '=', 'tt.transaksi_id')
            ->join('barangentry_m as bm', 'bm.barangentry_id', '=', 'tdt.transaksidetail_barang_id')
            ->join('code_m as cm', 'cm.code_id', '=', 'bm.barangentry_code_id')
            ->whereIn('tt.transaksi_customer_id', $customerIds)
            ->whereNull('tt.deleted_at')
            ->whereNull('tdt.deleted_at')
            ->select(
                'tt.transaksi_id',
                'tt.created_at',
                DB::raw("CONCAT(UPPER(SUBSTR(tt.transaksi_tipe,1,1)), LOWER(SUBSTR(tt.transaksi_tipe,2))) as transaksi_tipe"),
                'cm.code_nama',
                'bm.barangentry_nama',
                'tdt.transaksidetail_harga_barang',
                'tdt.transaksidetail_status_penjualan',
                DB::raw("COALESCE(NULLIF(tdt.transaksidetail_platform, ''), '-') as transaksi_platform")
            )
            ->orderBy('tt.created_at', 'desc')
            ->get();

        $totalPembelian = $transactions->sum('transaksidetail_harga_barang');
        $totalTransaksi = $transactions->count();

        return response()->json([
            'code' => 200,
            'message' => 'Customer detail retrieved',
            'data' => [
                'customer' => [
                    'customer_id'       => $customer->customer_id,
                    'customer_nama'     => $latestCustomer->customer_nama,
                    'customer_notelepon' => $latestCustomer->customer_notelepon,
                    'customer_alamat'   => $latestCustomer->customer_alamat,
                    'created_at'        => $customer->created_at,
                ],
                'total_pembelian' => $totalPembelian,
                'total_transaksi' => $totalTransaksi,
                'transactions'    => $transactions
            ]
        ], 200);
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $customer = CustomerM::find($id);

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'code' => 200,
            'message' => 'Customer retrieved',
            'data' => $customer
        ], 200);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'customer_nama' => 'required|string|max:255',
            'customer_akun' => 'nullable|string|max:255',
            'customer_alamat' => 'nullable|string',
            'customer_notelpon' => 'required|string|max:50|unique:customer_m,customer_notelpon',
            'customer_platform' => 'nullable|string|max:255',
        ]);

        // Tambah create_id
        $validated['create_id'] = Auth::id();

        $customer = CustomerM::create($validated);

        return response()->json([
            'code' => 201,
            'message' => 'Customer created',
            'data' => $customer
        ], 201);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $customer = CustomerM::find($id);

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        $validated = $request->validate([
            'customer_nama' => 'sometimes|required|string|max:255',
            'customer_akun' => 'nullable|string|max:255',
            'customer_alamat' => 'nullable|string',
            'customer_notelpon' => [
                'sometimes',
                'required',
                'string',
                'max:50',
                Rule::unique('customer_m', 'customer_notelpon')->ignore($customer->customer_id, 'customer_id'),
            ],
            'customer_platform' => 'nullable|string|max:255',
        ]);

        // Tambah update_id
        $validated['update_id'] = Auth::id();

        $customer->update($validated);

        return response()->json([
            'code' => 200,
            'message' => 'Customer updated',
            'data' => $customer
        ], 200);
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $customer = CustomerM::find($id);

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        // Tambah delete_id
        $customer->delete_id = Auth::id();
        $customer->save();

        $customer->delete(); // soft delete

        return response()->json([
            'code' => 200,
            'message' => 'Customer deleted',
            'data' => null
        ], 200);
    }
}
