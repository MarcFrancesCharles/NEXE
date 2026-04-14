<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('categorias', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_id')->nullable()->after('nom_cat');
            $table->string('descripcio', 255)->nullable()->after('parent_id');
            $table->string('icona', 10)->nullable()->after('descripcio'); 
            
            $table->foreign('parent_id')->references('id_categoria')->on('categorias')->onDelete('cascade');
        });
    }
    
    public function down(): void
    {
        //
    }
};
