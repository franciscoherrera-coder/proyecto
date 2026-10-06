@extends('frontend.layout.main')

@section('content')
<style>
    .grafico-asistencia { height: 210px; position: relative; width: 210px; }
    .grafico-centro { left: 50%; position: absolute; text-align: center; top: 50%; transform: translate(-50%, -50%); }
    .grafico-centro strong { display: block; font-size: 1.7rem; }
    .indicador-estado { border: 1px solid #dee2e6; border-radius: .4rem; padding: .65rem .8rem; white-space: nowrap; }
    .punto-estado { border-radius: 50%; display: inline-block; height: 11px; margin-right: .4rem; width: 11px; }
    .punto-presente { background: #198754; }
    .punto-ausente { background: #dc3545; }
    .punto-tarde { background: #ffc107; }
    .punto-justificado { background: #0dcaf0; }
</style>

<main class="asistencia-page">
    <div class="container py-5">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        <div class="mb-4">
            <a class="btn btn-outline-secondary" href="{{ $volverUrl }}">&larr; {{ $volverTexto }}</a>
        </div>

        <section class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <span class="text-uppercase text-muted fw-bold small">Detalle de asistencia · {{ $materia->descripcion }}</span>
                <h1 class="h2 mt-2 mb-1">{{ $registro->apellido }}, {{ $registro->nombre }}</h1>
                <p class="mb-0 text-muted">DNI {{ $registro->dni }} · {{ $registro->email }}</p>
            </div>
        </section>

        <section class="card shadow-sm border-0 mb-4">
            <div class="card-body p-4">
                <div class="row align-items-center g-4">
                    <div class="col-md-4 d-flex justify-content-center">
                        <div class="grafico-asistencia">
                            <canvas id="grafico_asistencia"></canvas>
                            <div class="grafico-centro">
                                <strong>{{ number_format($estadisticas['porcentaje'], 1, ',', '.') }}%</strong>
                                <span>asistencia</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            <span class="indicador-estado"><i class="punto-estado punto-presente"></i>Presentes: <strong>{{ $estadisticas['presentes'] }}</strong></span>
                            <span class="indicador-estado"><i class="punto-estado punto-ausente"></i>Ausentes: <strong>{{ $estadisticas['ausentes'] }}</strong></span>
                            <span class="indicador-estado"><i class="punto-estado punto-tarde"></i>Tardanzas: <strong>{{ $estadisticas['tardanzas'] }}</strong></span>
                            <span class="indicador-estado"><i class="punto-estado punto-justificado"></i>Justificadas: <strong>{{ $estadisticas['justificadas'] }}</strong></span>
                        </div>
                        <p class="mb-2"><strong>Presencias computadas:</strong> {{ number_format($estadisticas['presencias'], $estadisticas['presencias'] == floor($estadisticas['presencias']) ? 0 : 1, ',', '.') }} / {{ $estadisticas['clases_totales'] }} clases.</p>
                        <p class="mb-0"><strong>Justificadas:</strong> {{ $estadisticas['justificadas_enfermedad'] }} por enfermedad · {{ $estadisticas['justificadas_trabajo'] }} por trabajo.</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h2 class="h4 mb-3">Historial completo</h2>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Fecha</th><th>Estado</th><th>Motivo</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($historial as $asistencia)
                                @php
                                    $estado = $asistencia->estado === 'justificado' ? 'Justificado' : ucfirst($asistencia->estado);
                                @endphp
                                <tr>
                                    <td>{{ $asistencia->fecha->format('d/m/Y') }}</td>
                                    <td>{{ $estado }}</td>
                                    <td>
                                        @if ($asistencia->es_ausencia_automatica ?? false)
                                            <span class="text-muted">Sin registro (computada automáticamente)</span>
                                        @else
                                            {{ $asistencia->motivo_justificacion ? ucfirst($asistencia->motivo_justificacion) : '—' }}
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted py-4">No hay asistencias registradas.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>
</main>

@php
    $datosGrafico = [
        $estadisticas['presentes'],
        $estadisticas['ausentes'],
        $estadisticas['tardanzas'],
        $estadisticas['justificadas'],
    ];
@endphp
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('grafico_asistencia');
    if (!canvas || typeof Chart === 'undefined') return;

    new Chart(canvas, {
        type: 'doughnut',
        data: {
            labels: ['Presentes', 'Ausentes', 'Tardanzas', 'Justificadas'],
            datasets: [{
                data: @json($datosGrafico),
                backgroundColor: ['#198754', '#dc3545', '#ffc107', '#0dcaf0'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '66%',
            maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { enabled: true } }
        }
    });
});
</script>
@endsection
