<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('carrera_user')) {
            return;
        }

        $duplicadas = DB::table('carrera_user')
            ->select('carrera_id', DB::raw('MAX(id) as id_conservado'))
            ->groupBy('carrera_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicadas as $duplicada) {
            DB::table('carrera_user')
                ->where('carrera_id', $duplicada->carrera_id)
                ->where('id', '!=', $duplicada->id_conservado)
                ->delete();
        }

        Schema::table('carrera_user', function (Blueprint $table) {
            $table->unique('carrera_id', 'carrera_user_carrera_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('carrera_user')) {
            return;
        }

        Schema::table('carrera_user', function (Blueprint $table) {
            $table->dropUnique('carrera_user_carrera_unique');
        });
    }
};
