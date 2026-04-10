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
        Schema::table('comercs', function (Blueprint $table) {
            // Afegim els camps just després de 'direccio' per mantenir un ordre lògic
            $table->text('descripcio')->nullable()->after('direccio');
            $table->string('telefon', 20)->nullable()->after('descripcio');
            $table->string('email_contacte', 100)->nullable()->after('telefon');
            $table->string('enllac_web', 255)->nullable()->after('email_contacte');
            $table->string('instagram', 100)->nullable()->after('enllac_web');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comercs', function (Blueprint $table) {
            // És vital definir el down per si hem de fer un rollback
            $table->dropColumn([
                'descripcio', 
                'telefon', 
                'email_contacte', 
                'enllac_web', 
                'instagram'
            ]);
        });
    }
};