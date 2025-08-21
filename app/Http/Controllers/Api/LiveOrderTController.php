<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LiveOrderT;
use App\Models\BarangEntryM;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\DB;


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

    // GET by ID
    public function show($id)
    {
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
        $validated = $request->validate([
            'live_order_barang_id'     => 'required|integer',
            'live_order_nama_akun'     => 'required|string|max:255',
            'live_order_platform'      => 'required|string|max:100',
            'live_order_harga_terjual' => 'required|numeric|min:0'
        ]);
        $validated['live_order_jumlah_barang'] = 1;

        $barang_entry = BarangEntryM::find($validated['live_order_barang_id']);
        if(!$barang_entry){
            return response()->json([
                'code'    => 404,
                'message' => 'Data Barang is not Found!',
            ], 404);
        };

        if($barang_entry->barangentry_jumlah_barang <= 0){
            return response()->json([
                'code'    => 422,
                'message' => 'Stock is empty, cannot create order!',
            ], 422);
        }

        $barang_entry->barangentry_jumlah_barang -= $validated['live_order_jumlah_barang'];
        $barang_entry->update();
        
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
        $order = LiveOrderT::find($id);
        if (!$order) {
            return response()->json([
                'code'    => 404,
                'message' => 'Live order not found',
                'data'    => null
            ], 404);
        }

        $barang_entry = BarangEntryM::find($order->live_order_barang_id);
        if(!$barang_entry){
            return response()->json([
                'code'    => 404,
                'message' => 'Data Barang is not Found!',
            ], 404);
        };
        
        $barang_entry->barangentry_jumlah_barang += $order->live_order_jumlah_barang;
        $barang_entry->update();

        $order->delete();

        return response()->json([
            'code'    => 200,
            'message' => 'Live order deleted successfully',
            'data'    => null
        ], 200);
    }

    public function countByNamaAkun()
    {
        $results = LiveOrderT::select(
        'live_order_t.live_order_nama_akun'
            )
            ->selectRaw('COUNT(*) as jumlah')
            ->groupBy('live_order_t.live_order_nama_akun')
            ->get();
        
        return response()->json([
            'code'    => 200,
            'message' => 'Live order counts retrieved successfully by nama akun',
            'data'    => $results
        ], 200);
    }

    public function getLiveOrderByNamaAkun($namaAkun){
        // $data = LiveOrderT::select(
        //         'live_order_t.live_order_barang_id',
        //         'barangentry_m.barangentry_nama',
        //         'code_m.code_nama',
        //         DB::raw('COUNT(*) as total_order'),
        //         DB::raw('SUM(live_order_t.live_order_jumlah_barang) as total_qty'),
        //         DB::raw('SUM(live_order_harga_terjual) as total_harga')
        //     )
        //     ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
        //     ->join('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
        //     ->where('live_order_t.live_order_nama_akun', $namaAkun)
        //     ->groupBy('live_order_t.live_order_barang_id', 'barangentry_m.barangentry_nama', 'code_m.code_nama')
        //     ->get();

        $data = LiveOrderT::select(
            'live_order_t.*',
            'barangentry_m.barangentry_nama',
            'code_m.code_nama'
            )
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->join('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
            ->where('live_order_t.live_order_nama_akun', $namaAkun)
            ->get();

        if($data->isEmpty()){
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

    public function getDataTabelLiveOrder(){

        $data = LiveOrderT::select(
            'live_order_t.*',
            'barangentry_m.barangentry_nama',
            'code_m.code_nama'
            )
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->join('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
            ->get();

        // return $data;
        if($data->isEmpty()){
            return response()->json([
                'code'    => 401,
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

    public function getLiveOrderByLiveId($id){
        $data = LiveOrderT::select(
            'live_order_t.*',
            'barangentry_m.barangentry_nama',
            'code_m.code_nama'
            )
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->join('code_m', 'code_m.code_id', '=', 'barangentry_m.barangentry_code_id')
            ->where('live_order_t.live_order_id', $id)
            ->get();

        if($data->isEmpty()){
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
}

