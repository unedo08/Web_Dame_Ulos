<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackagingM extends Model
{
    use HasFactory;

    protected $table = 'packaging_m';
    protected $primaryKey = 'packaging_id';

    protected $fillable = [
        'packaging_transactiondetail_id',
        'packaging_nama_akun',
        'packaging_alamat',
    ];
}
