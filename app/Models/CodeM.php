<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeM extends Model
{
    use HasFactory;
    protected $table = 'code_m';
    protected $primaryKey = 'code_id';
    protected $fillable = ['code_nama', 'code_jenisbarang_id'];

    public function jenisBarang() {
        return $this->belongsTo(JenisBarang::class, 'code_jenisbarang_id');
    }

    public function barangEntries() {
        return $this->hasMany(BarangEntry::class, 'barangentry_code_id');
    }
}
