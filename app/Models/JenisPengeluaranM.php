<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisPengeluaranM extends Model
{
    use SoftDeletes;

    protected $table = 'jenis_pengeluaran_m';
    protected $primaryKey = 'jenis_pengeluaran_id';

    protected $fillable = [
        'jenis_pengeluaran_nama',
        'jenis_pengeluaran_kode',
        'jenis_pengeluaran_status',
        'create_id',
        'update_id',
        'delete_id',
    ];
}
