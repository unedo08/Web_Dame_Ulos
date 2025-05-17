<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisBarangM extends Model
{
    protected $table = 'jenisbarang_m';
    protected $primaryKey = 'jenisbarang_id';

    protected $fillable = [
        'jenisbarang_nama',
        'jenisbarang_kode',
        'jenisbarang_tipe',
        'jenisbarang_jumlah',
    ];
}
