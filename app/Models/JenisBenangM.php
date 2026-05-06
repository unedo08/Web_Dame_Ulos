<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class JenisBenangM extends Model
{
    use SoftDeletes;

    protected $table = 'jenis_benang_m';

    protected $fillable = [
        'jenisbenang_nama',
        'jenisbenang_kode',
        'jenisbenang_status',
        'create_id',
        'update_id',
        'delete_id',
    ];
}
