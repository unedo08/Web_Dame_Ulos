<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CaraBayarM extends Model
{
    protected $table = 'carabayar_m';

    protected $fillable = [
        'carabayar_nama',
        'carabayar_kode'
    ];
}
