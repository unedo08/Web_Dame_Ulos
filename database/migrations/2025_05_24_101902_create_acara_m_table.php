<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('acara_m', function (Blueprint $table) {
            $table->id('acara_id');
            $table->string('acara_nama')->nullable();
            $table->integer('acara_jumlahbarang')->nullable();
            $table->decimal('acara_modalbarang', 12, 2)->nullable();
            $table->decimal('acara_harganetbarang', 12, 2)->nullable();
            $table->decimal('acara_hargapricetagbarang', 12, 2)->nullable();
            $table->text('acara_keterangan')->nullable();
            $table->string('acara_status')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acara_m');
    }
};
