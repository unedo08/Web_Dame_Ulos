<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CaraBayarM extends Model
{
    use SoftDeletes;
    protected $table = 'carabayar_m';

    protected $fillable = [
        'carabayar_nama',
        'carabayar_kode',
        'create_id',
        'update_id',
        'delete_id'
    ];
}
