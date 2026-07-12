<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenangMasukT extends Model
{
    use SoftDeletes;

    protected $table = 'benang_masuk_t';
    protected $primaryKey = 'benang_masuk_id';

    protected $fillable = [
        'benang_masuk_tipe',
        'benang_masuk_jenis_id',
        'benang_masuk_warna',
        'benang_masuk_jumlah',
        'benang_masuk_sumber_warna',
        'create_id',
        'update_id',
        'delete_id',
    ];

    public function jenis()
    {
        return $this->belongsTo(JenisBenangM::class, 'benang_masuk_jenis_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'create_id', 'id');
    }
}
