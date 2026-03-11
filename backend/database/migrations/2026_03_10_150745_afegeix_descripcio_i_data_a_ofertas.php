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
            Schema::table('ofertas', function (Blueprint $table) {
                $table->text('descripcio')->nullable()->after('titol');
                // Afegim data_fi. Si és null, l'oferta no caduca mai.
                $table->timestamp('data_fi')->nullable()->after('estat'); 
            });
        }

    public function down(): void
    {
        Schema::table('ofertas', function (Blueprint $table) {
            $table->dropColumn(['descripcio', 'data_fi']);
        });
    }
};
