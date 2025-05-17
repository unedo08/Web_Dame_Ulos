<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCodeMTable extends Migration
{
    public function up()
    {
        Schema::create('code_m', function (Blueprint $table) {
            $table->id('code_id');
            $table->string('code_nama');
            $table->unsignedBigInteger('code_jenisbarang_id');
            $table->timestamps();

            // Menambahkan foreign key jika ada relasi ke tabel jenisbarang_m
            $table->foreign('code_jenisbarang_id')->references('jenisbarang_id')->on('jenisbarang_m')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('code_m');
    }
}
