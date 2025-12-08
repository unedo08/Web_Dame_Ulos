<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisBarangM extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'jenisbarang_m';
    protected $primaryKey = 'jenisbarang_id';
    protected $fillable = ['jenisbarang_nama', 'jenisbarang_kode', 'jenisbarang_tipe', 'jenisbarang_jumlah', 'create_id',
        'update_id',
        'delete_id'];

    
}
