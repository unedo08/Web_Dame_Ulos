<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangKeluarDetailT extends Model
{
    use SoftDeletes;

    protected $table = 'barang_keluar_detail_t';
    protected $primaryKey = 'barang_keluar_detail_id';

    protected $fillable = [
        'barang_keluar_detail_barang_keluar_id',
        'barang_keluar_detail_code_id',
        'barang_keluar_detail_kode_barang',
        'barang_keluar_detail_nama_ulos',
        'barang_keluar_detail_jumlah',
        'create_id',
        'update_id',
        'delete_id',
    ];

    public function barangKeluar()
    {
        return $this->belongsTo(BarangKeluarT::class, 'barang_keluar_detail_barang_keluar_id', 'barang_keluar_id');
    }
}
