<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TiquetValidat;

class TiquetValidatSeeder extends Seeder
{
    public function run(): void
    {
        TiquetValidat::factory(50)->create();
    }
}
