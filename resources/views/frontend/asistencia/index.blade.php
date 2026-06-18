@extends('frontend.layout.main')

@section('content')
@php
    $rolActivo = request()->query('rol', 'alumno');
    $adminTabActivo = request()->query('admin_tab', 'usuarios');
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

    .admin-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 22px;
    }

    .admin-nav-button {
        background: #f8fafc;
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        color: #243b53;
        font-weight: 900;
        padding: 10px 14px;
        text-transform: uppercase;
    }

    .admin-nav-button.active {
        background: #700101;
        border-color: #700101;
        color: #ffffff;
    }

    .admin-tab-panel {
        display: none;
    }

    .admin-tab-panel.active {
        display: block;
    }

    .filter-grid {
        display: grid;
        grid-template-columns: 1.4fr 1fr 0.7fr auto;
        gap: 12px;
        margin-bottom: 16px;
    }

    .editable-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 18px;
    }

    .edit-stack {
        display: grid;
        gap: 14px;
        max-height: 620px;
        overflow-y: auto;
        padding-right: 4px;
    }

    .edit-item {
        border: 1px solid #e4e7eb;
        border-radius: 8px;
        padding: 16px;
    }

    .edit-form-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
    }

    .edit-form-grid .wide {
        grid-column: 1 / -1;
    }

    .autocomplete-field {
        position: relative;
    }

    .autocomplete-results {
        background: #ffffff;
        border: 1px solid #d9e2ec;
        border-radius: 8px;
        box-shadow: 0 14px 30px rgba(31, 41, 51, 0.14);
        display: none;
        left: 0;
        max-height: 230px;
        overflow-y: auto;
        position: absolute;
        right: 0;
        top: calc(100% + 6px);
        z-index: 20;
    }

    .autocomplete-results.active {
        display: block;
    }

    .autocomplete-option {
        background: #ffffff;
        border: 0;
        border-bottom: 1px solid #edf2f7;
        color: #243b53;
        display: block;
        font-weight: 700;
        padding: 10px 12px;
        text-align: left;
        width: 100%;
    }

    .autocomplete-option:hover,
    .autocomplete-option:focus {
        background: #fce8e6;
        color: #700101;
        outline: none;
    }

    .autocomplete-empty {
        color: #627d98;
        font-weight: 700;
        padding: 10px 12px;
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

        .filter-grid,
        .editable-grid {
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

        .edit-form-grid {
            grid-template-columns: 1fr;
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

                    <div class="admin-nav" role="tablist" aria-label="Secciones de administracion">
                        <button class="admin-nav-button {{ $adminTabActivo === 'usuarios' ? 'active' : '' }}" type="button" data-admin-tab="usuarios">Usuarios</button>
                        <button class="admin-nav-button {{ $adminTabActivo === 'carreras' ? 'active' : '' }}" type="button" data-admin-tab="carreras">Carreras y materias</button>
                    </div>

                    <div id="admin-tab-usuarios" class="admin-tab-panel {{ $adminTabActivo === 'usuarios' ? 'active' : '' }}">
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
                                    <div class="autocomplete-field">
                                        <input id="materia_profesor" class="form-control js-buscador" type="text" placeholder="Buscar por materia, carrera o anio" autocomplete="off" data-hidden-target="materia_profesor_id" data-source="materias" required>
                                        <input id="materia_profesor_id" type="hidden" name="materia_id">
                                        <div class="autocomplete-results" data-results-for="materia_profesor"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="profesor_materia" class="form-label">Profesor</label>
                                    <div class="autocomplete-field">
                                        <input id="profesor_materia" class="form-control js-buscador" type="text" placeholder="Buscar profesor por nombre o apellido" autocomplete="off" data-hidden-target="profesor_materia_id" data-source="profesores">
                                        <input id="profesor_materia_id" type="hidden" name="profesor_id">
                                        <div class="autocomplete-results" data-results-for="profesor_materia"></div>
                                    </div>
                                    <div class="form-text">Dejalo vacio para quitar el profesor de la materia.</div>
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
                                    <div class="autocomplete-field">
                                        <input id="materia_alumno" class="form-control js-buscador" type="text" placeholder="Buscar por materia, carrera o anio" autocomplete="off" data-hidden-target="materia_alumno_id" data-source="materias" required>
                                        <input id="materia_alumno_id" type="hidden" name="materia_id">
                                        <div class="autocomplete-results" data-results-for="materia_alumno"></div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="registro_alumno" class="form-label">Alumno</label>
                                    <div class="autocomplete-field">
                                        <input id="registro_alumno" class="form-control js-buscador" type="text" placeholder="Buscar alumno por nombre, apellido o DNI" autocomplete="off" data-hidden-target="registro_alumno_id" data-source="alumnos" required>
                                        <input id="registro_alumno_id" type="hidden" name="registro_id">
                                        <div class="autocomplete-results" data-results-for="registro_alumno"></div>
                                    </div>
                                </div>
                                <button class="asistencia-btn" type="submit" {{ !($tieneTablaAsignaciones ?? false) ? 'disabled' : '' }}>Asignar alumno</button>
                            </form>
                        </div>
                    </div>

                    <div class="tool-panel mt-4">
                        <h3>Materias configuradas</h3>
                        <div class="filter-grid">
                            <div>
                                <label for="filtro_materia_configurada" class="form-label">Buscar materia</label>
                                <input id="filtro_materia_configurada" class="form-control" type="text" placeholder="Buscar por materia, profesor, alumno o carrera">
                            </div>
                            <div>
                                <label for="filtro_carrera_configurada" class="form-label">Carrera</label>
                                <select id="filtro_carrera_configurada" class="form-select">
                                    <option value="">Todas</option>
                                    @foreach (($carreras ?? collect()) as $carrera)
                                        <option value="{{ $carrera->id }}">{{ $carrera->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label for="filtro_anio_configurado" class="form-label">Año</label>
                                <select id="filtro_anio_configurado" class="form-select">
                                    <option value="">Todos</option>
                                    @foreach (($anios ?? collect()) as $anio)
                                        <option value="{{ $anio->id }}">{{ $anio->descripcion ?? $anio->anio }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="d-flex align-items-end">
                                <button id="limpiar_filtros_configurados" class="asistencia-btn secondary" type="button">Limpiar</button>
                            </div>
                        </div>
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
                                        @php
                                            $alumnosTextoFiltro = '';
                                            if ($tieneTablaAsignaciones ?? false) {
                                                foreach ($materia->alumnos as $alumnoFiltro) {
                                                    $alumnosTextoFiltro .= ' ' . $alumnoFiltro->apellido . ' ' . $alumnoFiltro->nombre . ' ' . $alumnoFiltro->dni;
                                                }
                                            }
                                            $textoFiltroMateria = $materia->descripcion . ' ' .
                                                ($materia->deCarrera->descripcion ?? '') . ' ' .
                                                ($materia->deAnio->descripcion ?? '') . ' ' .
                                                ($materia->deAnio->anio ?? '') . ' ' .
                                                ($materia->deProfesor ? $materia->deProfesor->apellido . ' ' . $materia->deProfesor->nombre : '') . ' ' .
                                                $alumnosTextoFiltro;
                                        @endphp
                                        <tr class="js-materia-configurada d-none"
                                            data-search="{{ $textoFiltroMateria }}"
                                            data-carrera-id="{{ $materia->carrera_id }}"
                                            data-anio-id="{{ $materia->anio_id }}">
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
                                    <tr id="materias_configuradas_vacio">
                                        <td colspan="4" class="text-center text-muted">Usa el buscador o elegi carrera y año para ver materias.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    </div>

                    <div id="admin-tab-carreras" class="admin-tab-panel {{ $adminTabActivo === 'carreras' ? 'active' : '' }}">
                        <div class="editable-grid">
                            <div class="tool-panel">
                                <h3>Editar carreras</h3>
                                <div class="edit-stack">
                                    @forelse (($carreras ?? collect()) as $carrera)
                                        <form class="edit-item" action="{{ route('asistencia.admin.carreras.actualizar', $carrera) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="edit-form-grid">
                                                <div class="wide">
                                                    <label class="form-label" for="carrera_descripcion_{{ $carrera->id }}">Nombre</label>
                                                    <input id="carrera_descripcion_{{ $carrera->id }}" class="form-control" name="descripcion" value="{{ $carrera->descripcion }}" required>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="carrera_anios_{{ $carrera->id }}">Años</label>
                                                    <input id="carrera_anios_{{ $carrera->id }}" class="form-control" name="anios" type="number" min="1" value="{{ $carrera->anios }}">
                                                </div>
                                                <div>
                                                    <label class="form-label" for="carrera_resolucion_{{ $carrera->id }}">Resolución</label>
                                                    <input id="carrera_resolucion_{{ $carrera->id }}" class="form-control" name="resolucion" value="{{ $carrera->resolucion }}">
                                                </div>
                                                <div class="wide">
                                                    <label class="form-label" for="carrera_carpeta_{{ $carrera->id }}">Carpeta</label>
                                                    <input id="carrera_carpeta_{{ $carrera->id }}" class="form-control" name="nombre_carpeta" value="{{ $carrera->nombre_carpeta }}">
                                                </div>
                                                <div class="wide">
                                                    <label class="form-label" for="carrera_texto_{{ $carrera->id }}">Descripción</label>
                                                    <textarea id="carrera_texto_{{ $carrera->id }}" class="form-control" name="texto" rows="3">{{ $carrera->texto }}</textarea>
                                                </div>
                                            </div>
                                            <button class="asistencia-btn mt-3" type="submit">Guardar carrera</button>
                                        </form>
                                    @empty
                                        <p class="text-muted mb-0">Todavía no hay carreras cargadas.</p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="tool-panel">
                                <h3>Editar materias</h3>
                                <div class="filter-grid" style="grid-template-columns: 1fr;">
                                    <div>
                                        <label for="filtro_editar_materia" class="form-label">Buscar materia</label>
                                        <input id="filtro_editar_materia" class="form-control" type="text" placeholder="Buscar por materia, carrera, año o profesor">
                                    </div>
                                </div>
                                <div class="edit-stack">
                                    @forelse (($materias ?? collect()) as $materia)
                                        @php
                                            $textoEditarMateria = $materia->descripcion . ' ' .
                                                ($materia->deCarrera->descripcion ?? '') . ' ' .
                                                ($materia->deAnio->descripcion ?? '') . ' ' .
                                                ($materia->deAnio->anio ?? '') . ' ' .
                                                ($materia->deProfesor ? $materia->deProfesor->apellido . ' ' . $materia->deProfesor->nombre : '');
                                        @endphp
                                        <form class="edit-item js-editar-materia d-none" data-search="{{ $textoEditarMateria }}" action="{{ route('asistencia.admin.materias.actualizar', $materia) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="edit-form-grid">
                                                <div class="wide">
                                                    <label class="form-label" for="materia_descripcion_{{ $materia->id }}">Materia</label>
                                                    <input id="materia_descripcion_{{ $materia->id }}" class="form-control" name="descripcion" value="{{ $materia->descripcion }}" required>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="materia_carrera_{{ $materia->id }}">Carrera</label>
                                                    <select id="materia_carrera_{{ $materia->id }}" class="form-select" name="carrera_id">
                                                        <option value="">Sin carrera</option>
                                                        @foreach (($carreras ?? collect()) as $carrera)
                                                            <option value="{{ $carrera->id }}" {{ $materia->carrera_id == $carrera->id ? 'selected' : '' }}>{{ $carrera->descripcion }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="materia_anio_{{ $materia->id }}">Año</label>
                                                    <select id="materia_anio_{{ $materia->id }}" class="form-select" name="anio_id">
                                                        <option value="">Sin año</option>
                                                        @foreach (($anios ?? collect()) as $anio)
                                                            <option value="{{ $anio->id }}" {{ $materia->anio_id == $anio->id ? 'selected' : '' }}>{{ $anio->descripcion ?? $anio->anio }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="materia_profesor_edit_{{ $materia->id }}">Profesor</label>
                                                    <select id="materia_profesor_edit_{{ $materia->id }}" class="form-select" name="profesor_id">
                                                        <option value="">Sin profesor</option>
                                                        @foreach (($profesores ?? collect()) as $profesor)
                                                            <option value="{{ $profesor->id }}" {{ $materia->profesor_id == $profesor->id ? 'selected' : '' }}>{{ $profesor->apellido }}, {{ $profesor->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="materia_orden_{{ $materia->id }}">Orden</label>
                                                    <input id="materia_orden_{{ $materia->id }}" class="form-control" name="orden" type="number" min="0" value="{{ $materia->orden }}">
                                                </div>
                                            </div>
                                            <button class="asistencia-btn mt-3" type="submit">Guardar materia</button>
                                        </form>
                                    @empty
                                        <p class="text-muted mb-0">Todavía no hay materias cargadas.</p>
                                    @endforelse
                                    <p id="editar_materias_vacio" class="text-muted mb-0">Busca una materia para editarla.</p>
                                </div>
                            </div>
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
        const searchInputs = document.querySelectorAll('.js-buscador');
        const adminTabButtons = document.querySelectorAll('.admin-nav-button');
        const adminTabPanels = document.querySelectorAll('.admin-tab-panel');
        const configuredSearch = document.getElementById('filtro_materia_configurada');
        const configuredCareer = document.getElementById('filtro_carrera_configurada');
        const configuredYear = document.getElementById('filtro_anio_configurado');
        const configuredClear = document.getElementById('limpiar_filtros_configurados');
        const configuredRows = document.querySelectorAll('.js-materia-configurada');
        const configuredEmpty = document.getElementById('materias_configuradas_vacio');
        const editMatterSearch = document.getElementById('filtro_editar_materia');
        const editMatterForms = document.querySelectorAll('.js-editar-materia');
        const editMatterEmpty = document.getElementById('editar_materias_vacio');
        const autocompleteSources = {
            materias: [
                @foreach (($materias ?? collect()) as $materia)
                    {
                        id: '{{ $materia->id }}',
                        label: @json($materia->descripcion . ($materia->deCarrera ? ' - ' . $materia->deCarrera->descripcion : '') . ($materia->deAnio ? ' - ' . ($materia->deAnio->anio ?? $materia->deAnio->descripcion) : ''))
                    },
                @endforeach
            ],
            profesores: [
                @foreach (($profesores ?? collect()) as $profesor)
                    {
                        id: '{{ $profesor->id }}',
                        label: @json($profesor->apellido . ', ' . $profesor->nombre)
                    },
                @endforeach
            ],
            alumnos: [
                @foreach (($alumnos ?? collect()) as $alumno)
                    {
                        id: '{{ $alumno->id }}',
                        label: @json($alumno->apellido . ', ' . $alumno->nombre . ' - DNI ' . $alumno->dni)
                    },
                @endforeach
            ]
        };

        function normalizeText(value) {
            return value
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        }

        function closeAllResults(exceptInputId) {
            document.querySelectorAll('.autocomplete-results').forEach(function (resultsBox) {
                if (resultsBox.dataset.resultsFor !== exceptInputId) {
                    resultsBox.classList.remove('active');
                    resultsBox.innerHTML = '';
                }
            });
        }

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

        adminTabButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                const tab = button.dataset.adminTab;

                adminTabButtons.forEach(function (item) {
                    item.classList.remove('active');
                });

                adminTabPanels.forEach(function (panel) {
                    panel.classList.remove('active');
                });

                button.classList.add('active');
                document.getElementById('admin-tab-' + tab).classList.add('active');
            });
        });

        function filterConfiguredSubjects() {
            if (!configuredRows.length) {
                return;
            }

            const query = normalizeText(configuredSearch ? configuredSearch.value : '');
            const careerId = configuredCareer ? configuredCareer.value : '';
            const yearId = configuredYear ? configuredYear.value : '';
            const hasFilter = query || careerId || yearId;
            let visibleRows = 0;

            configuredRows.forEach(function (row) {
                const matchesText = !query || normalizeText(row.dataset.search || '').includes(query);
                const matchesCareer = !careerId || row.dataset.carreraId === careerId;
                const matchesYear = !yearId || row.dataset.anioId === yearId;
                const visible = hasFilter && matchesText && matchesCareer && matchesYear;

                row.classList.toggle('d-none', !visible);
                if (visible) {
                    visibleRows++;
                }
            });

            if (configuredEmpty) {
                configuredEmpty.classList.toggle('d-none', visibleRows > 0);
                configuredEmpty.querySelector('td').textContent = hasFilter
                    ? 'No se encontraron materias con esos filtros.'
                    : 'Usa el buscador o elegi carrera y año para ver materias.';
            }
        }

        if (configuredSearch) {
            configuredSearch.addEventListener('input', filterConfiguredSubjects);
        }
        if (configuredCareer) {
            configuredCareer.addEventListener('change', filterConfiguredSubjects);
        }
        if (configuredYear) {
            configuredYear.addEventListener('change', filterConfiguredSubjects);
        }
        if (configuredClear) {
            configuredClear.addEventListener('click', function () {
                configuredSearch.value = '';
                configuredCareer.value = '';
                configuredYear.value = '';
                filterConfiguredSubjects();
            });
        }
        filterConfiguredSubjects();

        function filterEditableSubjects() {
            if (!editMatterForms.length) {
                return;
            }

            const query = normalizeText(editMatterSearch ? editMatterSearch.value : '');
            let visibleForms = 0;

            editMatterForms.forEach(function (form) {
                const visible = query && normalizeText(form.dataset.search || '').includes(query);
                form.classList.toggle('d-none', !visible);
                if (visible) {
                    visibleForms++;
                }
            });

            if (editMatterEmpty) {
                editMatterEmpty.classList.toggle('d-none', visibleForms > 0);
                editMatterEmpty.textContent = query
                    ? 'No se encontraron materias para editar.'
                    : 'Busca una materia para editarla.';
            }
        }

        if (editMatterSearch) {
            editMatterSearch.addEventListener('input', filterEditableSubjects);
        }
        filterEditableSubjects();

        searchInputs.forEach(function (input) {
            const hiddenInput = document.getElementById(input.dataset.hiddenTarget);
            const resultsBox = document.querySelector('[data-results-for="' + input.id + '"]');
            const source = autocompleteSources[input.dataset.source] || [];

            function syncHiddenValue() {
                const normalizedInput = normalizeText(input.value);
                const selectedItem = source.find(function (item) {
                    return normalizeText(item.label) === normalizedInput;
                });

                hiddenInput.value = selectedItem ? selectedItem.id : '';
            }

            function selectItem(item) {
                input.value = item.label;
                hiddenInput.value = item.id;
                input.setCustomValidity('');
                resultsBox.classList.remove('active');
                resultsBox.innerHTML = '';
            }

            function renderResults() {
                const query = normalizeText(input.value);
                hiddenInput.value = '';
                closeAllResults(input.id);

                if (!query) {
                    resultsBox.classList.remove('active');
                    resultsBox.innerHTML = '';
                    return;
                }

                const matches = source.filter(function (item) {
                    return normalizeText(item.label).includes(query);
                }).slice(0, 8);

                if (!matches.length) {
                    resultsBox.innerHTML = '<div class="autocomplete-empty">Sin resultados</div>';
                    resultsBox.classList.add('active');
                    return;
                }

                resultsBox.innerHTML = '';
                matches.forEach(function (item) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'autocomplete-option';
                    button.textContent = item.label;
                    button.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                        selectItem(item);
                    });
                    resultsBox.appendChild(button);
                });
                resultsBox.classList.add('active');
                syncHiddenValue();
            }

            input.addEventListener('input', renderResults);
            input.addEventListener('focus', renderResults);
            input.addEventListener('change', syncHiddenValue);
            input.addEventListener('blur', function () {
                setTimeout(function () {
                    resultsBox.classList.remove('active');
                }, 120);
            });
        });

        document.querySelectorAll('#panel-admin form').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                const formSearches = form.querySelectorAll('.js-buscador');
                let valid = true;

                formSearches.forEach(function (input) {
                    const hiddenInput = document.getElementById(input.dataset.hiddenTarget);
                    const needsSelection = input.hasAttribute('required') || input.value.trim() !== '';
                    const source = autocompleteSources[input.dataset.source] || [];
                    const exactMatch = source.find(function (item) {
                        return normalizeText(item.label) === normalizeText(input.value);
                    });

                    if (exactMatch) {
                        hiddenInput.value = exactMatch.id;
                    }

                    if (needsSelection && !hiddenInput.value) {
                        input.setCustomValidity('Selecciona una opcion de la lista.');
                        input.reportValidity();
                        valid = false;
                    } else {
                        input.setCustomValidity('');
                    }
                });

                if (!valid) {
                    event.preventDefault();
                }
            });
        });
    });
</script>
@endsection
