<?php

namespace App\Http\Controllers;

use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Registro;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class AsistenciaController extends Controller
{
    public function index()
    {
        $tieneTablaAsignaciones = Schema::hasTable('materia_registro');
        $materiasQuery = Materia::with(['deCarrera', 'deAnio', 'deProfesor'])
            ->orderBy('carrera_id')
            ->orderBy('anio_id')
            ->orderBy('orden');

        if ($tieneTablaAsignaciones) {
            $materiasQuery->with('alumnos');
        }

        return view('frontend.asistencia.index', [
            'materias' => $materiasQuery->get(),
            'profesores' => Profesor::orderBy('apellido')->orderBy('nombre')->get(),
            'alumnos' => Registro::orderBy('apellido')->orderBy('nombre')->get(),
            'tieneTablaAsignaciones' => $tieneTablaAsignaciones,
        ]);
    }

    public function asignarProfesor(Request $request)
    {
        $request->merge([
            'profesor_id' => $request->input('profesor_id') ?: null,
        ]);

        $data = $request->validate([
            'materia_id' => ['required', 'exists:materias,id'],
            'profesor_id' => ['nullable', 'exists:profesors,id'],
        ]);

        $materia = Materia::findOrFail($data['materia_id']);
        $materia->profesor_id = $data['profesor_id'] ?? null;
        $materia->save();

        return redirect()
            ->route('asistencia.index', ['rol' => 'admin'])
            ->with('status', 'Se actualizo el profesor de ' . $materia->descripcion . '.');
    }

    public function asignarAlumno(Request $request)
    {
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
}
