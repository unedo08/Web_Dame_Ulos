<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisPengirimanM extends Model
{
    use SoftDeletes;

    protected $table = 'jenispengiriman_m';

    protected $fillable = [
        'jenispengiriman_nama',
        'jenispengiriman_kode',
        'jenispengiriman_status',
        'create_id',
        'update_id',
        'delete_id',
    ];
}
