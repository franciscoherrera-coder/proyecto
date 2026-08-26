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
        if (Schema::hasTable('carreras')) {
            return;
        }

        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->string('descripcion');
            $table->string('anios');
            $table->string ('resolucion', 50); 
            $table->string ('texto',3000);
            $table->string ('imagen', 255);
            $table->string('nombre_carpeta', 120);
            $table->timestamps();
        }); 
    }

    /*
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (!Schema::hasTable('carreras')) {
            return;
        }

        Schema::dropIfExists('carreras');
    }
};
