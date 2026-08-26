<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('noticias') || Schema::hasColumn('noticias', 'archivo1')) {
            return;
        }

        Schema::table('noticias', function (Blueprint $table) {
            $table->string('archivo1',255)->nullable();
            $table->string('archivo2',255)->nullable();
            $table->string('archivo3',255)->nullable();
         });   
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        /*Schema::dropColumn('noticias.archivo1');
        Schema::dropColumn('noticias.archivo2');
        Schema::dropColumn('noticias.archivo3');*/

    }
};
