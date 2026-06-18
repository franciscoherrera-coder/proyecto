<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('materia_registro', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('registro_id')->constrained('registros')->cascadeOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->unique(['materia_id', 'registro_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materia_registro');
    }
};
