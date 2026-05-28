<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PengeluaranT extends Model
{
    use SoftDeletes;

    protected $table = 'pengeluaran_t';
    protected $primaryKey = 'pengeluaran_id';

    protected $fillable = [
        'pengeluaran_tanggal',
        'pengeluaran_nama',
        'pengeluaran_jenis_id',
        'pengeluaran_divisi_id',
        'pengeluaran_jumlah',
        'pengeluaran_sumber_dana_id',
        'pengeluaran_cara_bayar_id',
        'create_id',
        'update_id',
        'delete_id',
    ];

    protected $casts = [
        'pengeluaran_tanggal' => 'date:Y-m-d',
        'pengeluaran_jumlah'  => 'float',
    ];

    public function jenisPengeluaran()
    {
        return $this->belongsTo(JenisPengeluaranM::class, 'pengeluaran_jenis_id', 'jenis_pengeluaran_id');
    }

    public function divisi()
    {
        return $this->belongsTo(DivisiM::class, 'pengeluaran_divisi_id', 'divisi_id');
    }

    public function sumberDana()
    {
        return $this->belongsTo(SumberDanaM::class, 'pengeluaran_sumber_dana_id', 'sumber_dana_id');
    }

    public function caraBayar()
    {
        return $this->belongsTo(CaraBayarM::class, 'pengeluaran_cara_bayar_id', 'id');
    }
}
