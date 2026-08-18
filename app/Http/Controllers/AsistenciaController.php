<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Horario;
use App\Models\Registro;
use App\Models\Carrera;
use App\Models\Anio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AsistenciaController extends Controller
{
    public function index()
    {
        $tieneTablaAsignaciones = Schema::hasTable('materia_registro');
        $usuarioActual = Auth::user();
        $rolUsuario = $this->rolDesdeUsuario(Auth::user());
        $adminPuedeCrearAdmins = $this->esDirectoraAsistencia($usuarioActual);
        $carreraAdminId = $this->carreraIdUsuario($usuarioActual);
        $materiasQuery = Materia::with(['deCarrera', 'deAnio', 'horario.profesor'])
            ->orderBy('carrera_id')
            ->orderBy('anio_id')
            ->orderBy('orden');

        if ($tieneTablaAsignaciones) {
            $materiasQuery->with('alumnos');
        }

        $emailsSinCarrera = Registro::whereNull('carrera_id')->pluck('email');
        $usuariosProfesores = User::where('is_admin', 2)
            ->orderBy('name')
            ->orderBy('email')
            ->get()
            ->map(function ($user) {
                [$nombre, $apellido] = $this->partesNombreUsuario($user);
                $profesor = Profesor::where('nombre', $nombre)
                    ->where('apellido', $apellido)
                    ->first();

                $materiasAsignadas = $profesor
                    ? Horario::where('profesor_id', $profesor->id)
                        ->whereNotNull('materia_id')
                        ->pluck('materia_id')
                        ->unique()
                        ->values()
                    : collect();

                $user->profesor_asistencia_id = $profesor ? $profesor->id : null;
                $user->materias_asignadas_ids = $materiasAsignadas;
                $user->materias_asignadas_count = $materiasAsignadas->count();

                return $user;
            });

        $profesoresDesdeHorarios = Horario::with('profesor')
            ->whereNotNull('profesor_id')
            ->get()
            ->pluck('profesor')
            ->filter()
            ->unique('id')
            ->sortBy(function ($profesor) {
                return $profesor->apellido . ' ' . $profesor->nombre;
            })
            ->values();
        $materiasProfesor = collect();

        if ($rolUsuario === 'profesor') {
            $profesorIdsUsuario = $this->profesoresDesdeUsuario(Auth::user())->pluck('id');
            $materiaIdsProfesor = collect();

            if ($profesorIdsUsuario->isNotEmpty()) {
                $materiaIdsProfesor = Horario::whereIn('profesor_id', $profesorIdsUsuario)
                    ->whereNotNull('materia_id')
                    ->pluck('materia_id')
                    ->merge(Materia::whereIn('profesor_id', $profesorIdsUsuario)->pluck('id'))
                    ->unique()
                    ->values();
            }

            $materiasProfesor = Materia::with(['deCarrera', 'deAnio'])
                ->when($tieneTablaAsignaciones, function ($query) {
                    $query->with('alumnos');
                })
                ->whereIn('id', $materiaIdsProfesor)
                ->orderBy('carrera_id')
                ->orderBy('anio_id')
                ->orderBy('orden')
                ->get();
        }

        return view('frontend.asistencia.index', [
            'materias' => $materiasQuery->get(),
            'materiasAdministrablesIds' => $this->materiasAdministrablesQuery($usuarioActual)->pluck('id'),
            'materiasProfesor' => $materiasProfesor,
            'profesores' => $profesoresDesdeHorarios,
            'alumnos' => Registro::orderBy('apellido')->orderBy('nombre')->get(),
            'carreras' => Carrera::orderBy('descripcion')->get(),
            'carrerasAdministrables' => $adminPuedeCrearAdmins
                ? Carrera::orderBy('descripcion')->get()
                : Carrera::where('id', $carreraAdminId)->orderBy('descripcion')->get(),
            'anios' => Anio::orderBy('anio')->get(),
            'usuarios' => User::orderBy('name')->orderBy('email')->get(),
            'usuariosProfesores' => $usuariosProfesores,
            'usuariosSinCarrera' => User::whereIn('email', $emailsSinCarrera)
                ->where(function ($query) {
                    $query->whereNull('is_admin')->orWhere('is_admin', 0);
                })
                ->orderBy('name')
                ->orderBy('email')
                ->get(),
            'tieneTablaAsignaciones' => $tieneTablaAsignaciones,
            'rolUsuario' => $rolUsuario,
            'adminPuedeCrearAdmins' => $adminPuedeCrearAdmins,
            'carreraAdminId' => $carreraAdminId,
        ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'El email o la contraseña no son correctos.'])
                ->withInput($request->only('email'));
        }

        $request->session()->regenerate();

        return redirect()
            ->route('asistencia.index')
            ->with('status', 'Ingresaste correctamente.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('asistencia.index')
            ->with('status', 'Sesión cerrada.');
    }

    public function registrar(Request $request)
    {
        return $this->registrarUsuario($request, false);
    }

    public function registrarDesdeAdmin(Request $request)
    {
        $this->abortUnlessDirectoraAsistencia();
        $request->merge(['rol' => 'admin']);

        return $this->registrarUsuario($request, true);
    }

    private function registrarUsuario(Request $request, bool $permiteAdmin)
    {
        $request->merge([
            'rol' => $permiteAdmin ? $request->input('rol', 'alumno') : 'alumno',
        ]);

        if ($request->input('rol') === 'alumno' && !$request->filled('cuil')) {
            $request->merge(['cuil' => $request->input('dni')]);
        }

        $data = $request->validate([
            'rol' => ['required', $permiteAdmin ? 'in:alumno,profesor,admin' : 'in:alumno'],
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required_unless:rol,admin', 'nullable', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'dni' => ['required_if:rol,alumno', 'nullable', 'integer', 'unique:registros,dni'],
            'cuil' => ['required_if:rol,alumno', 'nullable', 'integer', 'unique:registros,cuil'],
            'carrera_id' => [$permiteAdmin ? 'required_if:rol,admin' : 'nullable', 'nullable', 'exists:carreras,id'],
        ]);

        if ($data['rol'] === 'alumno') {
            $registro = new Registro();
            $registro->nombre = $data['nombre'];
            $registro->apellido = $data['apellido'];
            $registro->dni = $data['dni'];
            $registro->cuil = $data['cuil'];
            $registro->email = $data['email'];
            $registro->carrera_id = $data['carrera_id'] ?? null;
            $this->completarDatosAlumno($registro);
            $registro->save();
        }

        if ($data['rol'] === 'profesor') {
            $profesor = new Profesor();
            $profesor->nombre = $data['nombre'];
            $profesor->apellido = $data['apellido'];
            $profesor->save();
        }

        $this->crearUsuarioAcceso(
            trim($data['nombre'] . ' ' . ($data['apellido'] ?? '')),
            $data['email'],
            $data['password'],
            $data['rol'],
            $data['carrera_id'] ?? null
        );

        return redirect()
            ->route('asistencia.index', $permiteAdmin ? ['admin_tab' => 'usuarios'] : [])
            ->with('status', 'Cuenta creada correctamente. Ya podés iniciar sesión.');
    }

    public function validarProfesor(Request $request)
    {
        $this->abortUnlessAdmin();

        $data = $request->validate([
            'user_ids' => ['required', 'array', 'min:1'],
            'user_ids.*' => ['exists:users,id'],
        ]);

        $profesoresValidados = 0;

        foreach ($data['user_ids'] as $userId) {
            $user = User::findOrFail($userId);

            if (Schema::hasColumn('users', 'is_admin')) {
                $user->is_admin = 2;
            }

            if (Schema::hasColumn('users', 'rol')) {
                $user->rol = 'Profesor';
            }

            $user->save();

            $nombrePartes = preg_split('/\s+/', trim($user->name), 2);
            $nombre = $nombrePartes[0] ?? $user->name;
            $apellido = $nombrePartes[1] ?? 'No informado';

            $existeProfesor = Profesor::where('nombre', $nombre)
                ->where('apellido', $apellido)
                ->exists();

            if (!$existeProfesor) {
                $profesor = new Profesor();
                $profesor->nombre = $nombre;
                $profesor->apellido = $apellido;
                $profesor->save();
            }

            $profesoresValidados++;
        }

        return redirect()
            ->route('asistencia.index', ['admin_tab' => 'usuarios'])
            ->with('status', 'Se validaron ' . $profesoresValidados . ' profesor(es).');
    }

    public function asignarProfesor(Request $request)
    {
        $this->abortUnlessAdmin();

        $request->merge([
            'profesor_id' => $request->input('profesor_id') ?: null,
        ]);

        $data = $request->validate([
            'materia_id' => ['required', 'exists:materias,id'],
            'profesor_id' => ['nullable', 'exists:profesors,id'],
        ]);

        $materia = Materia::findOrFail($data['materia_id']);
        $this->actualizarProfesorEnHorario($materia, $data['profesor_id'] ?? null);

        return redirect()
            ->route('asistencia.index', ['rol' => 'admin'])
            ->with('status', 'Se actualizó el profesor de ' . $materia->descripcion . '.');
    }

    public function asignarMateriasProfesor(Request $request)
    {
        $this->abortUnlessAdmin();

        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'materia_ids' => ['nullable', 'array'],
            'materia_ids.*' => ['exists:materias,id'],
        ]);

        $user = User::findOrFail($data['user_id']);
        $profesor = $this->profesorDesdeUsuario($user);
        $materiaIds = collect($data['materia_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();
        $materiasAdministrablesIds = $this->materiasAdministrablesQuery(Auth::user())->pluck('id');

        if ($materiaIds->diff($materiasAdministrablesIds)->isNotEmpty()) {
            abort(403, 'No podés asignar materias de otra carrera.');
        }

        $horariosDelProfesor = Horario::where('profesor_id', $profesor->id)
            ->whereNotNull('materia_id')
            ->whereIn('materia_id', $materiasAdministrablesIds);

        if ($materiaIds->isNotEmpty()) {
            $horariosDelProfesor->whereNotIn('materia_id', $materiaIds);
        }

        $horariosDelProfesor->update(['profesor_id' => null]);

        if ($materiaIds->isNotEmpty()) {
            $materias = Materia::whereIn('id', $materiaIds)->get();

            foreach ($materias as $materia) {
                $this->actualizarProfesorEnHorario($materia, $profesor->id);
            }
        }

        return redirect()
            ->route('asistencia.index', ['admin_tab' => 'usuarios'])
            ->with('status', 'Se actualizaron las materias de ' . $user->name . '.');
    }

    public function asignarAlumno(Request $request)
    {
        $this->abortUnlessAdmin();

        if (!Schema::hasTable('materia_registro')) {
            return redirect()
                ->route('asistencia.index', ['rol' => 'admin'])
                ->withErrors('Falta ejecutar php artisan migrate para crear la tabla de asignaciones.');
        }

        $data = $request->validate([
            'materia_id' => ['required', 'exists:materias,id'],
            'registro_id' => ['required', 'exists:registros,id'],
        ]);

        $materia = Materia::findOrFail($data['materia_id']);
        $alumno = Registro::findOrFail($data['registro_id']);

        $materia->alumnos()->syncWithoutDetaching([$alumno->id]);

        return redirect()
            ->route('asistencia.index', ['rol' => 'admin'])
            ->with('status', $alumno->apellido . ', ' . $alumno->nombre . ' fue asignado a ' . $materia->descripcion . '.');
    }

    public function quitarAlumno(Request $request)
    {
        $this->abortUnlessAdmin();

        if (!Schema::hasTable('materia_registro')) {
            return redirect()
                ->route('asistencia.index', ['rol' => 'admin'])
                ->withErrors('Falta ejecutar php artisan migrate para crear la tabla de asignaciones.');
        }

        $data = $request->validate([
            'materia_id' => ['required', 'exists:materias,id'],
            'registro_id' => ['required', 'exists:registros,id'],
        ]);

        $materia = Materia::findOrFail($data['materia_id']);
        $alumno = Registro::findOrFail($data['registro_id']);

        $materia->alumnos()->detach($alumno->id);

        return redirect()
            ->route('asistencia.index', ['rol' => 'admin'])
            ->with('status', $alumno->apellido . ', ' . $alumno->nombre . ' fue quitado de ' . $materia->descripcion . '.');
    }

    public function asignarAlumnosProfesor(Request $request)
    {
        $this->abortUnlessProfesor();

        if (!Schema::hasTable('materia_registro')) {
            return redirect()
                ->route('asistencia.index')
                ->withErrors('Falta ejecutar php artisan migrate para crear la tabla de asignaciones.');
        }

        $data = $request->validate([
            'materia_id' => ['required', 'exists:materias,id'],
            'registro_ids' => ['required', 'array', 'min:1'],
            'registro_ids.*' => ['exists:registros,id'],
        ]);

        $profesores = $this->profesoresDesdeUsuario(Auth::user());
        $materia = Materia::findOrFail($data['materia_id']);

        if ($profesores->isEmpty() || !$this->profesorTieneMateria($profesores->pluck('id'), $materia)) {
            abort(403, 'No podés modificar una materia que no tenés asignada.');
        }

        $materia->alumnos()->syncWithoutDetaching($data['registro_ids']);

        return redirect()
            ->route('asistencia.index')
            ->with('status', 'Se agregaron ' . count($data['registro_ids']) . ' alumno(s) a ' . $materia->descripcion . '.');
    }

    public function actualizarCarrera(Request $request, Carrera $carrera)
    {
        $this->abortUnlessAdmin();

        $data = $request->validate([
            'descripcion' => ['required', 'string', 'max:255'],
            'anios' => ['nullable', 'numeric'],
            'resolucion' => ['nullable', 'string', 'max:50'],
            'texto' => ['nullable', 'string', 'max:3000'],
            'nombre_carpeta' => ['nullable', 'string', 'max:120'],
        ]);

        $carrera->descripcion = $data['descripcion'];
        $carrera->anios = $data['anios'] ?? $carrera->anios;
        $carrera->resolucion = $data['resolucion'] ?? '';
        $carrera->texto = $data['texto'] ?? '';
        $carrera->nombre_carpeta = $data['nombre_carpeta'] ?? '';
        $carrera->save();

        return redirect()
            ->route('asistencia.index', ['rol' => 'admin', 'admin_tab' => 'carreras'])
            ->with('status', 'Se actualizó la carrera ' . $carrera->descripcion . '.');
    }

    public function actualizarMateria(Request $request, Materia $materia)
    {
        $this->abortUnlessAdmin();

        $request->merge([
            'profesor_id' => $request->input('profesor_id') ?: null,
        ]);

        $data = $request->validate([
            'descripcion' => ['required', 'string', 'max:255'],
            'carrera_id' => ['nullable', 'exists:carreras,id'],
            'anio_id' => ['nullable', 'exists:anios,id'],
            'profesor_id' => ['nullable', 'exists:profesors,id'],
            'orden' => ['nullable', 'integer', 'min:0'],
        ]);

        $materia->descripcion = $data['descripcion'];
        $materia->carrera_id = $data['carrera_id'] ?? null;
        $materia->anio_id = $data['anio_id'] ?? null;
        $this->actualizarProfesorEnHorario($materia, $data['profesor_id'] ?? null);
        $materia->orden = $data['orden'] ?? null;
        $materia->save();

        return redirect()
            ->route('asistencia.index', ['rol' => 'admin', 'admin_tab' => 'carreras'])
            ->with('status', 'Se actualizó la materia ' . $materia->descripcion . '.');
    }

    private function crearUsuarioAcceso(string $name, string $email, string $password, string $rol, ?int $carreraId = null): User
    {
        $user = new User();
        $user->name = $name;
        $user->email = $email;
        $user->password = Hash::make($password);

        if (Schema::hasColumn('users', 'is_admin')) {
            $user->is_admin = match ($rol) {
                'admin' => 1,
                'profesor' => 2,
                default => 0,
            };
        }

        if (Schema::hasColumn('users', 'rol')) {
            $user->rol = ucfirst($rol);
        }

        if (Schema::hasColumn('users', 'carrera_id')) {
            $user->carrera_id = $carreraId;
        }

        $user->save();

        return $user;
    }

    private function completarDatosAlumno(Registro $registro): void
    {
        $defaults = [
            'sexo' => 'No informado',
            'est_civil' => 'No informado',
            'domicilio' => 'No informado',
            'ciudad' => 'No informado',
            'partido' => 'No informado',
            'provincia' => 'No informado',
            'cod_postal' => 0,
            'fec_nac' => now()->subYears(18)->toDateString(),
            'lug_nac' => 'No informado',
            'prov_nac' => 'No informado',
            'nacionalidad' => 'No informado',
            'celular' => 0,
            'titulo_intermedio' => 'No informado',
            'escuela_egreso' => 'No informado',
            'distrito_egreso' => 'No informado',
            'trabaja' => false,
        ];

        foreach ($defaults as $field => $value) {
            $registro->{$field} = $registro->{$field} ?? $value;
        }

        $registro->setAttribute('año_egreso', now()->year);
    }

    private function profesorDesdeUsuario(User $user): Profesor
    {
        $profesor = $this->buscarProfesorDesdeUsuario($user);

        if ($profesor) {
            return $profesor;
        }

        [$nombre, $apellido] = $this->partesNombreUsuario($user);
        $profesor = new Profesor();
        $profesor->nombre = $nombre;
        $profesor->apellido = $apellido;
        $profesor->save();

        return $profesor;
    }

    private function buscarProfesorDesdeUsuario(?User $user): ?Profesor
    {
        return $this->profesoresDesdeUsuario($user)->first();
    }

    private function profesoresDesdeUsuario(?User $user)
    {
        if (!$user) {
            return collect();
        }

        $nombreCompleto = $this->normalizarTexto($user->name);
        [$nombre, $apellido] = $this->partesNombreUsuario($user);
        $nombreNormalizado = $this->normalizarTexto($nombre);
        $apellidoNormalizado = $this->normalizarTexto($apellido);

        return Profesor::all()
            ->filter(function ($profesor) use ($nombreCompleto, $nombreNormalizado, $apellidoNormalizado) {
                $profesorNombre = $this->normalizarTexto($profesor->nombre);
                $profesorApellido = $this->normalizarTexto($profesor->apellido);
                $ordenNombreApellido = trim($profesorNombre . ' ' . $profesorApellido);
                $ordenApellidoNombre = trim($profesorApellido . ' ' . $profesorNombre);

                return ($profesorNombre === $nombreNormalizado && $profesorApellido === $apellidoNormalizado)
                    || ($profesorNombre === $apellidoNormalizado && $profesorApellido === $nombreNormalizado)
                    || $ordenNombreApellido === $nombreCompleto
                    || $ordenApellidoNombre === $nombreCompleto;
            })
            ->values();
    }

    private function profesorTieneMateria($profesorIds, Materia $materia): bool
    {
        $profesorIds = collect($profesorIds)->filter()->values();

        if ($profesorIds->isEmpty()) {
            return false;
        }

        return Horario::whereIn('profesor_id', $profesorIds)
            ->where('materia_id', $materia->id)
            ->exists()
            || Materia::where('id', $materia->id)
                ->whereIn('profesor_id', $profesorIds)
                ->exists();
    }

    private function normalizarTexto(?string $texto): string
    {
        $texto = trim((string) $texto);
        $texto = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $texto) ?: $texto;
        $texto = preg_replace('/\s+/', ' ', $texto);

        return strtolower($texto);
    }

    private function actualizarProfesorEnHorario(Materia $materia, ?int $profesorId): void
    {
        $horarios = Horario::where('materia_id', $materia->id)->get();

        if ($horarios->isEmpty()) {
            $horario = new Horario();
            $horario->materia_id = $materia->id;
            $horario->carrera_id = $materia->carrera_id;
            $horario->anio_id = $materia->anio_id;
            $horario->profesor_id = $profesorId;
            $horario->duracion = 0;
            $horario->comentario = 'Asignado desde asistencia';
            $horario->save();

            return;
        }

        foreach ($horarios as $horario) {
            $horario->profesor_id = $profesorId;
            $horario->save();
        }
    }

    private function partesNombreUsuario(User $user): array
    {
        $nombrePartes = preg_split('/\s+/', trim($user->name), 2);

        return [
            $nombrePartes[0] ?? $user->name,
            $nombrePartes[1] ?? 'No informado',
        ];
    }

    private function rolDesdeUsuario(?User $user): string
    {
        if (!$user || !Schema::hasColumn('users', 'is_admin')) {
            return 'alumno';
        }

        return match ((int) $user->is_admin) {
            1 => 'admin',
            2 => 'profesor',
            default => 'alumno',
        };
    }

    private function abortUnlessAdmin(): void
    {
        if ($this->rolDesdeUsuario(Auth::user()) !== 'admin') {
            abort(403, 'Acceso denegado');
        }
    }

    private function abortUnlessDirectoraAsistencia(): void
    {
        $this->abortUnlessAdmin();

        if (!$this->esDirectoraAsistencia(Auth::user())) {
            abort(403, 'Solo la directora puede crear nuevos admins.');
        }
    }

    private function abortUnlessProfesor(): void
    {
        if ($this->rolDesdeUsuario(Auth::user()) !== 'profesor') {
            abort(403, 'Acceso denegado');
        }
    }

    private function esDirectoraAsistencia(?User $user): bool
    {
        return $user && strtolower((string) $user->email) === 'admin.asistencia@isft38.test';
    }

    private function carreraIdUsuario(?User $user): ?int
    {
        if (!$user || !Schema::hasColumn('users', 'carrera_id')) {
            return null;
        }

        return $user->carrera_id ? (int) $user->carrera_id : null;
    }

    private function materiasAdministrablesQuery(?User $user)
    {
        $query = Materia::query();

        if ($this->rolDesdeUsuario($user) === 'admin' && !$this->esDirectoraAsistencia($user)) {
            $query->where('carrera_id', $this->carreraIdUsuario($user));
        }

        return $query;
    }
}
