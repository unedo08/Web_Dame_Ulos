<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PewarnaM;

class PewarnaSeeder extends Seeder
{
    public function run(): void
    {
        $items = [
            ['nama' => 'Pewarna Alam', 'kode' => 'PEWARNA_ALAM'],
            ['nama' => 'Textile',      'kode' => 'TEXTILE'],
        ];

        foreach ($items as $item) {
            PewarnaM::updateOrCreate(
                ['pewarna_kode' => $item['kode']],
                [
                    'pewarna_nama'   => $item['nama'],
                    'pewarna_status' => 1,
                ]
            );
        }
    }
}
