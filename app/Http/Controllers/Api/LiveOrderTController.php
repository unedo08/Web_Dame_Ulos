<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\LiveOrderT;
use Illuminate\Http\Request;

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
                'live_order_t.live_order_nama_akun',
                'barangentry_m.barangentry_nama'
            )
            ->selectRaw('COUNT(*) as jumlah')
            ->join('barangentry_m', 'barangentry_m.barangentry_id', '=', 'live_order_t.live_order_barang_id')
            ->groupBy('live_order_t.live_order_nama_akun', 'live_order_t.live_order_barang_id', 'barangentry_m.barangentry_nama')
            ->get();

        return response()->json([
            'code'    => 200,
            'message' => 'Live order counts retrieved successfully',
            'data'    => $results
        ], 200);
    }
}

