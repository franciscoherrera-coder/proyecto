<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Horario;
use App\Models\AsistenciaDiaria;
use App\Models\Registro;
use App\Models\Carrera;
use App\Models\Anio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AsistenciaController extends Controller
{
    public function index()
    {
        $tieneTablaAsignaciones = Schema::hasTable('materia_registro');
        $usuarioActual = Auth::user();
        $rolUsuario = $this->rolDesdeUsuario(Auth::user());
        $adminPuedeCrearAdmins = $this->esDirectoraAsistencia($usuarioActual);
        $carreraAdminId = $this->carreraIdUsuario($usuarioActual);
        $materiaIdsDesdeHorarios = Horario::whereNotNull('materia_id')
            ->pluck('materia_id')
            ->unique();
        $materiasQuery = Materia::with(['deCarrera', 'deAnio', 'horario.profesor'])
            ->whereIn('id', $materiaIdsDesdeHorarios)
            ->orderBy('carrera_id')
            ->orderBy('anio_id')
            ->orderBy('orden');

        if ($tieneTablaAsignaciones) {
            $materiasQuery->with('alumnos');
        }

        $emailsSinCarrera = Registro::whereNull('carrera_id')->pluck('email');
        $alumnosPorCarrera = Registro::with('carrera')
            ->when(!$adminPuedeCrearAdmins, function ($query) use ($carreraAdminId) {
                $query->where('carrera_id', $carreraAdminId);
            })
            ->orderBy('carrera_id')
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();
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
        $materiasAlumno = collect();

        if ($rolUsuario === 'profesor') {
            $profesorIdsUsuario = $this->profesoresDesdeUsuario(Auth::user())->pluck('id');
            $materiaIdsProfesor = collect();

            if ($profesorIdsUsuario->isNotEmpty()) {
                $materiaIdsProfesor = Horario::whereIn('profesor_id', $profesorIdsUsuario)
                    ->whereNotNull('materia_id')
                    ->pluck('materia_id')
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

        if ($rolUsuario === 'alumno' && $tieneTablaAsignaciones) {
            $registroAlumno = $this->registroDesdeUsuario($usuarioActual);
            if ($registroAlumno) {
                $materiasAlumno = $registroAlumno->materias()
                    ->with(['deCarrera', 'deAnio', 'horario.profesor'])
                    ->whereIn('materias.id', $materiaIdsDesdeHorarios)
                    ->orderBy('carrera_id')
                    ->orderBy('anio_id')
                    ->orderBy('orden')
                    ->get();
            }
        }

        return view('frontend.asistencia.index', [
            'materias' => $materiasQuery->get(),
            'materiasAdministrablesIds' => $this->materiasAdministrablesQuery($usuarioActual)->pluck('id'),
            'materiasProfesor' => $materiasProfesor,
            'materiasAlumno' => $materiasAlumno,
            'profesores' => $profesoresDesdeHorarios,
            'alumnos' => Registro::orderBy('apellido')->orderBy('nombre')->get(),
            'alumnosPorCarrera' => $alumnosPorCarrera,
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
            'identificador' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ]);

        $identificador = trim($credentials['identificador']);
        $campoLogin = Schema::hasColumn('users', 'dni') && preg_match('/^\d+$/', $identificador)
            ? 'dni'
            : 'email';

        if (!Auth::attempt([$campoLogin => $identificador, 'password' => $credentials['password']], $request->boolean('remember'))) {
            return back()
                ->withErrors(['identificador' => 'El DNI o correo electrónico, o la contraseña no son correctos.'])
                ->withInput($request->only('identificador'));
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

    public function crearAlumno(Request $request)
    {
        $this->abortUnlessAdmin();
        $data = $this->validarDatosAlumno($request);
        $this->asegurarCarreraAdministrable((int) $data['carrera_id']);

        DB::transaction(function () use ($data) {
            $registro = new Registro();
            $this->guardarDatosAlumno($registro, $data);
            $registro->save();
            $this->crearUsuarioAcceso(
                trim($data['nombre'] . ' ' . $data['apellido']),
                $data['email'],
                $data['password'],
                'alumno',
                (int) $data['carrera_id'],
                (int) $data['dni']
            );
        });

        return redirect()
            ->route('asistencia.index', ['admin_tab' => 'alumnos'])
            ->with('status', 'Alumno creado y vinculado al acceso mediante su DNI.');
    }

    public function actualizarAlumno(Request $request, Registro $registro)
    {
        $this->abortUnlessAdmin();
        $this->asegurarAlumnoAdministrable($registro);
        $data = $this->validarDatosAlumno($request, $registro);
        $this->asegurarCarreraAdministrable((int) $data['carrera_id']);

        DB::transaction(function () use ($registro, $data) {
            $dniAnterior = $registro->dni;
            $this->guardarDatosAlumno($registro, $data);
            $registro->save();

            $usuario = User::where('dni', $dniAnterior)->first();
            if ($usuario) {
                $usuario->name = trim($data['nombre'] . ' ' . $data['apellido']);
                $usuario->email = $data['email'];
                $usuario->dni = $data['dni'];
                $usuario->carrera_id = $data['carrera_id'];
                if (!empty($data['password'])) {
                    $usuario->password = Hash::make($data['password']);
                }
                $usuario->save();
            }
        });

        return redirect()
            ->route('asistencia.index', ['admin_tab' => 'alumnos'])
            ->with('status', 'Alumno actualizado correctamente.');
    }

    public function eliminarAlumno(Registro $registro)
    {
        $this->abortUnlessAdmin();
        $this->asegurarAlumnoAdministrable($registro);

        DB::transaction(function () use ($registro) {
            User::where('dni', $registro->dni)->delete();
            $registro->delete();
        });

        return redirect()
            ->route('asistencia.index', ['admin_tab' => 'alumnos'])
            ->with('status', 'Alumno y acceso asociado eliminados correctamente.');
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

        if ($data['rol'] === 'alumno' && Schema::hasColumn('users', 'dni') && User::where('dni', $data['dni'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'dni' => 'El DNI ya está asociado a otro acceso.',
            ]);
        }

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
            $data['carrera_id'] ?? null,
            $data['rol'] === 'alumno' ? (int) $data['dni'] : null
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

        $cantidadAlumnosDeLaCarrera = Registro::whereIn('id', $data['registro_ids'])
            ->where('carrera_id', $materia->carrera_id)
            ->count();

        if ($cantidadAlumnosDeLaCarrera !== count($data['registro_ids'])) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'registro_ids' => 'Solo podés agregar alumnos registrados en la carrera de esta materia.',
            ]);
        }

        $materia->alumnos()->syncWithoutDetaching($data['registro_ids']);

        return redirect()
            ->route('asistencia.profesor.materia', $materia)
            ->with('status', 'Se agregaron ' . count($data['registro_ids']) . ' alumno(s) a ' . $materia->descripcion . '.');
    }

    public function verMateriaProfesor(Materia $materia)
    {
        $this->abortUnlessProfesor();

        $profesores = $this->profesoresDesdeUsuario(Auth::user());
        if ($profesores->isEmpty() || !$this->profesorTieneMateria($profesores->pluck('id'), $materia)) {
            abort(403, 'No podés acceder a una materia que no tenés asignada.');
        }

        $materia->load(['deCarrera', 'deAnio', 'horario.profesor']);
        $alumnosAsignados = Schema::hasTable('materia_registro')
            ? $materia->alumnos()->orderBy('apellido')->orderBy('nombre')->get()
            : collect();
        $alumnosCarrera = Registro::where('carrera_id', $materia->carrera_id)
            ->whereNotIn('id', $alumnosAsignados->pluck('id'))
            ->orderBy('apellido')
            ->orderBy('nombre')
            ->get();

        return view('frontend.asistencia.materia-profesor', [
            'materia' => $materia,
            'alumnosCarrera' => $alumnosCarrera,
            'alumnosAsignados' => $alumnosAsignados,
            'tieneTablaAsignaciones' => Schema::hasTable('materia_registro'),
        ]);
    }

    public function quitarAlumnoProfesor(Materia $materia, Registro $registro)
    {
        $this->autorizarMateriaProfesor($materia);

        if (!$materia->alumnos()->whereKey($registro->id)->exists()) {
            abort(404, 'El alumno no está asignado a esta materia.');
        }

        $materia->alumnos()->detach($registro->id);

        return redirect()
            ->route('asistencia.profesor.materia', $materia)
            ->with('status', 'Alumno eliminado de la materia correctamente.');
    }

    public function listadoMateriaProfesor(Materia $materia)
    {
        $this->autorizarMateriaProfesor($materia);
        $materia->load(['deCarrera', 'deAnio', 'horario.profesor']);
        $alumnosAsignados = $materia->alumnos()->orderBy('apellido')->orderBy('nombre')->get();

        return view('frontend.asistencia.listado-materia-profesor', compact('materia', 'alumnosAsignados'));
    }

    public function planillaDiariaProfesor(Request $request, Materia $materia)
    {
        $this->autorizarMateriaProfesor($materia);

        $fecha = $request->validate([
            'fecha' => ['nullable', 'date', 'before_or_equal:today'],
        ])['fecha'] ?? now()->toDateString();

        $materia->load(['deCarrera', 'deAnio', 'horario.profesor']);
        $alumnos = $materia->alumnos()->orderBy('apellido')->orderBy('nombre')->get();
        $asistencias = AsistenciaDiaria::where('materia_id', $materia->id)
            ->whereDate('fecha', $fecha)
            ->get()
            ->keyBy('registro_id');
        $planillaCerrada = $asistencias->isNotEmpty();

        return view('frontend.asistencia.planilla-diaria-profesor', compact(
            'materia', 'alumnos', 'asistencias', 'fecha', 'planillaCerrada'
        ));
    }

    public function guardarPlanillaDiariaProfesor(Request $request, Materia $materia)
    {
        $this->autorizarMateriaProfesor($materia);

        $data = $request->validate([
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'asistencias' => ['nullable', 'array'],
            'asistencias.*.estado' => ['required', Rule::in(['presente', 'ausente', 'tarde', 'justificado'])],
            'asistencias.*.motivo_justificacion' => ['nullable', Rule::in(['enfermedad', 'trabajo'])],
        ]);

        if (AsistenciaDiaria::where('materia_id', $materia->id)->whereDate('fecha', $data['fecha'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'fecha' => 'La asistencia de esta fecha ya fue guardada y no se puede modificar.',
            ]);
        }

        $asistencias = collect($data['asistencias'] ?? []);

        foreach ($asistencias as $registroId => $valores) {
            if ($valores['estado'] === 'justificado' && empty($valores['motivo_justificacion'])) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "asistencias.{$registroId}.motivo_justificacion" => 'Seleccioná si la justificación es por enfermedad o trabajo.',
                ]);
            }
        }

        $alumnoIds = $materia->alumnos()->pluck('registros.id')->map(fn ($id) => (int) $id);
        $idsRecibidos = $asistencias->keys()->map(fn ($id) => (int) $id);

        if ($idsRecibidos->diff($alumnoIds)->isNotEmpty()) {
            abort(403, 'La planilla contiene alumnos que no pertenecen a esta materia.');
        }

        DB::transaction(function () use ($materia, $data, $asistencias) {
            foreach ($asistencias as $registroId => $valores) {
                AsistenciaDiaria::create([
                    'materia_id' => $materia->id,
                    'registro_id' => (int) $registroId,
                    'fecha' => $data['fecha'],
                    'estado' => $valores['estado'],
                    'motivo_justificacion' => $valores['estado'] === 'justificado'
                        ? $valores['motivo_justificacion']
                        : null,
                ]);
            }
        });

        return redirect()
            ->route('asistencia.profesor.materia.planilla', ['materia' => $materia, 'fecha' => $data['fecha']])
            ->with('status', 'La asistencia del día se guardó correctamente.');
    }

    public function perfilAlumnoProfesor(Registro $registro)
    {
        $this->abortUnlessProfesor();
        $materiaIdsProfesor = Horario::whereIn('profesor_id', $this->profesoresDesdeUsuario(Auth::user())->pluck('id'))
            ->whereNotNull('materia_id')
            ->pluck('materia_id');

        if (!$registro->materias()->whereIn('materias.id', $materiaIdsProfesor)->exists()) {
            abort(403, 'No podés acceder a este perfil de alumno.');
        }

        $materiasAlumno = $registro->materias()
            ->with(['deCarrera', 'deAnio', 'horario.profesor'])
            ->whereIn('materias.id', Horario::whereNotNull('materia_id')->select('materia_id'))
            ->orderBy('carrera_id')
            ->orderBy('anio_id')
            ->orderBy('orden')
            ->get();

        return view('frontend.asistencia.perfil-alumno-profesor', compact('registro', 'materiasAlumno'));
    }

    public function verMateriaAlumno(Materia $materia)
    {
        if ($this->rolDesdeUsuario(Auth::user()) !== 'alumno' || !Schema::hasTable('materia_registro')) {
            abort(403, 'Acceso denegado');
        }

        $registroAlumno = $this->registroDesdeUsuario(Auth::user());
        if (!$registroAlumno || !$registroAlumno->materias()->whereKey($materia->id)->exists()) {
            abort(403, 'No estás asignado a esta materia.');
        }

        $materia->load(['deCarrera', 'deAnio', 'horario.profesor']);

        return view('frontend.asistencia.materia-alumno', compact('materia'));
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

    private function validarDatosAlumno(Request $request, ?Registro $registro = null): array
    {
        $registroId = $registro?->id;
        $reglasPassword = $registro
            ? ['nullable', 'string', 'min:8', 'confirmed']
            : ['required', 'string', 'min:8', 'confirmed'];

        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'apellido' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'integer', Rule::unique('registros', 'dni')->ignore($registroId)],
            'cuil' => ['required', 'integer', Rule::unique('registros', 'cuil')->ignore($registroId)],
            'email' => ['required', 'email', 'max:100'],
            'carrera_id' => ['required', 'exists:carreras,id'],
            'password' => $reglasPassword,
        ]);

        if (Schema::hasColumn('users', 'dni')) {
            $usuarioPorDni = User::where('dni', $data['dni'])->first();
            if ($usuarioPorDni && (!$registro || $usuarioPorDni->dni !== $registro->dni)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'dni' => 'El DNI ya está asociado a otro acceso.',
                ]);
            }
        }

        $usuarioPorEmail = User::where('email', $data['email'])->first();
        if ($usuarioPorEmail && (!$registro || $usuarioPorEmail->dni !== $registro->dni)) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'email' => 'El correo electrónico ya está asociado a otro acceso.',
            ]);
        }

        return $data;
    }

    private function guardarDatosAlumno(Registro $registro, array $data): void
    {
        $registro->nombre = $data['nombre'];
        $registro->apellido = $data['apellido'];
        $registro->dni = $data['dni'];
        $registro->cuil = $data['cuil'];
        $registro->email = $data['email'];
        $registro->carrera_id = $data['carrera_id'];
        $this->completarDatosAlumno($registro);
    }

    private function asegurarAlumnoAdministrable(Registro $registro): void
    {
        $this->asegurarCarreraAdministrable((int) $registro->carrera_id);
    }

    private function asegurarCarreraAdministrable(int $carreraId): void
    {
        if (!$this->esDirectoraAsistencia(Auth::user()) && $carreraId !== $this->carreraIdUsuario(Auth::user())) {
            abort(403, 'No podés administrar alumnos de otra carrera.');
        }
    }

    private function crearUsuarioAcceso(string $name, string $email, string $password, string $rol, ?int $carreraId = null, ?int $dni = null): User
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

        if (Schema::hasColumn('users', 'dni')) {
            $user->dni = $dni;
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

    private function registroDesdeUsuario(?User $user): ?Registro
    {
        if (!$user) {
            return null;
        }

        if (Schema::hasColumn('users', 'dni') && $user->dni) {
            return Registro::where('dni', $user->dni)->first();
        }

        return Registro::where('email', $user->email)->first();
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

        $profesorIdsDesdeHorarios = Horario::whereNotNull('profesor_id')
            ->select('profesor_id')
            ->distinct();

        return Profesor::whereIn('id', $profesorIdsDesdeHorarios)
            ->get()
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
            ->exists();
    }

    private function autorizarMateriaProfesor(Materia $materia): void
    {
        $this->abortUnlessProfesor();

        if (!Schema::hasTable('materia_registro')) {
            abort(403, 'Las asignaciones de alumnos no están disponibles.');
        }

        $profesores = $this->profesoresDesdeUsuario(Auth::user());
        if ($profesores->isEmpty() || !$this->profesorTieneMateria($profesores->pluck('id'), $materia)) {
            abort(403, 'No podés acceder a una materia que no tenés asignada.');
        }
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
        $query = Materia::whereIn('id', Horario::whereNotNull('materia_id')->select('materia_id'));

        if ($this->rolDesdeUsuario($user) === 'admin' && !$this->esDirectoraAsistencia($user)) {
            $query->where('carrera_id', $this->carreraIdUsuario($user));
        }

        return $query;
    }
}
