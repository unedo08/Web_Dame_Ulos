<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CodeM extends Model
{
    use SoftDeletes;
    use HasFactory;
    protected $table = 'code_m';
    protected $primaryKey = 'code_id';
    protected $fillable = ['code_nama', 'code_jenisbarang_id','create_id',
        'update_id',
        'delete_id'];

}
