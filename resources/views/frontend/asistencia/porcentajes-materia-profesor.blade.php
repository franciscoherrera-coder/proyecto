@extends('frontend.layout.main')

@section('content')
<style>
    .tabla-porcentajes th { font-size: .78rem; text-transform: uppercase; white-space: nowrap; }
    .tabla-porcentajes td { vertical-align: middle; }
    .barra-porcentaje { height: 5px; }
    .alumno-enlace { font-weight: 700; }
    .filtro-porcentaje {
        align-items: flex-end;
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: .375rem;
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        margin-bottom: 16px;
        padding: 16px;
    }
    .filtro-porcentaje-campo { max-width: 280px; width: 100%; }
    .filtro-porcentaje-resultado { color: #6c757d; flex-basis: 100%; margin: 0; }

    @media (max-width: 576px) {
        .filtro-porcentaje > button,
        .filtro-porcentaje-campo { max-width: none; width: 100%; }
    }
</style>

<main class="asistencia-page">
    <div class="container py-5">
        <div class="mb-4">
            <a class="btn btn-outline-secondary" href="{{ route('asistencia.profesor.materia', $materia) }}">&larr; Volver a la materia</a>
        </div>

        <section class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <span class="text-uppercase text-muted fw-bold small">Porcentaje de asistencias</span>
                <h1 class="h2 mt-2 mb-1">{{ $materia->descripcion }}</h1>
                <p class="mb-0 text-muted">
                    {{ $materia->deCarrera->descripcion ?? 'Sin carrera' }} · Año {{ $materia->deAnio->anio ?? $materia->deAnio->descripcion ?? 'Sin año' }}
                </p>
            </div>
        </section>

        <section class="card shadow-sm border-0">
            <div class="card-body p-4">
                <form id="form-filtro-porcentaje" class="filtro-porcentaje">
                    <div class="filtro-porcentaje-campo">
                        <label class="form-label fw-bold" for="filtro-porcentaje-maximo">Porcentaje máximo de asistencia</label>
                        <div class="input-group">
                            <input id="filtro-porcentaje-maximo" class="form-control" type="number" min="1" max="100" step="1" inputmode="numeric" placeholder="Ej.: 75" required>
                            <span class="input-group-text">%</span>
                        </div>
                        <div class="form-text">Se mostrarán los alumnos que estén por debajo del porcentaje ingresado.</div>
                    </div>
                    <button class="btn btn-primary" type="submit">Aplicar filtro</button>
                    <button id="limpiar-filtro-porcentaje" class="btn btn-outline-secondary" type="button">Mostrar todos</button>
                    <p id="resultado-filtro-porcentaje" class="filtro-porcentaje-resultado" aria-live="polite">
                        Mostrando los {{ $porcentajes->count() }} alumno(s) de la materia.
                    </p>
                </form>

                <div class="table-responsive border rounded">
                    <table class="table table-bordered tabla-porcentajes mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Alumno</th>
                                <th>Porcentaje</th>
                                <th>Presencias / clases</th>
                                <th>Justificadas</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($porcentajes as $resultado)
                                <tr class="js-fila-porcentaje" data-porcentaje="{{ $resultado['porcentaje'] }}">
                                    <td class="py-2">
                                        <a class="alumno-enlace" href="{{ route('asistencia.profesor.materia.porcentajes.alumno', [$materia, $resultado['alumno']]) }}">
                                            {{ $resultado['alumno']->apellido }}, {{ $resultado['alumno']->nombre }}
                                        </a>
                                        <div class="small text-muted">DNI {{ $resultado['alumno']->dni }}</div>
                                    </td>
                                    <td class="py-2">
                                        <strong>{{ number_format($resultado['porcentaje'], 1, ',', '.') }}%</strong>
                                        <div class="progress barra-porcentaje mt-1">
                                            <div class="progress-bar" role="progressbar" style="width: {{ min(100, $resultado['porcentaje']) }}%" aria-valuenow="{{ $resultado['porcentaje'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </td>
                                    <td class="py-2">
                                        {{ number_format($resultado['presencias'], $resultado['presencias'] == floor($resultado['presencias']) ? 0 : 1, ',', '.') }} / {{ $resultado['clases_totales'] }}
                                    </td>
                                    <td class="py-2">
                                        <strong>Total: {{ $resultado['faltas_justificadas'] }}</strong>
                                        <div class="small text-muted">Enfermedad: {{ $resultado['justificadas_enfermedad'] }} · Trabajo: {{ $resultado['justificadas_trabajo'] }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">Todavía no hay alumnos asignados a esta materia.</td></tr>
                            @endforelse
                            @if ($porcentajes->isNotEmpty())
                                <tr id="sin-resultados-porcentaje" class="d-none">
                                    <td colspan="4" class="text-center text-muted py-4">No hay alumnos por debajo de ese porcentaje.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.getElementById('form-filtro-porcentaje');
        const input = document.getElementById('filtro-porcentaje-maximo');
        const clearButton = document.getElementById('limpiar-filtro-porcentaje');
        const resultMessage = document.getElementById('resultado-filtro-porcentaje');
        const rows = document.querySelectorAll('.js-fila-porcentaje');
        const emptyRow = document.getElementById('sin-resultados-porcentaje');

        function showAllStudents() {
            rows.forEach(function (row) {
                row.classList.remove('d-none');
            });

            if (emptyRow) {
                emptyRow.classList.add('d-none');
            }

            resultMessage.textContent = 'Mostrando los ' + rows.length + ' alumno(s) de la materia.';
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            if (!input.checkValidity()) {
                input.reportValidity();
                return;
            }

            const limit = Number(input.value);
            let visibleStudents = 0;

            rows.forEach(function (row) {
                const isVisible = Number(row.dataset.porcentaje) < limit;
                row.classList.toggle('d-none', !isVisible);
                if (isVisible) {
                    visibleStudents++;
                }
            });

            if (emptyRow) {
                emptyRow.classList.toggle('d-none', visibleStudents > 0);
            }

            resultMessage.textContent = visibleStudents > 0
                ? 'Mostrando ' + visibleStudents + ' alumno(s) con menos de ' + limit + '% de asistencia.'
                : 'No hay alumnos con menos de ' + limit + '% de asistencia.';
        });

        clearButton.addEventListener('click', function () {
            input.value = '';
            showAllStudents();
            input.focus();
        });
    });
</script>
@endsection
