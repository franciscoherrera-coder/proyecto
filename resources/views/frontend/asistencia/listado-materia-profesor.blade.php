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
        .asistencia-page, .asistencia-page .container, .card, .card-body {
            background: #fff !important;
            border: 0 !important;
            box-shadow: none !important;
            margin: 0 !important;
            max-width: none !important;
            overflow: visible !important;
            padding: 0 !important;
        }
        .listado-alumnos {
            font-size: 11pt;
            margin-top: 8mm !important;
            width: 100% !important;
        }
        .listado-alumnos th, .listado-alumnos td {
            padding: 5px 7px !important;
        }
        .listado-alumnos tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }
    }
</style>
<main class="asistencia-page">
    <div class="container py-5">
        <div class="no-imprimir d-flex gap-2 mb-4">
            <a class="btn btn-outline-secondary" href="{{ route('asistencia.profesor.materia', $materia) }}">Volver</a>
            <button class="btn btn-primary" type="button" onclick="window.print()">Imprimir listado</button>
        </div>
        <section class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h3">Listado de alumnos</h1>
                <p class="mb-1"><strong>Materia:</strong> {{ $materia->descripcion }}</p>
                <p class="mb-1"><strong>Carrera:</strong> {{ $materia->deCarrera->descripcion ?? 'Sin carrera' }}</p>
                <p class="mb-4"><strong>Profesor:</strong> {{ $materia->horario && $materia->horario->profesor ? $materia->horario->profesor->apellido . ', ' . $materia->horario->profesor->nombre : 'No informado' }}</p>
                <table class="table table-bordered listado-alumnos">
                    <thead><tr><th>#</th><th>Apellido y nombre</th><th>DNI</th><th>Correo electrónico</th></tr></thead>
                    <tbody>
                        @forelse ($alumnosAsignados as $indice => $alumno)
                            <tr><td>{{ $indice + 1 }}</td><td>{{ $alumno->apellido }}, {{ $alumno->nombre }}</td><td>{{ $alumno->dni }}</td><td>{{ $alumno->email }}</td></tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted">No hay alumnos asignados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</main>
@endsection
