<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenangKeluarDetailT extends Model
{
    use SoftDeletes;

    protected $table = 'benang_keluar_detail_t';
    protected $primaryKey = 'benang_keluar_detail_id';

    protected $fillable = [
        'benang_keluar_detail_keluar_id',
        'benang_keluar_detail_warna',
        'benang_keluar_detail_tipe',
        'benang_keluar_detail_jenis_id',
        'benang_keluar_detail_jumlah',
        'create_id',
        'update_id',
        'delete_id',
    ];

    public function keluar()
    {
        return $this->belongsTo(BenangKeluarT::class, 'benang_keluar_detail_keluar_id', 'benang_keluar_id');
    }

    public function jenis()
    {
        return $this->belongsTo(JenisBenangM::class, 'benang_keluar_detail_jenis_id', 'id');
    }
}
