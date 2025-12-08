<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransaksiT;
use App\Models\BarangEntryM;
use App\Models\PengirimanBarangT;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiDetailT extends Model
{
    use SoftDeletes;
    protected $table = 'transaksidetail_t';
    protected $primaryKey = 'transaksidetail_id';
    protected $fillable = [
        'transaksidetail_transaksi_id',
        'transaksidetail_barang_id',
        'transaksidetail_jumlah_barang',
        'transaksidetail_harga_barang',
        'transaksidetail_status_penjualan',
        'create_id',
        'update_id',
        'delete_id'
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiT::class, 'transaksidetail_transaksi_id', 'transaksi_id');
    }

    public function pengirimanBarang()
    {
        return $this->hasOne(PengirimanBarangT::class, 'pengirimanBarang_transaksi_id', 'transaksidetail_transaksi_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangEntryM::class, 'transaksidetail_barang_id', 'barangentry_id');
    }

    public function pengiriman()
    {
        return $this->hasOne(PengirimanBarangT::class, 'pengirimanBarang_transaksi_id', 'transaksidetail_transaksi_id');
    }

    

}
