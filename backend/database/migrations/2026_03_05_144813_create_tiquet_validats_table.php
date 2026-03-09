<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('tiquet_validats', function (Blueprint $table) {
                $table->id('id_tiquet'); // PK, AUTO_INCREMENT 
                $table->string('codi_qr', 255)->unique(); // UK, NN. Hash únic del tiquet 
                $table->decimal('import_compra', 10, 2); // Valor en euros, NN 
                $table->dateTime('data_emissio'); // Data real de la compra, NN 
                $table->timestamps();
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiquet_validats');
    }
};
