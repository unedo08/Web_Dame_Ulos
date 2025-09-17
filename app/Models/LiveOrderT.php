<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LiveOrderT extends Model
{
    use HasFactory;

    protected $table = 'live_order_t';
    protected $primaryKey = 'live_order_id';

    protected $fillable = [
        'live_order_barang_id',
        'live_order_nama_akun',
        'live_order_platform',
        'live_order_harga_terjual',
        'live_order_jumlah_barang',
        'is_check'
    ];
}