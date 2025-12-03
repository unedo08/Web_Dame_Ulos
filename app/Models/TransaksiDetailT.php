<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransaksiT;

class TransaksiDetailT extends Model
{
    protected $table = 'transaksidetail_t';
    protected $primaryKey = 'transaksidetail_id';
    protected $fillable = [
        'transaksidetail_transaksi_id',
        'transaksidetail_barang_id',
        'transaksidetail_jumlah_barang',
        'transaksidetail_harga_barang',
        'transaksidetail_status_penjualan'
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiT::class, 'transaksidetail_transaksi_id', 'transaksi_id');
    }

    public function pengirimanBarang()
    {
        return $this->hasOne(PengirimanBarangT::class, 'pengirimanBarang_transaksi_id', 'transaksidetail_transaksi_id');
    }

}
