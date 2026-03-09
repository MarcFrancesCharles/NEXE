<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
                $table->id('id_categoria'); // PK, AUTO_INCREMENT 
                $table->string('nom_cat', 50)->unique(); // UK, NN 
                $table->timestamps();
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
