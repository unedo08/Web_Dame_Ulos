<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisBarangM extends Model
{
    use HasFactory;
    protected $table = 'jenisbarang_m';
    protected $primaryKey = 'jenisbarang_id';
    protected $fillable = ['jenisbarang_nama', 'jenisbarang_kode', 'jenisbarang_tipe', 'jenisbarang_jumlah'];

    public function codes() {
        return $this->hasMany(Code::class, 'code_jenisbarang_id');
    }
}
