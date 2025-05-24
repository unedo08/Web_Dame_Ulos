<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AcaradetM extends Model
{
    use HasFactory;

    protected $table = 'acaradet_m';
    protected $primaryKey = 'acaradet_id';
    public $timestamps = true;

    protected $fillable = [
        'acaradet_acara_id',
        'acaradet_barangentry_id',
    ];

    public function acara()
    {
        return $this->belongsTo(AcaraM::class, 'acaradet_acara_id', 'acara_id');
    }

    public function barangEntry()
    {
        return $this->belongsTo(BarangEntryM::class, 'acaradet_barangentry_id', 'barangentry_id');
    }
}
