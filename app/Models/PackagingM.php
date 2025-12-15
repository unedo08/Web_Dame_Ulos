<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PackagingM extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $dates = ['deleted_at'];
    protected $table = 'packaging_m';
    protected $primaryKey = 'packaging_id';

    protected $fillable = [
        'packaging_transactiondetail_id',
        'packaging_nama_akun',
        'packaging_alamat',
        'packaging_status',
        'create_id',
        'update_id',
        'delete_id'
    ];
}
