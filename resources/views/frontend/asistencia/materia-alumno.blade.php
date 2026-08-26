@extends('frontend.layout.main')

@section('content')
<main class="asistencia-page">
    <div class="container py-5">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
            <a class="btn btn-outline-secondary" href="{{ route('asistencia.index') }}">&larr; Volver a mis materias</a>
            <form action="{{ route('asistencia.logout') }}" method="POST" class="m-0">
                @csrf
                <button class="btn btn-outline-secondary" type="submit">Cerrar sesión</button>
            </form>
        </div>

        <section class="card shadow-sm border-0">
            <div class="card-body p-4">
                <span class="text-uppercase text-muted fw-bold small">{{ $materia->deCarrera->descripcion ?? 'Sin carrera' }}</span>
                <h1 class="h2 mt-2">{{ $materia->descripcion }}</h1>
                <dl class="row mb-0 mt-4">
                    <dt class="col-sm-3">Año</dt>
                    <dd class="col-sm-9">{{ $materia->deAnio->anio ?? $materia->deAnio->descripcion ?? 'Sin año' }}</dd>
                    <dt class="col-sm-3">Profesor</dt>
                    <dd class="col-sm-9">{{ $materia->horario && $materia->horario->profesor ? $materia->horario->profesor->apellido . ', ' . $materia->horario->profesor->nombre : 'No informado' }}</dd>
                    <dt class="col-sm-3">Estado</dt>
                    <dd class="col-sm-9"><span class="badge text-bg-success">Asignado a la materia</span></dd>
                </dl>
            </div>
        </section>
    </div>
</main>
@endsection
