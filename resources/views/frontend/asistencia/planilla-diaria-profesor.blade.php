@extends('frontend.layout.main')

@section('content')
<style>
    .planilla-asistencia th { white-space: nowrap; }
    .planilla-asistencia .alumno { min-width: 220px; }
    .planilla-asistencia .estado { min-width: 145px; }
    .solo-impresion { display: none; }
    @media print {
        @page { margin: 12mm; size: auto; }
        html, body { background: #fff !important; height: auto !important; margin: 0 !important; padding: 0 !important; }
        body > nav, body > .footer, .navbar, .footer, footer, .no-imprimir, .no-imprimir-planilla { display: none !important; }
        .asistencia-page, .asistencia-page .container, .card, .card-body, form, .table-responsive {
            background: #fff !important;
            border: 0 !important;
            box-shadow: none !important;
            margin: 0 !important;
            max-width: none !important;
            overflow: visible !important;
            padding: 0 !important;
        }
        .solo-impresion { display: inline !important; }
        .control-pantalla { display: none !important; }
        .planilla-asistencia { font-size: 11pt; margin-top: 8mm !important; width: 100% !important; }
        .planilla-asistencia th, .planilla-asistencia td { padding: 5px 7px !important; }
        .planilla-asistencia tr { break-inside: avoid; page-break-inside: avoid; }
        .encabezado-planilla { margin-bottom: 0 !important; }
    }
</style>

<main class="asistencia-page">
    <div class="container py-5">
        <div class="no-imprimir d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <a class="btn btn-outline-secondary" href="{{ route('asistencia.profesor.materia', $materia) }}">&larr; Volver a la materia</a>
            <button class="btn btn-outline-primary" type="button" onclick="window.print()">Imprimir planilla</button>
        </div>

        @if (session('status'))
            <div class="alert alert-success no-imprimir">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger no-imprimir"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        @if ($planillaCerrada)
            <div class="alert alert-info no-imprimir">Esta planilla ya fue guardada y es de solo lectura.</div>
        @endif

        <section class="card shadow-sm border-0">
            <div class="card-body p-4">
                <div class="encabezado-planilla d-flex flex-wrap align-items-start justify-content-between gap-3 mb-4">
                    <div>
                        <span class="text-uppercase text-muted fw-bold small no-imprimir-planilla">Planilla diaria de asistencia</span>
                        <h1 class="h3 mt-2 mb-1">{{ $materia->descripcion }}</h1>
                        <p class="text-muted mb-0 no-imprimir-planilla">
                            {{ $materia->deCarrera->descripcion ?? 'Sin carrera' }}
                            · Año {{ $materia->deAnio->anio ?? $materia->deAnio->descripcion ?? 'Sin informar' }}
                        </p>
                    </div>

                    <form class="no-imprimir d-flex align-items-end gap-2" method="GET" action="{{ route('asistencia.profesor.materia.planilla', $materia) }}">
                        <div>
                            <label class="form-label" for="fecha_planilla">Fecha</label>
                            <input id="fecha_planilla" class="form-control" type="date" name="fecha" value="{{ $fecha }}" max="{{ now()->toDateString() }}" required>
                        </div>
                        <button class="btn btn-outline-secondary" type="submit">Consultar</button>
                    </form>
                    <p class="d-none d-print-block mb-0"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</p>
                </div>

                <form method="POST" action="{{ route('asistencia.profesor.materia.planilla.guardar', $materia) }}">
                    @csrf
                    <input type="hidden" name="fecha" value="{{ $fecha }}">

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle planilla-asistencia">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th class="alumno">Apellido y nombre</th>
                                    <th>DNI</th>
                                    <th class="estado">Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alumnos as $indice => $alumno)
                                    @php($asistencia = $asistencias->get($alumno->id))
                                    <tr>
                                        <td>{{ $indice + 1 }}</td>
                                        <td><strong>{{ $alumno->apellido }}, {{ $alumno->nombre }}</strong></td>
                                        <td>{{ $alumno->dni }}</td>
                                        <td>
                                            @php($estadoSeleccionado = old("asistencias.{$alumno->id}.estado", $asistencia->estado ?? 'presente'))
                                            @php($motivoSeleccionado = old("asistencias.{$alumno->id}.motivo_justificacion", $asistencia->motivo_justificacion ?? ''))
                                            <span class="solo-impresion">
                                                {{ ucfirst($estadoSeleccionado) }}@if($estadoSeleccionado === 'justificado' && $motivoSeleccionado) ({{ ucfirst($motivoSeleccionado) }})@endif
                                            </span>
                                            <select class="form-select js-estado-asistencia control-pantalla" name="asistencias[{{ $alumno->id }}][estado]" required {{ $planillaCerrada ? 'disabled' : '' }}>
                                                @foreach (['presente' => 'Presente', 'ausente' => 'Ausente', 'tarde' => 'Tarde', 'justificado' => 'Justificado'] as $valor => $etiqueta)
                                                    <option value="{{ $valor }}" {{ $estadoSeleccionado === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                                @endforeach
                                            </select>
                                            <div class="js-motivo-justificacion control-pantalla mt-2 {{ $estadoSeleccionado === 'justificado' ? '' : 'd-none' }}">
                                                <div class="form-check">
                                                    <input class="form-check-input" id="enfermedad_{{ $alumno->id }}" type="radio" name="asistencias[{{ $alumno->id }}][motivo_justificacion]" value="enfermedad" {{ $motivoSeleccionado === 'enfermedad' ? 'checked' : '' }} {{ $planillaCerrada ? 'disabled' : '' }}>
                                                    <label class="form-check-label" for="enfermedad_{{ $alumno->id }}">Enfermedad</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" id="trabajo_{{ $alumno->id }}" type="radio" name="asistencias[{{ $alumno->id }}][motivo_justificacion]" value="trabajo" {{ $motivoSeleccionado === 'trabajo' ? 'checked' : '' }} {{ $planillaCerrada ? 'disabled' : '' }}>
                                                    <label class="form-check-label" for="trabajo_{{ $alumno->id }}">Trabajo</label>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">No hay alumnos asignados a esta materia.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if ($alumnos->isNotEmpty() && !$planillaCerrada)
                        <div class="no-imprimir d-flex justify-content-end mt-3">
                            <button class="btn btn-primary" type="submit">Guardar asistencia del día</button>
                        </div>
                    @endif
                </form>
            </div>
        </section>
    </div>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-estado-asistencia').forEach(function (select) {
        select.addEventListener('change', function () {
            const motivos = select.parentElement.querySelector('.js-motivo-justificacion');
            const esJustificado = select.value === 'justificado';
            motivos.classList.toggle('d-none', !esJustificado);
            motivos.querySelectorAll('input[type="radio"]').forEach(function (radio) {
                radio.required = esJustificado;
                if (!esJustificado) radio.checked = false;
            });
        });
    });
});
</script>
@endsection
