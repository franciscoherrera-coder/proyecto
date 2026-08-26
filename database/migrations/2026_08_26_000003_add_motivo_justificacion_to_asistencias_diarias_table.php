<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('asistencias_diarias', 'motivo_justificacion')) {
            Schema::table('asistencias_diarias', function (Blueprint $table) {
                $table->string('motivo_justificacion', 20)->nullable()->after('estado');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('asistencias_diarias', 'motivo_justificacion')) {
            Schema::table('asistencias_diarias', function (Blueprint $table) {
                $table->dropColumn('motivo_justificacion');
            });
        }
    }
};
