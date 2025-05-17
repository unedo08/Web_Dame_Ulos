<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('barangentry_m', function (Blueprint $table) {
            $table->id('barangentry_id');
            $table->string('barangentry_code_id');
            $table->string('barangentry_nama')->nullable();
            $table->string('barangentry_warna')->nullable();
            $table->string('barangentry_nama_penenun')->nullable();
            $table->string('barangentry_nama_panirat')->nullable();
            $table->string('barangentry_dryer')->nullable();
            $table->bigInteger('barangentry_modal')->nullable();
            $table->bigInteger('barangentry_price_tag')->nullable();
            $table->bigInteger('barangentry_harga_net')->nullable();
            $table->string('barangentry_acara')->nullable();
            $table->integer('barangentry_ukuran_mandar')->nullable();
            $table->integer('barangentry_ukuran_ulos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('barangentry_m');
    }
};