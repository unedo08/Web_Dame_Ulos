<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarangEntryTempM extends Model
{
    use HasFactory;

    protected $table = 'barangentry_temp_m';
    protected $primaryKey = 'barangentry_temp_id';

    protected $fillable = [
        'barangentry_temp_code_id',
        'barangentry_temp_nama',
        'barangentry_temp_warna',
        'barangentry_temp_nama_penenun',
        'barangentry_temp_nama_panirat',
        'barangentry_temp_dryer',
        'barangentry_temp_modal',
        'barangentry_temp_price_tag',
        'barangentry_temp_harga_net',
        'barangentry_temp_acara_id',
        'barangentry_temp_ukuran_mandar',
        'barangentry_temp_ukuran_ulos',
        'barangentry_temp_jumlah_barang',
        'barangentry_temp_status'
    ];

}
