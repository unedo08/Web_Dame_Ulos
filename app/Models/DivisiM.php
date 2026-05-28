<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DivisiM extends Model
{
    use SoftDeletes;

    protected $table = 'divisi_m';
    protected $primaryKey = 'divisi_id';

    protected $fillable = [
        'divisi_nama',
        'divisi_kode',
        'divisi_status',
        'create_id',
        'update_id',
        'delete_id',
    ];
}
