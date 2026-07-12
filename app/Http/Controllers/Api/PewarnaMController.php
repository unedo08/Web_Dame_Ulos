<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PewarnaM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PewarnaMController extends Controller
{
    public function getActive()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PewarnaM::where('pewarna_status', 1)
            ->orderBy('pewarna_nama')
            ->get();

        return $this->ok($data, 'Berhasil mendapatkan data pewarna aktif');
    }

    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PewarnaM::orderBy('created_at', 'desc')->get();

        return $this->ok($data, 'Berhasil mendapatkan data pewarna');
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PewarnaM::find($id);
        if (!$data) return $this->notFound('Data tidak ditemukan');

        return $this->ok($data);
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'pewarna_nama'   => 'required|string|max:100',
            'pewarna_status' => 'required|in:0,1',
        ]);

        $validated['pewarna_kode'] = strtoupper(str_replace(' ', '_', $request->pewarna_nama));
        $validated['create_id']    = Auth::id();

        $data = PewarnaM::create($validated);

        return $this->created($data, 'Pewarna berhasil ditambahkan');
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PewarnaM::find($id);
        if (!$data) return $this->notFound('Data tidak ditemukan');

        $validated = $request->validate([
            'pewarna_nama'   => 'required|string|max:100',
            'pewarna_status' => 'required|in:0,1',
        ]);

        $validated['pewarna_kode'] = strtoupper(str_replace(' ', '_', $request->pewarna_nama));
        $validated['update_id']    = Auth::id();

        $data->update($validated);

        return $this->ok($data, 'Pewarna berhasil diupdate');
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = PewarnaM::find($id);
        if (!$data) return $this->notFound('Data tidak ditemukan');

        $data->delete_id = Auth::id();
        $data->save();
        $data->delete();

        return $this->ok(null, 'Pewarna berhasil dihapus');
    }
}
