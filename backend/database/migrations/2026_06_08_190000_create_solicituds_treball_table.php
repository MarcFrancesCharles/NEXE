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
        Schema::create('solicituds_treball', function (Blueprint $table) {
            $table->id();
            $table->string('nom', 100);
            $table->string('correu', 100);
            $table->string('posicio', 50)->default('ADMIN');
            $table->text('missatge');
            $table->string('cv_path', 500);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('solicituds_treball');
    }
};
