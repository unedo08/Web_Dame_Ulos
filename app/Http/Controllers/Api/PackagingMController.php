<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PackagingM;
use App\Models\TransaksiDetailT;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class PackagingMController extends Controller
{
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        return $this->ok(PackagingM::all(), 'List of all packaging');
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'packaging_transactiondetail_id' => 'nullable|integer',
            'packaging_nama_akun' => 'required|string|max:255',
            'packaging_alamat' => 'nullable|string',
        ]);

        $validated['create_id'] = Auth::id();
        $validated['update_id'] = Auth::id();

        $packaging = PackagingM::create($validated);

        if (!empty($validated['packaging_transactiondetail_id'])) {
            TransaksiDetailT::where('transaksidetail_id', $validated['packaging_transactiondetail_id'])
                ->update(['transaksidetail_status_penjualan' => 1]);
        }

        return $this->created($packaging, 'Packaging created successfully');
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $packaging = PackagingM::find($id);
        if (!$packaging) {
            return $this->notFound('Packaging not found');
        }

        return $this->ok($packaging, 'Packaging detail');
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $packaging = PackagingM::find($id);
        if (!$packaging) {
            return $this->notFound('Packaging not found');
        }

        $validated = $request->validate([
            'packaging_transactiondetail_id' => 'nullable|integer',
            'packaging_nama_akun' => 'required|string|max:255',
            'packaging_alamat' => 'nullable|string',
        ]);

        $validated['update_id'] = Auth::id();
        $packaging->update($validated);

        return $this->ok($packaging, 'Packaging updated successfully');
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $packaging = PackagingM::find($id);
        if (!$packaging) {
            return $this->notFound('Packaging not found');
        }

        $packaging->delete_id = Auth::id();
        $packaging->save();
        $packaging->delete();

        return $this->ok(null, 'Packaging deleted successfully');
    }

    public function updateStatus(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $packaging = PackagingM::find($id);
        if (!$packaging) {
            return $this->notFound('Packaging not found');
        }

        $validated = $request->validate([
            'packaging_status' => 'required|string',
        ]);

        $packaging->update_id = Auth::id();
        $packaging->update($validated);

        return $this->ok($packaging, 'Packaging status updated successfully');
    }
}
