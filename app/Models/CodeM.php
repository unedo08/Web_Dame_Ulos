<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CodeM extends Model
{
    use HasFactory;

    // Tentukan nama tabel
    protected $table = 'code_m';

    // Tentukan kolom yang bisa diisi (fillable)
    protected $fillable = [
        'code_nama',
        'code_jenisbarang_id'
    ];

    // Tentukan primary key jika tidak menggunakan id (optional)
    protected $primaryKey = 'code_id';
}
