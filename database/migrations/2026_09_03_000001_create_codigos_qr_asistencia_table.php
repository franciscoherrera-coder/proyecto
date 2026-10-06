<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('codigos_qr_asistencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->date('fecha');
            $table->string('token', 64)->unique();
            $table->string('tipo', 20)->default('presente');
            $table->boolean('habilitado')->default(true);
            $table->timestamps();

            $table->index(['materia_id', 'fecha']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('codigos_qr_asistencia');
    }
};
