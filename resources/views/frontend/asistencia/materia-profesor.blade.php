@extends('frontend.layout.main')

@section('content')
<style>
    .alumnos-lista { max-height: 500px; }
    .alumno-fila { cursor: pointer; font-size: .9rem; line-height: 1.2; min-height: 42px; padding: 6px 12px !important; }
    .alumno-fila:hover { background: #f8f9fa; }
    .alumno-fila .form-check-input { flex: 0 0 auto; height: 1rem; margin: 0; width: 1rem; }
    .alumno-detalle { color: #6c757d; font-size: .82rem; white-space: nowrap; }
    .alumno-fila .d-block { display: inline !important; font-size: .82rem; margin-left: 8px; white-space: nowrap; }
    @media (max-width: 576px) {
        .alumno-detalle { display: block; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; }
    }

    .sumar-alumnos-details > summary {
        cursor: pointer;
        list-style: none;
    }

    .sumar-alumnos-details > summary::-webkit-details-marker {
        display: none;
    }

    .sumar-alumnos-details > summary::after {
        content: 'Mostrar';
        font-size: .85rem;
        margin-left: 8px;
    }

    .sumar-alumnos-details[open] > summary::after {
        content: 'Ocultar';
    }
</style>
<main class="asistencia-page">
    <div class="container py-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <a class="btn btn-outline-secondary" href="{{ route('asistencia.index') }}">&larr; Volver a mis materias</a>
            <form action="{{ route('asistencia.logout') }}" method="POST" class="m-0">
                @csrf
                <button class="btn btn-outline-secondary" type="submit">Cerrar sesión</button>
            </form>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <section class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <span class="text-uppercase text-muted fw-bold small">{{ $materia->deCarrera->descripcion ?? 'Sin carrera' }}</span>
                <h1 class="h2 mt-2">{{ $materia->descripcion }}</h1>
                <p class="mb-0 text-muted">
                    Año: {{ $materia->deAnio->anio ?? $materia->deAnio->descripcion ?? 'Sin año' }}
                    @if ($materia->horario && $materia->horario->profesor)
                        · Profesor: {{ $materia->horario->profesor->apellido }}, {{ $materia->horario->profesor->nombre }}
                    @endif
                </p>
            </div>
        </section>

        @if (!$tieneTablaAsignaciones)
            <div class="alert alert-warning">Falta ejecutar la migración de asignaciones para poder sumar alumnos.</div>
        @else
            <details class="sumar-alumnos-details">
                <summary class="btn btn-outline-primary mb-3">Sumar alumnos</summary>
            <section class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Alumnos disponibles</h2>
                            <p class="text-muted mb-0">Alumnos registrados en {{ $materia->deCarrera->descripcion ?? 'esta carrera' }}.</p>
                        </div>
                        <span class="badge text-bg-secondary">{{ $alumnosAsignados->count() }} ya asignado(s)</span>
                    </div>

                    <label class="form-label" for="buscar_alumno_materia">Buscar alumno</label>
                    <input id="buscar_alumno_materia" class="form-control mb-3" type="search" placeholder="Nombre, apellido, DNI o correo electrónico" autocomplete="off">

                    <form action="{{ route('asistencia.profesor.alumnos') }}" method="POST">
                        @csrf
                        <input type="hidden" name="materia_id" value="{{ $materia->id }}">
                        <div class="alumnos-lista border rounded overflow-auto">
                            @forelse ($alumnosCarrera as $alumno)
                                <label class="alumno-fila d-flex align-items-center gap-2 border-bottom mb-0 js-alumno-materia" data-search="{{ $alumno->apellido }} {{ $alumno->nombre }} {{ $alumno->dni }} {{ $alumno->email }}">
                                    <input class="form-check-input m-0" type="checkbox" name="registro_ids[]" value="{{ $alumno->id }}">
                                    <span class="flex-grow-1"><strong>{{ $alumno->apellido }}, {{ $alumno->nombre }}</strong><span class="d-block small text-muted">DNI {{ $alumno->dni }} · {{ $alumno->email }}</span></span>
                                </label>
                            @empty
                                <p class="p-3 mb-0 text-muted">No hay alumnos registrados en esta carrera.</p>
                            @endforelse
                            <p id="sin_resultados_alumnos" class="p-3 mb-0 text-muted d-none">No se encontraron alumnos.</p>
                        </div>
                        <button class="btn btn-primary mt-3" type="submit">Agregar alumnos seleccionados</button>
                    </form>
                </div>
            </section>
            </details>

            <section class="card shadow-sm border-0 mt-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h2 class="h4 mb-1">Alumnos de la materia</h2>
                            <p class="text-muted mb-0">{{ $alumnosAsignados->count() }} alumno(s) asignado(s).</p>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <a class="btn btn-primary" href="{{ route('asistencia.profesor.materia.planilla', $materia) }}">Tomar asistencia diaria</a>
                            <a class="btn btn-outline-primary" target="_blank" href="{{ route('asistencia.profesor.materia.listado', $materia) }}">Generar listado imprimible</a>
                        </div>
                    </div>

                    <div class="alumnos-lista border rounded overflow-auto">
                        @forelse ($alumnosAsignados as $alumno)
                            <div class="alumno-fila d-flex align-items-center gap-2 border-bottom">
                                <a class="flex-grow-1 text-decoration-none text-dark" href="{{ route('asistencia.profesor.alumno.perfil', $alumno) }}">
                                    <strong>{{ $alumno->apellido }}, {{ $alumno->nombre }}</strong>
                                    <span class="d-block small text-muted">DNI {{ $alumno->dni }} · {{ $alumno->email }}</span>
                                </a>
                                <form action="{{ route('asistencia.profesor.materia.alumno.quitar', [$materia, $alumno]) }}" method="POST" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-outline-danger btn-sm" type="submit">Eliminar</button>
                                </form>
                            </div>
                        @empty
                            <p class="p-3 mb-0 text-muted">Todavía no hay alumnos asignados a esta materia.</p>
                        @endforelse
                    </div>
                </div>
            </section>
        @endif
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const input = document.getElementById('buscar_alumno_materia');
    const rows = document.querySelectorAll('.js-alumno-materia');
    const empty = document.getElementById('sin_resultados_alumnos');
    if (!input) return;
    input.addEventListener('input', function () {
        const normalize = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        const query = normalize(input.value.trim());
        let visible = 0;
        rows.forEach(function (row) {
            const matches = !query || normalize(row.dataset.search).includes(query);
            row.classList.toggle('d-none', !matches);
            if (matches) visible++;
        });
        if (empty) empty.classList.toggle('d-none', visible > 0);
    });
});
</script>
@endsection
