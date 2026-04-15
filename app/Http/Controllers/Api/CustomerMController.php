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

    private function checkAuth()
    {
        if (!Auth::check()) {
            return response()->json([
                'code'    => 401,
                'message' => 'Unauthorized. Please login.',
                'data'    => null
            ], 401);
        }
        return null;
    }

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

        $data = DB::table('transaksidetail_t as tt')
            ->join('transaksi_t as tt2', 'tt2.transaksi_id', '=', 'tt.transaksidetail_transaksi_id')
            ->join('customer_m as cm', 'cm.customer_nama', '=', 'tt2.transaksi_nama_customer')
            ->select(
                'cm.customer_id',
                'cm.customer_nama',
                'cm.customer_akun',
                'cm.customer_alamat',
                'cm.customer_notelepon',
                'tt2.transaksi_tipe',
                DB::raw('COUNT(DISTINCT tt2.transaksi_id) as jumlah_transaksi'),
                DB::raw('COUNT(tt.transaksidetail_barang_id) as jumlah_barang')
            )
            ->groupBy(
                'cm.customer_id',
                'cm.customer_nama',
                'cm.customer_akun',
                'cm.customer_alamat',
                'cm.customer_notelepon',
                'tt2.transaksi_tipe'
            )
            ->orderBy('cm.customer_nama', 'asc')
            ->orderBy('tt2.transaksi_tipe', 'asc')
            ->get();

        return response()->json([
            'code' => 200,
            'message' => 'Summary transaksi per customer ditemukan',
            'count' => $data->count(),
            'data' => $data
        ]);
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
