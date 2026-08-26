@extends('frontend.layout.main')

@section('content')
<main class="asistencia-page">
    <div class="container py-5">
        <a class="btn btn-outline-secondary mb-4" href="{{ url()->previous() }}">&larr; Volver</a>
        <section class="card shadow-sm border-0">
            <div class="card-body p-4">
                <h1 class="h3">{{ $registro->apellido }}, {{ $registro->nombre }}</h1>
                <p class="text-muted mb-4">DNI {{ $registro->dni }} · {{ $registro->email }}</p>
                <h2 class="h5">Materias asignadas</h2>
                <div class="list-group">
                    @forelse ($materiasAlumno as $materia)
                        <div class="list-group-item">
                            <strong>{{ $materia->descripcion }}</strong>
                            <span class="text-muted ms-2">{{ $materia->deCarrera->descripcion ?? 'Sin carrera' }} · {{ $materia->deAnio->anio ?? $materia->deAnio->descripcion ?? 'Sin año' }}</span>
                        </div>
                    @empty
                        <p class="text-muted mb-0">El alumno no tiene materias asignadas.</p>
                    @endforelse
                </div>
            </div>
        </section>
    </div>
</main>
@endsection
