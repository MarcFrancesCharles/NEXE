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
        Schema::create('notificacions', function (Blueprint $table) {
            $table->id('id_notificacio');
            $table->unsignedBigInteger('id_usuari');
            $table->string('titol');
            $table->text('missatge');
            $table->string('icona')->nullable();
            $table->string('categoria')->default('general');
            $table->boolean('llegida')->default(false);
            $table->timestamps();

            $table->foreign('id_usuari')->references('id_usuari')->on('usuaris')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificacions');
    }
};
