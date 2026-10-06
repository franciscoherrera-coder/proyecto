@extends('frontend.layout.main')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    .planilla-asistencia th { white-space: nowrap; }
    .planilla-asistencia .alumno { min-width: 220px; }
    .planilla-asistencia .estado { min-width: 145px; }
    .solo-impresion { display: none; }
    #qr_asistencia { background: #fff; border: 1px solid #dee2e6; border-radius: .35rem; padding: 8px; }
    #qr_asistencia:empty { display: none; }
    #qr_asistencia img, #qr_asistencia canvas { height: 125px !important; width: 125px !important; }
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
                            <label class="form-label" for="fecha_planilla">Fecha de cursada</label>
                            <input
                                id="fecha_planilla"
                                class="form-control"
                                type="text"
                                name="fecha"
                                value="{{ $fecha }}"
                                required
                            >
                            <div class="form-text">
                                Cursada: {{ $diasPermitidos->map(fn ($dia) => ucfirst($nombresDias[$dia]))->join(', ') }}.
                            </div>
                        </div>
                        <button class="btn btn-outline-secondary" type="submit">Consultar</button>
                    </form>
                    <p class="d-none d-print-block mb-0"><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}</p>
                </div>

                @if ($planillaEditable)
                    <section id="panel_qr_asistencia" class="qr-asistencia card shadow-sm border-0 no-imprimir p-3 mb-4">
                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <div id="qr_asistencia" aria-label="Código QR para registrar asistencia"></div>
                            <div>
                                <h2 class="h5 mb-2">
                                    Registrar asistencia con QR
                                    @if ($codigoQr)
                                        <span class="badge {{ $codigoQr->habilitado ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $codigoQr->habilitado ? 'Habilitado' : 'Inhabilitado' }}</span>
                                        <span class="badge text-bg-primary">{{ $codigoQr->tipo === 'tarde' ? 'Tardanza' : 'Presente' }}</span>
                                    @endif
                                </h2>
                                <p class="text-muted mb-3">Cada código es exclusivo de esta materia y de la fecha de hoy.</p>
                                <div class="d-flex flex-wrap align-items-end gap-2">
                                    <form class="d-flex flex-wrap align-items-end gap-2" method="POST" action="{{ route('asistencia.profesor.materia.qr.generar', $materia) }}">
                                        @csrf
                                        <input type="hidden" name="fecha" value="{{ $fecha }}">
                                        <div>
                                            <label class="form-label mb-1" for="tipo_qr">Tipo de registro</label>
                                            <select id="tipo_qr" class="form-select" name="tipo">
                                                <option value="presente" {{ optional($codigoQr)->tipo === 'presente' ? 'selected' : '' }}>Presente</option>
                                                <option value="tarde" {{ optional($codigoQr)->tipo === 'tarde' ? 'selected' : '' }}>Tardanza</option>
                                            </select>
                                        </div>
                                        <button class="btn btn-primary" type="submit">Generar uno nuevo</button>
                                    </form>
                                    @if ($codigoQr)
                                        <form method="POST" action="{{ route('asistencia.profesor.materia.qr.estado', [$materia, $codigoQr]) }}">
                                            @csrf
                                            @method('PATCH')
                                            <button class="btn {{ $codigoQr->habilitado ? 'btn-outline-danger' : 'btn-outline-success' }}" type="submit">
                                                {{ $codigoQr->habilitado ? 'Inhabilitar' : 'Habilitar' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </section>
                @endif

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
                                            <select class="form-select js-estado-asistencia control-pantalla" name="asistencias[{{ $alumno->id }}][estado]" required {{ $planillaEditable ? '' : 'disabled' }}>
                                                @foreach (['presente' => 'Presente', 'ausente' => 'Ausente', 'tarde' => 'Tarde', 'justificado' => 'Justificado'] as $valor => $etiqueta)
                                                    <option value="{{ $valor }}" {{ $estadoSeleccionado === $valor ? 'selected' : '' }}>{{ $etiqueta }}</option>
                                                @endforeach
                                            </select>
                                            <div class="js-motivo-justificacion control-pantalla mt-2 {{ $estadoSeleccionado === 'justificado' ? '' : 'd-none' }}">
                                                <div class="form-check">
                                                    <input class="form-check-input" id="enfermedad_{{ $alumno->id }}" type="radio" name="asistencias[{{ $alumno->id }}][motivo_justificacion]" value="enfermedad" {{ $motivoSeleccionado === 'enfermedad' ? 'checked' : '' }} {{ $planillaEditable ? '' : 'disabled' }}>
                                                    <label class="form-check-label" for="enfermedad_{{ $alumno->id }}">Enfermedad</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" id="trabajo_{{ $alumno->id }}" type="radio" name="asistencias[{{ $alumno->id }}][motivo_justificacion]" value="trabajo" {{ $motivoSeleccionado === 'trabajo' ? 'checked' : '' }} {{ $planillaEditable ? '' : 'disabled' }}>
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

                    @if ($alumnos->isNotEmpty() && $planillaEditable)
                        <div class="no-imprimir d-flex justify-content-end mt-3">
                            <button class="btn btn-primary" type="submit">{{ $planillaCerrada ? 'Guardar cambios' : 'Guardar asistencia del día' }}</button>
                        </div>
                    @endif
                </form>
            </div>
        </section>
    </div>
</main>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
@if ($qrAsistenciaUrl)
<script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
@endif
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if ($planillaEditable)
    const qrPanel = document.getElementById('panel_qr_asistencia');
    if (qrPanel) {
        const planillaCard = qrPanel.parentElement.closest('.card');
        if (planillaCard) planillaCard.parentElement.insertBefore(qrPanel, planillaCard);
    }

    const qrContainer = document.getElementById('qr_asistencia');
    if (qrContainer && typeof QRCode !== 'undefined') {
        new QRCode(qrContainer, {
            text: @json($qrAsistenciaUrl),
            width: 125,
            height: 125,
            correctLevel: QRCode.CorrectLevel.H
        });
    }
    @endif

    flatpickr('#fecha_planilla', {
        altInput: true,
        altFormat: 'd/m/Y',
        dateFormat: 'Y-m-d',
        defaultDate: @json($fecha),
        enable: @json($fechasDisponibles->pluck('valor')->values()),
        locale: 'es',
        disableMobile: true,
        allowInput: false
    });

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
