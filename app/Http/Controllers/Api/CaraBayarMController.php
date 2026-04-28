<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\CaraBayarM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CaraBayarMController extends Controller
{
    public function index()
    {
        if ($resp = $this->checkAuth()) return $resp;

        return $this->ok(CaraBayarM::all());
    }

    public function store(Request $request)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $validated = $request->validate([
            'carabayar_nama' => 'required|string|max:100',
            'carabayar_kode' => 'nullable|string|max:50',
        ]);

        return $this->created(CaraBayarM::create($validated), 'Cara bayar berhasil ditambahkan');
    }

    public function show($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = CaraBayarM::find($id);
        if (!$data) {
            return $this->notFound('Data tidak ditemukan');
        }

        return $this->ok($data);
    }

    public function update(Request $request, $id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = CaraBayarM::find($id);
        if (!$data) {
            return $this->notFound('Data tidak ditemukan');
        }

        $validated = $request->validate([
            'carabayar_nama' => 'required|string|max:100',
            'carabayar_kode' => 'nullable|string|max:50',
        ]);

        $data->update($validated);

        return $this->ok($data, 'Data berhasil diupdate');
    }

    public function destroy($id)
    {
        if ($resp = $this->checkAuth()) return $resp;

        $data = CaraBayarM::find($id);
        if (!$data) {
            return $this->notFound('Data tidak ditemukan');
        }

        $data->delete();

        return $this->ok(null, 'Data berhasil dihapus');
    }
}
