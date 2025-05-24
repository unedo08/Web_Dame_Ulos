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
        Schema::create('acaradet_m', function (Blueprint $table) {
            $table->id('acaradet_id');
            $table->unsignedBigInteger('acaradet_acara_id');
            $table->unsignedBigInteger('acaradet_barangentry_id');
            $table->timestamps();

            // Foreign keys (optional)
            $table->foreign('acaradet_acara_id')->references('acara_id')->on('acara_m')->onDelete('cascade');
            $table->foreign('acaradet_barangentry_id')->references('barangentry_id')->on('barangentry_m')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acaradet_m');
    }
};
