<?php

namespace Database\Seeders;

use App\Models\TiquetValidat;
use App\Models\Transaccio;
use Illuminate\Database\Seeder;

class TiquetValidatSeeder extends Seeder
{
    public function run(): void
    {
        // Agafem transaccions de tipus ACUMULACIO que no tinguin tiquet
        $transaccions = Transaccio::where('tipus', 'ACUMULACIO')->whereNull('id_tiquet')->get();

        foreach ($transaccions as $index => $transaccio) {
            $tiquet = TiquetValidat::create([
                'codi_qr' => 'TX-QR-' . str_pad($index + 1, 6, '0', STR_PAD_LEFT) . '-' . strtoupper(bin2hex(random_bytes(4))),
                'import_compra' => rand(10, 150) + (rand(0, 99) / 100),
                'data_emissio' => $transaccio->data_hora,
            ]);

            $transaccio->update(['id_tiquet' => $tiquet->id_tiquet]);
        }

        // Creem alguns tiquets validats extra que no estiguin vinculats encara
        for ($i = 0; $i < 5; $i++) {
            TiquetValidat::create([
                'codi_qr' => 'TX-QR-EXTRA-' . $i . '-' . strtoupper(bin2hex(random_bytes(4))),
                'import_compra' => rand(15, 80) + (rand(0, 99) / 100),
                'data_emissio' => now()->subDays(rand(1, 10)),
            ]);
        }
    }
}
