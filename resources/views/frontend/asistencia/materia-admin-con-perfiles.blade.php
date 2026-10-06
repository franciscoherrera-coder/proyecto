@extends('frontend.layout.main')

@section('content')
<main class="asistencia-page">
    <div class="container py-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <button class="btn btn-outline-secondary" type="button" onclick="window.close()">Cerrar ventana</button>
            <a class="btn btn-outline-primary" href="{{ route('asistencia.index') }}">Ir a Asistencia</a>
        </div>

        <section class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <span class="text-uppercase text-muted fw-bold small">Información de la materia</span>
                <h1 class="h2 mt-2 mb-4">{{ $materia->descripcion }}</h1>
                <dl class="row mb-0">
                    <dt class="col-sm-3">Carrera</dt>
                    <dd class="col-sm-9">{{ $materia->deCarrera->descripcion ?? 'Sin carrera' }}</dd>
                    <dt class="col-sm-3">Año</dt>
                    <dd class="col-sm-9">{{ $materia->deAnio->anio ?? $materia->deAnio->descripcion ?? 'Sin año' }}</dd>
                    <dt class="col-sm-3">Profesor</dt>
                    <dd class="col-sm-9">
                        {{ $materia->horario && $materia->horario->profesor
                            ? $materia->horario->profesor->apellido . ', ' . $materia->horario->profesor->nombre
                            : 'Sin profesor' }}
                    </dd>
                    <dt class="col-sm-3">Alumnos asignados</dt>
                    <dd class="col-sm-9">{{ $alumnos->count() }}</dd>
                </dl>
            </div>
        </section>

        <section class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Listado de alumnos</h2>
                @if ($tieneTablaAsignaciones)
                    <div class="table-responsive border rounded">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr><th>#</th><th>Apellido y nombre</th><th>DNI</th><th>Correo electrónico</th></tr>
                            </thead>
                            <tbody>
                                @forelse ($alumnos as $indice => $alumno)
                                    <tr>
                                        <td>{{ $indice + 1 }}</td>
                                        <td>
                                            <a class="text-decoration-none" href="{{ route('asistencia.admin.materia.alumno', [$materia, $alumno]) }}">
                                                {{ $alumno->apellido }}, {{ $alumno->nombre }}
                                            </a>
                                        </td>
                                        <td>{{ $alumno->dni }}</td>
                                        <td>{{ $alumno->email }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Sin alumnos asignados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">La tabla de asignaciones todavía no está disponible.</div>
                @endif
            </div>
        </section>
    </div>
</main>
@endsection
