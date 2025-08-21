<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangEntryM extends Model
{
    use HasFactory;
    protected $table = 'barangentry_m';
    protected $primaryKey = 'barangentry_id';
    protected $fillable = [
        'barangentry_code_id',
        'barangentry_nama',
        'barangentry_warna',
        'barangentry_nama_penenun',
        'barangentry_nama_panirat',
        'barangentry_dryer',
        'barangentry_modal',
        'barangentry_price_tag',
        'barangentry_harga_net',
        'barangentry_acara_id',
        'barangentry_ukuran_mandar',
        'barangentry_ukuran_ulos',
        'barangentry_jumlah_barang',
        'barangentry_status'
    ];

}
