<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('carrera_profesor_user')) {
            Schema::create('carrera_profesor_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->cascadeOnUpdate();
                $table->foreignId('carrera_id')->constrained('carreras')->cascadeOnDelete()->cascadeOnUpdate();
                $table->timestamps();
                $table->unique(['user_id', 'carrera_id']);
            });
        }

        $this->migrarValidacionesExistentes();
    }

    public function down(): void
    {
        Schema::dropIfExists('carrera_profesor_user');
    }

    private function migrarValidacionesExistentes(): void
    {
        if (!Schema::hasColumn('users', 'is_admin')) {
            return;
        }

        $profesores = DB::table('profesors')->get(['id', 'nombre', 'apellido']);
        $carrerasPorProfesor = DB::table('horarios')
            ->join('materias', 'materias.id', '=', 'horarios.materia_id')
            ->whereNotNull('horarios.profesor_id')
            ->whereNotNull('materias.carrera_id')
            ->get(['horarios.profesor_id', 'materias.carrera_id'])
            ->groupBy('profesor_id');

        DB::table('users')
            ->where('is_admin', 2)
            ->orderBy('id')
            ->chunkById(100, function ($users) use ($profesores, $carrerasPorProfesor) {
                $ahora = now();

                foreach ($users as $user) {
                    $nombreUsuario = $this->normalizar($user->name);
                    $profesoresCoincidentes = $profesores->filter(function ($profesor) use ($nombreUsuario) {
                        $nombreApellido = $this->normalizar($profesor->nombre . ' ' . $profesor->apellido);
                        $apellidoNombre = $this->normalizar($profesor->apellido . ' ' . $profesor->nombre);

                        return $nombreUsuario === $nombreApellido || $nombreUsuario === $apellidoNombre;
                    });

                    $carreraIds = collect();
                    if (Schema::hasColumn('users', 'carrera_id') && $user->carrera_id) {
                        $carreraIds->push((int) $user->carrera_id);
                    }

                    foreach ($profesoresCoincidentes as $profesor) {
                        $carreraIds = $carreraIds->merge(
                            collect($carrerasPorProfesor->get($profesor->id, collect()))->pluck('carrera_id')
                        );
                    }

                    $filas = $carreraIds
                        ->filter()
                        ->map(fn ($carreraId) => [
                            'user_id' => $user->id,
                            'carrera_id' => (int) $carreraId,
                            'created_at' => $ahora,
                            'updated_at' => $ahora,
                        ])
                        ->unique('carrera_id')
                        ->values()
                        ->all();

                    if ($filas) {
                        DB::table('carrera_profesor_user')->insertOrIgnore($filas);
                    }
                }
            });
    }

    private function normalizar(?string $texto): string
    {
        $texto = Str::lower(Str::ascii((string) $texto));

        return trim(preg_replace('/\s+/', ' ', $texto));
    }
};
