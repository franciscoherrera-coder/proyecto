<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('asistencias_diarias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('registro_id')->constrained('registros')->cascadeOnDelete()->cascadeOnUpdate();
            $table->date('fecha');
            $table->string('estado', 20)->default('presente');
            $table->string('motivo_justificacion', 20)->nullable();
            $table->timestamps();

            $table->unique(['materia_id', 'registro_id', 'fecha'], 'asistencia_materia_alumno_fecha_unique');
            $table->index(['materia_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_diarias');
    }
};
