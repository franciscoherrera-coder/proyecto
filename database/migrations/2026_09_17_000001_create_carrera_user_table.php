<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('carrera_user')) {
            Schema::create('carrera_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete()->cascadeOnUpdate();
                $table->timestamps();
                $table->unique(['user_id', 'carrera_id']);
            });
        }

        if (Schema::hasColumn('users', 'carrera_id')) {
            $usuariosConCarrera = DB::table('users')->whereNotNull('carrera_id');

            if (Schema::hasColumn('users', 'is_admin')) {
                $usuariosConCarrera->where('is_admin', 1);
            }

            $usuariosConCarrera->orderBy('id')->chunkById(100, function ($users) {
                    $ahora = now();
                    $filas = $users->map(function ($user) use ($ahora) {
                        return [
                            'user_id' => $user->id,
                            'carrera_id' => $user->carrera_id,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ];
                    })->all();

                    DB::table('carrera_user')->insertOrIgnore($filas);
                });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('carrera_user');
    }
};
