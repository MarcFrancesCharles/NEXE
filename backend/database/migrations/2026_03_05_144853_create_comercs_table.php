<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::create('comercs', function (Blueprint $table) {
            $table->id('id_comerc'); // PK
            $table->unsignedBigInteger('id_usuari'); // FK, NN
            $table->unsignedBigInteger('id_categoria'); // FK, NN
            $table->string('nom_comercial', 100); // NN
            $table->string('cif', 20)->unique(); // NN, Unique
            $table->decimal('latitud', 10, 8)->nullable();
            $table->decimal('longitud', 11, 8)->nullable();// format: "latitud,longitud"
            $table->timestamps();

            //Definicións de les claus foranies 
            $table->foreign('id_usuari')->references('id_usuari')->on('usuaris')->onDelete('cascade');
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comercs');
    }
};
