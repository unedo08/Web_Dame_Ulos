<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('jenisbarang_m', function (Blueprint $table) {
            $table->id('jenisbarang_id');
            $table->string('jenisbarang_nama', 100);
            $table->string('jenisbarang_kode', 50);
            $table->string('jenisbarang_tipe', 50);
            $table->integer('jenisbarang_jumlah');
            $table->timestamps(); // ini akan otomatis menambahkan created_at dan updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenisbarang_m');
    }
};
