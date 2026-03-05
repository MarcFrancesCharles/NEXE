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
        Schema::create('sol_altas', function (Blueprint $table) {
            $table->id('id_solicitud'); // PK
            $table->unsignedBigInteger('id_usuari'); // ASPIRANT (FK, NN)
            $table->text('dades_fiscals'); //NN
            $table->enum('estat', ['PENDENT', 'APROVADA', 'DENEGADA'])->default('PENDENT'); // NN, Default: 'PENDENT'
            $table->timestamps();

            //Definición de la clau Foranea
            $table->foreign('id_usuari')->references('id_usuari')->on('usuaris');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sol_altas');
    }
};
