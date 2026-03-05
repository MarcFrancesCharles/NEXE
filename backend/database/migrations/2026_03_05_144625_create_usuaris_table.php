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
        Schema::create('usuaris', function (Blueprint $table) {
            $table->id('id_usuari'); // PK, AUTO_INCREMENT 
            $table->string('correu', 100)->unique(); // UK, NN 
            $table->string('contrasenya', 255); // NN, Hash de la contrasenya
            $table->enum('rol', ['ESTANDARD', 'COMERÇ', 'ADMIN']); // NN 
            $table->enum('estat', ['ACTIU', 'BLOQUEJAT'])->default('ACTIU'); // NN, Per defecte 'ACTIU' 
            $table->timestamps(); // Crea data de registre i d'actualització
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuaris');
    }
};
