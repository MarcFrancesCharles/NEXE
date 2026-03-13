<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Canviem la columna a string per poder posar qualsevol valor
        Schema::table('usuaris', function (Blueprint $table) {
            $table->string('rol')->change();
        });

        // 2. Actualitzem els rols existents
        DB::table('usuaris')->where('rol', 'COMERÇ')->update(['rol' => 'COMERC']);
    }

    public function down(): void
    {
        // No tornem a enum per seguretat, però podríem si calgués
        DB::table('usuaris')->where('rol', 'COMERC')->update(['rol' => 'COMERÇ']);
    }
};
