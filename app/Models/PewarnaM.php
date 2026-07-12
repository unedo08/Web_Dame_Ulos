<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PewarnaM extends Model
{
    use SoftDeletes;

    protected $table = 'pewarna_m';
    protected $primaryKey = 'pewarna_id';

    protected $fillable = [
        'pewarna_nama',
        'pewarna_kode',
        'pewarna_status',
        'create_id',
        'update_id',
        'delete_id',
    ];
}
