<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\CustomerM;

class PengirimanBarangT extends Model
{
    protected $table = 'pengirimanBarang_t';
    protected $primaryKey = 'pengirimanBarang_id';
    protected $fillable = [
        'pengirimanBarang_transaksi_id',
        'pengirimanBarang_nama_penerima',
        'pengirimanBarang_akun_penerima',
        'pengirimanBarang_no_telepon',
        'pengirimanBarang_harga_kirim_barang',
        'pengirimanBarang_jenis_pengiriman_barang',
        'pengirimanBarang_alamat_pengiriman_barang',
        'pengirimanBarang_catatan',
        'pengirimanBarang_status',
        'pengirimanBarang_customer_id',
    ];

    public function customer()
    {
        return $this->belongsTo(CustomerM::class, 'pengirimanBarang_customer_id', 'customer_id');
    }
}
