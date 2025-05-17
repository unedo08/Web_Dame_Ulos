<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('barangentry_temp', function (Blueprint $table) {
            $table->id('barangentry_temp_id');
            $table->string('barangentry_temp_code_id');
            $table->string('barangentry_temp_nama')->nullable();
            $table->string('barangentry_temp_warna')->nullable();
            $table->string('barangentry_temp_nama_penenun')->nullable();
            $table->string('barangentry_temp_nama_panirat')->nullable();
            $table->string('barangentry_temp_dryer')->nullable();
            $table->bigInteger('barangentry_temp_modal')->nullable();
            $table->bigInteger('barangentry_temp_price_tag')->nullable();
            $table->bigInteger('barangentry_temp_harga_net')->nullable();
            $table->string('barangentry_temp_acara')->nullable();
            $table->integer('barangentry_temp_ukuran_mandar')->nullable();
            $table->integer('barangentry_temp_ukuran_ulos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('barangentry_temp');
    }
};
