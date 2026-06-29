<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BarangKeluarT extends Model
{
    use SoftDeletes;

    protected $table = 'barang_keluar_t';
    protected $primaryKey = 'barang_keluar_id';

    protected $fillable = [
        'barang_keluar_nama_outsource',
        'barang_keluar_status',
        'barang_keluar_tanggal_masuk',
        'barang_keluar_tanggal_keluar',
        'barang_keluar_complete_id',
        'create_id',
        'update_id',
        'delete_id',
    ];

    public function details()
    {
        return $this->hasMany(BarangKeluarDetailT::class, 'barang_keluar_detail_barang_keluar_id', 'barang_keluar_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_id', 'id');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'barang_keluar_complete_id', 'id');
    }
}
