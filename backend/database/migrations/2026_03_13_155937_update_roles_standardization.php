<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Actualitzem els rols existents perquè no hi hagi problemes d'encoding
        DB::table('usuaris')->where('rol', 'COMERÇ')->update(['rol' => 'COMERC']);
    }

    public function down(): void
    {
        DB::table('usuaris')->where('rol', 'COMERC')->update(['rol' => 'COMERÇ']);
    }
};
