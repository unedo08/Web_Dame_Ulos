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
        Schema::table('barangentry_m', function (Blueprint $table) {
            $table->string('barangentry_status')->default('ready');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('barangentry_m', function (Blueprint $table) {
            $table->dropColumn('barangentry_status');
        });
    }
};
