<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangEntryRiwayat extends Model
{
    protected $table = 'barangentry_riwayat';
    protected $primaryKey = 'riwayat_id';
    protected $fillable = [
        'riwayat_barangentry_id',
        'riwayat_aktivitas',
        'riwayat_user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'riwayat_user_id');
    }
}
