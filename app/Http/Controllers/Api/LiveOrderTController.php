<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LiveOrderT;
use App\Models\BarangEntryM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class LiveOrderTController extends Controller
{
    // GET all
    public function index()
    {
        $orders = LiveOrderT::all();
        return response()->json([
            'code'    => 200,
            'message' => 'Live orders retrieved successfully',
            'data'    => $orders
        ], 200);
    }

    public function getDataLiveOrderWithBarangId(){

        if ($resp = $this->checkAuth()) return $resp;

        $data = LiveOrderT::select(
            'live_order_t.*'
            )
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->where('live_order_t.is_check', '=', '0')
            ->get();
        
        return response()->json([
            'code'    => 200,
            'message' => 'Live order counts retrieved successfully',
            'data'    => $data
        ], 200);
        
    }

    // GET by ID
    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $order = LiveOrderT::find($id);
        if (!$order) {
            return response()->json([
                'code'    => 404,
                'message' => 'Live order not found',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Live order retrieved successfully',
            'data'    => $order
        ], 200);
    }

    // POST create
    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'live_order_barang_id'     => 'required|integer',
            'live_order_nama_akun'     => 'required|string|max:255',
            'live_order_platform'      => 'required|string|max:100',
            'live_order_harga_terjual' => 'required|numeric|min:0'
        ]);
        $validated['live_order_jumlah_barang'] = 1;

        $barang_entry = BarangEntryM::find($validated['live_order_barang_id']);

        if (!$barang_entry) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data Barang is not Found!',
            ], 404);
        }

        if ($barang_entry->barangentry_jumlah_barang <= 0) {
            return response()->json([
                'code'    => 422,
                'message' => 'Stock is empty, cannot create order!',
            ], 422);
        }

        // kurangi stok
        $barang_entry->barangentry_jumlah_barang -= 1;
        $barang_entry->save();

        // Inject create & update id
        $validated['create_id'] = Auth::id();
        $validated['update_id'] = Auth::id();

        // simpan order
        $order = LiveOrderT::create($validated);

        return response()->json([
            'code'    => 201,
            'message' => 'Live order created successfully',
            'data'    => $order
        ], 201);
    }

    // PUT update
    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $order = LiveOrderT::find($id);
        if (!$order) {
            return response()->json([
                'code'    => 404,
                'message' => 'Live order not found',
                'data'    => null
            ], 404);
        }

        $validated = $request->validate([
            'live_order_barang_id'     => 'integer',
            'live_order_nama_akun'     => 'string|max:255',
            'live_order_platform'      => 'string|max:100',
            'live_order_harga_terjual' => 'numeric|min:0'
        ]);

        // update id
        $validated['update_id'] = Auth::id();

        $order->update($validated);

        return response()->json([
            'code'    => 200,
            'message' => 'Live order updated successfully',
            'data'    => $order
        ], 200);
    }

    // DELETE
    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $order = LiveOrderT::find($id);

        if (!$order) {
            return response()->json([
                'code'    => 404,
                'message' => 'Live order not found',
                'data'    => null
            ], 404);
        }

        // kembalikan stok barang
        $barang_entry = BarangEntryM::find($order->live_order_barang_id);

        if (!$barang_entry) {
            return response()->json([
                'code'    => 404,
                'message' => 'Data Barang is not Found!',
            ], 404);
        }

        $barang_entry->barangentry_jumlah_barang += $order->live_order_jumlah_barang;
        $barang_entry->save();

        // simpan delete_id
        $order->delete_id = Auth::id();
        $order->save();

        // soft delete
        $order->delete();

        return response()->json([
            'code'    => 200,
            'message' => 'Live order deleted successfully',
            'data'    => null
        ], 200);
    }

    public function countByNamaAkun()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $results = LiveOrderT::select('live_order_t.live_order_nama_akun')
            ->selectRaw('COUNT(*) as jumlah')
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->where('live_order_t.is_check', "0")
            ->groupBy('live_order_t.live_order_nama_akun')
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Live order counts retrieved successfully by nama akun',
            'data'    => $results
        ], 200);
    }

    public function getLiveOrderByNamaAkun($namaAkun)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = LiveOrderT::select(
            'live_order_t.*',
            'barangentry_m.barangentry_nama',
            'code_m.code_nama'
        )
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->join('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
            ->where('live_order_t.live_order_nama_akun', $namaAkun)
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'code'    => 404,
                'message' => 'Live order not found',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Data Live By Nama Akun',
            'data'    => $data
        ], 200);
    }

    public function getDataTabelLiveOrder()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = LiveOrderT::select(
            'live_order_t.*',
            'barangentry_m.barangentry_nama',
            'code_m.code_nama'
        )
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->join('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'code'    => 404,
                'message' => 'Live order not found',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Success Retrieve Data',
            'data'    => $data
        ], 200);
    }

    public function getLiveOrderByLiveId($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = LiveOrderT::select(
            'live_order_t.*',
            'barangentry_m.barangentry_nama',
            'code_m.code_nama'
        )
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->join('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
            ->where('live_order_t.live_order_id', $id)
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'code'    => 404,
                'message' => 'Live order not found',
                'data'    => null
            ], 404);
        }

        return response()->json([
            'code'    => 200,
            'message' => 'Data Live By Nama Akun',
            'data'    => $data
        ], 200);
    }

    public function updateStatusCheck(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;
        
        $liveorder = LiveOrderT::find($id);

        if (!$liveorder) {
            return response()->json([
                'code' => 404,
                'message' => 'Data Live Order tidak ditemukan.',
                'data' => null
            ], 404);
        }

        $liveorder->is_check = true;
        $liveorder->update_id = Auth::id();
        $liveorder->save();

        return response()->json([
            'code' => 200,
            'message' => 'Status updated successfully.',
            'data' => $liveorder
        ], 200);
    }

    public function getGroupedByNamaAkun()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = LiveOrderT::select(
            'live_order_t.*',
            'barangentry_m.barangentry_nama',
            'code_m.code_nama'
        )
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->join('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
            ->get();

        if ($data->isEmpty()) {
            return response()->json([
                'code'    => 404,
                'message' => 'Live order not found',
                'data'    => null
            ], 404);
        }

        $groupedData = $data->groupBy('live_order_nama_akun');

        return response()->json([
            'code'    => 200,
            'message' => 'Data Live Grouped By Nama Akun',
            'data'    => $groupedData
        ], 200);
    }
}
