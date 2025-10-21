<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CustomerM;
use Illuminate\Validation\Rule;

class CustomerMController extends Controller
{
    public function index()
    {
        $customers = CustomerM::orderBy('customer_nama')->get();

        return response()->json([
            'code' => 200,
            'message' => 'Customers retrieved',
            'data' => $customers
        ], 200);
    }

    public function show($id)
    {
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
        $validated = $request->validate([
            'customer_nama' => 'required|string|max:255',
            'customer_akun' => 'nullable|string|max:255',
            'customer_alamat' => 'nullable|string',
            'customer_notelpon' => 'required|string|max:50|unique:customer_m,customer_notelpon',
            'customer_platform' => 'nullable|string|max:255',
        ]);

        $customer = CustomerM::create($validated);

        return response()->json([
            'code' => 201,
            'message' => 'Customer created',
            'data' => $customer
        ], 201);
    }

    public function update(Request $request, $id)
    {
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

        $customer->update($validated);

        return response()->json([
            'code' => 200,
            'message' => 'Customer updated',
            'data' => $customer
        ], 200);
    }

    public function destroy($id)
    {
        $customer = CustomerM::find($id);

        if (!$customer) {
            return response()->json([
                'code' => 404,
                'message' => 'Customer not found',
                'data' => null
            ], 404);
        }

        $customer->delete();

        return response()->json([
            'code' => 200,
            'message' => 'Customer deleted',
            'data' => null
        ], 200);
    }
}
