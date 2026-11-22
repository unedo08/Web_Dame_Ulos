<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TransaksiT;
use App\Models\BarangEntryM;
use App\Models\PengirimanBarangT;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransaksiDetailT extends Model
{
    use SoftDeletes;
    protected $table = 'transaksidetail_t';
    protected $primaryKey = 'transaksidetail_id';
    protected $fillable = [
        'transaksidetail_transaksi_id',
        'transaksidetail_barang_id',
        'transaksidetail_jumlah_barang',
        'transaksidetail_harga_barang',
        'transaksidetail_status_penjualan',
        'transaksidetail_platform',
        'create_id',
        'update_id',
        'delete_id'
    ];

    public function transaksi()
    {
        return $this->belongsTo(TransaksiT::class, 'transaksidetail_transaksi_id', 'transaksi_id');
    }

    public function pengirimanBarang()
    {
        return $this->hasOne(PengirimanBarangT::class, 'pengirimanBarang_transaksi_id', 'transaksidetail_transaksi_id');
    }

    public function barang()
    {
        return $this->belongsTo(BarangEntryM::class, 'transaksidetail_barang_id', 'barangentry_id');
    }

    public function pengiriman()
    {
        return $this->hasOne(PengirimanBarangT::class, 'pengirimanBarang_transaksi_id', 'transaksidetail_transaksi_id');
    }

    /**
     * Kembalikan stok barang saat detail transaksi dihapus.
     *
     * - Barang biasa: stok dikurangi saat detail dibuat, jadi dikembalikan sejumlah detail.
     * - Barang dari LIVE: stok dikurangi saat live order dibuat. Live order yang terkait
     *   ikut dihapus (soft delete) dan stoknya dikembalikan, sama seperti hapus live order.
     */
    public function kembalikanStok($userId)
    {
        $barang = BarangEntryM::find($this->transaksidetail_barang_id);
        if (!$barang) {
            return;
        }

        $isFromLive = LiveOrderT::where('live_order_barang_id', $this->transaksidetail_barang_id)->exists();

        if (!$isFromLive) {
            $barang->increment('barangentry_jumlah_barang', $this->transaksidetail_jumlah_barang);
            return;
        }

        $liveOrder = LiveOrderT::where('live_order_barang_id', $this->transaksidetail_barang_id)
            ->orderByDesc('is_check')
            ->orderByDesc('live_order_id')
            ->first();

        $barang->increment('barangentry_jumlah_barang', $liveOrder->live_order_jumlah_barang);

        $liveOrder->delete_id = $userId;
        $liveOrder->save();
        $liveOrder->delete();
    }
}
