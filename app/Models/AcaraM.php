<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AcaraM extends Model
{
    use SoftDeletes;
    use HasFactory;

    protected $table = 'acara_m';
    protected $primaryKey = 'acara_id';
    public $timestamps = true; // Set to false if your table doesn't use timestamps

    protected $fillable = [
        'acara_nama',
        'acara_jumlahbarang',
        'acara_modalbarang',
        'acara_harganetbarang',
        'acara_hargapricetagbarang',
        'acara_keterangan',
        'acara_status',
        'create_id',
        'update_id',
        'delete_id'
    ];
}