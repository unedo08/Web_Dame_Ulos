<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PreOrdeBarangT extends Model
{
    protected $table = 'preOrdeBarang_t';
    protected $primaryKey = 'preOrdeBarang_id';
    protected $fillable = [
        'preOrderBarang_transaksi_id',
        'preOrderBarang_nama_barang',
        'preOrderBarang_nama_akun',
        'preOrderBarang_no_telepon',
        'preOrderBarang_target_selesai',
        'preOrderBarang_total_pembayaran',
        'preOrderBarang_uang_muka',
        'preOrderBarang_sisa_pembayaran',
        'preOrderBarang_deskripsi_barang',
        'preOrderBarang_catatan',
        'preOrderBarang_path_gambar',
        'preOrderBarang_barang_entry_id',
        'preOrderBarang_cara_bayar'
    ];
}