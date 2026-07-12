<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenangKeluarT extends Model
{
    use SoftDeletes;

    protected $table = 'benang_keluar_t';
    protected $primaryKey = 'benang_keluar_id';

    protected $fillable = [
        'benang_keluar_nama_penenun',
        'benang_keluar_status',
        'benang_keluar_tanggal_keluar',
        'benang_keluar_tanggal_selesai',
        'benang_keluar_catatan',
        'benang_keluar_foto_hasil',
        'benang_keluar_complete_id',
        'create_id',
        'update_id',
        'delete_id',
    ];

    public function details()
    {
        return $this->hasMany(BenangKeluarDetailT::class, 'benang_keluar_detail_keluar_id', 'benang_keluar_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_id', 'id');
    }

    public function completer()
    {
        return $this->belongsTo(User::class, 'benang_keluar_complete_id', 'id');
    }
}
