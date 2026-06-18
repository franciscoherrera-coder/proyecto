@extends('frontend.layout.main')

@section('content')
@php
    $rolActivo = request()->query('rol', 'alumno');
    $cantidadAsignaciones = 0;
    if ($tieneTablaAsignaciones ?? false) {
        foreach (($materias ?? collect()) as $materia) {
            $cantidadAsignaciones += $materia->alumnos->count();
        }
    }
@endphp
<style>
    .asistencia-page {
        background: #f4f6f8;
        color: #1f2933;
        min-height: 72vh;
        padding: 48px 0;
    }

    .asistencia-hero {
        background: #ffffff;
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        padding: 28px;
        box-shadow: 0 10px 30px rgba(31, 41, 51, 0.08);
    }

    .asistencia-kicker {
        color: #700101;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .asistencia-title {
        color: #111827;
        font-size: clamp(2rem, 5vw, 3.3rem);
        font-weight: 800;
        line-height: 1.05;
        margin: 8px 0 12px;
    }

    .asistencia-copy {
        color: #52606d;
        font-size: 1.05rem;
        max-width: 760px;
    }

    .role-selector {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 14px;
        margin-top: 26px;
    }

    .role-button {
        background: #ffffff;
        border: 2px solid #d9e2ec;
        border-radius: 8px;
        color: #243b53;
        cursor: pointer;
        display: flex;
        gap: 12px;
        align-items: center;
        min-height: 86px;
        padding: 16px;
        text-align: left;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, transform 0.2s ease;
        width: 100%;
    }

    .role-button:hover,
    .role-button.active {
        border-color: #700101;
        box-shadow: 0 10px 24px rgba(112, 1, 1, 0.13);
        transform: translateY(-2px);
    }

    .role-icon {
        align-items: center;
        background: #fce8e6;
        border-radius: 8px;
        color: #700101;
        display: flex;
        flex: 0 0 46px;
        height: 46px;
        justify-content: center;
    }

    .role-button strong {
        display: block;
        font-size: 1.05rem;
        line-height: 1.1;
    }

    .role-button span:last-child {
        color: #627d98;
        display: block;
        font-size: 0.86rem;
        font-weight: 600;
        margin-top: 4px;
    }

    .dashboard-panel {
        display: none;
        margin-top: 24px;
    }

    .dashboard-panel.active {
        display: block;
    }

    .dashboard-shell {
        background: #ffffff;
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        box-shadow: 0 10px 30px rgba(31, 41, 51, 0.08);
        overflow: hidden;
    }

    .dashboard-header {
        align-items: center;
        background: #111827;
        color: #ffffff;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        justify-content: space-between;
        padding: 20px 24px;
    }

    .dashboard-header h2 {
        font-size: 1.35rem;
        font-weight: 800;
        margin: 0;
    }

    .dashboard-header p {
        color: #cbd2d9;
        margin: 4px 0 0;
    }

    .status-pill {
        background: #e3f9e5;
        border-radius: 999px;
        color: #0b6b35;
        font-size: 0.82rem;
        font-weight: 800;
        padding: 8px 13px;
        white-space: nowrap;
    }

    .dashboard-body {
        padding: 24px;
    }

    .metric-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 22px;
    }

    .metric {
        background: #f8fafc;
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        padding: 16px;
    }

    .metric small {
        color: #627d98;
        display: block;
        font-weight: 800;
        margin-bottom: 8px;
        text-transform: uppercase;
    }

    .metric strong {
        color: #111827;
        display: block;
        font-size: 1.7rem;
        line-height: 1;
    }

    .work-grid {
        display: grid;
        grid-template-columns: 1.15fr 0.85fr;
        gap: 18px;
    }

    .tool-panel {
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        padding: 18px;
    }

    .tool-panel h3 {
        color: #111827;
        font-size: 1.08rem;
        font-weight: 800;
        margin-bottom: 14px;
    }

    .class-row,
    .student-row,
    .report-row {
        align-items: center;
        border-bottom: 1px solid #edf2f7;
        display: flex;
        gap: 12px;
        justify-content: space-between;
        padding: 12px 0;
    }

    .class-row:last-child,
    .student-row:last-child,
    .report-row:last-child {
        border-bottom: 0;
    }

    .row-title {
        color: #243b53;
        font-weight: 800;
    }

    .row-subtitle {
        color: #627d98;
        font-size: 0.88rem;
        font-weight: 600;
    }

    .asistencia-btn {
        background: #700101;
        border: 0;
        border-radius: 8px;
        color: #ffffff;
        font-weight: 800;
        padding: 9px 14px;
        white-space: nowrap;
    }

    .asistencia-btn.secondary {
        background: #e4e7eb;
        color: #243b53;
    }

    .qr-box {
        align-items: center;
        background:
            linear-gradient(90deg, #111827 10px, transparent 10px) 0 0 / 28px 28px,
            linear-gradient(#111827 10px, transparent 10px) 0 0 / 28px 28px,
            #ffffff;
        border: 10px solid #f8fafc;
        box-shadow: inset 0 0 0 1px #cbd2d9;
        display: flex;
        height: 180px;
        justify-content: center;
        margin: 0 auto 16px;
        max-width: 180px;
    }

    .qr-box span {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        color: #700101;
        font-weight: 900;
        padding: 8px 10px;
    }

    .progress {
        background-color: #e4e7eb;
        height: 10px;
    }

    .progress-bar {
        background-color: #700101;
    }

    .badge-soft {
        background: #fce8e6;
        border-radius: 999px;
        color: #700101;
        font-size: 0.78rem;
        font-weight: 800;
        padding: 6px 10px;
        white-space: nowrap;
    }

    .table-responsive {
        border: 1px solid #e4e7eb;
        border-radius: 8px;
    }

    .table {
        margin-bottom: 0;
    }

    .table th {
        color: #52606d;
        font-size: 0.78rem;
        text-transform: uppercase;
    }

    @media (max-width: 992px) {
        .metric-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .work-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 768px) {
        .asistencia-page {
            padding: 24px 0;
        }

        .asistencia-hero,
        .dashboard-body {
            padding: 18px;
        }

        .role-selector,
        .metric-grid {
            grid-template-columns: 1fr;
        }

        .class-row,
        .student-row,
        .report-row {
            align-items: flex-start;
            flex-direction: column;
        }

        .asistencia-btn {
            width: 100%;
        }
    }
</style>

<main class="asistencia-page">
    <div class="container">
        <section class="asistencia-hero">
            <div class="asistencia-kicker">Prototipo sin login</div>
            <h1 class="asistencia-title">Sistema de asistencia</h1>
            <p class="asistencia-copy">
                Elegi como queres ingresar para visualizar la pantalla que podria ver cada usuario. Mas adelante estos accesos se pueden conectar al login real.
            </p>

            <div class="role-selector" role="tablist" aria-label="Seleccionar tipo de usuario">
                <button class="role-button {{ $rolActivo === 'alumno' ? 'active' : '' }}" type="button" data-role="alumno" role="tab" aria-selected="{{ $rolActivo === 'alumno' ? 'true' : 'false' }}">
                    <span class="role-icon" aria-hidden="true">
                        <i class="fa-solid fa-user-graduate"></i>
                    </span>
                    <span>
                        <strong>Alumno</strong>
                        <span>Ver mi asistencia y proximas clases</span>
                    </span>
                </button>

                <button class="role-button {{ $rolActivo === 'profesor' ? 'active' : '' }}" type="button" data-role="profesor" role="tab" aria-selected="{{ $rolActivo === 'profesor' ? 'true' : 'false' }}">
                    <span class="role-icon" aria-hidden="true">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </span>
                    <span>
                        <strong>Profesor</strong>
                        <span>Tomar asistencia del curso</span>
                    </span>
                </button>

                <button class="role-button {{ $rolActivo === 'admin' ? 'active' : '' }}" type="button" data-role="admin" role="tab" aria-selected="{{ $rolActivo === 'admin' ? 'true' : 'false' }}">
                    <span class="role-icon" aria-hidden="true">
                        <i class="fa-solid fa-user-gear"></i>
                    </span>
                    <span>
                        <strong>Admin</strong>
                        <span>Control general y reportes</span>
                    </span>
                </button>
            </div>
        </section>

        <section id="panel-alumno" class="dashboard-panel {{ $rolActivo === 'alumno' ? 'active' : '' }}" role="tabpanel">
            <div class="dashboard-shell">
                <div class="dashboard-header">
                    <div>
                        <h2>Vista Alumno</h2>
                        <p>Hola, Martina Perez. Estos son tus datos de asistencia de ejemplo.</p>
                    </div>
                    <span class="status-pill">Regular</span>
                </div>

                <div class="dashboard-body">
                    <div class="metric-grid">
                        <div class="metric">
                            <small>Asistencia total</small>
                            <strong>86%</strong>
                        </div>
                        <div class="metric">
                            <small>Presentes</small>
                            <strong>24</strong>
                        </div>
                        <div class="metric">
                            <small>Ausencias</small>
                            <strong>4</strong>
                        </div>
                        <div class="metric">
                            <small>Proxima clase</small>
                            <strong>18:00</strong>
                        </div>
                    </div>

                    <div class="work-grid">
                        <div class="tool-panel">
                            <h3>Mis materias</h3>
                            <div class="class-row">
                                <div>
                                    <div class="row-title">Programacion II</div>
                                    <div class="row-subtitle">Lunes y miercoles - Aula 4</div>
                                </div>
                                <span class="badge-soft">92%</span>
                            </div>
                            <div class="class-row">
                                <div>
                                    <div class="row-title">Base de Datos</div>
                                    <div class="row-subtitle">Martes - Laboratorio</div>
                                </div>
                                <span class="badge-soft">84%</span>
                            </div>
                            <div class="class-row">
                                <div>
                                    <div class="row-title">Ingles Tecnico</div>
                                    <div class="row-subtitle">Jueves - Aula 2</div>
                                </div>
                                <span class="badge-soft">78%</span>
                            </div>
                        </div>

                        <div class="tool-panel">
                            <h3>Credencial QR</h3>
                            <div class="qr-box"><span>ALU-238</span></div>
                            <p class="row-subtitle mb-3">Codigo de muestra para registrar ingreso en clase.</p>
                            <button class="asistencia-btn w-100" type="button">Mostrar QR</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="panel-profesor" class="dashboard-panel {{ $rolActivo === 'profesor' ? 'active' : '' }}" role="tabpanel">
            <div class="dashboard-shell">
                <div class="dashboard-header">
                    <div>
                        <h2>Vista Profesor</h2>
                        <p>Clase activa: Programacion II - 2do A - Aula 4.</p>
                    </div>
                    <span class="status-pill">Clase en curso</span>
                </div>

                <div class="dashboard-body">
                    <div class="metric-grid">
                        <div class="metric">
                            <small>Alumnos</small>
                            <strong>31</strong>
                        </div>
                        <div class="metric">
                            <small>Presentes</small>
                            <strong>26</strong>
                        </div>
                        <div class="metric">
                            <small>Ausentes</small>
                            <strong>5</strong>
                        </div>
                        <div class="metric">
                            <small>Porcentaje</small>
                            <strong>84%</strong>
                        </div>
                    </div>

                    <div class="work-grid">
                        <div class="tool-panel">
                            <h3>Lista de clase</h3>
                            <div class="student-row">
                                <div>
                                    <div class="row-title">Martina Perez</div>
                                    <div class="row-subtitle">DNI 41.234.567</div>
                                </div>
                                <button class="asistencia-btn secondary" type="button">Presente</button>
                            </div>
                            <div class="student-row">
                                <div>
                                    <div class="row-title">Lucas Gomez</div>
                                    <div class="row-subtitle">DNI 40.876.210</div>
                                </div>
                                <button class="asistencia-btn" type="button">Marcar</button>
                            </div>
                            <div class="student-row">
                                <div>
                                    <div class="row-title">Camila Torres</div>
                                    <div class="row-subtitle">DNI 43.112.908</div>
                                </div>
                                <button class="asistencia-btn secondary" type="button">Presente</button>
                            </div>
                        </div>

                        <div class="tool-panel">
                            <h3>Toma rapida</h3>
                            <button class="asistencia-btn w-100 mb-2" type="button">Escanear QR</button>
                            <button class="asistencia-btn secondary w-100 mb-3" type="button">Cargar manualmente</button>
                            <div class="progress mb-2">
                                <div class="progress-bar" style="width: 84%"></div>
                            </div>
                            <p class="row-subtitle mb-0">26 de 31 alumnos ya fueron registrados.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="panel-admin" class="dashboard-panel {{ $rolActivo === 'admin' ? 'active' : '' }}" role="tabpanel">
            <div class="dashboard-shell">
                <div class="dashboard-header">
                    <div>
                        <h2>Vista Admin</h2>
                        <p>Gestion de materias, profesores y alumnos para el sistema de asistencia.</p>
                    </div>
                    <span class="status-pill">Administracion real</span>
                </div>

                <div class="dashboard-body">
                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <strong>Revisa los datos:</strong>
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (!($tieneTablaAsignaciones ?? false))
                        <div class="alert alert-warning">
                            Para asignar alumnos a materias falta ejecutar la migracion nueva:
                            <code>php artisan migrate</code>.
                        </div>
                    @endif

                    <div class="metric-grid">
                        <div class="metric">
                            <small>Materias</small>
                            <strong>{{ ($materias ?? collect())->count() }}</strong>
                        </div>
                        <div class="metric">
                            <small>Profesores</small>
                            <strong>{{ ($profesores ?? collect())->count() }}</strong>
                        </div>
                        <div class="metric">
                            <small>Alumnos</small>
                            <strong>{{ ($alumnos ?? collect())->count() }}</strong>
                        </div>
                        <div class="metric">
                            <small>Asignaciones</small>
                            <strong>{{ $cantidadAsignaciones }}</strong>
                        </div>
                    </div>

                    <div class="work-grid">
                        <div class="tool-panel">
                            <h3>Asignar profesor a materia</h3>
                            <form action="{{ route('asistencia.admin.profesor') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="materia_profesor" class="form-label">Materia</label>
                                    <select id="materia_profesor" name="materia_id" class="form-select" required>
                                        <option value="">Seleccionar materia</option>
                                        @foreach (($materias ?? collect()) as $materia)
                                            <option value="{{ $materia->id }}">
                                                {{ $materia->descripcion }}
                                                @if ($materia->deCarrera)
                                                    - {{ $materia->deCarrera->descripcion }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="profesor_materia" class="form-label">Profesor</label>
                                    <select id="profesor_materia" name="profesor_id" class="form-select">
                                        <option value="">Sin profesor asignado</option>
                                        @foreach (($profesores ?? collect()) as $profesor)
                                            <option value="{{ $profesor->id }}">{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="asistencia-btn" type="submit">Guardar profesor</button>
                            </form>
                        </div>

                        <div class="tool-panel">
                            <h3>Asignar alumno a materia</h3>
                            <form action="{{ route('asistencia.admin.alumno') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="materia_alumno" class="form-label">Materia</label>
                                    <select id="materia_alumno" name="materia_id" class="form-select" required>
                                        <option value="">Seleccionar materia</option>
                                        @foreach (($materias ?? collect()) as $materia)
                                            <option value="{{ $materia->id }}">
                                                {{ $materia->descripcion }}
                                                @if ($materia->deCarrera)
                                                    - {{ $materia->deCarrera->descripcion }}
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="registro_alumno" class="form-label">Alumno</label>
                                    <select id="registro_alumno" name="registro_id" class="form-select" required>
                                        <option value="">Seleccionar alumno</option>
                                        @foreach (($alumnos ?? collect()) as $alumno)
                                            <option value="{{ $alumno->id }}">{{ $alumno->apellido }}, {{ $alumno->nombre }} - DNI {{ $alumno->dni }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <button class="asistencia-btn" type="submit" {{ !($tieneTablaAsignaciones ?? false) ? 'disabled' : '' }}>Asignar alumno</button>
                            </form>
                        </div>
                    </div>

                    <div class="tool-panel mt-4">
                        <h3>Materias configuradas</h3>
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th>Materia</th>
                                        <th>Profesor</th>
                                        <th>Alumnos asignados</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse (($materias ?? collect()) as $materia)
                                        <tr>
                                            <td>
                                                <strong>{{ $materia->descripcion }}</strong>
                                                <div class="row-subtitle">
                                                    {{ $materia->deCarrera->descripcion ?? 'Sin carrera' }}
                                                    @if ($materia->deAnio)
                                                        - {{ $materia->deAnio->anio ?? $materia->deAnio->descripcion }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                @if ($materia->deProfesor)
                                                    {{ $materia->deProfesor->apellido }}, {{ $materia->deProfesor->nombre }}
                                                @else
                                                    <span class="text-muted">Sin profesor</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($tieneTablaAsignaciones ?? false)
                                                    @forelse ($materia->alumnos as $alumno)
                                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                                            <span class="badge-soft">{{ $alumno->apellido }}, {{ $alumno->nombre }}</span>
                                                            <form action="{{ route('asistencia.admin.alumno.quitar') }}" method="POST" class="m-0">
                                                                @csrf
                                                                @method('DELETE')
                                                                <input type="hidden" name="materia_id" value="{{ $materia->id }}">
                                                                <input type="hidden" name="registro_id" value="{{ $alumno->id }}">
                                                                <button class="btn btn-sm btn-outline-danger" type="submit">Quitar</button>
                                                            </form>
                                                        </div>
                                                    @empty
                                                        <span class="text-muted">Sin alumnos</span>
                                                    @endforelse
                                                @else
                                                    <span class="text-muted">Pendiente de migracion</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="badge-soft">{{ ($tieneTablaAsignaciones ?? false) ? $materia->alumnos->count() : 0 }} alumno(s)</span>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Todavia no hay materias cargadas.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
</main>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const roleButtons = document.querySelectorAll('.role-button');
        const panels = document.querySelectorAll('.dashboard-panel');

        roleButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const role = button.dataset.role;

                roleButtons.forEach(function (item) {
                    item.classList.remove('active');
                    item.setAttribute('aria-selected', 'false');
                });

                panels.forEach(function (panel) {
                    panel.classList.remove('active');
                });

                button.classList.add('active');
                button.setAttribute('aria-selected', 'true');
                document.getElementById('panel-' + role).classList.add('active');
            });
        });
    });
</script>
@endsection
