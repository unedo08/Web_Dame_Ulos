<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CaraBayarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['carabayar_nama' => 'Cash', 'carabayar_kode' => 'CASH'],

            ['carabayar_nama' => 'EDC BCA', 'carabayar_kode' => 'EDC_BCA'],
            ['carabayar_nama' => 'EDC BNI', 'carabayar_kode' => 'EDC_BNI'],
            ['carabayar_nama' => 'EDC BRI', 'carabayar_kode' => 'EDC_BRI'],
            ['carabayar_nama' => 'EDC Mandiri', 'carabayar_kode' => 'EDC_MANDIRI'],

            ['carabayar_nama' => 'Transfer BCA', 'carabayar_kode' => 'TRF_BCA'],
            ['carabayar_nama' => 'Transfer BNI', 'carabayar_kode' => 'TRF_BNI'],
            ['carabayar_nama' => 'Transfer BRI', 'carabayar_kode' => 'TRF_BRI'],
            ['carabayar_nama' => 'Transfer Mandiri', 'carabayar_kode' => 'TRF_MANDIRI'],

            ['carabayar_nama' => 'QRIS BCA', 'carabayar_kode' => 'QRIS_BCA'],
            ['carabayar_nama' => 'QRIS BNI', 'carabayar_kode' => 'QRIS_BNI'],
            ['carabayar_nama' => 'QRIS BRI', 'carabayar_kode' => 'QRIS_BRI'],
            ['carabayar_nama' => 'QRIS Mandiri', 'carabayar_kode' => 'QRIS_MANDIRI'],

            ['carabayar_nama' => 'Shopee', 'carabayar_kode' => 'SHOPEE'],
            ['carabayar_nama' => 'Tokopedia', 'carabayar_kode' => 'TOKOPEDIA'],
            ['carabayar_nama' => 'TikTok', 'carabayar_kode' => 'TIKTOK'],
        ];

        DB::table('carabayar_m')->insert($data);
    }
}
