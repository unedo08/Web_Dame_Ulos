<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransaksiDetailT;

class TransaksiT extends Model
{
    protected $table = 'transaksi_t';
    protected $primaryKey = 'transaksi_id';
    protected $fillable = [
        'transaksi_nama_customer',
        'transaksi_nomor_telepon',
        'transaksi_jumlah_barang',
        'transaksi_total_harga',
        'transaksi_cara_bayar',
        'transaksi_tipe',
        'transaksi_status',
        'transaksi_catatan'
    ];

    public function details()
    {
        return $this->hasMany(TransaksiDetailT::class, 'transaksidetail_transaksi_id', 'transaksi_id');
    }
}
