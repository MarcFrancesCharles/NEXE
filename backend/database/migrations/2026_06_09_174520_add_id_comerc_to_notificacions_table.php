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
        Schema::table('notificacions', function (Blueprint $table) {
            $table->unsignedBigInteger('id_comerc')->nullable()->after('id_usuari');
            $table->foreign('id_comerc')->references('id_comerc')->on('comercs')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notificacions', function (Blueprint $table) {
            $table->dropForeign(['id_comerc']);
            $table->dropColumn('id_comerc');
        });
    }
};
