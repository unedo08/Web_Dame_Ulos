<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerM extends Model
{
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
    ];
}
