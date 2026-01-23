<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransaksiDetailT;
use App\Models\CustomerM;
use App\Models\CaraBayarM;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;



class TransaksiT extends Model
{
    use SoftDeletes;
    protected $table = 'transaksi_t';
    protected $primaryKey = 'transaksi_id';
    protected $fillable = [
        'transaksi_nama_customer',
        'transaksi_nomor_telepon',
        'transaksi_jumlah_barang',
        'transaksi_total_harga',
        'transaksi_cara_bayar',
        'transaksi_tipe',
        'transaksi_status',
        'transaksi_catatan',
        'transaksi_customer_id',
        'transaksi_platform',
        'create_id',
        'update_id',
        'delete_id'
    ];

    public function details()
    {
        return $this->hasMany(TransaksiDetailT::class, 'transaksidetail_transaksi_id', 'transaksi_id');
    }

    public function customer()
    {
        return $this->belongsTo(CustomerM::class, 'transaksi_customer_id', 'customer_id');
    }


    public function caraBayar()
    {
        return $this->belongsTo(CaraBayarM::class, 'transaksi_cara_bayar', 'carabayar_kode');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'create_id', 'id');
    }
}
