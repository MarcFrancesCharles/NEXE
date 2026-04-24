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
        Schema::create('solicituds_comerc', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_usuari');
            $table->unsignedBigInteger('id_categoria');
            $table->string('nom_comercial', 100);
            $table->string('descripcio', 255)->nullable();
            $table->integer('telefon')->nullable();
            $table->string('email_contacte', 100)->nullable();
            $table->string('enllac_web', 255)->nullable();
            $table->string('instagram', 100)->nullable();
            $table->string('cif', 20);
            $table->decimal('latitud', 10, 8);
            $table->decimal('longitud', 11, 8);
            $table->string('imatge_url', 500)->nullable();
            $table->enum('estat', ['PENDENT', 'APROVADA', 'DENEGADA'])->default('PENDENT');
            $table->timestamps();

            $table->foreign('id_usuari')->references('id_usuari')->on('usuaris')->onDelete('cascade');
            $table->foreign('id_categoria')->references('id_categoria')->on('categorias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicituds_comerc');
    }
};
