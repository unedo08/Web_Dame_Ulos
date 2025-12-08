<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CustomerM extends Model
{
    use SoftDeletes;
    protected $table = 'customer_m';
    protected $primaryKey = 'customer_id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'customer_nama',
        'customer_akun',
        'customer_alamat',
        'customer_notelepon',
        'customer_platform',
        'create_id',
        'update_id',
        'delete_id'
    ];
}
