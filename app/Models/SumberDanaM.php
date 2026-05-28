<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SumberDanaM extends Model
{
    use SoftDeletes;

    protected $table = 'sumber_dana_m';
    protected $primaryKey = 'sumber_dana_id';

    protected $fillable = [
        'sumber_dana_nama',
        'sumber_dana_kode',
        'sumber_dana_status',
        'create_id',
        'update_id',
        'delete_id',
    ];
}
