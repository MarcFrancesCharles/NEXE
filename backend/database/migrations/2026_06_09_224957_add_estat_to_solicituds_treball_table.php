<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicituds_treball', function (Blueprint $table) {
            $table->enum('estat', ['PENDENT', 'APROVADA', 'DENEGADA'])->default('PENDENT')->after('cv_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('solicituds_treball', function (Blueprint $table) {
            $table->dropColumn('estat');
        });
    }
};
