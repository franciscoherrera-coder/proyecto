<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasColumn('asistencias_diarias', 'observacion')) {
            Schema::table('asistencias_diarias', function (Blueprint $table) {
                $table->dropColumn('observacion');
            });
        }
    }

    public function down(): void
    {
        Schema::table('asistencias_diarias', function (Blueprint $table) {
            $table->string('observacion')->nullable();
        });
    }
};
