@extends('frontend.layout.main')

@section('content')
<style>
    @media print {
        @page { margin: 12mm; size: auto; }

        html, body {
            background: #fff !important;
            height: auto !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        body > nav, body > .footer, .navbar, .footer, footer, .no-imprimir {
            display: none !important;
        }

        .asistencia-page, .asistencia-page .container, .card, .card-body, .table-responsive {
            background: #fff !important;
            border: 0 !important;
            box-shadow: none !important;
            margin: 0 !important;
            max-width: none !important;
            overflow: visible !important;
            padding: 0 !important;
        }

        .asistencia-page section + section {
            margin-top: 5mm !important;
        }

        .informacion-materia > .card-body {
            font-size: 8.5pt !important;
        }

        .informacion-materia .small {
            font-size: 7pt !important;
        }

        .informacion-materia h1 {
            font-size: 14pt !important;
            line-height: 1.15 !important;
            margin: 2mm 0 3mm !important;
        }

        .informacion-materia dt,
        .informacion-materia dd {
            line-height: 1.15 !important;
            margin-bottom: 1.5mm !important;
        }

        .listado-alumnos {
            font-size: 9pt;
            width: 100% !important;
        }

        .listado-alumnos th, .listado-alumnos td {
            padding: 5px 7px !important;
        }

        .listado-alumnos tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        .listado-alumnos a {
            color: #000 !important;
            text-decoration: none !important;
        }
    }
</style>
<main class="asistencia-page">
    <div class="container py-5">
        <div class="no-imprimir d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4">
            <button class="btn btn-outline-secondary" type="button" onclick="window.close()">Cerrar ventana</button>
            <button class="btn btn-primary" type="button" onclick="window.print()">Imprimir</button>
        </div>

        <section class="card shadow-sm border-0 mb-4 informacion-materia">
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
                        <table class="table table-hover align-middle mb-0 listado-alumnos">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Apellido y nombre</th>
                                    <th>DNI</th>
                                    <th>Correo electrónico</th>
                                    <th>Presencias / clases</th>
                                    <th>Asistencia</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($alumnos as $indice => $alumno)
                                    @php($estadisticas = $estadisticasAlumnos->get($alumno->id))
                                    <tr>
                                        <td>{{ $indice + 1 }}</td>
                                        <td>
                                            <a class="text-decoration-none" href="{{ route('asistencia.admin.materia.alumno', [$materia, $alumno]) }}">
                                                {{ $alumno->apellido }}, {{ $alumno->nombre }}
                                            </a>
                                        </td>
                                        <td>{{ $alumno->dni }}</td>
                                        <td>{{ $alumno->email }}</td>
                                        <td>
                                            {{ number_format($estadisticas['presencias'], $estadisticas['presencias'] == floor($estadisticas['presencias']) ? 0 : 1, ',', '.') }}
                                            / {{ $estadisticas['clases_totales'] }}
                                        </td>
                                        <td>{{ number_format($estadisticas['porcentaje'], 1, ',', '.') }}%</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">Sin alumnos asignados.</td></tr>
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
