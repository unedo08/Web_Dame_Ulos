<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PlatformM extends Model
{
    use SoftDeletes;

    protected $table = 'platform_m';

    protected $fillable = [
        'platform_nama',
        'platform_kode',
        'platform_status',
        'create_id',
        'update_id',
        'delete_id',
    ];
}
